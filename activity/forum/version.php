<?php
/**
 * Version information.
 *
 * @package geniaicourseactivity_forum
 */

defined('MOODLE_INTERNAL') || die;

$plugin->component = 'geniaicourseactivity_forum';
$plugin->version = 2026092802;
$plugin->requires = 2024100700;
$plugin->maturity = MATURITY_ALPHA;
$plugin->dependencies = [
    'local_geniaicourse' => 2026092802,
    'mod_forum' => ANY_VERSION,
];
