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

namespace local_geniaicourse\task;

use core\task\scheduled_task;
use local_geniaicourse\project_manager;

/**
 * Remove stale source projects and their uploaded files.
 *
 * @package local_geniaicourse
 */
class cleanup extends scheduled_task {
    /**
     * Get name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('taskcleanup', 'local_geniaicourse');
    }

    /**
     * Execute.
     *
     * @return void
     */
    public function execute(): void {
        global $DB;
        $cutoff = time() - (30 * DAYSECS);
        $projects = $DB->get_records_select('local_geniaicourse_project', 'timemodified < :cutoff', ['cutoff' => $cutoff]);
        foreach ($projects as $project) {
            project_manager::delete_project($project);
        }
    }
}
