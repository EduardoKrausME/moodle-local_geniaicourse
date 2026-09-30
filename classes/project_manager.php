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

/**
 * Project persistence helpers.
 *
 * @package local_geniaicourse
 */
class project_manager {
    /**
     * Create.
     *
     * @param int $courseid Parameter value.
     * @param int $userid Parameter value.
     * @param string $prompt Parameter value.
     * @return \stdClass
     */
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

    /**
     * Get owned.
     *
     * @param int $projectid Parameter value.
     * @param int $userid Parameter value.
     * @return \stdClass
     */
    public static function get_owned(int $projectid, int $userid): \stdClass {
        global $DB;
        return $DB->get_record('local_geniaicourse_project', [
            'id' => $projectid,
            'userid' => $userid,
        ], '*', MUST_EXIST);
    }

    /**
     * Get sources.
     *
     * @param int $projectid Parameter value.
     * @return array
     */
    public static function get_sources(int $projectid): array {
        global $DB;
        return $DB->get_records('local_geniaicourse_source', ['projectid' => $projectid], 'id ASC');
    }

    /**
     * Set status.
     *
     * @param int $projectid Parameter value.
     * @param string $status Parameter value.
     * @return void
     */
    public static function set_status(int $projectid, string $status): void {
        global $DB;
        $DB->update_record('local_geniaicourse_project', (object) [
            'id' => $projectid,
            'status' => $status,
            'timemodified' => time(),
        ]);
    }

    /**
     * Delete project.
     *
     * @param \stdClass $project Parameter value.
     * @return void
     */
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
