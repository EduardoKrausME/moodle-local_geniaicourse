<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Include generated H5P assets inside the activity's own portable .h5p package.
 *
 * @package local_geniaicourse
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse;

use moodle_exception;
use stored_file;
use ZipArchive;

/**
 * Adds uploaded media to content/ inside an H5P ZIP before mod_h5pactivity receives it.
 */
class h5p_package {
    /**
     * Add stored media using relative paths referenced by H5P content.json.
     *
     * @param stored_file $package Draft H5P package.
     * @param array $assets Array with path and stored_file entries.
     * @return stored_file Updated draft package.
     */
    public static function embed_files(stored_file $package, array $assets): stored_file {
        if (!$assets) {
            return $package;
        }

        $tempdir = make_temp_directory('geniaicourse_h5p');
        $pathname = tempnam($tempdir, 'package-');
        if ($pathname === false || !$package->copy_content_to($pathname)) {
            throw new moodle_exception('h5pembedfailed', 'local_geniaicourse');
        }

        $zip = new ZipArchive();
        $tempfiles = [];
        try {
            if ($zip->open($pathname) !== true) {
                throw new moodle_exception('h5pembedfailed', 'local_geniaicourse');
            }

            foreach ($assets as $asset) {
                $path = (string)($asset['path'] ?? '');
                $file = $asset['file'] ?? null;
                if (!preg_match('~^videos/[a-zA-Z0-9._-]+$~D', $path) || !$file instanceof stored_file) {
                    throw new moodle_exception('h5pembedfailed', 'local_geniaicourse');
                }
                $tmpfile = tempnam($tempdir, 'media-');
                if ($tmpfile === false || !$file->copy_content_to($tmpfile)) {
                    throw new moodle_exception('h5pembedfailed', 'local_geniaicourse');
                }
                $tempfiles[] = $tmpfile;
                if (!$zip->addFile($tmpfile, 'content/' . $path)) {
                    throw new moodle_exception('h5pembedfailed', 'local_geniaicourse');
                }
            }

            if (!$zip->close()) {
                throw new moodle_exception('h5pembedfailed', 'local_geniaicourse');
            }

            $fs = get_file_storage();
            $record = [
                'contextid' => $package->get_contextid(),
                'component' => $package->get_component(),
                'filearea' => $package->get_filearea(),
                'itemid' => $package->get_itemid(),
                'filepath' => $package->get_filepath(),
                'filename' => $package->get_filename(),
                'mimetype' => $package->get_mimetype(),
                'userid' => $package->get_userid(),
            ];
            $package->delete();
            return $fs->create_file_from_pathname($record, $pathname);
        } finally {
            if ($zip->status === ZipArchive::ER_OK) {
                $zip->close();
            }
            foreach ($tempfiles as $tmpfile) {
                @unlink($tmpfile);
            }
            @unlink($pathname);
        }
    }
}
