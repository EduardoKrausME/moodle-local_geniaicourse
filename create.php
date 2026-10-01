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
require_once($CFG->dirroot . '/course/modlib.php');

use core\output\notification;
use local_geniaicourse\plugin_manager;
use local_geniaicourse\project_manager;

$projectid = required_param('projectid', PARAM_INT);
$choiceflags = optional_param_array('choice', [], PARAM_BOOL);
$sections = optional_param_array('section', [], PARAM_INT);

require_login();
$project = project_manager::get_owned($projectid, $USER->id);
$course = get_course($project->courseid);
require_login($course);
$context = context_course::instance($course->id);
require_capability('local/geniaicourse:use', $context);
require_capability('moodle/course:manageactivities', $context);
require_sesskey();

if (in_array($project->status, ['creating', 'created'], true)) {
    redirect(
        new moodle_url('/course/view.php', ['id' => $course->id]),
        get_string('projectalreadycreated', 'local_geniaicourse'),
        null,
        notification::NOTIFY_WARNING
    );
}

// The review form uses flat keys (<sourceid>_<pluginname>) because Moodle's
// optional_param_array() intentionally cleans only one array level.
$selectedbysource = [];
foreach ($choiceflags as $key => $enabled) {
    if (!$enabled || !preg_match('/^([0-9]+)_([a-z0-9_]+)$/', (string)$key, $matches)) {
        continue;
    }
    $sourceid = (int)$matches[1];
    $selectedbysource[$sourceid][] = $matches[2];
}

core_php_time_limit::raise();
project_manager::set_status($project->id, 'creating');

$validsections = get_fast_modinfo($course)->get_section_info_all();
$results = [];
foreach (project_manager::get_sources($project->id) as $source) {
    $selectedplugins = array_values(array_unique($selectedbysource[(int)$source->id] ?? []));
    if (!$selectedplugins) {
        continue;
    }

    $sectionnum = (int)($sections[$source->id] ?? 0);
    if (!array_key_exists($sectionnum, $validsections)) {
        $sectionnum = 0;
    }

    $analysisall = json_decode((string)$source->analysisjson, true) ?: [];

    $prepared = plugin_manager::prepare_selected($selectedplugins, $analysisall);
    $analysisall = $prepared['analyses'];
    $consumedplugins = $prepared['consumed'];

    foreach ($selectedplugins as $pluginname) {
        if (in_array($pluginname, $consumedplugins, true)) {
            continue;
        }
        if (!isset($analysisall[$pluginname]) || !is_array($analysisall[$pluginname])) {
            $results[] = [
                'success' => false,
                'name' => $source->filename,
                'error' => get_string('missinganalysis', 'local_geniaicourse'),
            ];
            continue;
        }
        $analysis = $analysisall[$pluginname];

        try {
            $created = plugin_manager::create($pluginname, $course, $sectionnum, $source, $analysis);
            $results[] = [
                'success' => true,
                'name' => $created['name'] ?? $source->filename,
                'url' => $created['url'] ?? '',
                'warning' => $created['warning'] ?? '',
                'haswarning' => !empty($created['warning']),
            ];
        } catch (Throwable $e) {
            $results[] = [
                'success' => false,
                'name' => ($analysis['activityname'] ?? $pluginname) . ' — ' . $source->filename,
                'error' => $e->getMessage(),
            ];
        }
    }
}
project_manager::set_status($project->id, 'created');
rebuild_course_cache($course->id, true);

$PAGE->set_url(new moodle_url('/local/geniaicourse/create.php', ['projectid' => $project->id]));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string('createdtitle', 'local_geniaicourse'));
$PAGE->set_heading(format_string($course->fullname));

$data = [
    'results' => $results,
    'courseurl' => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
    'newurl' => (new moodle_url('/local/geniaicourse/index.php', ['courseid' => $course->id]))->out(false),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_geniaicourse/result', $data);
echo $OUTPUT->footer();
