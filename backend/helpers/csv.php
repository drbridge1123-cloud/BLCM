<?php
function outputCSV($filename, $headers, $rows) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for Excel

    fputcsv($output, $headers);

    foreach ($rows as $row) {
        $line = [];
        foreach ($headers as $key) {
            $line[] = $row[$key] ?? '';
        }
        fputcsv($output, $line);
    }

    fclose($output);
    exit;
}

function parseCSV($filePath) {
    $rows = [];
    if (($handle = fopen($filePath, 'r')) !== false) {
        $headers = fgetcsv($handle);
        if ($headers) {
            // Remove BOM if present
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            $headers = array_map('trim', $headers);
            $headers = array_map('strtolower', $headers);

            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) === count($headers)) {
                    $rows[] = array_combine($headers, $data);
                }
            }
        }
        fclose($handle);
    }
    return $rows;
}
