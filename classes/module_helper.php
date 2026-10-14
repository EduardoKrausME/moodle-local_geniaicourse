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
 * GeniAI Course Builder.
 *
 * @package local_geniaicourse
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse;

use stdClass;

/**
 * Helpers for adding native Moodle activities.
 *
 * @package local_geniaicourse
 */
class module_helper {
    /**
     * Base.
     *
     * @param stdClass $course Parameter value.
     * @param int $sectionnum Parameter value.
     * @param string $modulename Parameter value.
     * @param string $name Parameter value.
     * @param string $intro Parameter value.
     * @return stdClass
     */
    /**
     * Confirm that Moodle allows this user to add this module in this section.
     *
     * @param stdClass $course
     * @param int $sectionnum
     * @param string $modulename
     * @return void
     */
    public static function check_can_add(stdClass $course, int $sectionnum, string $modulename): void {
        global $CFG;
        require_once($CFG->dirroot . '/course/modlib.php');
        can_add_moduleinfo($course, $modulename, $sectionnum);
    }

    /**
     * Construct base configuration for a native Moodle module.
     *
     * @param stdClass $course
     * @param int $sectionnum
     * @param string $modulename
     * @param string $name
     * @param string $intro
     * @return stdClass
     */
    public static function base(stdClass $course, int $sectionnum, string $modulename,
                                string $name, string $intro = ''): stdClass {
        global $DB;
        self::check_can_add($course, $sectionnum, $modulename);
        $module = $DB->get_record('modules', ['name' => $modulename], '*', MUST_EXIST);

        $info = new stdClass();
        $info->course = $course->id;
        $info->module = $module->id;
        $info->modulename = $modulename;
        $info->section = $sectionnum;
        $info->name = clean_param($name, PARAM_TEXT);
        $info->intro = clean_text($intro, FORMAT_HTML);
        $info->introformat = FORMAT_HTML;
        $info->visible = 1;
        $info->visibleoncoursepage = 1;
        $info->showdescription = 0;
        $info->groupmode = NOGROUPS;
        $info->groupingid = 0;
        $info->completion = COMPLETION_TRACKING_NONE;
        $info->completionview = 0;
        $info->completiongradeitemnumber = '';
        $info->completionpassgrade = 0;
        $info->completionexpected = 0;
        $info->availability = null;
        $info->cmidnumber = '';
        return $info;
    }
}
