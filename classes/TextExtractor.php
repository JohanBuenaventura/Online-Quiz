<?php

class TextExtractor {
    // Extract text from uploaded file path
    public static function extract($filePath, $mimeType = null) {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!$mimeType) {
            $mimeType = mime_content_type($filePath) ?: '';
        }

        if (in_array($ext, ['txt'])) {
            return file_get_contents($filePath);
        }

        if ($ext === 'pdf') {
            // Prefer PHP PDF parser if installed (smalot/pdfparser)
            if (class_exists('\Smalot\PdfParser\Parser')) {
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($filePath);
                    $text = $pdf->getText();
                    return $text;
                } catch (Exception $e) {
                    // fallthrough to pdftotext fallback
                }
            }

            // Try pdftotext CLI if available
            if (function_exists('shell_exec')) {
                $in = escapeshellarg($filePath);
                $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'oq_txt_' . uniqid() . '.txt';
                $tmpArg = escapeshellarg($tmp);
                @shell_exec("pdftotext -layout $in $tmpArg 2> /dev/null");
                if (file_exists($tmp)) {
                    $out = file_get_contents($tmp);
                    @unlink($tmp);
                    return $out;
                }
            }

            // No extractor available
            return "";
        }

        if ($ext === 'docx') {
            // DOCX is a ZIP archive containing word/document.xml
            $zip = new ZipArchive;
            if ($zip->open($filePath) === true) {
                if (($idx = $zip->locateName('word/document.xml')) !== false) {
                    $data = $zip->getFromIndex($idx);
                    $zip->close();
                    // strip xml tags
                    $text = strip_tags($data);
                    // replace multiple spaces/newlines
                    $text = preg_replace('/\s+/', ' ', $text);
                    return $text;
                }
                $zip->close();
            }
            return "";
        }

        // fallback: try file_get_contents
        return @file_get_contents($filePath) ?: "";
    }
}
