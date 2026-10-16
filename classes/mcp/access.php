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
 * Course, permission and project ownership checks for MCP.
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse\mcp;

use context_course;
use local_geniaicourse\project_manager;
use local_mcp\security\authenticated_identity;
use stdClass;

/**
 * Use exactly the author and Moodle course permissions required by web pages.
 */
final class access {
    /**
     * Load a course and verify connected-user permissions.
     *
     * @param int $courseid Course.
     * @param authenticated_identity $identity MCP user.
     * @return stdClass
     */
    public static function course(int $courseid, authenticated_identity $identity): stdClass {
        $course = get_course($courseid);
        $context = context_course::instance($courseid, MUST_EXIST);
        require_capability("local/geniaicourse:use", $context, $identity->userid);
        require_capability("moodle/course:manageactivities", $context, $identity->userid);
        return $course;
    }

    /**
     * Load an owned project and verify course access.
     *
     * @param int $projectid Project.
     * @param authenticated_identity $identity MCP user.
     * @return stdClass
     */
    public static function project(int $projectid, authenticated_identity $identity): stdClass {
        $project = project_manager::get_owned($projectid, $identity->userid);
        self::course((int)$project->courseid, $identity);
        return $project;
    }
}
