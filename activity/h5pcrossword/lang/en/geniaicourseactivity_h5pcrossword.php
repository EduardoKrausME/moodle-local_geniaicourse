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
 * @package geniaicourseactivity_h5pcrossword
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['h5pl10n_across'] = 'Across';
$string['h5pl10n_down'] = 'Down';
$string['h5pl10n_checkAnswer'] = 'Check';
$string['h5pl10n_submitAnswer'] = 'Submit';
$string['h5pl10n_tryAgain'] = 'Retry';
$string['h5pl10n_showSolution'] = 'Show solution';
$string['h5pl10n_couldNotGenerateCrossword'] = 'Could not generate a crossword with the given words. Please try again with fewer words or words that have more characters in common.';
$string['h5pl10n_couldNotGenerateCrosswordTooFewWords'] = 'Could not generate a crossword. You need at least two words.';
$string['h5pl10n_probematicWords'] = 'Some words could not be placed. Problematic word(s): @words';
$string['h5pl10n_problematicWords'] = 'Some words could not be placed. Problematic word(s): @words';
$string['h5pl10n_extraClue'] = 'Extra clue';
$string['h5pl10n_closeWindow'] = 'Close window';
$string['h5pa11y_crosswordGrid'] = 'Crossword grid. Use arrow keys to navigate and the keyboard to enter characters.';
$string['h5pa11y_column'] = 'Column';
$string['h5pa11y_row'] = 'Row';
$string['h5pa11y_across'] = 'Across';
$string['h5pa11y_down'] = 'Down';
$string['h5pa11y_empty'] = 'Empty';
$string['h5pa11y_resultFor'] = 'Result for: @clue';
$string['h5pa11y_correct'] = 'Correct';
$string['h5pa11y_wrong'] = 'Wrong';
$string['h5pa11y_point'] = 'point';
$string['h5pa11y_solutionFor'] = 'For @clue the solution is: @solution';
$string['h5pa11y_extraClueFor'] = 'Open extra clue for @clue';
$string['h5pa11y_letterSevenOfNine'] = 'Letter @position of @length';
$string['h5pa11y_lettersWord'] = '@length letter word';
$string['h5pa11y_check'] = 'Check the characters. The responses will be marked as correct, incorrect, or unanswered.';
$string['h5pa11y_submitAndcheck'] = 'Submit the answer and check the characters.';
$string['h5pa11y_showSolution'] = 'Show the solution. The crossword will be filled with its correct solution.';
$string['h5pa11y_retry'] = 'Retry the task. Reset all responses and start the task over again.';
$string['h5pa11y_yourResult'] = 'You got @score out of @total points';


$string['privacy:metadata'] = 'The generator creates native Moodle activities and does not keep its own user information.';

$string['description'] = 'Creates a native Moodle H5P crossword from source-grounded terms and clues.';
$string['h5pcontentcreatefailed'] = 'Moodle could not build the H5P content. {$a}';
$string['h5pexportmissing'] = 'The H5P editor did not generate an export package for the activity.';
$string['h5pinvalidcontent'] = 'The generated content is not sufficient to create {$a}.';
$string['h5plibrarymissing'] = 'The required H5P library is not installed or enabled in Moodle: {$a}';
$string['pluginname'] = 'H5P Crossword';
