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
 * GeniAI Course activity subplugin.
 *
 * @package geniaicourseactivity_h5pinteractivevideo
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace geniaicourseactivity_h5pinteractivevideo;

use local_geniaicourse\source_manager;
use stdClass;

/**
 * Interactive Video source handling owned by this subplugin.
 */
class video_helper {
    /** @var string[] */
    public const VIDEO_EXTENSIONS = ['mp4', 'webm', 'ogv', 'm4v'];

    /**
     * Resolve an uploaded or explicit external video source.
     */
    public static function source(stdClass $course, stdClass $source, array $analysis): ?array {
        $url = trim((string)($analysis['video_url'] ?? ''));
        if ($url !== '') {
            $external = self::external_video($url);
            if ($external !== null) {
                return $external;
            }
        }

        $extension = strtolower((string)$source->extension);
        if ($source->sourcetype === 'file' && in_array($extension, self::VIDEO_EXTENSIONS, true)) {
            return self::copy_source_video($course, $source);
        }
        return null;
    }

    /**
     * External video.
     */
    private static function external_video(string $url): ?array {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        $parts = parse_url($url);
        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }
        $host = strtolower((string)($parts['host'] ?? ''));
        if ($host === 'youtu.be' || str_ends_with($host, '.youtube.com') || $host === 'youtube.com' ||
            str_ends_with($host, '.youtube-nocookie.com') || $host === 'youtube-nocookie.com') {
            return ['path' => $url, 'mime' => 'video/YouTube'];
        }

        $extension = strtolower(pathinfo((string)($parts['path'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($extension, self::VIDEO_EXTENSIONS, true)) {
            return null;
        }
        return ['path' => $url, 'mime' => self::video_mime($extension)];
    }

    /**
     * Copy source video.
     */
    private static function copy_source_video(stdClass $course, stdClass $source): ?array {
        $sourcefile = source_manager::get_stored_file($source);
        if (!$sourcefile) {
            return null;
        }

        $filename = clean_param($sourcefile->get_filename(), PARAM_FILE);
        $filename = 'geniaicourse-' . $source->id . '-' . $filename;
        return [
            'path' => 'videos/' . $filename,
            'mime' => self::video_mime(strtolower((string)$source->extension)),
            'file' => $sourcefile,
        ];
    }

    /**
     * Video mime.
     */
    private static function video_mime(string $extension): string {
        return match ($extension) {
            'webm' => 'video/webm',
            'ogv' => 'video/ogg',
            default => 'video/mp4',
        };
    }
}
