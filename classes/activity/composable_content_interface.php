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

namespace local_geniaicourse\activity;

/**
 * Optional contract for subplugins that expose content reusable by another subplugin.
 */
interface composable_content_interface {
    /**
     * Family identifier used by compatible consumer subplugins, for example 'h5p'.
     *
     * @return string
     */
    public static function get_composable_family(): string;

    /**
     * Build a reusable content definition without creating the final Moodle activity.
     * The structure is owned by the implementing/consuming subplugins.
     */
    public static function build_composable_content(\stdClass $course, \stdClass $source, array $analysis): array;
}
