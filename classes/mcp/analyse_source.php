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
 * Confirm AI analysis of one GeniAI project source.
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse\mcp;

use local_geniaicourse\plugin_manager;
use local_geniaicourse\project_manager;
use local_geniaicourse\source_manager;
use local_mcp\security\authenticated_identity;
use moodle_exception;

/**
 * Analyse a single source per call to bound external AI usage.
 */
final class analyse_source extends write_project_base {
    /**
     * Return the unique MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return "geniaicourse_analyse_source";
    }

    /**
     * Return the translated MCP tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string("mcpanalysesource", "local_geniaicourse");
    }

    /**
     * Return the translated MCP tool description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string("mcpanalysesourcedesc", "local_geniaicourse");
    }

    /**
     * Declare the permitted arguments for this tool.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema(project_state::input() + [
            "sourceid" => ["type" => "integer", "minimum" => 1],
            "force" => ["type" => "boolean"],
        ], ["projectid", "sourceid", "expected_state_hash"]);
    }

    /**
     * Check source ownership, project state and confirmation metadata.
     *
     * @param array $arguments Tool arguments.
     * @param authenticated_identity $identity User.
     * @return array
     */
    public function preview(array $arguments, authenticated_identity $identity): array {
        global $DB;
        $project = access::project((int)$arguments["projectid"], $identity);
        project_state::require_editable($project);
        project_state::require_state(project_state::inspect($project),
            (string)$arguments["expected_state_hash"]);
        $source = $DB->get_record("local_geniaicourse_source", [
            "id" => (int)$arguments["sourceid"], "projectid" => (int)$project->id,
        ], "id,filename,analysisjson", MUST_EXIST);
        if ($source->analysisjson !== null && empty($arguments["force"])) {
            throw new moodle_exception("mcpalreadyanalysed", "local_geniaicourse");
        }
        return [
            "operation" => $this->get_name(),
            "projectid" => (int)$project->id,
            "sourceid" => (int)$source->id,
            "filename" => $source->filename,
            "overwrite_existing_analysis" => $source->analysisjson !== null,
            "generator_count" => count(plugin_manager::get_plugins()),
            "may_consume_ai_credits" => true,
            "will_create_activities" => false,
        ];
    }

    /**
     * Execute the confirmed operation with appropriate permissions.
     *
     * @param array $arguments Tool arguments.
     * @param authenticated_identity $identity User.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        global $DB;
        $this->preview($arguments, $identity);
        $project = access::project((int)$arguments["projectid"], $identity);
        $source = $DB->get_record("local_geniaicourse_source", [
            "id" => (int)$arguments["sourceid"], "projectid" => (int)$project->id,
        ], "*", MUST_EXIST);
        $analysis = plugin_manager::analyse_source($project, $source);
        source_manager::save_analysis($source, $analysis);
        $sources = project_manager::get_sources((int)$project->id);
        $allanalysed = true;
        foreach ($sources as $item) {
            if ($item->analysisjson === null && (int)$item->id !== (int)$source->id) {
                $allanalysed = false;
                break;
            }
        }
        project_manager::set_status((int)$project->id, $allanalysed ? "analysed" : "draft");
        return [
            "projectid" => (int)$project->id,
            "sourceid" => (int)$source->id,
            "analysed_plugins" => count($analysis),
            "matches" => array_values(array_keys(array_filter($analysis,
                static fn(array $row): bool => !empty($row["match"]) && empty($row["error"])))),
            "next_tool" => "geniaicourse_get_project",
        ];
    }
}
