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
 * Publish selected GeniAI analyses through confirmed Moodle MCP WRITE.
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse\mcp;

use local_mcp\security\authenticated_identity;

/**
 * Explicit, non-retriable publishing from saved and reviewed suggestions.
 */
final class publish_activities extends write_project_base {
    /**
     * Return the unique MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return "geniaicourse_publish_activities";
    }

    /**
     * Return the translated MCP tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string("mcppublish", "local_geniaicourse");
    }

    /**
     * Return the translated MCP tool description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string("mcppublishdesc", "local_geniaicourse");
    }

    /**
     * Mark this operation as creating persistent Moodle content.
     *
     * @return bool
     */
    public function is_destructive(): bool {
        return true;
    }

    /**
     * Declare the permitted arguments for this tool.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema(project_state::input() + [
            "selections" => ["type" => "array", "minItems" => 1, "maxItems" => 12,
                "items" => [
                    "type" => "object",
                    "properties" => [
                        "sourceid" => ["type" => "integer", "minimum" => 1],
                        "plugin" => ["type" => "string", "pattern" => "^[a-z0-9_]{1,80}$"],
                        "section" => ["type" => "integer", "minimum" => 0],
                    ],
                    "required" => ["sourceid", "plugin", "section"],
                    "additionalProperties" => false,
                ],
            ],
        ], ["projectid", "expected_state_hash", "selections"]);
    }

    /**
     * Confirm exact selected activities without creating anything.
     *
     * @param array $arguments Arguments.
     * @param authenticated_identity $identity User.
     * @return array
     */
    public function preview(array $arguments, authenticated_identity $identity): array {
        $project = access::project((int)$arguments["projectid"], $identity);
        project_state::require_state(project_state::inspect($project),
            (string)$arguments["expected_state_hash"]);
        $grouped = publish_service::validate($project, (array)$arguments["selections"], $identity);
        $selection = [];
        foreach ($grouped as $entry) {
            foreach ($entry["plugins"] as $name) {
                $analysis = $entry["analyses"][$name];
                $selection[] = [
                    "sourceid" => (int)$entry["source"]->id,
                    "source_filename" => (string)$entry["source"]->filename,
                    "plugin" => $name,
                    "section" => (int)$entry["section"],
                    "suggested_title" => (string)($analysis["title"] ?? ""),
                    "matched" => !empty($analysis["match"]),
                ];
            }
        }
        return [
            "operation" => $this->get_name(),
            "projectid" => (int)$project->id,
            "courseid" => (int)$project->courseid,
            "selections" => $selection,
            "will_create_moodle_activities_or_questions" => true,
            "will_use_saved_ai_analysis" => true,
            "no_ai_calls" => true,
            "cannot_retry_without_new_project" => true,
        ];
    }

    /**
     * Execute the confirmed operation with appropriate permissions.
     *
     * @param array $arguments Arguments.
     * @param authenticated_identity $identity User.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        $project = access::project((int)$arguments["projectid"], $identity);
        return publish_service::publish($project, (array)$arguments["selections"],
            (string)$arguments["expected_state_hash"], $identity);
    }
}
