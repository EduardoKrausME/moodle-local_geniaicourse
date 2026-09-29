<?php
namespace local_geniaicourse\local\activity;

/**
 * Contract implemented by activity subplugins.
 *
 * @package local_geniaicourse
 */
interface activity_interface {
    /** Friendly name shown to teachers. */
    public static function get_name(): string;

    /** Short description shown in review. */
    public static function get_description(): string;

    /**
     * Analyse one source and return a normalized proposal.
     *
     * Expected keys: match, confidence, title, summary, reason.
     * Additional keys are owned by the subplugin and passed back to create().
     *
     * @param \stdClass $project
     * @param \stdClass $source
     * @return array
     */
    public static function analyse(\stdClass $project, \stdClass $source): array;

    /**
     * Create the Moodle activity.
     *
     * @param \stdClass $course
     * @param int $sectionnum
     * @param \stdClass $source
     * @param array $analysis
     * @return array Must contain cmid, name, url; may contain warning.
     */
    public static function create(\stdClass $course, int $sectionnum, \stdClass $source, array $analysis): array;
}
