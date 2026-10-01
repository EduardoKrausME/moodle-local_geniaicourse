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
 * quiz GeniAI Course activity subplugin.
 *
 * @package geniaicourseactivity_quiz
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace geniaicourseactivity_quiz;

use context_course;
use core_question\category_manager;
use core_text;
use moodle_exception;
use moodle_url;
use qformat_gift;
use stdClass;
use Throwable;

/**
 * Creates native Moodle question bank questions using Moodle's GIFT importer.
 *
 * @package geniaicourseactivity_quiz
 */
class question_builder {
    /** @var string[] Question types accepted from AI subplugins. */
    private const ALLOWED_TYPES = ['multichoice', 'truefalse', 'shortanswer', 'essay'];

    /**
     * Normalise AI question structures before they are handed to Moodle.
     *
     * @param array $questions
     * @return array
     */
    public static function normalize(array $questions): array {
        $result = [];
        foreach (array_slice($questions, 0, 50) as $index => $question) {
            if (!is_array($question)) {
                continue;
            }
            $type = strtolower(trim((string)($question['type'] ?? 'multichoice')));
            $aliases = [
                'multiplechoice' => 'multichoice',
                'multiple_choice' => 'multichoice',
                'multiple-choice' => 'multichoice',
                'mcq' => 'multichoice',
                'true_false' => 'truefalse',
                'true-false' => 'truefalse',
                'tf' => 'truefalse',
                'short_answer' => 'shortanswer',
                'short-answer' => 'shortanswer',
                'open' => 'essay',
                'openended' => 'essay',
                'open_ended' => 'essay',
            ];
            $type = $aliases[$type] ?? $type;
            if (!in_array($type, self::ALLOWED_TYPES, true)) {
                $type = 'multichoice';
            }

            $text = trim((string)($question['question'] ?? $question['text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $name = trim((string)($question['name'] ?? ''));
            if ($name === '') {
                $name = get_string('questiondefaultname', 'geniaicourseactivity_quiz', $index + 1);
            }

            $normalized = [
                'type' => $type,
                'name' => clean_param(core_text::substr(strip_tags($name), 0, 200), PARAM_TEXT),
                'question' => clean_text($text, FORMAT_HTML),
                'generalfeedback' => clean_text((string)($question['generalfeedback'] ?? ''), FORMAT_HTML),
                'defaultmark' => max(0.01, min(100, (float)($question['defaultmark'] ?? 1))),
            ];

            if ($type === 'multichoice') {
                $normalized['answers'] = self::normalize_multichoice_answers($question);
                if (count($normalized['answers']) < 2) {
                    continue;
                }
            } else if ($type === 'truefalse') {
                $correct = $question['correct'] ?? $question['answer'] ?? true;
                if (is_string($correct)) {
                    $parsed = filter_var($correct, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if ($parsed === null) {
                        $parsed = in_array(strtolower(trim($correct)), ['verdadeiro', 'true', 'v', 'yes', 'sim', '1'], true);
                    }
                    $correct = $parsed;
                }
                $normalized['correct'] = (bool)$correct;
                $normalized['feedbacktrue'] = clean_text((string)($question['feedbacktrue'] ?? ''), FORMAT_HTML);
                $normalized['feedbackfalse'] = clean_text((string)($question['feedbackfalse'] ?? ''), FORMAT_HTML);
            } else if ($type === 'shortanswer') {
                $accepted = $question['accepted_answers'] ?? $question['acceptedanswers'] ?? $question['answers'] ?? [];
                if (is_string($accepted)) {
                    $accepted = [$accepted];
                }
                $answers = [];
                foreach ((array)$accepted as $answer) {
                    if (is_array($answer)) {
                        $isanswercorrect = $answer['correct'] ?? true;
                        if (!$isanswercorrect) {
                            continue;
                        }
                        $answer = $answer['text'] ?? $answer['answer'] ?? '';
                    }
                    $answer = trim(strip_tags((string)$answer));
                    if ($answer !== '') {
                        $answers[] = $answer;
                    }
                }
                $normalized['answers'] = array_values(array_unique(array_slice($answers, 0, 10)));
                if (!$normalized['answers']) {
                    continue;
                }
            } else if ($type === 'essay') {
                $normalized['graderinfo'] = clean_text(
                    (string)($question['graderinfo'] ?? $question['rubric'] ?? ''),
                    FORMAT_HTML
                );
            }

            $result[] = $normalized;
        }
        return $result;
    }

    /**
     * Create a category below the course-context top category.
     *
     * @param stdClass $course
     * @param string $name
     * @return stdClass
     */
    public static function create_category(stdClass $course, string $name): stdClass {
        global $CFG, $DB;
        require_once($CFG->libdir . '/questionlib.php');

        $context = context_course::instance($course->id);
        require_capability('moodle/question:add', $context);

        $top = question_get_top_category($context->id, true);
        $manager = new category_manager();
        $categoryname = clean_param(core_text::substr($name, 0, 255), PARAM_TEXT);
        if ($categoryname === '') {
            $categoryname = get_string('questioncategorydefault', 'geniaicourseactivity_quiz');
        }
        // Use Moodle's category API so capabilities, sort order and creation event are handled by core.
        $categoryid = $manager->add_category(
            $top->id . ',' . $context->id,
            $categoryname,
            '',
            FORMAT_HTML,
            null
        );
        return $DB->get_record('question_categories', ['id' => $categoryid], '*', MUST_EXIST);
    }

    /**
     * Import questions into Moodle using the native GIFT importer.
     *
     * @param stdClass $course
     * @param stdClass $category
     * @param array $questions
     * @return array{ids: int[], output: string}
     */
    public static function import(stdClass $course, stdClass $category, array $questions): array {
        global $CFG;
        require_once($CFG->libdir . '/questionlib.php');
        require_once($CFG->dirroot . '/question/format.php');
        require_once($CFG->dirroot . '/question/format/gift/format.php');

        $questions = self::normalize($questions);
        if (!$questions) {
            throw new moodle_exception('noquestionsgenerated', 'geniaicourseactivity_quiz');
        }

        $context = context_course::instance($course->id);
        require_capability('moodle/question:add', $context);

        $tempdir = make_temp_directory('geniaicourse');
        $filename = 'questions-' . bin2hex(random_bytes(8)) . '.gift';
        $pathname = $tempdir . '/' . $filename;
        if (file_put_contents($pathname, self::to_gift($questions)) === false) {
            throw new moodle_exception('cannotwritefile', 'error');
        }

        $format = new qformat_gift();
        $format->setCategory($category);
        $format->setCourse($course);
        $format->setContexts([$context]);
        $format->setFilename($pathname);
        $format->setRealfilename($filename);
        $format->setMatchgrades('nearest');
        $format->setCatfromfile(false);
        $format->setContextfromfile(false);
        $format->setStoponerror(true);
        $format->set_display_progress(false);

        $output = '';
        try {
            ob_start();
            $ok = $format->importprocess();
            $output = trim((string)ob_get_clean());
        } catch (Throwable $e) {
            if (ob_get_level()) {
                $output = trim((string)ob_get_clean());
            }
            @unlink($pathname);
            throw $e;
        }
        @unlink($pathname);

        if (!$ok || !$format->questionids) {
            $detail = trim(strip_tags($output));
            throw new moodle_exception('questionimportfailed', 'geniaicourseactivity_quiz', '', $detail);
        }

        return [
            'ids' => array_values(array_map('intval', $format->questionids)),
            'output' => trim(strip_tags($output)),
        ];
    }

    /**
     * Build a Moodle-version-compatible question bank URL.
     *
     * @param int $courseid
     * @return moodle_url
     */
    public static function bank_url(int $courseid): moodle_url {
        global $CFG;
        if (file_exists($CFG->dirroot . '/question/banks.php')) {
            return new moodle_url('/question/banks.php', ['courseid' => $courseid]);
        }
        return new moodle_url('/question/edit.php', ['courseid' => $courseid]);
    }

    /**
     * Method normalize_multichoice_answers.
     *
     * @param array $question Parameter question.
     * @return array Return value.
     */
    private static function normalize_multichoice_answers(array $question): array {
        $input = $question['answers'] ?? $question['options'] ?? [];
        $answers = [];
        $correctfound = false;
        $correctindex = isset($question['correctindex']) ? (int)$question['correctindex'] : null;
        $correctanswer = isset($question['correctanswer']) ? trim((string)$question['correctanswer']) : null;

        foreach (array_slice((array)$input, 0, 12) as $index => $answer) {
            if (is_array($answer)) {
                $text = trim((string)($answer['text'] ?? $answer['answer'] ?? $answer['label'] ?? ''));
                $correct = $answer['correct'] ?? false;
                $feedback = (string)($answer['feedback'] ?? '');
            } else {
                $text = trim((string)$answer);
                $correct = false;
                $feedback = '';
            }
            if ($text === '') {
                continue;
            }
            if ($correctindex !== null && $index === $correctindex) {
                $correct = true;
            }
            if ($correctanswer !== null && trim(strip_tags($text)) === trim(strip_tags($correctanswer))) {
                $correct = true;
            }
            $correct = (bool)$correct && !$correctfound;
            if ($correct) {
                $correctfound = true;
            }
            $answers[] = [
                'text' => trim(strip_tags($text)),
                'correct' => $correct,
                'feedback' => trim(strip_tags($feedback)),
            ];
        }

        if ($answers && !$correctfound) {
            $answers[0]['correct'] = true;
        }
        return $answers;
    }

    /**
     * Convert normalized structures to Moodle GIFT.
     */
    private static function to_gift(array $questions): string {
        $blocks = [];
        foreach ($questions as $question) {
            $name = self::gift_escape(trim(strip_tags($question['name'])));
            $text = self::gift_escape($question['question']);
            $prefix = '::' . $name . '::[html]' . $text;

            switch ($question['type']) {
                case 'truefalse':
                    $blocks[] = $prefix . '{' . ($question['correct'] ? 'TRUE' : 'FALSE') . '}';
                    break;
                case 'shortanswer':
                    $parts = [];
                    foreach ($question['answers'] as $answer) {
                        $parts[] = '=' . self::gift_escape($answer);
                    }
                    $blocks[] = $prefix . '{' . implode(' ', $parts) . '}';
                    break;
                case 'essay':
                    $blocks[] = $prefix . '{}';
                    break;
                case 'multichoice':
                default:
                    $parts = [];
                    foreach ($question['answers'] as $answer) {
                        $part = ($answer['correct'] ? '=' : '~') . self::gift_escape($answer['text']);
                        if ($answer['feedback'] !== '') {
                            $part .= '#' . self::gift_escape($answer['feedback']);
                        }
                        $parts[] = $part;
                    }
                    $blocks[] = $prefix . '{' . implode(' ', $parts) . '}';
                    break;
            }
        }
        return implode("\n\n", $blocks) . "\n";
    }

    /**
     * Escape text according to GIFT control characters.
     */
    private static function gift_escape(string $text): string {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(
            [':', '#', '=', '{', '}', '~'],
            ['\\:', '\\#', '\\=', '\\{', '\\}', '\\~'],
            $text
        );
        return $text;
    }
}
