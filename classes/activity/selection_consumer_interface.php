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
 * Optional contract for subplugins that consume other selected subplugins.
 *
 * The core orchestrator stays unaware of concrete plugin names. A consumer
 * subplugin can enrich its own analysis and declare which selected plugins it
 * consumes so they are not created a second time.
 *
 * @package local_geniaicourse
 */
interface selection_consumer_interface {
    /**
     * Enrich this subplugin analysis with the current selection context.
     *
     * @param array $analysis Parameter value.
     * @param array $selectedplugins Parameter value.
     * @param array $allanalysis Parameter value.
     * @return array
     */
    public static function prepare_selection(array $analysis, array $selectedplugins, array $allanalysis): array;

    /**
     * Return selected plugin names consumed by this subplugin.
     *
     * @param array $selectedplugins Parameter value.
     * @param array $allanalysis Parameter value.
     * @return array
     */
    public static function consumed_plugins(array $selectedplugins, array $allanalysis): array;
}
