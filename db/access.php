<?php
/**
 * Capabilities.
 *
 * @package local_geniaicourse
 */

defined('MOODLE_INTERNAL') || die;

$capabilities = [
    'local/geniaicourse:use' => [
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],
];
