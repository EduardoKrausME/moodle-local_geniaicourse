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
 * Read the owner's GeniAI project metadata and stored generator matches.
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse\mcp;

use local_mcp\security\authenticated_identity;

/**
 * Page source metadata without returning full prompt or raw source content.
 */
final class get_project extends read_project_base {
    /**
     * Return the unique MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return "geniaicourse_get_project";
    }

    /**
     * Return the translated MCP tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string("mcpgetproject", "local_geniaicourse");
    }

    /**
     * Return the translated MCP tool description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string("mcpgetprojectdesc", "local_geniaicourse");
    }

    /**
     * Declare accepted input arguments.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            "projectid" => ["type" => "integer", "minimum" => 1],
            "offset" => ["type" => "integer", "minimum" => 0],
            "limit" => ["type" => "integer", "minimum" => 1, "maximum" => 30],
        ], ["projectid"]);
    }

    /**
     * Read the authorized project information without side effects.
     *
     * @param array $arguments Arguments.
     * @param authenticated_identity $identity User.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        $project = access::project((int)$arguments["projectid"], $identity);
        $snapshot = project_state::inspect($project);
        $offset = max(0, (int)($arguments["offset"] ?? 0));
        $limit = max(1, min(30, (int)($arguments["limit"] ?? 20)));
        $snapshot["sources"] = array_slice($snapshot["sources"], $offset, $limit);
        $snapshot["offset"] = $offset;
        $snapshot["limit"] = $limit;
        $snapshot["has_more"] = $offset + count($snapshot["sources"]) < $snapshot["total_sources"];
        return $snapshot;
    }
}
