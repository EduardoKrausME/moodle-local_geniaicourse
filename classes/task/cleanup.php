<?php
namespace local_geniaicourse\task;

use local_geniaicourse\local\project_manager;

/**
 * Remove stale source projects and their uploaded files.
 *
 * @package local_geniaicourse
 */
class cleanup extends \core\task\scheduled_task {
    public function get_name(): string {
        return get_string('taskcleanup', 'local_geniaicourse');
    }

    public function execute(): void {
        global $DB;
        $cutoff = time() - (30 * DAYSECS);
        $projects = $DB->get_records_select('local_geniaicourse_project', 'timemodified < :cutoff', ['cutoff' => $cutoff]);
        foreach ($projects as $project) {
            project_manager::delete_project($project);
        }
    }
}
