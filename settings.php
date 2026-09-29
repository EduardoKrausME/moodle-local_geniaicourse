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

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_geniaicourse', get_string('pluginname', 'local_geniaicourse'));


    $settings->add(new admin_setting_configtext(
        'local_geniaicourse/pdftotextpath',
        get_string('pdftotextpath', 'local_geniaicourse'),
        get_string('pdftotextpath_desc', 'local_geniaicourse'),
        '/usr/bin/pdftotext',
        PARAM_RAW_TRIMMED
    ));

    $settings->add(new admin_setting_configtext(
        'local_geniaicourse/maxextractchars',
        get_string('maxextractchars', 'local_geniaicourse'),
        get_string('maxextractchars_desc', 'local_geniaicourse'),
        40000,
        PARAM_INT
    ));


    // Let installed activity subplugins contribute their own settings without the main plugin knowing their names.
    foreach (core_component::get_plugin_list('geniaicourseactivity') as $subpluginpath) {
        $subsettings = $subpluginpath . '/settings.php';
        if (is_readable($subsettings)) {
            include($subsettings);
        }
    }
    $ADMIN->add('localplugins', $settings);
}
