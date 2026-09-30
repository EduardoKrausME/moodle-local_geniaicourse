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

namespace local_geniaicourse;

use context_user;
use stored_file;

/**
 * Creates source records and stores uploads.
 *
 * @package local_geniaicourse
 */
class source_manager {
    /**
     * BASE ALLOWED EXTENSIONS.
     */
    private const BASE_ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'md', 'csv', 'html', 'htm',
        'png', 'jpg', 'jpeg', 'gif', 'webp',
        'odt', 'ods', 'odp',
    ];

    /** Extensions accepted by core extraction plus extensions contributed by subplugins. */
 * Allowed extensions.
 *
    /**
     * Allowed extensions.
     *
     * @return array
     */
    public static function allowed_extensions(): array {
        return array_values(array_unique(array_merge(
            self::BASE_ALLOWED_EXTENSIONS,
            plugin_manager::get_source_extensions()
        )));
    }

    /**
     * Create text.
     *
     * @param int $projectid Parameter value.
     * @param string $text Parameter value.
     * @return \stdClass
     */
    public static function create_text(int $projectid, string $text): \stdClass {
        global $DB;
        $record = (object) [
            'projectid' => $projectid,
            'sourcetype' => 'text',
            'filename' => 'prompt.txt',
            'mimetype' => 'text/plain',
            'extension' => 'txt',
            'instruction' => get_string('prompt', 'local_geniaicourse'),
            'extractedtext' => $text,
            'metadatajson' => json_encode(['origin' => 'prompt'], JSON_UNESCAPED_UNICODE),
            'analysisjson' => null,
            'timecreated' => time(),
        ];
        $record->id = $DB->insert_record('local_geniaicourse_source', $record);
        return $record;
    }

    /**
     * Create upload.
     *
     * @param int $projectid Parameter value.
     * @param int $userid Parameter value.
     * @param array $upload Parameter value.
     * @param string $instruction Parameter value.
     * @return \stdClass
     */
    public static function create_upload(int $projectid, int $userid, array $upload, string $instruction): \stdClass {
        global $DB;

        $filename = clean_param(basename($upload['name']), PARAM_FILE);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, self::allowed_extensions(), true)) {
            throw new \moodle_exception('unsupportedfile', 'local_geniaicourse', '', $filename);
        }
        if (($upload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || empty($upload['tmp_name'])) {
            throw new \moodle_exception('uploaderror', 'local_geniaicourse', '', $filename);
        }

        $mimetype = (string) mimeinfo('type', $filename);
        $record = (object) [
            'projectid' => $projectid,
            'sourcetype' => 'file',
            'filename' => $filename,
            'mimetype' => $mimetype,
            'extension' => $extension,
            'instruction' => trim($instruction),
            'extractedtext' => '',
            'metadatajson' => null,
            'analysisjson' => null,
            'timecreated' => time(),
        ];
        $record->id = $DB->insert_record('local_geniaicourse_source', $record);

        $context = context_user::instance($userid);
        $fs = get_file_storage();
        $filerecord = [
            'contextid' => $context->id,
            'component' => 'local_geniaicourse',
            'filearea' => 'source',
            'itemid' => $record->id,
            'filepath' => '/',
            'filename' => $filename,
            'mimetype' => $mimetype,
        ];
        $storedfile = $fs->create_file_from_pathname($filerecord, $upload['tmp_name']);

        $extraction = plugin_manager::process_source($storedfile, $extension) ?? extractor::extract($storedfile, $extension);
        $record->extractedtext = $extraction['text'];
        $record->metadatajson = json_encode($extraction['metadata'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $DB->update_record('local_geniaicourse_source', $record);
        return $record;
    }

    /**
     * Get stored file.
     *
     * @param \stdClass $source Parameter value.
     * @param ?int $userid Parameter value.
     * @return ?stored_file
     */
    public static function get_stored_file(\stdClass $source, ?int $userid = null): ?stored_file {
        global $DB;

        if ($source->sourcetype !== 'file') {
            return null;
        }
        if ($userid === null) {
            $userid = (int) $DB->get_field(
                'local_geniaicourse_project',
                'userid',
                ['id' => $source->projectid],
                MUST_EXIST
            );
        }
        $context = context_user::instance($userid);
        $fs = get_file_storage();
        return $fs->get_file($context->id, 'local_geniaicourse', 'source', $source->id, '/', $source->filename) ?: null;
    }

    /**
     * Save analysis.
     *
     * @param \stdClass $source Parameter value.
     * @param array $analysis Parameter value.
     * @return void
     */
    public static function save_analysis(\stdClass $source, array $analysis): void {
        global $DB;
        $DB->set_field(
            'local_geniaicourse_source',
            'analysisjson',
            json_encode($analysis, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ['id' => $source->id]
        );
    }
}
