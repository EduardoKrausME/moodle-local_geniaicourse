<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Read and verify MCP project snapshots without side effects.
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse\mcp;

use local_geniaicourse\project_manager;
use moodle_exception;
use stdClass;

/**
 * Small metadata snapshots and optimistic concurrency checks.
 */
final class project_state {
    /**
     * Read the project and its sources as a concurrency-safe snapshot.
     *
     * @param stdClass $project Project record.
     * @return array
     */
    public static function inspect(stdClass $project): array {
        $sources = project_manager::get_sources((int)$project->id);
        $items = [];
        $signature = [(int)$project->id, (int)$project->courseid, (string)$project->status,
            hash("sha256", (string)$project->prompt)];
        foreach ($sources as $source) {
            $analyses = json_decode((string)$source->analysisjson, true);
            $summaries = [];
            if (is_array($analyses)) {
                foreach ($analyses as $name => $analysis) {
                    if (!is_array($analysis)) {
                        continue;
                    }
                    $summaries[] = [
                        "plugin" => (string)$name,
                        "matched" => !empty($analysis["match"]),
                        "confidence" => (int)($analysis["confidence"] ?? 0),
                        "error" => !empty($analysis["error"]),
                        "title" => (string)($analysis["title"] ?? ""),
                    ];
                }
            }
            $items[] = [
                "sourceid" => (int)$source->id,
                "filename" => (string)$source->filename,
                "sourcetype" => (string)$source->sourcetype,
                "instruction" => (string)$source->instruction,
                "extracted_chars" => strlen((string)$source->extractedtext),
                "analysed" => $source->analysisjson !== null,
                "generators" => $summaries,
            ];
            $signature[] = [(int)$source->id, (string)$source->filename,
                hash("sha256", (string)$source->extractedtext),
                hash("sha256", (string)$source->analysisjson),
                (string)$source->instruction];
        }
        return [
            "projectid" => (int)$project->id,
            "courseid" => (int)$project->courseid,
            "status" => (string)$project->status,
            "total_sources" => count($items),
            "state_hash" => hash("sha256", json_encode($signature, JSON_THROW_ON_ERROR)),
            "sources" => $items,
        ];
    }

    /**
     * Reject outdated snapshots before a confirmed write.
     *
     * @param array $snapshot Current projection.
     * @param string $expectedhash Caller-provided state hash.
     * @return void
     */
    public static function require_state(array $snapshot, string $expectedhash): void {
        if (!preg_match("/^[a-f0-9]{64}$/", $expectedhash) ||
                !hash_equals((string)$snapshot["state_hash"], $expectedhash)) {
            throw new moodle_exception("mcpstaleproject", "local_geniaicourse");
        }
    }

    /**
     * Ensure the project has not already been published.
     *
     * @param stdClass $project Project.
     * @return void
     */
    public static function require_editable(stdClass $project): void {
        if (in_array((string)$project->status, ["creating", "created"], true)) {
            throw new moodle_exception("projectalreadycreated", "local_geniaicourse");
        }
    }

    /**
     * Common schema for project operations requiring state consistency.
     *
     * @return array
     */
    public static function input(): array {
        return [
            "projectid" => ["type" => "integer", "minimum" => 1],
            "expected_state_hash" => ["type" => "string", "pattern" => "^[a-f0-9]{64}$"],
        ];
    }
}
