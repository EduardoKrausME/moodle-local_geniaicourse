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
 * @package geniaicourseactivity_h5pflashcards
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['h5p_progressText'] = 'Card @card of @total';
$string['h5p_next'] = 'Next';
$string['h5p_previous'] = 'Previous';
$string['h5p_checkAnswerText'] = 'Check';
$string['h5p_defaultAnswerText'] = 'Your answer';
$string['h5p_correctAnswerText'] = 'Correct';
$string['h5p_incorrectAnswerText'] = 'Incorrect';
$string['h5p_showSolutionText'] = 'Correct answer(s)';
$string['h5p_results'] = 'Results';
$string['h5p_cardsHeader'] = 'Cards';
$string['h5p_scoreHeader'] = 'Score';
$string['h5p_ofCorrect'] = '@score of @total correct';
$string['h5p_showResults'] = 'Show results';
$string['h5p_answerShortText'] = 'A:';
$string['h5p_retry'] = 'Retry';
$string['h5p_cardAnnouncement'] = 'Incorrect answer. Correct answer was @answer';
$string['h5p_correctAnswerAnnouncement'] = '@answer is correct.';
$string['h5p_pageAnnouncement'] = 'Page @current of @total';
$string['h5p_defaultinstruction'] = 'Review the cards and enter the correct answer.';


$string['privacy:metadata'] = 'The generator creates native Moodle activities and does not keep its own user information.';

$string['description'] = 'Creates a native Moodle H5P Flashcards recall activity.';
$string['h5pcontentcreatefailed'] = 'Moodle could not build the H5P content. {$a}';
$string['h5pexportmissing'] = 'The H5P editor did not generate an export package for the activity.';
$string['h5pinvalidcontent'] = 'The generated content is not sufficient to create {$a}.';
$string['h5plibrarymissing'] = 'The required H5P library is not installed or enabled in Moodle: {$a}';
$string['pluginname'] = 'H5P Flashcards';
