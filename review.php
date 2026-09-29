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

use local_geniaicourse\local\project_manager;

$projectid = required_param('projectid', PARAM_INT);
require_login();
$project = project_manager::get_owned($projectid, $USER->id);
$course = get_course($project->courseid);
require_login($course);
$context = context_course::instance($course->id);
require_capability('local/geniaicourse:use', $context);
require_capability('moodle/course:manageactivities', $context);

$PAGE->set_url(new moodle_url('/local/geniaicourse/review.php', ['projectid' => $project->id]));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string('reviewtitle', 'local_geniaicourse'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->js_call_amd('local_geniaicourse/upload', 'initReview');

$sections = [];
$modinfo = get_fast_modinfo($course);
foreach ($modinfo->get_section_info_all() as $sectionnum => $sectioninfo) {
    $sections[] = [
        'num' => $sectionnum,
        'name' => get_section_name($course, $sectioninfo),
    ];
}

$sourcedata = [];
foreach (project_manager::get_sources($project->id) as $source) {
    $analyses = json_decode((string) $source->analysisjson, true) ?: [];
    $bestname = '';
    $bestconfidence = -1;
    foreach ($analyses as $pluginname => $analysis) {
        if (!empty($analysis['match']) && empty($analysis['error']) &&
                (int) ($analysis['confidence'] ?? 0) > $bestconfidence) {
            $bestname = $pluginname;
            $bestconfidence = (int) ($analysis['confidence'] ?? 0);
        }
    }

    $activities = [];
    foreach ($analyses as $pluginname => $analysis) {
        $activities[] = [
            'sourceid' => $source->id,
            'pluginname' => $pluginname,
            'activityname' => $analysis['activityname'] ?? $pluginname,
            'description' => $analysis['description'] ?? '',
            'match' => !empty($analysis['match']),
            'confidence' => (int) ($analysis['confidence'] ?? 0),
            'reason' => $analysis['reason'] ?? '',
            'title' => $analysis['title'] ?? '',
            'summary' => $analysis['summary'] ?? '',
            'checked' => $bestname === $pluginname,
            'error' => !empty($analysis['error']),
        ];
    }

    $metadata = json_decode((string) $source->metadatajson, true) ?: [];
    $warnings = [];
    foreach (($metadata['warnings'] ?? []) as $warning) {
        $warnings[] = ['text' => $warning];
    }
    $preview = trim((string) $source->extractedtext);
    if (core_text::strlen($preview) > 1200) {
        $preview = core_text::substr($preview, 0, 1200) . '…';
    }

    $sourcedata[] = [
        'id' => $source->id,
        'filename' => $source->filename,
        'mimetype' => $source->mimetype,
        'instruction' => $source->instruction,
        'preview' => $preview,
        'haspreview' => $preview !== '',
        'warnings' => $warnings,
        'haswarnings' => !empty($warnings),
        'activities' => $activities,
        'sections' => $sections,
    ];
}

$data = [
    'projectid' => $project->id,
    'courseid' => $course->id,
    'sesskey' => sesskey(),
    'sources' => $sourcedata,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_geniaicourse/review', $data);
echo $OUTPUT->footer();
