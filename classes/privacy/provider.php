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

namespace local_geniaicourse\privacy;

use context;
use context_course;
use context_user;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_user_data_provider;
use core_privacy\local\request\writer;
use local_geniaicourse\project_manager;

/**
 * Privacy API provider.
 *
 * @package local_geniaicourse
 */
class provider implements
    \core_privacy\local\metadata\provider,
    core_user_data_provider {

    /**
     * Get metadata.
     *
     * @param collection $collection Parameter value.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_geniaicourse_project', [
            'userid' => 'privacy:metadata:project:userid',
            'courseid' => 'privacy:metadata:project:courseid',
            'prompt' => 'privacy:metadata:project:prompt',
        ], 'privacy:metadata:project');
        $collection->add_database_table('local_geniaicourse_source', [
            'instruction' => 'privacy:metadata:source:instruction',
            'extractedtext' => 'privacy:metadata:source:extractedtext',
            'analysisjson' => 'privacy:metadata:source:analysisjson',
        ], 'privacy:metadata:source');
        $collection->add_subsystem_link('core_files', [], 'privacy:metadata:files');
        $collection->add_external_location_link('ai_provider', [
            'prompt' => 'privacy:metadata:aibridge:prompt',
            'filename' => 'privacy:metadata:aibridge:filename',
            'instruction' => 'privacy:metadata:aibridge:instruction',
            'sourcecontent' => 'privacy:metadata:aibridge:sourcecontent',
        ], 'privacy:metadata:aibridge');
        return $collection;
    }

    /**
     * Get contexts for userid.
     *
     * @param int $userid Parameter value.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;
        $contextlist = new contextlist();
        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {local_geniaicourse_project} p ON p.courseid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextlevel AND p.userid = :userid";
        $contextlist->add_from_sql($sql, ['contextlevel' => CONTEXT_COURSE, 'userid' => $userid]);

        // Source uploads live temporarily in the user's context.
        if ($DB->record_exists('local_geniaicourse_project', ['userid' => $userid])) {
            $contextlist->add_user_context($userid);
        }
        return $contextlist;
    }

    /**
     * Export user data.
     *
     * @param approved_contextlist $contextlist Parameter value.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof context_user && $context->instanceid === $userid) {
                $projects = $DB->get_records('local_geniaicourse_project', ['userid' => $userid], 'timecreated ASC');
                foreach ($projects as $project) {
                    foreach (project_manager::get_sources($project->id) as $source) {
                        writer::with_context($context)->export_area_files([
                            get_string('pluginname', 'local_geniaicourse'),
                            'project-' . $project->id,
                            'source-' . $source->id,
                        ], 'local_geniaicourse', 'source', $source->id);
                    }
                }
                continue;
            }

            if (!$context instanceof context_course) {
                continue;
            }
            $projects = $DB->get_records('local_geniaicourse_project', [
                'courseid' => $context->instanceid,
                'userid' => $userid,
            ], 'timecreated ASC');
            foreach ($projects as $project) {
                $sources = project_manager::get_sources($project->id);
                $data = (object)[
                    'prompt' => $project->prompt,
                    'status' => $project->status,
                    'timecreated' => $project->timecreated,
                    'sources' => array_values(array_map(static function ($source) {
                        return [
                            'filename' => $source->filename,
                            'instruction' => $source->instruction,
                            'extractedtext' => $source->extractedtext,
                            'analysis' => json_decode((string)$source->analysisjson, true),
                        ];
                    }, $sources)),
                ];
                writer::with_context($context)->export_data([
                    get_string('pluginname', 'local_geniaicourse'),
                    'project-' . $project->id,
                ], $data);
            }
        }
    }

    /**
     * Delete data for all users in context.
     *
     * @param context $context Parameter value.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;
        if ($context instanceof context_course) {
            $projects = $DB->get_records('local_geniaicourse_project', ['courseid' => $context->instanceid]);
        } else if ($context instanceof context_user) {
            $projects = $DB->get_records('local_geniaicourse_project', ['userid' => $context->instanceid]);
        } else {
            return;
        }
        foreach ($projects as $project) {
            project_manager::delete_project($project);
        }
    }

    /**
     * Delete data for user.
     *
     * @param approved_contextlist $contextlist Parameter value.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        $projects = [];
        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof context_course) {
                foreach ($DB->get_records('local_geniaicourse_project', [
                    'courseid' => $context->instanceid,
                    'userid' => $userid,
                ]) as $project) {
                    $projects[$project->id] = $project;
                }
            } else if ($context instanceof context_user && $context->instanceid === $userid) {
                foreach ($DB->get_records('local_geniaicourse_project', ['userid' => $userid]) as $project) {
                    $projects[$project->id] = $project;
                }
            }
        }
        foreach ($projects as $project) {
            project_manager::delete_project($project);
        }
    }
}
