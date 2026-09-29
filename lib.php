<?php
/**
 * Library callbacks.
 *
 * @package local_geniaicourse
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Add the AI course builder to course navigation.
 *
 * @param navigation_node $navigation
 * @param stdClass $course
 * @param context_course $context
 */
function local_geniaicourse_extend_navigation_course($navigation, $course, $context) {
    if (!has_capability('local/geniaicourse:use', $context) ||
            !has_capability('moodle/course:manageactivities', $context)) {
        return;
    }

    $url = new moodle_url('/local/geniaicourse/index.php', ['courseid' => $course->id]);
    $navigation->add(
        get_string('navtitle', 'local_geniaicourse'),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'local_geniaicourse'
    );
}
