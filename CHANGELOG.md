# Changelog

This changelog documents only the two most recent **published** releases of `local_geniaicourse`, verified on October 24, 2026 (America/Sao_Paulo). Each entry reflects the changes between that release's Git tag and the preceding published tag. The `Mustache`, `Langs Order`, `Boilerplate Headers`, `-core_text` and `Order` commits after release `1.5.6` are excluded.

## [1.5.6] - 2026-10-19

### Optional Moodle MCP integration
- Added an optional auto-discovered Moodle MCP provider at `classes/mcp/provider.php` when `local_mcp` is installed, without adding a hard dependency or replacing the existing course-builder UI.
- Added four READ tools: `geniaicourse_list_generators`, `geniaicourse_list_projects`, `geniaicourse_get_project` and `geniaicourse_get_analysis`. These list available generators, inspect owned projects and read bounded stored analysis previews without modifying Moodle data or calling AI.
- Added four confirmation-gated WRITE tools: `geniaicourse_create_project` (create an owned project from a prompt), `geniaicourse_upload_source` (store an allowed Base64 source file), `geniaicourse_analyse_source` (run the configured source analyzers) and `geniaicourse_publish_activities` (publish explicitly selected activities or questions from saved analyses).
- Restricted all operations to users with `local/geniaicourse:use` and `moodle/course:manageactivities` in the relevant course; access to project data also requires ownership by the connected Moodle user.
- Limited uploaded files to 8 MiB of decoded content and Moodle's applicable course upload limit, allowed source extensions and no remote URL fetching. Confirmed AI analysis is performed per source and may consume AI credits.
- Added a project `expected_state_hash` concurrency check for uploads, analysis and publishing; publication validates selected generators and Moodle permissions, uses a lock and reports item-level successes and failures. Completed publication cannot simply be retried against the same project, reducing duplicate content creation.
- Limited publishing to explicitly reviewed selections of source, generator and section, using the existing generator/subplugin APIs rather than bypassing Moodle activity creation checks.

### Administration and documentation
- Added an optional, dismissible MCP integration notice for site administrators when `local_mcp` is missing, including a Mustache template, responsive CSS, translations and a per-user dismissal preference protected by POST and session-key checks.
- Added MCP setup, permission, safe usage and workflow documentation to `README.md`, along with regression tests for the integration.
- Added Git-ref-scoped concurrency controls to the historical GitHub release workflow.
- Updated `version.php` metadata from internal version `2026101500`, release `1.5.5`, to internal version `2026101900`, release `1.5.6`.

**Full comparison:** [1.5.5...1.5.6](https://github.com/EduardoKrausME/moodle-local_geniaicourse/compare/1.5.5...1.5.6)

## [1.5.5] - 2026-10-15

### Release metadata
- Updated `version.php` from internal version `2026101400`, release `1.5.4`, to internal version `2026101500`, release `1.5.5`.
- No other files or plugin functionality changed between these two published release tags.

**Full comparison:** [1.5.4...1.5.5](https://github.com/EduardoKrausME/moodle-local_geniaicourse/compare/1.5.4...1.5.5)
