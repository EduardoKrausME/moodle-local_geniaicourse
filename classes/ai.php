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

namespace local_geniaicourse;

use local_ai_bridge\api;
use moodle_exception;

/**
 * phpcs:disable moodle.Strings.ForbiddenStrings.Found
 * AI facade used by the course builder and its activity subplugins.
 *
 * Provider selection, credentials, routing, credits and usage accounting are
 * delegated to local_ai_bridge. Activity subplugins remain provider-agnostic.
 *
 * @package local_geniaicourse
 */
class ai {
    /** AI Bridge purpose used by this plugin. */
    private const PURPOSE = 'geniaicourse';

    /**
     * Send text through AI Bridge and return JSON as an array.
     *
     * @param string $system
     * @param string $user
     * @return array
     */
    public static function json(string $system, string $user): array {
        $response = api::generate(self::PURPOSE, [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ]);

        $text = trim($response->text);
        if ($text === '') {
            throw new moodle_exception(
                'analysiserror',
                'local_geniaicourse',
                '',
                get_string('emptyairesponse', 'local_geniaicourse')
            );
        }

        $json = self::extract_json($text);
        if ($json === null) {
            throw new moodle_exception(
                'analysiserror',
                'local_geniaicourse',
                '',
                get_string('invalidjsonresponse', 'local_geniaicourse')
            );
        }
        return $json;
    }

    /**
     * Extract the first JSON object from a response, including fenced responses.
     *
     * @param string $text Parameter value.
     * @return ?array
     */
    private static function extract_json(string $text): ?array {
        $text = preg_replace('/^(?:```json)?\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/', '', trim($text));

        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }
        $decoded = json_decode(substr($text, $start, $end - $start + 1), true);
        return is_array($decoded) ? $decoded : null;
    }
}
