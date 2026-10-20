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
 * Shared course and project resolution for confirmed GeniAI MCP writes.
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse\mcp;

use context;
use context_course;
use local_mcp\write\tool\base_tool;

/**
 * Provide the same capability checks as the core course workflow.
 */
abstract class write_project_base extends base_tool {
    /**
     * Return the capability required by this tool.
     *
     * @return string
     */
    public function get_required_capability(): string {
        return "local/geniaicourse:use";
    }

    /**
     * Resolve the course context for permission checking.
     *
     * @param array $arguments Arguments.
     * @return context
     */
    public function resolve_context(array $arguments): context {
        global $DB;
        $project = $DB->get_record("local_geniaicourse_project",
            ["id" => (int)$arguments["projectid"]], "id,courseid", MUST_EXIST);
        return context_course::instance((int)$project->courseid, MUST_EXIST);
    }
}
