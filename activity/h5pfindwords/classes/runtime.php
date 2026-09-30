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
 * @package geniaicourseactivity_h5pfindwords
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace geniaicourseactivity_h5pfindwords;

use context_user;
use local_geniaicourse\module_helper;
use core_h5p\editor;
use core_h5p\factory;
use core_h5p\helper;
use mod_h5pactivity\local\manager;
use moodle_exception;
use moodle_url;
use stored_file;

/**
 * Generic adapter for Moodle's H5P runtime.
 *
 * This class contains only infrastructure shared by H5P subplugins. Content-type
 * rules belong to the individual geniaicourseactivity_* subplugins.
 *
 * @package geniaicourseactivity_h5pfindwords
 */
class runtime {
    /** Resolve the newest installed version of one H5P library. */
    public static function library(string $machinename, bool $requireenabled = true): \stdClass {
        global $DB;

        $where = 'machinename = :machinename';
        $params = ['machinename' => $machinename];
        if ($requireenabled) {
            $where .= ' AND enabled = :enabled';
            $params['enabled'] = 1;
        }

        $records = $DB->get_records_select(
            'h5p_libraries',
            $where,
            $params,
            'majorversion DESC, minorversion DESC, patchversion DESC',
            '*',
            0,
            1
        );
        if (!$records) {
            throw new moodle_exception('h5plibrarymissing', 'geniaicourseactivity_h5pfindwords', '', $machinename);
        }
        return reset($records);
    }

    /** Return the H5P editor library string for an installed library. */
    public static function library_string(string $machinename, bool $requireenabled = true): string {
        $library = self::library($machinename, $requireenabled);
        return $library->machinename . ' ' . $library->majorversion . '.' . $library->minorversion;
    }

