<?php
namespace local_geniaicourse\local;

use context_user;
use stored_file;

/**
 * Creates source records and stores uploads.
 *
 * @package local_geniaicourse
 */
class source_manager {
    private const BASE_ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'md', 'csv', 'html', 'htm',
        'png', 'jpg', 'jpeg', 'gif', 'webp',
        'odt', 'ods', 'odp',
    ];

    /** Extensions accepted by core extraction plus extensions contributed by subplugins. */
    public static function allowed_extensions(): array {
        return array_values(array_unique(array_merge(
            self::BASE_ALLOWED_EXTENSIONS,
            plugin_manager::get_source_extensions()
        )));
    }

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
