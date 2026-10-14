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
 * @package geniaicourseactivity_h5pinteractivebook
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['h5p_read'] = 'Read';
$string['h5p_displayTOC'] = 'Display \'Table of contents\'';
$string['h5p_hideTOC'] = 'Hide \'Table of contents\'';
$string['h5p_page'] = 'Page';
$string['h5p_next'] = 'Next';
$string['h5p_nextPage'] = 'Next page';
$string['h5p_previous'] = 'Previous';
$string['h5p_previousPage'] = 'Previous page';
$string['h5p_chapterCompleted'] = 'Page completed!';
$string['h5p_partCompleted'] = '@pages of @total completed';
$string['h5p_incompleteChapter'] = 'Incomplete page';
$string['h5p_navigateToTop'] = 'Navigate to the top';
$string['h5p_markAsFinished'] = 'I have finished this page';
$string['h5p_fullscreen'] = 'Fullscreen';
$string['h5p_exitFullscreen'] = 'Exit fullscreen';
$string['h5p_bookProgressSubtext'] = '@count of @total pages';
$string['h5p_interactionsProgressSubtext'] = '@count of @total interactions';
$string['h5p_submitReport'] = 'Submit Report';
$string['h5p_restartLabel'] = 'Restart';
$string['h5p_summaryHeader'] = 'Summary';
$string['h5p_allInteractions'] = 'All interactions';
$string['h5p_unansweredInteractions'] = 'Unanswered interactions';
$string['h5p_scoreText'] = '@score / @maxscore';
$string['h5p_leftOutOfTotalCompleted'] = '@left of @max interactions completed';
$string['h5p_noInteractions'] = 'No interactions';
$string['h5p_score'] = 'Score';
$string['h5p_summaryAndSubmit'] = 'Summary & submit';
$string['h5p_noChapterInteractionBoldText'] = 'You have not interacted with any pages.';
$string['h5p_noChapterInteractionText'] = 'You have to interact with at least one page before you can see the summary.';
$string['h5p_yourAnswersAreSubmittedForReview'] = 'Your answers are submitted for review!';
$string['h5p_bookProgress'] = 'Book progress';
$string['h5p_interactionsProgress'] = 'Interactions progress';
$string['h5p_totalScoreLabel'] = 'Total score';
$string['h5p_progress'] = 'Page @page of @total.';
$string['h5p_menu'] = 'Toggle navigation menu';


$string['privacy:metadata:files'] = 'Teacher-generated media files are stored in the course context.';

$string['description'] = 'Creates an H5P Interactive Book and can reuse selected H5P subplugins as book pages. Select the book together with one or more H5P types to place those types inside it; select only the book to use the H5P types recommended by the analysis.';
$string['h5pbookchildfailed'] = '{$a->name} could not be added to the Interactive Book: {$a->error}';
$string['h5pbookembedunsupported'] = 'H5P Interactive Book cannot embed {$a} with the H5P.Column libraries installed on this Moodle site.';
$string['h5pbookiframewarning'] = 'These H5P types are embedded in the book through an internal iframe because the installed H5P.Column library does not accept them as direct children: {$a}. Their own interaction score is not aggregated into the Interactive Book summary.';
$string['h5pbookpagedefault'] = 'Book page {$a}';
$string['h5pcontentcreatefailed'] = 'Moodle could not build the H5P content. {$a}';
$string['h5pexportmissing'] = 'The H5P editor did not generate an export package for the activity.';
$string['h5pinvalidcontent'] = 'The generated content is not sufficient to create {$a}.';
$string['h5plibrarymissing'] = 'The required H5P library is not installed or enabled in Moodle: {$a}';
$string['pluginname'] = 'H5P Interactive Book';
