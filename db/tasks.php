<?php
/**
 * Scheduled tasks.
 *
 * @package local_geniaicourse
 */

defined('MOODLE_INTERNAL') || die;

$tasks = [
    [
        'classname' => '\\local_geniaicourse\\task\\cleanup',
        'blocking' => 0,
        'minute' => 'R',
        'hour' => '3',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];