    /** Determine whether a library can be used for authoring. */
    public static function has_library(string $machinename, bool $requireenabled = true): bool {
        try {
            self::library($machinename, $requireenabled);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Build a valid H5P export file with Moodle's H5P editor. */
    public static function create_h5p_file(int $contextid, string $component, string $filearea, int $itemid,
            string $filename, string $machinename, array $params, string $title, ?int $userid = null): stored_file {
        global $USER;

        $userid = $userid ?? $USER->id;
        $librarystring = self::library_string($machinename, true);
        $title = clean_param($title, PARAM_TEXT);
        if ($title === '') {
            $title = $machinename;
        }
        $filename = clean_param($filename, PARAM_FILE);
        if ($filename === '') {
            $filename = 'content-' . $itemid . '.h5p';
        }

        $editor = new editor();
        $editor->set_library(
            $librarystring,
            $contextid,
            $component,
            $filearea,
            $itemid,
            '/',
            $filename,
            $userid
        );

        $content = new \stdClass();
        $content->h5plibrary = $librarystring;
        $content->h5pparams = json_encode([
            'params' => $params,
            'metadata' => [
                'title' => $title,
                'license' => 'U',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        try {
            $editor->save_content($content);
        } catch (\Throwable $e) {
            throw new moodle_exception('h5pcontentcreatefailed', 'geniaicourseactivity_h5pfindwords', '', $e->getMessage());
        }

        $fs = get_file_storage();
        $file = $fs->get_file($contextid, $component, $filearea, $itemid, '/', $filename);
        if ($file && !$file->is_directory()) {
            return $file;
        }

        $files = $fs->get_area_files($contextid, $component, $filearea, $itemid, 'id ASC', false);
        foreach ($files as $candidate) {
            if (strtolower(pathinfo($candidate->get_filename(), PATHINFO_EXTENSION)) === 'h5p') {
                return $candidate;
            }
        }
        throw new moodle_exception('h5pexportmissing', 'geniaicourseactivity_h5pfindwords');
    }

    /** Create a native mod_h5pactivity from content-type params. */
    public static function create_activity(\stdClass $course, int $sectionnum, string $name, string $intro,
            string $machinename, array $params): array {
        global $CFG, $USER;

        require_once($CFG->dirroot . '/course/modlib.php');
        require_once($CFG->dirroot . '/mod/h5pactivity/lib.php');

        $name = clean_param($name, PARAM_TEXT);
        if ($name === '') {
            $name = $machinename;
        }

        $draftitemid = file_get_unused_draft_itemid();
        $usercontext = context_user::instance($USER->id);
        $filename = clean_param(str_replace(['.', ' '], ['-', '-'], strtolower($machinename)) . '-' .
            $draftitemid . '.h5p', PARAM_FILE);

        self::create_h5p_file(
            $usercontext->id,
            'user',
            'draft',
            $draftitemid,
            $filename,
            $machinename,
            $params,
            $name,
            $USER->id
        );

        $moduleinfo = module_helper::base($course, $sectionnum, 'h5pactivity', $name, $intro);
        $moduleinfo->packagefile = $draftitemid;
        $moduleinfo->grade = 100;
        $moduleinfo->enabletracking = 1;
        $moduleinfo->grademethod = manager::GRADEHIGHESTATTEMPT;
        $moduleinfo->reviewmode = manager::REVIEWCOMPLETION;

        $factory = new factory();
        $core = $factory->get_core();
        $displayconfig = helper::decode_display_options($core);
        $moduleinfo->displayoptions = helper::get_display_options($core, $displayconfig);

        $created = add_moduleinfo($moduleinfo, $course, null);
        $cmid = (int) $created->coursemodule;

        return [
            'cmid' => $cmid,
            'name' => $name,
            'url' => (new moodle_url('/mod/h5pactivity/view.php', ['id' => $cmid]))->out(false),
            'warning' => '',
        ];
    }

    /** Create the standard nested H5P library object used by container content types. */
    public static function content_object(string $machinename, array $params, string $title,
            ?string $librarystring = null): array {
        $library = self::library($machinename, false);
        return [
            'library' => $librarystring ?? self::library_string($machinename, false),
            'params' => $params,
            'subContentId' => self::uuid(),
            'metadata' => [
                'contentType' => (string) ($library->title ?? $machinename),
                'license' => 'U',
                'title' => clean_param($title, PARAM_TEXT) ?: (string) ($library->title ?? $machinename),
            ],
        ];
    }

    /** Return the exact child library version accepted by a parent library semantics. */
    public static function parent_library_option(string $parentmachinename, string $childmachinename): ?string {
        $parent = self::library($parentmachinename, true);
        $semantics = json_decode((string) ($parent->semantics ?? ''), true);
        if (!is_array($semantics)) {
            return null;
        }
        return self::find_library_option($semantics, $childmachinename);
    }

    /** Shared source prompt formatting for H5P subplugins. */
    public static function source_prompt(\stdClass $project, \stdClass $source): string {
        $text = trim((string) $source->extractedtext);
        return "Global teacher prompt:\n" . trim((string) $project->prompt) .
            "\n\nSource filename: {$source->filename}" .
            "\nMIME type: {$source->mimetype}" .
            "\nExtension: {$source->extension}" .
            "\nTeacher instruction for this source: " . trim((string) $source->instruction) .
            "\n\nExtracted source content:\n" . ($text !== '' ? $text : '[No text was extracted from this source.]');
    }

    /** Basic semantic HTML paragraph from plain text. */
    public static function paragraph(string $text): string {
        $text = trim(strip_tags($text));
        return $text === '' ? '' : '<p>' . s($text) . '</p>';
    }

    /** Generate a RFC 4122-like UUID for H5P subContentId values. */
    public static function uuid(): string {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /** Recursively find one exact child library option in H5P semantics. */
    private static function find_library_option(array $nodes, string $childmachinename): ?string {
        foreach ($nodes as $node) {
            if (!is_array($node)) {
                continue;
            }
            if (($node['type'] ?? '') === 'library' && isset($node['options']) && is_array($node['options'])) {
                foreach ($node['options'] as $option) {
                    if (is_string($option) && str_starts_with($option, $childmachinename . ' ')) {
                        return $option;
                    }
                }
            }
            foreach (['fields', 'field'] as $key) {
                if (!isset($node[$key])) {
                    continue;
                }
                $children = $node[$key];
                if ($key === 'field' && is_array($children) && isset($children['type'])) {
                    $children = [$children];
                }
                if (is_array($children)) {
                    $result = self::find_library_option($children, $childmachinename);
                    if ($result !== null) {
                        return $result;
                    }
                }
            }
        }
        return null;
    }
}
