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
 * GeniAI Course activity subplugin.
 *
 * @package geniaicourseactivity_h5pdragdrop
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['description'] = 'Creates a native Moodle H5P Drag and Drop matching activity.';
$string['h5p_a11yCheck'] = 'Check the answers. The responses will be marked as correct, incorrect, or unanswered.';
$string['h5p_a11yRetry'] = 'Retry the task. Reset all responses and start the task over again.';
$string['h5p_correctAnswer'] = 'Correct answer';
$string['h5p_defaultinstruction'] = 'Drag each item to the correct destination.';
$string['h5p_dropzonePrefix'] = 'Dropzone {num} of {total}.';
$string['h5p_exitFullscreen'] = 'Exit fullscreen';
$string['h5p_feedbackHeader'] = 'Feedback';
$string['h5p_fullscreen'] = 'Fullscreen';
$string['h5p_grabbablePrefix'] = 'Grabbable {num} of {total}.';
$string['h5p_grabbableSuffix'] = 'Placed in dropzone {num}.';
$string['h5p_noDropzone'] = 'No dropzone.';
$string['h5p_scoreBarLabel'] = 'You got :num out of :total points';
$string['h5p_scoreExplanation'] = 'Correct answers give points. Incorrect answers may reduce the score.';
$string['h5p_scoreExplanationButtonLabel'] = 'Show score explanation';
$string['h5p_scoreShow'] = 'Check';
$string['h5p_submit'] = 'Submit';
$string['h5p_tipAvailable'] = 'Tip available';
$string['h5p_tipLabel'] = 'Show tip.';
$string['h5p_tryAgain'] = 'Retry';
$string['h5p_wrongAnswer'] = 'Wrong answer';
$string['h5pcontentcreatefailed'] = 'Moodle could not build the H5P content. {$a}';
$string['h5pexportmissing'] = 'The H5P editor did not generate an export package for the activity.';
$string['h5pinvalidcontent'] = 'The generated content is not sufficient to create {$a}.';
$string['h5plibrarymissing'] = 'The required H5P library is not installed or enabled in Moodle: {$a}';
$string['pluginname'] = 'H5P Drag and Drop';
$string['privacy:metadata'] = 'The generator creates native Moodle activities and does not keep its own user information.';
