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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Inspect a single saved GeniAI subplugin analysis in bounded JSON.
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse\mcp;

use local_geniaicourse\project_manager;
use local_mcp\security\authenticated_identity;
use moodle_exception;

/**
 * Return only one saved generator plan at a time and never run AI in READ.
 */
final class get_analysis extends read_project_base {
    /**
     * @return string
     */
    public function get_name(): string {
        return "geniaicourse_get_analysis";
    }

    /**
     * @return string
     */
    public function get_title(): string {
        return get_string("mcpgetanalysis", "local_geniaicourse");
    }

    /**
     * @return string
     */
    public function get_description(): string {
        return get_string("mcpgetanalysisdesc", "local_geniaicourse");
    }

    /**
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            "projectid" => ["type" => "integer", "minimum" => 1],
            "sourceid" => ["type" => "integer", "minimum" => 1],
            "plugin" => ["type" => "string", "pattern" => "^[a-z0-9_]{1,80}$"],
            "offset" => ["type" => "integer", "minimum" => 0],
            "length" => ["type" => "integer", "minimum" => 1, "maximum" => 12000],
        ], ["projectid", "sourceid", "plugin"]);
    }

    /**
     * @param array $arguments Arguments.
     * @param authenticated_identity $identity User.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        global $DB;
        $project = access::project((int)$arguments["projectid"], $identity);
        $source = $DB->get_record("local_geniaicourse_source", [
            "id" => (int)$arguments["sourceid"],
            "projectid" => (int)$project->id,
        ], "id,analysisjson", MUST_EXIST);
        $analyses = json_decode((string)$source->analysisjson, true);
        $name = (string)$arguments["plugin"];
        if (!is_array($analyses) || !isset($analyses[$name]) || !is_array($analyses[$name])) {
            throw new moodle_exception("missinganalysis", "local_geniaicourse");
        }
        $json = json_encode($analyses[$name], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $offset = max(0, (int)($arguments["offset"] ?? 0));
        $length = max(1, min(12000, (int)($arguments["length"] ?? 4000)));
        return [
            "projectid" => (int)$project->id,
            "sourceid" => (int)$source->id,
            "plugin" => $name,
            "json_chars" => \core_text::strlen($json),
            "offset" => $offset,
            "length" => $length,
            "json_chunk" => \core_text::substr($json, $offset, $length),
            "has_more" => $offset + $length < \core_text::strlen($json),
        ];
    }
}
