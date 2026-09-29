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

namespace local_geniaicourse\local\activity;

/**
 * Contract implemented by activity subplugins.
 *
 * @package local_geniaicourse
 */
interface activity_interface {
    /** Friendly name shown to teachers. */
 * Get name.
 *
    /**
     * Get name.
     *
     * @return string
     */
    public static function get_name(): string;

    /** Short description shown in review. */
 * Get description.
 *
    /**
     * Get description.
     *
     * @return string
     */
    public static function get_description(): string;

    /**
     * Analyse one source and return a normalized proposal.
     *
     * Expected keys: match, confidence, title, summary, reason.
     * Additional keys are owned by the subplugin and passed back to create().
     *
     * @param \stdClass $project
     * @param \stdClass $source
     * @return array
     */
    public static function analyse(\stdClass $project, \stdClass $source): array;

    /**
     * Create the Moodle activity.
     *
     * @param \stdClass $course
     * @param int $sectionnum
     * @param \stdClass $source
     * @param array $analysis
     * @return array Must contain cmid, name, url; may contain warning.
     */
    public static function create(\stdClass $course, int $sectionnum, \stdClass $source, array $analysis): array;
}
