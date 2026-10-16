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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Safely publish the existing subplugins' generated Moodle activities.
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse\mcp;

use context_course;
use local_geniaicourse\module_helper;
use local_geniaicourse\plugin_manager;
use local_geniaicourse\project_manager;
use local_mcp\security\authenticated_identity;
use moodle_exception;
use stdClass;
use Throwable;

/**
 * Validate full project selections and preserve the native composition flow.
 */
final class publish_service {
    /**
     * Validate all selection entries before the first Moodle activity is created.
     *
     * @param stdClass $project Project.
     * @param array $selections User-selected source/generator/section combinations.
     * @param authenticated_identity $identity Authenticated user.
     * @return array
     */
    public static function validate(stdClass $project, array $selections, authenticated_identity $identity): array {
        global $DB;
        project_state::require_editable($project);
        if (count($selections) < 1 || count($selections) > 12) {
            throw new moodle_exception("mcpinvalidselection", "local_geniaicourse");
        }
        $course = access::course((int)$project->courseid, $identity);
        $sections = get_fast_modinfo($course)->get_section_info_all();
        $plugins = plugin_manager::get_plugins();
        $sources = project_manager::get_sources((int)$project->id);
        $grouped = [];
        $seen = [];
        foreach ($selections as $selection) {
            if (!is_array($selection) || !isset($selection["sourceid"], $selection["plugin"], $selection["section"])) {
                throw new moodle_exception("mcpinvalidselection", "local_geniaicourse");
            }
            $id = (int)$selection["sourceid"];
            $name = (string)$selection["plugin"];
            $section = (int)$selection["section"];
            if (!isset($sources[$id], $plugins[$name]) || !array_key_exists($section, $sections)) {
                throw new moodle_exception("mcpinvalidselection", "local_geniaicourse");
            }
            $key = $id . ":" . $name;
            if (isset($seen[$key]) || (isset($grouped[$id]) && $grouped[$id]["section"] !== $section)) {
                throw new moodle_exception("mcpinvalidselection", "local_geniaicourse");
            }
            $analyses = json_decode((string)$sources[$id]->analysisjson, true);
            if (!is_array($analyses) || !isset($analyses[$name]) || !is_array($analyses[$name]) ||
                    !empty($analyses[$name]["error"])) {
                throw new moodle_exception("missinganalysis", "local_geniaicourse");
            }
            $module = plugin_manager::module_for_plugin($name);
            if ($module === null) {
                require_capability("moodle/question:add",
                    context_course::instance((int)$course->id), $identity->userid);
            } else {
                module_helper::check_can_add($course, $section, $module);
            }
            $seen[$key] = true;
            if (!isset($grouped[$id])) {
                $grouped[$id] = ["source" => $sources[$id], "section" => $section,
                    "analyses" => $analyses, "plugins" => []];
            }
            $grouped[$id]["plugins"][] = $name;
        }
        return $grouped;
    }

    /**
     * Hold an exclusive lock, and never re-run a partially completed publication.
     *
     * @param stdClass $project Project.
     * @param array $selections Explicit selections.
     * @param string $expectedhash Current hash from get_project.
     * @param authenticated_identity $identity Authenticated user.
     * @return array
     */
    public static function publish(
        stdClass $project, array $selections, string $expectedhash,
        authenticated_identity $identity
    ): array {
        global $DB;
        $factory = \core\lock\lock_config::get_lock_factory("local_geniaicourse");
        $lock = $factory->get_lock("mcp_publish_" . $project->id, 10);
        if (!$lock) {
            throw new moodle_exception("mcppublishbusy", "local_geniaicourse");
        }
        try {
            $freshproject = access::project((int)$project->id, $identity);
            project_state::require_editable($freshproject);
            project_state::require_state(project_state::inspect($freshproject), $expectedhash);
            $grouped = self::validate($freshproject, $selections, $identity);
            $course = get_course((int)$freshproject->courseid);
            project_manager::set_status((int)$freshproject->id, "creating");
            $results = [];
            try {
                foreach ($grouped as $entry) {
                    $prepared = plugin_manager::prepare_selected($entry["plugins"], $entry["analyses"]);
                    foreach ($entry["plugins"] as $plugin) {
                        if (in_array($plugin, $prepared["consumed"], true)) {
                            continue;
                        }
                        try {
                            $created = plugin_manager::create($plugin, $course, $entry["section"],
                                $entry["source"], $prepared["analyses"][$plugin]);
                            $results[] = [
                                "success" => true, "sourceid" => (int)$entry["source"]->id,
                                "plugin" => $plugin, "name" => (string)($created["name"] ?? ""),
                                "url" => (string)($created["url"] ?? ""),
                                "warning" => (string)($created["warning"] ?? ""),
                            ];
                        } catch (Throwable $error) {
                            $results[] = [
                                "success" => false, "sourceid" => (int)$entry["source"]->id,
                                "plugin" => $plugin, "error" => $error->getMessage(),
                            ];
                        }
                    }
                }
            } finally {
                project_manager::set_status((int)$freshproject->id, "created");
                rebuild_course_cache((int)$course->id, true);
            }
            return [
                "projectid" => (int)$freshproject->id,
                "courseid" => (int)$course->id,
                "status" => "created",
                "results" => $results,
                "success_count" => count(array_filter($results,
                    static fn(array $r): bool => $r["success"])),
                "failure_count" => count(array_filter($results,
                    static fn(array $r): bool => !$r["success"])),
                "not_retriable" => true,
            ];
        } finally {
            $lock->release();
        }
    }
}
