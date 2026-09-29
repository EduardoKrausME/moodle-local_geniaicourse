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


/**
 * Add the AI course builder to course navigation.
 *
 * @param navigation_node $navigation
 * @param stdClass $course
 * @param context_course $context
 */
function local_geniaicourse_extend_navigation_course($navigation, $course, $context) {
    if (!has_capability('local/geniaicourse:use', $context) ||
            !has_capability('moodle/course:manageactivities', $context)) {
        return;
    }

    $url = new moodle_url('/local/geniaicourse/index.php', ['courseid' => $course->id]);
    $navigation->add(
        get_string('navtitle', 'local_geniaicourse'),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'local_geniaicourse'
    );
}
