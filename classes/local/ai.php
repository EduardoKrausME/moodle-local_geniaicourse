<?php
namespace local_geniaicourse\local;

use local_geniai\gpt\chatgpt;
use moodle_exception;

/**
 * Thin wrapper around local_geniai.
 *
 * @package local_geniaicourse
 */
class ai {
    /**
     * Check whether the currently installed local_geniai provider is ready for use.
     *
     * This is intentionally centralized here so a future local_geniai controller
     * facade can be adopted without changing the UI/analysis flow.
     *
     * @return bool
     */
    public static function is_configured(): bool {
        return class_exists(chatgpt::class) && trim((string) get_config('local_geniai', 'apikey')) !== '';
    }

    /**
     * Send text to local_geniai and return JSON as an array.
     *
     * @param string $system
     * @param string $user
     * @return array
     */
    public static function json(string $system, string $user): array {
        if (!class_exists(chatgpt::class)) {
            throw new moodle_exception('missingdependency', 'error', '', 'local_geniai');
        }
        if (!self::is_configured()) {
            throw new moodle_exception('noapikey', 'local_geniaicourse');
        }

        $response = chatgpt::completions([
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ]);

        if (!empty($response['error']['message'])) {
            throw new moodle_exception('analysiserror', 'local_geniaicourse', '', $response['error']['message']);
        }

        $text = trim((string) ($response['choices'][0]['message']['content'] ?? ''));
        if ($text === '') {
            throw new moodle_exception('analysiserror', 'local_geniaicourse', '', get_string('emptyairesponse', 'local_geniaicourse'));
        }

        $json = self::extract_json($text);
        if ($json === null) {
            throw new moodle_exception('analysiserror', 'local_geniaicourse', '', get_string('invalidjsonresponse', 'local_geniaicourse'));
        }
        return $json;
    }

    /** Extract the first JSON object from a response, including fenced responses. */
    private static function extract_json(string $text): ?array {
        $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
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
