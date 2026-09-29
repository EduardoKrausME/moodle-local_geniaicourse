<?php
namespace local_geniaicourse\local\activity;

/** Optional contract for subplugins that expose content reusable by another subplugin. */
interface composable_content_interface {
    /** Family identifier used by compatible consumer subplugins, for example 'h5p'. */
    public static function get_composable_family(): string;

    /**
     * Build a reusable content definition without creating the final Moodle activity.
     * The structure is owned by the implementing/consuming subplugins.
     */
    public static function build_composable_content(\stdClass $course, \stdClass $source, array $analysis): array;
}
