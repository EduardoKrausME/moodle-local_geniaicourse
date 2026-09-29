<?php
defined('MOODLE_INTERNAL') || die;

$plugin->component = 'geniaicourseactivity_lesson';
$plugin->version = 2026092802;
$plugin->requires = 2024100700;
$plugin->maturity = MATURITY_ALPHA;
$plugin->release = '1.0.0';
$plugin->dependencies = [
    'local_geniaicourse' => 2026092802,
    'mod_lesson' => ANY_VERSION,
];
