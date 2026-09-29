<?php
namespace geniaicourseactivity_page;

use stored_file;

/**
 * Optional Office/ODF to PDF conversion through LibreOffice.
 *
 * @package geniaicourseactivity_page
 */
class converter {
    private const PDF_INPUTS = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp'];

    public static function instruction_requests_pdf(string $instruction): bool {
        return (bool) preg_match('/\bpdf\b/i', $instruction);
    }

    public static function can_convert(string $extension): bool {
        return in_array(strtolower($extension), self::PDF_INPUTS, true);
    }

    /**
     * Convert a stored file to a temporary PDF.
     *
     * @return array{path:?string,filename:string,warning:string}
     */
    public static function to_pdf(stored_file $file): array {
        $tool = trim((string) get_config('geniaicourseactivity_page', 'libreofficepath'));
        $fallbackname = pathinfo($file->get_filename(), PATHINFO_FILENAME) . '.pdf';
        if (!$tool || !self::can_execute($tool)) {
            return [
                'path' => null,
                'filename' => $fallbackname,
                'warning' => get_string('libreofficeunavailable', 'geniaicourseactivity_page'),
            ];
        }

        $dir = make_request_directory();
        $input = $dir . '/' . clean_param($file->get_filename(), PARAM_FILE);
        $file->copy_content_to($input);

        $cmd = escapeshellarg($tool) . ' --headless --convert-to pdf --outdir ' . escapeshellarg($dir) . ' ' .
            escapeshellarg($input) . ' 2>&1';
        $output = [];
        $code = 1;
        exec($cmd, $output, $code);

        $pdf = $dir . '/' . $fallbackname;
        if ($code === 0 && is_readable($pdf) && filesize($pdf) > 0) {
            return ['path' => $pdf, 'filename' => $fallbackname, 'warning' => ''];
        }

        return [
            'path' => null,
            'filename' => $fallbackname,
            'warning' => get_string('libreofficeconversionfailed', 'geniaicourseactivity_page', trim(implode(' ', $output))),
        ];
    }

    private static function can_execute(string $path): bool {
        if (!function_exists('exec') || !is_executable($path)) {
            return false;
        }
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        return !in_array('exec', $disabled, true);
    }
}
