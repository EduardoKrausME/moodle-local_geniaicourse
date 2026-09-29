<?php
/** File serving callbacks for geniaicourseactivity_h5pinteractivevideo. */

defined('MOODLE_INTERNAL') || die;

/**
 * Serve generated course-scoped assets owned by this subplugin.
 */
function geniaicourseactivity_h5pinteractivevideo_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel !== CONTEXT_COURSE || $filearea !== 'generated') {
        return false;
    }
    require_login($course);
    if (!$args) {
        return false;
    }
    $itemid = (int) array_shift($args);
    if ($itemid <= 0 || !$args) {
        return false;
    }
    $filename = array_pop($args);
    $filepath = '/' . ($args ? implode('/', $args) . '/' : '');
    $file = get_file_storage()->get_file(
        $context->id,
        'geniaicourseactivity_h5pinteractivevideo',
        $filearea,
        $itemid,
        $filepath,
        $filename
    );
    if (!$file || $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 0, 0, false, $options);
}
