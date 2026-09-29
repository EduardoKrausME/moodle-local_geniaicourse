<?php
namespace local_geniaicourse\local;

use context_user;

/**
 * Project persistence helpers.
 *
 * @package local_geniaicourse
 */
class project_manager {
    public static function create(int $courseid, int $userid, string $prompt): \stdClass {
        global $DB;
        $now = time();
        $record = (object) [
            'courseid' => $courseid,
            'userid' => $userid,
            'prompt' => $prompt,
            'status' => 'draft',
            'analysisjson' => null,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $record->id = $DB->insert_record('local_geniaicourse_project', $record);
        return $record;
    }

    public static function get_owned(int $projectid, int $userid): \stdClass {
        global $DB;
        return $DB->get_record('local_geniaicourse_project', [
            'id' => $projectid,
            'userid' => $userid,
        ], '*', MUST_EXIST);
    }

    public static function get_sources(int $projectid): array {
        global $DB;
        return $DB->get_records('local_geniaicourse_source', ['projectid' => $projectid], 'id ASC');
    }

    public static function set_status(int $projectid, string $status): void {
        global $DB;
        $DB->update_record('local_geniaicourse_project', (object) [
            'id' => $projectid,
            'status' => $status,
            'timemodified' => time(),
        ]);
    }

    public static function delete_project(\stdClass $project): void {
        global $DB;
        $sources = self::get_sources($project->id);
        $fs = get_file_storage();
        $usercontext = context_user::instance($project->userid, IGNORE_MISSING);
        if ($usercontext) {
            foreach ($sources as $source) {
                $fs->delete_area_files($usercontext->id, 'local_geniaicourse', 'source', $source->id);
            }
        }
        $DB->delete_records('local_geniaicourse_source', ['projectid' => $project->id]);
        $DB->delete_records('local_geniaicourse_project', ['id' => $project->id]);
    }
}
