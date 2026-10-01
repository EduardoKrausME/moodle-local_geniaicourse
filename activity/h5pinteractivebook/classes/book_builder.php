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

use context_course;
use geniaicourseactivity_h5pinteractivebook\runtime as h5p_runtime;
use moodle_exception;
use moodle_url;
use stdClass;

/**
     * Interactive Book composition logic owned by this subplugin.
     */
class book_builder {
    /**
     * Convert a reusable H5P definition into one H5P.Column block.
     */
    public static function block(stdClass $course, stdClass $source, array $definition,
                                 bool     &$usediframe = false): array {
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

        if (!h5p_runtime::has_library('H5P.IFrameEmbed', false) ||
            self::column_library_option('H5P.IFrameEmbed') === null) {
            throw new moodle_exception('h5pbookembedunsupported', 'geniaicourseactivity_h5pinteractivebook', '', $title);
        }

        $usediframe = true;
        return self::iframe_block($course, $source, $definition);
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

    /**
     * Create a persistent standalone H5P child and wrap it in H5P.IFrameEmbed.
     */
    private static function iframe_block(stdClass $course, stdClass $source, array $definition): array {
        global $USER;

        $machinename = (string)$definition['machinename'];
        $params = (array)$definition['params'];
        $title = clean_param((string)($definition['title'] ?? $machinename), PARAM_TEXT) ?: $machinename;
        $coursecontext = context_course::instance($course->id);
        $itemid = file_get_unused_draft_itemid();
        $slug = clean_param(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $machinename)), PARAM_FILE);
        $filename = ($slug ?: 'h5p-child') . '-' . $source->id . '-' . $itemid . '.h5p';

        $file = h5p_runtime::create_h5p_file(
            $coursecontext->id,
            'geniaicourseactivity_h5pinteractivebook',
            'generated',
            $itemid,
            $filename,
            $machinename,
            $params,
            $title,
            $USER->id
        );

        $fileurl = moodle_url::make_pluginfile_url(
            $coursecontext->id,
            'geniaicourseactivity_h5pinteractivebook',
            'generated',
            $itemid,
            '/',
            $file->get_filename(),
            false
        );
        $embedurl = new moodle_url('/h5p/embed.php', [
            'url' => $fileurl->out_as_local_url(false),
            'component' => 'geniaicourseactivity_h5pinteractivebook',
        ]);
        $iframeversion = self::column_library_option('H5P.IFrameEmbed');

        return [
            'content' => h5p_runtime::content_object('H5P.IFrameEmbed', [
                'width' => '100%',
                'minWidth' => '300px',
                'height' => '650px',
                'source' => $embedurl->out_as_local_url(false),
                'resizeSupported' => true,
            ], $title, $iframeversion),
            'useSeparator' => 'auto',
        ];
    }
}
