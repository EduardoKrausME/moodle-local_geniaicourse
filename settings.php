<?php
/**
 * Admin settings.
 *
 * @package local_geniaicourse
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
