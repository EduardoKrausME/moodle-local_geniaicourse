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
 * List currently installed, Moodle-native GeniAI activity generators.
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse\mcp;

use context;
use context_course;
use local_geniaicourse\module_helper;
use local_geniaicourse\plugin_manager;
use local_mcp\read\tool\base_tool;
use local_mcp\security\authenticated_identity;
use Throwable;

/**
 * Inform selection without running any AI or modifying Moodle state.
 */
final class list_generators extends base_tool {
    /**
     * @return string
     */
    public function get_name(): string {
        return "geniaicourse_list_generators";
    }

    /**
     * @return string
     */
    public function get_title(): string {
        return get_string("mcpgenerators", "local_geniaicourse");
    }

    /**
     * @return string
     */
    public function get_description(): string {
        return get_string("mcpgeneratorsdesc", "local_geniaicourse");
    }

    /**
     * @return string
     */
    public function get_required_capability(): string {
        return "local/geniaicourse:use";
    }

    /**
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema(["courseid" => ["type" => "integer", "minimum" => 1]], ["courseid"]);
    }

    /**
     * @param array $arguments Arguments.
     * @return context
     */
    public function resolve_context(array $arguments): context {
        get_course((int)$arguments["courseid"]);
        return context_course::instance((int)$arguments["courseid"], MUST_EXIST);
    }

    /**
     * @param array $arguments Arguments.
     * @param authenticated_identity $identity User.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        $course = access::course((int)$arguments["courseid"], $identity);
        $context = context_course::instance((int)$course->id);
        $sections = array_keys(get_fast_modinfo($course)->get_section_info_all());
        $types = [];
        foreach (plugin_manager::get_plugins() as $name => $class) {
            $module = plugin_manager::module_for_plugin($name);
            $cancreate = false;
            if ($module === null) {
                $cancreate = has_capability("moodle/question:add", $context, $identity->userid);
            } else {
                foreach ($sections as $sectionnum) {
                    try {
                        module_helper::check_can_add($course, (int)$sectionnum, $module);
                        $cancreate = true;
                        break;
                    } catch (Throwable $exception) {
                        continue;
                    }
                }
            }
            $types[] = [
                "plugin" => $name,
                "name" => $class::get_name(),
                "description" => $class::get_description(),
                "moodle_module" => $module,
                "can_create" => $cancreate,
            ];
        }
        return ["courseid" => (int)$course->id, "generators" => $types];
    }
}
