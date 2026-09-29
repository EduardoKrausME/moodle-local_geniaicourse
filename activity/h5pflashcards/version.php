<?php
defined('MOODLE_INTERNAL') || die;

$plugin->component = 'geniaicourseactivity_h5pflashcards';
$plugin->version = 2026092802;
$plugin->requires = 2024100700;
$plugin->maturity = MATURITY_ALPHA;
$plugin->dependencies = [
    'local_geniaicourse' => 2026092802,
    'mod_h5pactivity' => ANY_VERSION,
];
