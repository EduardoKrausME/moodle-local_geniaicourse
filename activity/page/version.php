<?php
/**
 * Version information.
 *
 * @package geniaicourseactivity_page
 */

defined('MOODLE_INTERNAL') || die;

$plugin->component = 'geniaicourseactivity_page';
$plugin->version = 2026092802;
$plugin->requires = 2024100700;
$plugin->maturity = MATURITY_ALPHA;
$plugin->dependencies = [
    'local_geniaicourse' => 2026092802,
    'mod_page' => ANY_VERSION,
];
