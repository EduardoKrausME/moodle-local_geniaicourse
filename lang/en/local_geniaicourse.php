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

defined('MOODLE_INTERNAL') || die;

$string['activity'] = 'Activity';
$string['analysiserror'] = 'The AI analysis failed: {$a}';
$string['analyze'] = 'Analyze sources';
$string['analyzing'] = 'Analyzing...';
$string['backtocourse'] = 'Back to course';
$string['confidence'] = 'Confidence';
$string['create'] = 'Create selected activities';
$string['created'] = 'Created';
$string['createdtitle'] = 'Activities created';
$string['creating'] = 'Creating...';
$string['emptyairesponse'] = 'The AI provider returned an empty response.';
$string['extractionerror'] = 'Extraction error: {$a}';
$string['failed'] = 'Failed';
$string['fileinstruction'] = 'What should be done with this file?';
$string['fileinstructionplaceholder'] = 'Example: Use only this screenshot on the opening page';
$string['files'] = 'Files';
$string['files_help'] = 'Supported file formats depend on the installed activity subplugins.';
$string['geniaicourse:use'] = 'Use the GeniAI Course Builder';
$string['h5pembedfailed'] = 'Could not embed media inside the generated H5P package.';
$string['imagewarning'] = 'Image pixels are not sent to AI Bridge by this plugin. The activity analyzers use the filename, MIME type and teacher instruction.';
$string['instruction'] = 'Instruction';
$string['invalidjsonresponse'] = 'The AI provider returned invalid JSON.';
$string['invalidplugin'] = 'Unknown GeniAI activity type: {$a}';
$string['legacyofficewarning'] = 'Legacy binary Office format: text extraction is best-effort. DOCX/XLSX/PPTX is recommended.';
$string["mcpalreadyanalysed"] = "This source already has saved generator analysis; use force to replace it.";
$string["mcpanalysesource"] = "Analyse one project source";
$string["mcpanalysesourcedesc"] = "After preview and confirmation, ask every installed generator to analyse one source using local_ai_bridge. This may consume AI credits but does not create Moodle activities.";
$string["mcpcreateproject"] = "Create a GeniAI course project";
$string["mcpcreateprojectdesc"] = "Create a teacher-owned project from a text prompt and save it for subsequent source uploads and analysis, without AI calls.";
$string["mcpfilelimit"] = "The file exceeds the MCP upload or Moodle course file-size limit.";
$string["mcpgenerators"] = "List course activity generators";
$string["mcpgeneratorsdesc"] = "List installed GeniAI generators and whether the connected teacher has permissions to create the corresponding Moodle activities.";
$string["mcpgetanalysis"] = "Read saved generator analysis";
$string["mcpgetanalysisdesc"] = "Read a bounded chunk of one stored generator plan belonging to the connected teacher; no AI calls or changes.";
$string["mcpgetproject"] = "Read my GeniAI project";
$string["mcpgetprojectdesc"] = "Read project status, source metadata, saved generator summaries and concurrency hash, paginated and scoped to the owner.";
$string["mcpinvalidinstruction"] = "The file instruction is too long.";
$string["mcpinvalidprompt"] = "Enter a nonempty project prompt of at most 16000 characters.";
$string["mcpinvalidselection"] = "Select valid distinct source, generator and section combinations for this project.";
$string["mcplistprojects"] = "List my GeniAI projects";
$string["mcplistprojectsdesc"] = "List only the connected teacher's existing course projects with pagination.";
$string["mcppublish"] = "Publish selected GeniAI activities";
$string["mcppublishbusy"] = "Another publication is processing this project.";
$string["mcppublishdesc"] = "Confirm explicitly selected source and generator combinations and publish native Moodle activities or question-bank items. Publishing is non-retriable and may create multiple records.";
$string["mcpstaleproject"] = "This project was modified after it was inspected. Read it again to get a fresh state hash.";
$string["mcpuploadsource"] = "Upload source file to GeniAI project";
$string["mcpuploadsourcedesc"] = "After confirmation, append a Base64-encoded file to your project using existing Moodle file storage and extractors. Maximum decoded size 8 MiB; no URL fetching and no AI calls.";
$string['maxextractchars'] = 'Maximum extracted characters per source';
$string['maxextractchars_desc'] = 'Maximum source text sent to each activity analyzer.';
$string['maxuploadexceeded'] = 'File {$a->filename} exceeds the maximum allowed upload size of {$a->limit}.';
$string['missinganalysis'] = 'The selected activity type does not have a saved analysis for this source.';
$string['multipleselectionhint'] = 'You can select one or several activity types for this source. Consumer subplugins may combine selected activity types into a single generated activity.';
$string['navtitle'] = 'Build with AI';
$string['newanalysis'] = 'Start another analysis';
$string['noextractorwarning'] = 'No text extractor is available for this file type.';
$string['nosources'] = 'Enter a prompt or upload at least one file.';
$string['notsuggested'] = 'Not suggested';
$string['pdffallbackwarning'] = 'pdftotext was unavailable or returned no text; internal PDF extraction was used and may be incomplete.';
$string['pdfscannedwarning'] = 'The PDF may be scanned or image-only.';
$string['pdftotextpath'] = 'pdftotext executable';
$string['pdftotextpath_desc'] = 'Path to pdftotext. If unavailable, a best-effort internal PDF text extractor is used.';
$string['pluginname'] = 'GeniAI Course Builder';
$string['preview'] = 'Extracted preview';
$string['privacy:metadata:aibridge'] = 'Source material is sent through local_ai_bridge to the AI provider selected by the configured route so the installed activity subplugins can analyse it.';
$string['privacy:metadata:aibridge:filename'] = 'The uploaded source filename, MIME type and extension are sent for analysis.';
$string['privacy:metadata:aibridge:instruction'] = 'The teacher instruction associated with each source is sent for analysis.';
$string['privacy:metadata:aibridge:prompt'] = 'The global teacher prompt is sent for analysis.';
$string['privacy:metadata:aibridge:sourcecontent'] = 'Text extracted from the uploaded or pasted source is sent for analysis.';
$string['privacy:metadata:files'] = 'Uploaded source files are stored temporarily in the user context.';
$string['privacy:metadata:project'] = 'Stores AI course building projects created by a user.';
$string['privacy:metadata:project:courseid'] = 'The course where the project is being created.';
$string['privacy:metadata:project:prompt'] = 'The prompt entered by the user.';
$string['privacy:metadata:project:userid'] = 'The user who created the project.';
$string['privacy:metadata:source'] = 'Stores uploaded files, instructions and extracted source text.';
$string['privacy:metadata:source:analysisjson'] = 'AI analysis returned by installed subplugins.';
$string['privacy:metadata:source:extractedtext'] = 'Text extracted from the uploaded source.';
$string['privacy:metadata:source:instruction'] = 'The per-file instruction entered by the user.';
$string['projectalreadycreated'] = 'This analysis has already been processed. Start a new analysis to create another set of activities.';
$string['prompt'] = 'Prompt / pasted text';
$string['prompt_help'] = 'Describe what should be created. You can also paste the source text directly here.';
$string['reason'] = 'Reason';
$string['reviewsubtitle'] = 'Select one or more Moodle activities for each source. Leave all options unchecked to skip a source.';
$string['reviewtitle'] = 'Review AI suggestions';
$string['section'] = 'Course section';
$string['skip'] = 'Do not create anything';
$string['source'] = 'Source';
$string['subplugintype_geniaicourseactivity'] = 'GeniAI Course activity';
$string['subplugintype_geniaicourseactivity_plural'] = 'GeniAI Course activities';
$string['subtitle'] = 'Describe what you want and attach source files. Each installed activity subplugin analyses every source before anything is created.';
$string['suggested'] = 'Suggested';
$string['suggestedtitle'] = 'Suggested title';
$string['summary'] = 'Summary';
$string['taskcleanup'] = 'Clean old GeniAI course builder projects';
$string['title'] = 'Build course content with AI';
$string['truncatedwarning'] = 'Extracted text was truncated to {$a} characters for AI analysis.';
$string['unsupportedfile'] = 'Unsupported file type: {$a}';
$string['uploaderror'] = 'Could not upload {$a}.';
