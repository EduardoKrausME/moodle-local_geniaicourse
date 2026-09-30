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

use local_geniaicourse\ai;
use local_geniaicourse\plugin_manager;
use local_geniaicourse\project_manager;
use local_geniaicourse\source_manager;

$courseid = required_param('courseid', PARAM_INT);
$prompt = optional_param('prompt', '', PARAM_RAW_TRIMMED);
$instructions = optional_param_array('fileinstructions', [], PARAM_RAW_TRIMMED);

$course = get_course($courseid);
require_login($course);
$context = context_course::instance($course->id);
require_capability('local/geniaicourse:use', $context);
require_capability('moodle/course:manageactivities', $context);
require_sesskey();

if (!ai::is_configured()) {
    throw new moodle_exception('noapikey', 'local_geniaicourse');
}

// Multiple sources are intentionally analysed by every installed activity subplugin.
// Give the synchronous request enough room for those API calls and document extraction.
core_php_time_limit::raise();
raise_memory_limit(MEMORY_EXTRA);

$uploads = [];
if (!empty($_FILES['files']['name']) && is_array($_FILES['files']['name'])) {
    foreach ($_FILES['files']['name'] as $i => $name) {
        if ($name === '') {
            continue;
        }
        $upload = [
            'name' => $name,
            'type' => $_FILES['files']['type'][$i] ?? '',
            'tmp_name' => $_FILES['files']['tmp_name'][$i] ?? '',
            'error' => $_FILES['files']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $_FILES['files']['size'][$i] ?? 0,
        ];
        if ($upload['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) {
            throw new moodle_exception('uploaderror', 'local_geniaicourse', '', clean_param($name, PARAM_FILE));
        }
        $maxbytes = get_max_upload_file_size($CFG->maxbytes, $course->maxbytes ?? 0);
        if ($maxbytes > 0 && $upload['size'] > $maxbytes) {
            throw new moodle_exception('maxbytes', 'error');
        }
        $uploads[] = ['upload' => $upload, 'instruction' => $instructions[$i] ?? ''];
    }
}

if (trim($prompt) === '' && !$uploads) {
    redirect(new moodle_url('/local/geniaicourse/index.php', ['courseid' => $courseid]),
        get_string('nosources', 'local_geniaicourse'), null, \core\output\notification::NOTIFY_ERROR);
}

$project = project_manager::create($courseid, $USER->id, $prompt);
try {
    $sources = [];
    if (trim($prompt) !== '') {
        $sources[] = source_manager::create_text($project->id, $prompt);
    }
    foreach ($uploads as $item) {
        $sources[] = source_manager::create_upload($project->id, $USER->id, $item['upload'], $item['instruction']);
    }

    foreach ($sources as $source) {
        $analysis = plugin_manager::analyse_source($project, $source);
        source_manager::save_analysis($source, $analysis);
    }
    project_manager::set_status($project->id, 'analysed');
} catch (\Throwable $e) {
    project_manager::delete_project($project);
    throw $e;
}

redirect(new moodle_url('/local/geniaicourse/review.php', ['projectid' => $project->id]));
