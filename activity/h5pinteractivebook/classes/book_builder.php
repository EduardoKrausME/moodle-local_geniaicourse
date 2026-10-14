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

namespace geniaicourseactivity_h5pinteractivebook;

use geniaicourseactivity_h5pinteractivebook\runtime as h5p_runtime;
use moodle_exception;
use stdClass;

/**
 * Interactive Book composition logic owned by this subplugin.
 */
class book_builder {
    /**
     * Convert a reusable H5P definition into one H5P.Column block.
     */
    public static function block(stdClass $course, stdClass $source, array $definition,
                                 bool &$usediframe = false): array {
        $machinename = (string)($definition['machinename'] ?? '');
        $params = $definition['params'] ?? [];
        $title = (string)($definition['title'] ?? $machinename);
        if ($machinename === '' || !is_array($params)) {
            throw new moodle_exception('h5pinvalidcontent', 'geniaicourseactivity_h5pinteractivebook', '', $title);
        }

        $allowedversion = self::column_library_option($machinename);
        if ($allowedversion !== null) {
            return [
                'content' => h5p_runtime::content_object($machinename, $params, $title, $allowedversion),
                'useSeparator' => 'auto',
            ];
        }

        // A course-context iframe URL would not survive backup/restore.
        // The caller turns this unsupported child into an explanatory book page.
        throw new moodle_exception('h5pbookembedunsupported', 'geniaicourseactivity_h5pinteractivebook', '', $title);
    }

    /**
     * Build an AdvancedText block for one book page.
     */
    public static function text_block(string $html, string $title = 'Text'): array {
        if (!h5p_runtime::has_library('H5P.AdvancedText', false)) {
            throw new moodle_exception('h5plibrarymissing', 'geniaicourseactivity_h5pinteractivebook', '', 'H5P.AdvancedText');
        }
        $allowedversion = self::column_library_option('H5P.AdvancedText');
        if ($allowedversion === null) {
            throw new moodle_exception(
                'h5pbookembedunsupported',
                'geniaicourseactivity_h5pinteractivebook',
                '',
                'H5P.AdvancedText'
            );
        }
        return [
            'content' => h5p_runtime::content_object(
                'H5P.AdvancedText',
                ['text' => clean_text($html, FORMAT_HTML)],
                $title,
                $allowedversion
            ),
            'useSeparator' => 'auto',
        ];
    }

    /**
     * Build one H5P.Column chapter object.
     */
    public static function column(array $blocks, string $title): array {
        if (!$blocks) {
            $blocks[] = self::text_block('<p></p>', $title);
        }
        $columnversion = h5p_runtime::parent_library_option('H5P.InteractiveBook', 'H5P.Column');
        if ($columnversion === null) {
            throw new moodle_exception(
                'h5pbookembedunsupported',
                'geniaicourseactivity_h5pinteractivebook',
                '',
                'H5P.Column'
            );
        }
        return h5p_runtime::content_object(
            'H5P.Column',
            ['content' => $blocks],
            $title,
            $columnversion
        );
    }

    /**
     * Return the exact library version accepted directly by H5P.Column.
     */
    private static function column_library_option(string $machinename): ?string {
        $columnversion = h5p_runtime::parent_library_option('H5P.InteractiveBook', 'H5P.Column');
        if ($columnversion === null) {
            return null;
        }
        return h5p_runtime::library_option($columnversion, $machinename);
    }

}
