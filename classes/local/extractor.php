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
 * GeniAI Course Builder.
 *
 * @package local_geniaicourse
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_geniaicourse\local;

use core_text;
use stored_file;
use ZipArchive;

/**
 * Extract text from supported source files.
 *
 * @package local_geniaicourse
 */
class extractor {
    /**
 * Extract.
 *
     * @param stored_file $file
     * @param string $extension
     * @return array{text:string,metadata:array}
     */
    public static function extract(stored_file $file, string $extension): array {
        $extension = strtolower($extension);
        $metadata = [
            'filesize' => $file->get_filesize(),
            'mimetype' => $file->get_mimetype(),
            'warnings' => [],
        ];

        try {
            switch ($extension) {
                case 'txt':
                case 'md':
                case 'csv':
                case 'html':
                case 'htm':
                    $text = self::normalize_text($file->get_content());
                    if (in_array($extension, ['html', 'htm'], true)) {
                        $text = self::normalize_text(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    }
                    break;

                case 'docx':
                case 'odt':
                    $text = self::extract_zip_xml_document($file, $extension);
                    break;

                case 'pptx':
                case 'odp':
                    $text = self::extract_presentation($file, $extension);
                    break;

                case 'xlsx':
                case 'ods':
                    $text = self::extract_spreadsheet($file, $extension);
                    break;

                case 'pdf':
                    [$text, $warning] = self::extract_pdf($file);
                    if ($warning) {
                        $metadata['warnings'][] = $warning;
                    }
                    break;

                case 'doc':
                case 'xls':
                case 'ppt':
                    $text = self::extract_binary_strings($file->get_content());
                    $metadata['warnings'][] = get_string('legacyofficewarning', 'local_geniaicourse');
                    break;

                case 'png':
                case 'jpg':
                case 'jpeg':
                case 'gif':
                case 'webp':
                    $text = '';
                    $metadata['warnings'][] = get_string('imagewarning', 'local_geniaicourse');
                    break;

                default:
                    $text = '';
                    $metadata['warnings'][] = get_string('noextractorwarning', 'local_geniaicourse');
            }
        } catch (\Throwable $e) {
            $text = '';
            $metadata['warnings'][] = get_string('extractionerror', 'local_geniaicourse', $e->getMessage());
        }

        $configuredmax = (int) get_config('local_geniaicourse', 'maxextractchars');
        $max = $configuredmax > 0 ? max(1000, $configuredmax) : 40000;
        if (core_text::strlen($text) > $max) {
            $text = core_text::substr($text, 0, $max);
            $metadata['warnings'][] = get_string('truncatedwarning', 'local_geniaicourse', $max);
            $metadata['truncated'] = true;
        }

        $metadata['extractedchars'] = core_text::strlen($text);
        return ['text' => $text, 'metadata' => $metadata];
    }

    /**
     * Temp path.
     *
     * @param stored_file $file Parameter value.
     * @return string
     */
    private static function temp_path(stored_file $file): string {
        $dir = make_request_directory();
        $path = $dir . '/' . clean_param($file->get_filename(), PARAM_FILE);
        $file->copy_content_to($path);
        return $path;
    }

    /**
     * Extract zip xml document.
     *
     * @param stored_file $file Parameter value.
     * @param string $extension Parameter value.
     * @return string
     */
    private static function extract_zip_xml_document(stored_file $file, string $extension): string {
        $path = self::temp_path($file);
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $names = $extension === 'docx'
            ? ['word/document.xml']
            : ['content.xml'];
        $parts = [];
        foreach ($names as $name) {
            $xml = $zip->getFromName($name);
            if ($xml !== false) {
                $parts[] = self::xml_to_text($xml);
            }
        }
        if ($extension === 'docx') {
            for ($i = 1; $i <= 20; $i++) {
                foreach (["word/header{$i}.xml", "word/footer{$i}.xml"] as $name) {
                    $xml = $zip->getFromName($name);
                    if ($xml !== false) {
                        $parts[] = self::xml_to_text($xml);
                    }
                }
            }
        }
        $zip->close();
        return self::normalize_text(implode("\n", $parts));
    }

    /**
     * Extract presentation.
     *
     * @param stored_file $file Parameter value.
     * @param string $extension Parameter value.
     * @return string
     */
    private static function extract_presentation(stored_file $file, string $extension): string {
        $path = self::temp_path($file);
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        if ($extension === 'odp') {
            $xml = $zip->getFromName('content.xml');
            $zip->close();
            return $xml === false ? '' : self::xml_to_text($xml);
        }

        $slides = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^ppt/slides/slide([0-9]+)\.xml$#', $name, $m)) {
                $slides[(int) $m[1]] = $name;
            }
        }
        ksort($slides);
        $out = [];
        foreach ($slides as $number => $name) {
            $xml = $zip->getFromName($name);
            if ($xml === false) {
                continue;
            }
            preg_match_all('#<a:t[^>]*>(.*?)</a:t>#s', $xml, $matches);
            $texts = array_map(static function($value) {
                return html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_XML1, 'UTF-8');
            }, $matches[1] ?? []);
            $out[] = "Slide {$number}:\n" . implode("\n", array_filter(array_map('trim', $texts)));
        }
        $zip->close();
        return self::normalize_text(implode("\n\n", $out));
    }

    /**
     * Extract spreadsheet.
     *
     * @param stored_file $file Parameter value.
     * @param string $extension Parameter value.
     * @return string
     */
    private static function extract_spreadsheet(stored_file $file, string $extension): string {
        $path = self::temp_path($file);
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        if ($extension === 'ods') {
            $xml = $zip->getFromName('content.xml');
            $zip->close();
            return $xml === false ? '' : self::xml_to_text($xml);
        }

        $shared = [];
        $sharedxml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedxml !== false) {
            preg_match_all('#<si[^>]*>(.*?)</si>#s', $sharedxml, $items);
            foreach ($items[1] ?? [] as $item) {
                preg_match_all('#<t[^>]*>(.*?)</t>#s', $item, $texts);
                $shared[] = html_entity_decode(implode('', $texts[1] ?? []), ENT_QUOTES | ENT_XML1, 'UTF-8');
            }
        }

        $sheets = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^xl/worksheets/sheet([0-9]+)\.xml$#', $name, $m)) {
                $sheets[(int) $m[1]] = $name;
            }
        }
        ksort($sheets);
        $out = [];
        foreach ($sheets as $sheetnum => $name) {
            $xml = $zip->getFromName($name);
            if ($xml === false) {
                continue;
            }
            $out[] = "Sheet {$sheetnum}:";
            preg_match_all('#<row[^>]*>(.*?)</row>#s', $xml, $rows);
            foreach ($rows[1] ?? [] as $row) {
                $cells = [];
                preg_match_all('#<c([^>]*)>(.*?)</c>#s', $row, $cellmatches, PREG_SET_ORDER);
                foreach ($cellmatches as $cell) {
                    $attrs = $cell[1];
                    $body = $cell[2];
                    preg_match('/\br="([A-Z]+[0-9]+)"/', $attrs, $refmatch);
                    $ref = $refmatch[1] ?? '';
                    preg_match('/\bt="([^"]+)"/', $attrs, $typematch);
                    $type = $typematch[1] ?? '';
                    $value = '';
                    if ($type === 'inlineStr' && preg_match('#<t[^>]*>(.*?)</t>#s', $body, $m)) {
                        $value = html_entity_decode($m[1], ENT_QUOTES | ENT_XML1, 'UTF-8');
                    } else if (preg_match('#<v[^>]*>(.*?)</v>#s', $body, $m)) {
                        $value = html_entity_decode($m[1], ENT_QUOTES | ENT_XML1, 'UTF-8');
                        if ($type === 's' && isset($shared[(int) $value])) {
                            $value = $shared[(int) $value];
                        }
                    }
                    if ($value !== '') {
                        $cells[] = ($ref ? $ref . '=' : '') . trim($value);
                    }
                }
                if ($cells) {
                    $out[] = implode("\t", $cells);
                }
            }
            $out[] = '';
        }
        $zip->close();
        return self::normalize_text(implode("\n", $out));
    }

    /** @return array{0:string,1:string} */
 * Extract pdf.
 *
    /**
     * Extract pdf.
     *
     * @param stored_file $file Parameter value.
     * @return array
     */
    private static function extract_pdf(stored_file $file): array {
        $path = self::temp_path($file);
        $tool = trim((string) get_config('local_geniaicourse', 'pdftotextpath'));
        if ($tool && self::can_execute($tool)) {
            $cmd = escapeshellarg($tool) . ' -layout -enc UTF-8 ' . escapeshellarg($path) . ' - 2>&1';
            $output = [];
            $code = 1;
            exec($cmd, $output, $code);
            if ($code === 0) {
                $text = self::normalize_text(implode("\n", $output));
                if ($text !== '') {
                    return [$text, ''];
                }
            }
        }

        $text = self::extract_pdf_internal($file->get_content());
        $warning = get_string('pdffallbackwarning', 'local_geniaicourse');
        if ($text === '') {
            $warning .= ' ' . get_string('pdfscannedwarning', 'local_geniaicourse');
        }
        return [$text, $warning];
    }

    /**
     * Extract pdf internal.
     *
     * @param string $data Parameter value.
     * @return string
     */
    private static function extract_pdf_internal(string $data): string {
        $chunks = [];
        if (preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $data, $streams, PREG_SET_ORDER)) {
            foreach ($streams as $stream) {
                $raw = $stream[1];
                $candidates = [$raw];
                $decoded = @gzuncompress($raw);
                if ($decoded !== false) {
                    $candidates[] = $decoded;
                }
                $decoded = @gzinflate($raw);
                if ($decoded !== false) {
                    $candidates[] = $decoded;
                }
                foreach ($candidates as $candidate) {
                    if (!str_contains($candidate, 'BT') && !str_contains($candidate, 'Tj') && !str_contains($candidate, 'TJ')) {
                        continue;
                    }
                    preg_match_all('/\((?:\\.|[^\\)])*\)\s*Tj|\[(.*?)\]\s*TJ/s', $candidate, $ops, PREG_SET_ORDER);
                    foreach ($ops as $op) {
                        preg_match_all('/\((?:\\.|[^\\)])*\)/s', $op[0], $strings);
                        foreach ($strings[0] ?? [] as $literal) {
                            $literal = substr($literal, 1, -1);
                            $literal = preg_replace_callback('/\\([0-7]{1,3})/', static fn($m) => chr(octdec($m[1])), $literal);
                            $literal = str_replace(['\\n', '\\r', '\\t', '\\(', '\\)', '\\\\'], ["\n", "\r", "\t", '(', ')', '\\'], $literal);
                            $chunks[] = $literal;
                        }
                    }
                }
            }
        }
        return self::normalize_text(implode("\n", $chunks));
    }

    /**
     * Extract binary strings.
     *
     * @param string $content Parameter value.
     * @return string
     */
    private static function extract_binary_strings(string $content): string {
        $parts = [];
        if (preg_match_all('/[\x20-\x7E]{4,}/', $content, $ascii)) {
            $parts = array_merge($parts, $ascii[0]);
        }
        if (function_exists('mb_convert_encoding')) {
            $utf16 = @mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
            if (is_string($utf16) && preg_match_all('/[\p{L}\p{N}\p{P}\p{Zs}]{4,}/u', $utf16, $unicode)) {
                $parts = array_merge($parts, $unicode[0]);
            }
        }
        $parts = array_values(array_unique(array_map('trim', $parts)));
        return self::normalize_text(implode("\n", $parts));
    }

    /**
     * Xml to text.
     *
     * @param string $xml Parameter value.
     * @return string
     */
    private static function xml_to_text(string $xml): string {
        $xml = preg_replace('#</(?:w:p|text:p|text:h|table:table-row|a:p)>#', "\n", $xml);
        $xml = preg_replace('#</(?:w:tc|table:table-cell)>#', "\t", $xml);
        return self::normalize_text(html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }

    /**
     * Normalize text.
     *
     * @param string $text Parameter value.
     * @return string
     */
    private static function normalize_text(string $text): string {
        if (function_exists('mb_check_encoding') && !mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
        }
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[\t ]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    /**
     * Can execute.
     *
     * @param string $path Parameter value.
     * @return bool
     */
    private static function can_execute(string $path): bool {
        if (!function_exists('exec') || !is_executable($path)) {
            return false;
        }
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        return !in_array('exec', $disabled, true);
    }
}
