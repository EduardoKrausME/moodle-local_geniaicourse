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

use core_component;
use local_geniaicourse\activity\activity_interface;
use local_geniaicourse\activity\selection_consumer_interface;
use local_geniaicourse\activity\source_extension_interface;
use local_geniaicourse\activity\source_processor_interface;
use moodle_exception;

/**
 * Discovers and invokes installed activity subplugins.
 *
 * @package local_geniaicourse
 */
class plugin_manager {
    /** @return array<string, class-string<activity_interface>> */
 * Get plugins.
 *
    /**
     * Get plugins.
     *
     * @return array
     */
    public static function get_plugins(): array {
        $plugins = [];
        foreach (core_component::get_plugin_list('geniaicourseactivity') as $name => $path) {
            $class = '\\geniaicourseactivity_' . $name . '\\activity';
            if (class_exists($class) && is_subclass_of($class, activity_interface::class)) {
                $plugins[$name] = $class;
            }
        }
        ksort($plugins);
        return $plugins;
    }

    /** Return additional upload extensions declared by installed subplugins. */
 * Get source extensions.
 *
    /**
     * Get source extensions.
     *
     * @return array
     */
    public static function get_source_extensions(): array {
        $extensions = [];
        foreach (self::get_plugins() as $class) {
            if (!is_subclass_of($class, source_extension_interface::class)) {
                continue;
            }
            foreach ($class::get_source_extensions() as $extension) {
                $extension = strtolower(ltrim(trim((string) $extension), '.'));
                if ($extension !== '' && preg_match('/^[a-z0-9]+$/', $extension)) {
                    $extensions[] = $extension;
                }
            }
        }
        return array_values(array_unique($extensions));
    }

    /** Let an installed subplugin preprocess a file extension it owns. */
 * Process source.
 *
    /**
     * Process source.
     *
     * @param \stored_file $file Parameter value.
     * @param string $extension Parameter value.
     * @return ?array
     */
    public static function process_source(\stored_file $file, string $extension): ?array {
        foreach (self::get_plugins() as $class) {
            if (!is_subclass_of($class, source_processor_interface::class) ||
                    !$class::supports_source_extension($extension)) {
                continue;
            }
            $result = $class::process_source($file, $extension);
            if (is_array($result)) {
                return $result;
            }
        }
        return null;
    }

    /** Analyse a source with every installed subplugin. */
 * Analyse source.
 *
    /**
     * Analyse source.
     *
     * @param \stdClass $project Parameter value.
     * @param \stdClass $source Parameter value.
     * @return array
     */
    public static function analyse_source(\stdClass $project, \stdClass $source): array {
        $result = [];
        foreach (self::get_plugins() as $name => $class) {
            try {
                $analysis = $class::analyse($project, $source);
                $result[$name] = self::normalize($class, $analysis);
            } catch (\Throwable $e) {
                $result[$name] = [
                    'match' => false,
                    'confidence' => 0,
                    'title' => $source->filename ?: get_string('source', 'local_geniaicourse'),
                    'summary' => '',
                    'reason' => $e->getMessage(),
                    'error' => true,
                    'activityname' => $class::get_name(),
                    'description' => $class::get_description(),
                ];
            }
        }
        return $result;
    }


    /**
     * Let selected consumer subplugins prepare themselves and consume sibling selections.
     *
     * @return array{analyses:array,consumed:string[]}
     */
    public static function prepare_selected(array $selectedplugins, array $analyses): array {
        $plugins = self::get_plugins();
        $consumed = [];

        foreach ($selectedplugins as $pluginname) {
            if (!isset($plugins[$pluginname], $analyses[$pluginname]) || !is_array($analyses[$pluginname])) {
                continue;
            }
            $class = $plugins[$pluginname];
            if (!is_subclass_of($class, selection_consumer_interface::class)) {
                continue;
            }

            $analyses[$pluginname] = $class::prepare_selection(
                $analyses[$pluginname],
                $selectedplugins,
                $analyses
            );
            foreach ($class::consumed_plugins($selectedplugins, $analyses) as $consumedname) {
                if (is_string($consumedname) && $consumedname !== $pluginname) {
                    $consumed[] = $consumedname;
                }
            }
        }

        return [
            'analyses' => $analyses,
            'consumed' => array_values(array_unique($consumed)),
        ];
    }

    /** Create an activity using one subplugin. */
 * Create.
 *
    /**
     * Create.
     *
     * @param string $pluginname Parameter value.
     * @param \stdClass $course Parameter value.
     * @param int $sectionnum Parameter value.
     * @param \stdClass $source Parameter value.
     * @param array $analysis Parameter value.
     * @return array
     */
    public static function create(string $pluginname, \stdClass $course, int $sectionnum,
            \stdClass $source, array $analysis): array {
        $plugins = self::get_plugins();
        if (!isset($plugins[$pluginname])) {
            throw new moodle_exception('invalidplugin', 'error', '', $pluginname);
        }
        return $plugins[$pluginname]::create($course, $sectionnum, $source, $analysis);
    }

    /**
     * Normalize.
     *
     * @param string $class Parameter value.
     * @param array $analysis Parameter value.
     * @return array
     */
    private static function normalize(string $class, array $analysis): array {
        $match = $analysis['match'] ?? false;
        if (is_string($match)) {
            $parsedmatch = filter_var($match, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $analysis['match'] = $parsedmatch ?? false;
        } else {
            $analysis['match'] = (bool) $match;
        }
        $analysis['confidence'] = max(0, min(100, (int) ($analysis['confidence'] ?? 0)));
        $analysis['title'] = trim((string) ($analysis['title'] ?? ''));
        $analysis['summary'] = trim((string) ($analysis['summary'] ?? ''));
        $analysis['reason'] = trim((string) ($analysis['reason'] ?? ''));
        $analysis['activityname'] = $class::get_name();
        $analysis['description'] = $class::get_description();
        return $analysis;
    }
}
