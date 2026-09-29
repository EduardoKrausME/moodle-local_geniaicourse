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

require_once(__DIR__ . '/../../config.php');

use local_geniaicourse\local\ai;
use local_geniaicourse\local\source_manager;

$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($course->id);
require_capability('local/geniaicourse:use', $context);
require_capability('moodle/course:manageactivities', $context);

$PAGE->set_url(new moodle_url('/local/geniaicourse/index.php', ['courseid' => $course->id]));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string('title', 'local_geniaicourse'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->js_call_amd('local_geniaicourse/upload', 'init');

$data = [
    'courseid' => $course->id,
    'sesskey' => sesskey(),
    'noapikey' => !ai::is_configured(),
    'maxupload' => display_size(get_max_upload_file_size($CFG->maxbytes, $course->maxbytes ?? 0)),
    'fileaccept' => implode(',', array_map(static fn($ext) => '.' . $ext, source_manager::allowed_extensions())),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_geniaicourse/index', $data);
echo $OUTPUT->footer();
