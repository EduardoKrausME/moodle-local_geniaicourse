<?php
namespace local_geniaicourse\local\activity;

/**
 * Optional contract for subplugins that consume other selected subplugins.
 *
 * The core orchestrator stays unaware of concrete plugin names. A consumer
 * subplugin can enrich its own analysis and declare which selected plugins it
 * consumes so they are not created a second time.
 *
 * @package local_geniaicourse
 */
interface selection_consumer_interface {
    /** Enrich this subplugin analysis with the current selection context. */
    public static function prepare_selection(array $analysis, array $selectedplugins, array $allanalysis): array;

    /** Return selected plugin names consumed by this subplugin. */
    public static function consumed_plugins(array $selectedplugins, array $allanalysis): array;
}
