<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig && isset($settings)) {
    $settings->add(new admin_setting_configtext(
        'geniaicourseactivity_page/libreofficepath',
        get_string('libreofficepath', 'geniaicourseactivity_page'),
        get_string('libreofficepath_desc', 'geniaicourseactivity_page'),
        '/usr/bin/soffice',
        PARAM_RAW_TRIMMED
    ));
}
