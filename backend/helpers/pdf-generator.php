<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Convert rendered HTML to PDF binary string.
 *
 * @param string $html  Full HTML document
 * @param array  $opts  Optional: 'paper' (default 'letter'), 'orientation' (default 'portrait')
 * @return string Binary PDF content
 */
function generatePDFFromHTML($html, $opts = []) {
    $options = new Options();
    $options->set('isRemoteEnabled', true);   // allow base64 images
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'Times New Roman');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper($opts['paper'] ?? 'letter', $opts['orientation'] ?? 'portrait');
    $dompdf->render();

    return $dompdf->output();
}

/**
 * Generate a PDF from letter HTML and save to storage.
 *
 * @param string $html         Rendered letter HTML
 * @param string $caseNumber   For filename
 * @param string $providerName For filename
 * @return string|null Full file path on success, null on failure
 */
function saveLetterPDF($html, $caseNumber, $providerName) {
    try {
        $pdfContent = generatePDFFromHTML($html);

        $outputDir = __DIR__ . '/../../storage/documents/generated';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $providerName);
        $timestamp = date('Ymd_His');
        $filename = "Request_{$caseNumber}_{$safeName}_{$timestamp}.pdf";
        $fullPath = $outputDir . '/' . $filename;

        file_put_contents($fullPath, $pdfContent);

        return $fullPath;
    } catch (Exception $e) {
        error_log("PDF Generation Error: " . $e->getMessage());
        return null;
    }
}
