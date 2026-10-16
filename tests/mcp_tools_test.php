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
 * Tests for GeniAI MCP project scoping and confirmed operations.
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse;

use advanced_testcase;
use context_course;
use local_geniaicourse\mcp\project_state;
use local_geniaicourse\mcp\publish_service;
use local_geniaicourse\mcp\get_project;
use local_geniaicourse\mcp\create_project;
use local_geniaicourse\mcp\analyse_source;
use local_geniaicourse\mcp\publish_activities;
use moodle_exception;

/**
 * Separate snapshot validation from Moodle and AI side effects.
 *
 * @covers \local_geniaicourse\mcp\project_state
 * @covers \local_geniaicourse\mcp\publish_service
 * @covers \local_geniaicourse\mcp\get_project
 * @covers \local_geniaicourse\mcp\create_project
 * @covers \local_geniaicourse\mcp\analyse_source
 * @covers \local_geniaicourse\mcp\publish_activities
 */
final class mcp_tools_test extends advanced_testcase {
    /**
     * @return array{object, object, object}
     */
    private function fixture(): array {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $project = project_manager::create((int)$course->id, (int)$this->getUser()->id, "Create a page");
        $source = source_manager::create_text((int)$project->id, "Explain algorithms to beginners");
        return [$course, $project, $source];
    }

    /**
     * @return void
     */
    private function require_mcp(): void {
        if (!class_exists(\local_mcp\security\authenticated_identity::class)) {
            $this->markTestSkipped("local_mcp is an optional plugin and is not installed.");
        }
    }

    /**
     * A source analysis changes the optimistic concurrency hash.
     *
     * @return void
     */
    public function test_snapshot_invalidation_after_saved_analysis(): void {
        global $DB;
        $this->resetAfterTest();
        [$course, $project, $source] = $this->fixture();
        $before = project_state::inspect($project);
        $this->assertSame(1, $before["total_sources"]);
        $this->assertFalse($before["sources"][0]["analysed"]);
        source_manager::save_analysis($source, [
            "page" => ["match" => true, "confidence" => 90, "title" => "Intro", "summary" => ""],
        ]);
        $after = project_state::inspect($project);
        $this->assertTrue($after["sources"][0]["analysed"]);
        $this->assertNotSame($before["state_hash"], $after["state_hash"]);
        $this->expectException(moodle_exception::class);
        project_state::require_state($after, $before["state_hash"]);
    }

    /**
     * Once publication started, a project cannot be reused.
     *
     * @return void
     */
    public function test_created_project_cannot_be_edited_again(): void {
        $this->resetAfterTest();
        [$course, $project, $source] = $this->fixture();
        project_manager::set_status((int)$project->id, "created");
        $project = project_manager::get_owned((int)$project->id, (int)$this->getUser()->id);
        $this->expectException(moodle_exception::class);
        project_state::require_editable($project);
    }

    /**
     * A confirmed preview must not create projects, call AI, or create modules.
     *
     * @return void
     */
    public function test_preview_and_read_have_no_side_effects(): void {
        global $DB;
        $this->resetAfterTest();
        $this->require_mcp();
        [$course, $project, $source] = $this->fixture();
        $identity = new \local_mcp\security\authenticated_identity(
            (int)$this->getUser()->id, "oauth", ["read", "write"]
        );
        $count = $DB->count_records("local_geniaicourse_project");
        $modules = $DB->count_records("course_modules");
        $preview = (new create_project())->preview([
            "courseid" => (int)$course->id,
            "prompt" => "Draft another page",
        ], $identity);
        $this->assertFalse($preview["will_call_ai"]);
        $read = (new get_project())->execute(["projectid" => (int)$project->id], $identity);
        $this->assertSame(1, $read["total_sources"]);
        $analysispreview = (new analyse_source())->preview([
            "projectid" => (int)$project->id,
            "sourceid" => (int)$source->id,
            "expected_state_hash" => $read["state_hash"],
        ], $identity);
        $this->assertTrue($analysispreview["may_consume_ai_credits"]);
        $this->assertSame($count, $DB->count_records("local_geniaicourse_project"));
        $this->assertSame($modules, $DB->count_records("course_modules"));
        $stored = $DB->get_record("local_geniaicourse_source",
            ["id" => (int)$source->id], "*", MUST_EXIST);
        $this->assertNull($stored->analysisjson);
    }

    /**
     * Publish preview never adds native Moodle modules.
     *
     * @return void
     */
    public function test_publish_preview_is_side_effect_free(): void {
        global $DB;
        $this->resetAfterTest();
        $this->require_mcp();
        [$course, $project, $source] = $this->fixture();
        source_manager::save_analysis($source, [
            "page" => ["match" => true, "confidence" => 95, "title" => "Algorithms",
                "summary" => "Introduction", "content_html" => "<p>Introduction</p>"],
        ]);
        $identity = new \local_mcp\security\authenticated_identity(
            (int)$this->getUser()->id, "oauth", ["read", "write"]
        );
        $snapshot = (new get_project())->execute(["projectid" => (int)$project->id], $identity);
        $count = $DB->count_records("course_modules");
        $preview = (new publish_activities())->preview([
            "projectid" => (int)$project->id,
            "expected_state_hash" => $snapshot["state_hash"],
            "selections" => [["sourceid" => (int)$source->id, "plugin" => "page", "section" => 0]],
        ], $identity);
        $this->assertTrue($preview["will_create_moodle_activities_or_questions"]);
        $this->assertSame($count, $DB->count_records("course_modules"));
        $this->assertSame("draft", $DB->get_field("local_geniaicourse_project",
            "status", ["id" => (int)$project->id]));
    }

    /**
     * Project privacy still applies when the connector user differs from $USER.
     *
     * @return void
     */
    public function test_foreign_project_is_denied(): void {
        $this->resetAfterTest();
        $this->require_mcp();
        [$course, $project, $source] = $this->fixture();
        $other = $this->getDataGenerator()->create_user();
        $context = context_course::instance((int)$course->id);
        $this->assertFalse(has_capability("local/geniaicourse:use", $context, $other));
        $identity = new \local_mcp\security\authenticated_identity(
            (int)$other->id, "oauth", ["read", "write"]
        );
        $this->expectException(\dml_missing_record_exception::class);
        (new get_project())->execute(["projectid" => (int)$project->id], $identity);
    }
}
