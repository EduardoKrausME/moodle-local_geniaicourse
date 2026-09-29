<?php
namespace local_geniaicourse\local\activity;

use stored_file;

/** Optional contract for subplugins that own extraction/metadata handling for extra source formats. */
interface source_processor_interface {
    /** Return true when this subplugin owns preprocessing for the extension. */
    public static function supports_source_extension(string $extension): bool;

    /** @return array{text:string,metadata:array}|null */
    public static function process_source(stored_file $file, string $extension): ?array;
}
