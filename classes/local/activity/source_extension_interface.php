<?php
namespace local_geniaicourse\local\activity;

/** Optional contract for subplugins that introduce extra upload extensions. */
interface source_extension_interface {
    /** @return string[] Lowercase extensions without the leading dot. */
    public static function get_source_extensions(): array;
}
