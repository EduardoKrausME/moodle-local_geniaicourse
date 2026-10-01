# GeniAI Course Builder (`local_geniaicourse`)

Local Moodle plugin that receives a teacher prompt plus uploaded source files, extracts their content, asks every
installed activity subplugin to analyse each source, lets the teacher choose the final Moodle activity type, and then
creates native Moodle activities.

## Included activity subplugins

- `geniaicourseactivity_page`: analyses and creates native Moodle Page resources.
- `geniaicourseactivity_forum`: analyses and creates native Moodle Forum activities.
- `geniaicourseactivity_quiz`: creates a native Moodle Quiz, creates native question-bank items, and adds them to Quiz
  slots.
- `geniaicourseactivity_questions`: creates reusable native Moodle question-bank items without creating a Quiz activity.
- `geniaicourseactivity_lesson`: creates a native Moodle Lesson with an ordered sequence of content pages.
- `geniaicourseactivity_assignment`: creates a native Moodle Assignment and enables online text/file submission
  according to the analysis.
- `geniaicourseactivity_h5pinteractivevideo`: creates H5P Interactive Video from an uploaded video or explicit accepted
  video URL.
- `geniaicourseactivity_h5pfindwords`: creates H5P Find the Words from source vocabulary.
- `geniaicourseactivity_h5pcrossword`: creates H5P Crossword from source-grounded clues and answers.
- `geniaicourseactivity_h5pdragdrop`: creates H5P Drag and Drop mappings.
- `geniaicourseactivity_h5pflashcards`: creates H5P Flashcards for retrieval practice.
- `geniaicourseactivity_h5pinteractivebook`: creates an H5P Interactive Book and can consume selected H5P child
  subplugins as pages inside the book.

Question Bank and Quiz currently generate native `multichoice`, `truefalse`, `shortanswer`, and `essay` questions
through Moodle's own GIFT importer.

Subplugins are discovered through `db/subplugins.json`. Normal activity plugins
implement `\local_geniaicourse\activity\activity_interface`. Reusable generators
implement `\local_geniaicourse\activity\composable_content_interface`. Each implementation declares a composable family;
the bundled H5P generators declare `h5p`, so container plugins such as Interactive Book can reuse exactly the same
generated H5P content without treating unrelated future composable subplugins as H5P.

## Flow

1. Teacher opens **Build with AI** inside a course.
2. Teacher writes/pastes text into the prompt and optionally uploads multiple files.
3. JavaScript creates one instruction field for every selected `files[]` item.
4. Files are stored temporarily in the teacher's user context.
5. The core extracts source text and metadata.
6. Every installed `geniaicourseactivity_*` subplugin receives every source and independently returns its
   decision (`match`, confidence, reason, title, summary and plugin-specific plan).
7. The review screen shows all subplugin decisions. The teacher can select one or several activity types for each
   source. Leaving all checkboxes empty skips that source.
8. Only then are native Moodle activities created with `add_moduleinfo()`.
9. Temporary projects older than 30 days are removed by scheduled task.

## Source formats

Good extraction:

- TXT, Markdown, CSV, HTML
- DOCX
- XLSX
- PPTX
- ODT, ODS, ODP

PDF:

- Uses `pdftotext` when configured and executable.
- Falls back to an internal best-effort parser for text-based PDFs.
- Scanned or image-only PDFs need OCR before they are supplied to the plugin.

Legacy Office:

- DOC, XLS and PPT are accepted using best-effort binary string extraction. Prefer DOCX/XLSX/PPTX.

Images:

- PNG/JPG/GIF/WebP are stored and can be embedded by the Page subplugin.
- `local_geniai`'s current `chatgpt::completions()` API is text-only, so image pixels are not sent to OpenAI.
  Classification uses filename, MIME type and the teacher's instruction.

Videos:

- MP4, WebM, OGV and M4V are accepted as source files.
- Video bytes are not sent to the AI service. The Interactive Video analyzer uses filename/type, teacher instructions
  and any supplied transcript/timestamps.
- Interactive Video also accepts an explicit YouTube URL or accepted direct video-file URL.

## Office to PDF conversion

When an Office/ODF file instruction explicitly requests PDF, the Page subplugin attempts conversion with headless
LibreOffice. Configure the executable path under:

Site administration → Plugins → Local plugins → GeniAI Course Builder

Default: `/usr/bin/soffice`.

If LibreOffice is unavailable or conversion fails, the original file is attached to the Page and the teacher sees a
warning in the creation result.

## Privacy / external processing

The plugin stores project metadata, extracted text and temporary source uploads until cleanup. During analysis it sends
the global prompt, source filename/type, per-file instruction and extracted source text through `local_geniai` to the
configured OpenAI service. The Privacy API declares this external processing and exports/deletes both project data and
temporary uploaded files.

## Multiple activity selection

The review screen uses independent checkboxes. A teacher can select one or several activity subplugins for each source.
Normally each selected subplugin creates its own Moodle activity in the chosen course section.

Interactive Book is the exception by design. It dynamically discovers sibling subplugins that
implement `composable_content_interface` and declare the `h5p` family. When the book is selected together with any of
those H5P generators, those selections are consumed by the book and are not created a second time as standalone
activities. If the teacher selects only Interactive Book, the book automatically uses compatible sibling H5P analyses
that returned `match=true`.

The book uses `H5P.Column` pages. The builder resolves the exact `H5P.Column` library level expected by the
installed Interactive Book and validates child libraries against that Column semantics before nesting them
directly. Types not accepted by Column are generated with Moodle's `core_h5p\editor`, stored in a course-scoped plugin
filearea and embedded through `H5P.IFrameEmbed`; these iframe children remain functional but their own score is not
rolled up into the Interactive Book summary.

The H5P creators use the content-type libraries installed in Moodle and create native `mod_h5pactivity` activities.

## Subplugin isolation

The core local plugin only orchestrates sources, AI calls, persistence, extraction, installed subplugin discovery and
generic Moodle-module scaffolding. Activity-specific analysis and creation rules live in each `activity/<name>`
subplugin. H5P runtime adapters, generated H5P/video file serving, Page conversion rules, Question Bank builders, and
Interactive Book composition live in their owning subplugins. Consumer behavior such as Interactive Book composing
sibling selections is declared by the consumer subplugin through `selection_consumer_interface`; `create.php` does not
know concrete subplugin names.
