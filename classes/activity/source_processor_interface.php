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

use stored_file;

/**
 * Optional contract for subplugins that own extraction/metadata handling for extra source formats.
 */
interface source_processor_interface {
    /**
     * Supports source extension.
     *
     * @param string $extension Parameter value.
     * @return bool
     */
    public static function supports_source_extension(string $extension): bool;

    /**
     * Process source.
     *
     * @param stored_file $file Parameter value.
     * @param string $extension Parameter value.
     * @return ?array
     */
    public static function process_source(stored_file $file, string $extension): ?array;
}
