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
 * List the connected user's GeniAI projects in a Moodle course.
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse\mcp;

use context;
use context_course;
use local_mcp\read\tool\base_tool;
use local_mcp\security\authenticated_identity;

/**
 * Do not expose private project prompts from other teachers.
 */
final class list_projects extends base_tool {
    /**
     * @return string
     */
    public function get_name(): string {
        return "geniaicourse_list_projects";
    }

    /**
     * @return string
     */
    public function get_title(): string {
        return get_string("mcplistprojects", "local_geniaicourse");
    }

    /**
     * @return string
     */
    public function get_description(): string {
        return get_string("mcplistprojectsdesc", "local_geniaicourse");
    }

    /**
     * @return string
     */
    public function get_required_capability(): string {
        return "local/geniaicourse:use";
    }

    /**
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            "courseid" => ["type" => "integer", "minimum" => 1],
            "offset" => ["type" => "integer", "minimum" => 0],
            "limit" => ["type" => "integer", "minimum" => 1, "maximum" => 40],
        ], ["courseid"]);
    }

    /**
     * @param array $arguments Arguments.
     * @return context
     */
    public function resolve_context(array $arguments): context {
        get_course((int)$arguments["courseid"]);
        return context_course::instance((int)$arguments["courseid"], MUST_EXIST);
    }

    /**
     * @param array $arguments Arguments.
     * @param authenticated_identity $identity User.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        global $DB;
        $courseid = (int)$arguments["courseid"];
        access::course($courseid, $identity);
        $where = ["courseid" => $courseid, "userid" => $identity->userid];
        $total = $DB->count_records("local_geniaicourse_project", $where);
        $offset = max(0, (int)($arguments["offset"] ?? 0));
        $limit = max(1, min(40, (int)($arguments["limit"] ?? 20)));
        $rows = $DB->get_records("local_geniaicourse_project", $where,
            "timecreated DESC, id DESC", "id,status,timecreated,timemodified", $offset, $limit);
        $projects = [];
        foreach ($rows as $record) {
            $projects[] = [
                "projectid" => (int)$record->id,
                "status" => (string)$record->status,
                "timecreated" => (int)$record->timecreated,
                "timemodified" => (int)$record->timemodified,
            ];
        }
        return [
            "courseid" => $courseid,
            "total" => $total,
            "offset" => $offset,
            "limit" => $limit,
            "has_more" => $offset + count($projects) < $total,
            "projects" => $projects,
        ];
    }
}
