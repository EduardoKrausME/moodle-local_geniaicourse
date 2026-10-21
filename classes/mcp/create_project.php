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
 * Create a teacher-owned GeniAI project from a confirmed text prompt.
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse\mcp;

use context;
use context_course;
use local_geniaicourse\project_manager;
use local_geniaicourse\source_manager;
use local_mcp\security\authenticated_identity;
use local_mcp\write\tool\base_tool;
use moodle_exception;

/**
 * Create project and source records without calling an AI model.
 */
final class create_project extends base_tool {
    /**
     * Return the unique MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return "geniaicourse_create_project";
    }

    /**
     * Return the translated MCP tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string("mcpcreateproject", "local_geniaicourse");
    }

    /**
     * Return the translated MCP tool description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string("mcpcreateprojectdesc", "local_geniaicourse");
    }

    /**
     * Return the required Moodle course capability.
     *
     * @return string
     */
    public function get_required_capability(): string {
        return "local/geniaicourse:use";
    }

    /**
     * Declare the permitted arguments for this tool.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            "courseid" => ["type" => "integer", "minimum" => 1],
            "prompt" => ["type" => "string", "minLength" => 1, "maxLength" => 16000],
        ], ["courseid", "prompt"]);
    }

    /**
     * Resolve the requested Moodle course context.
     *
     * @param array $arguments Tool arguments.
     * @return context
     */
    public function resolve_context(array $arguments): context {
        get_course((int)$arguments["courseid"]);
        return context_course::instance((int)$arguments["courseid"], MUST_EXIST);
    }

    /**
     * Validate and preview this operation without modifying Moodle.
     *
     * @param array $arguments Tool arguments.
     * @param authenticated_identity $identity Authenticated user.
     * @return array
     */
    public function preview(array $arguments, authenticated_identity $identity): array {
        access::course((int)$arguments["courseid"], $identity);
        $prompt = (string)$arguments["prompt"];
        if (trim($prompt) === "" || strlen($prompt) > 16000) {
            throw new moodle_exception("mcpinvalidprompt", "local_geniaicourse");
        }
        return [
            "operation" => $this->get_name(),
            "courseid" => (int)$arguments["courseid"],
            "prompt_length" => strlen($prompt),
            "will_store_prompt" => true,
            "will_call_ai" => false,
            "course_content_changed" => false,
        ];
    }

    /**
     * Execute the confirmed operation with appropriate permissions.
     *
     * @param array $arguments Tool arguments.
     * @param authenticated_identity $identity Authenticated user.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        $this->preview($arguments, $identity);
        $project = project_manager::create((int)$arguments["courseid"],
            $identity->userid, (string)$arguments["prompt"]);
        source_manager::create_text((int)$project->id, (string)$arguments["prompt"]);
        return project_state::inspect($project);
    }
}
