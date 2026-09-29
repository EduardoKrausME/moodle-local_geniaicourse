<?php
namespace local_geniaicourse\local;

/**
 * Helpers for adding native Moodle activities.
 *
 * @package local_geniaicourse
 */
class module_helper {
    public static function base(\stdClass $course, int $sectionnum, string $modulename,
            string $name, string $intro = ''): \stdClass {
        global $DB;
        $module = $DB->get_record('modules', ['name' => $modulename], '*', MUST_EXIST);

        $info = new \stdClass();
        $info->course = $course->id;
        $info->module = $module->id;
        $info->modulename = $modulename;
        $info->section = $sectionnum;
        $info->name = clean_param($name, PARAM_TEXT);
        $info->intro = clean_text($intro, FORMAT_HTML);
        $info->introformat = FORMAT_HTML;
        $info->visible = 1;
        $info->visibleoncoursepage = 1;
        $info->showdescription = 0;
        $info->groupmode = NOGROUPS;
        $info->groupingid = 0;
        $info->completion = COMPLETION_TRACKING_NONE;
        $info->completionview = 0;
        $info->completiongradeitemnumber = '';
        $info->completionpassgrade = 0;
        $info->completionexpected = 0;
        $info->availability = null;
        $info->cmidnumber = '';
        return $info;
    }
}
