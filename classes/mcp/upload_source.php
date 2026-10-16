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
 * Append an authorized Base64 file to a GeniAI project.
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse\mcp;

use local_geniaicourse\project_manager;
use local_geniaicourse\source_manager;
use local_mcp\course\activity_upload;
use local_mcp\security\authenticated_identity;
use moodle_exception;

/**
 * Use the native source extractor without trusting remote URLs or arbitrary paths.
 */
final class upload_source extends write_project_base {
    /** Maximum decoded binary content for a single MCP request. */
    public const MAX_BYTES = 8388608;

    /**
     * @return string
     */
    public function get_name(): string {
        return "geniaicourse_upload_source";
    }

    /**
     * @return string
     */
    public function get_title(): string {
        return get_string("mcpuploadsource", "local_geniaicourse");
    }

    /**
     * @return string
     */
    public function get_description(): string {
        return get_string("mcpuploadsourcedesc", "local_geniaicourse");
    }

    /**
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            "projectid" => ["type" => "integer", "minimum" => 1],
            "expected_state_hash" => ["type" => "string", "pattern" => "^[a-f0-9]{64}$"],
            "filename" => ["type" => "string", "minLength" => 5, "maxLength" => 255],
            "file_base64" => ["type" => "string", "minLength" => 1,
                "description" => "Raw Base64 or data URI, maximum decoded size 8 MiB."],
            "instruction" => ["type" => "string", "maxLength" => 4000],
        ], ["projectid", "expected_state_hash", "filename", "file_base64"]);
    }

    /**
     * Validate without storing or extracting file content.
     *
     * @param array $arguments Tool arguments.
     * @param authenticated_identity $identity User.
     * @return array
     */
    public function preview(array $arguments, authenticated_identity $identity): array {
        global $CFG;
        $project = access::project((int)$arguments["projectid"], $identity);
        project_state::require_editable($project);
        project_state::require_state(project_state::inspect($project),
            (string)$arguments["expected_state_hash"]);
        if (strlen((string)($arguments["instruction"] ?? "")) > 4000) {
            throw new moodle_exception("mcpinvalidinstruction", "local_geniaicourse");
        }
        [$filename, $bytes] = activity_upload::decode((string)$arguments["file_base64"],
            (string)$arguments["filename"]);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, source_manager::allowed_extensions(), true)) {
            throw new moodle_exception("unsupportedfile", "local_geniaicourse", "", $filename);
        }
        $course = get_course((int)$project->courseid);
        $maxupload = get_max_upload_file_size($CFG->maxbytes, $course->maxbytes ?? 0);
        if (strlen($bytes) > self::MAX_BYTES ||
                ($maxupload > 0 && strlen($bytes) > $maxupload)) {
            throw new moodle_exception("mcpfilelimit", "local_geniaicourse");
        }
        return [
            "operation" => $this->get_name(),
            "projectid" => (int)$project->id,
            "filename" => $filename,
            "size_bytes" => strlen($bytes),
            "sha256" => hash("sha256", $bytes),
            "will_extract_and_store_source" => true,
            "will_call_ai" => false,
        ];
    }

    /**
     * @param array $arguments Tool arguments.
     * @param authenticated_identity $identity User.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        global $DB;
        $this->preview($arguments, $identity);
        $project = access::project((int)$arguments["projectid"], $identity);
        [$filename, $bytes] = activity_upload::decode((string)$arguments["file_base64"],
            (string)$arguments["filename"]);
        $tmp = tempnam(make_temp_directory("geniaicourse_mcp"), "source_");
        if ($tmp === false || file_put_contents($tmp, $bytes) !== strlen($bytes)) {
            throw new moodle_exception("mcpfilelimit", "local_geniaicourse");
        }
        try {
            $source = source_manager::create_upload(
                (int)$project->id,
                $identity->userid,
                ["name" => $filename, "tmp_name" => $tmp, "error" => UPLOAD_ERR_OK,
                    "size" => strlen($bytes)],
                (string)($arguments["instruction"] ?? "")
            );
        } finally {
            if (is_file($tmp)) {
                unlink($tmp);
            }
        }
        project_manager::set_status((int)$project->id, "draft");
        return [
            "projectid" => (int)$project->id,
            "sourceid" => (int)$source->id,
            "filename" => $filename,
            "extracted_chars" => \core_text::strlen((string)$source->extractedtext),
            "next_tool" => "geniaicourse_get_project",
        ];
    }
}
