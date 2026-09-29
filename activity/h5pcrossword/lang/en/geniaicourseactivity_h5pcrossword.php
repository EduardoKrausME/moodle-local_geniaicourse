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

$string['description'] = 'Creates a native Moodle H5P crossword from source-grounded terms and clues.';
$string['h5pcontentcreatefailed'] = 'Moodle could not build the H5P content. {$a}';
$string['h5pexportmissing'] = 'The H5P editor did not generate an export package for the activity.';
$string['h5pinvalidcontent'] = 'The generated content is not sufficient to create {$a}.';
$string['h5plibrarymissing'] = 'The required H5P library is not installed or enabled in Moodle: {$a}';
$string['pluginname'] = 'H5P Crossword';
