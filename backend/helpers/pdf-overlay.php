<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

/**
 * Generate a new PDF by overlaying provider name and optionally date on a template PDF
 */
function generateProviderPDF($templatePath, $providerName, $x, $y, $width, $height, $fontSize = 12, $dateOverlay = null, $customTextOverlay = null) {
    try {
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($templatePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            $pdf->SetAutoPageBreak(false);

            if ($pageNo === 1) {
                // Provider name overlay
                $pdf->SetFillColor(255, 255, 255);
                $pdf->Rect($x, $y, $width, $height, 'F');
                $pdf->SetFont('Arial', '', $fontSize);
                $pdf->SetTextColor(0, 0, 0);
                $lineHeight = $fontSize * 0.6;
                $pdf->SetXY($x, $y);
                $pdf->MultiCell($width, $lineHeight, $providerName, 0, 'L');

                // Date overlay
                if ($dateOverlay && isset($dateOverlay['x'], $dateOverlay['y'], $dateOverlay['width'], $dateOverlay['height'])) {
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->Rect($dateOverlay['x'], $dateOverlay['y'], $dateOverlay['width'], $dateOverlay['height'], 'F');
                    $dateFontSize = $dateOverlay['fontSize'] ?? 12;
                    $pdf->SetFont('Arial', '', $dateFontSize);
                    $pdf->SetTextColor(0, 0, 0);
                    $currentDate = date('m/d/Y');
                    $pdf->SetXY($dateOverlay['x'], $dateOverlay['y']);
                    $pdf->Cell($dateOverlay['width'], $dateOverlay['height'], $currentDate, 0, 0, 'L');
                }

                // Custom text overlay
                if ($customTextOverlay && isset($customTextOverlay['x'], $customTextOverlay['y'], $customTextOverlay['width'], $customTextOverlay['height']) && !empty($customTextOverlay['text'])) {
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->Rect($customTextOverlay['x'], $customTextOverlay['y'], $customTextOverlay['width'], $customTextOverlay['height'], 'F');
                    $ctFontSize = $customTextOverlay['fontSize'] ?? 12;
                    $pdf->SetFont('Arial', '', $ctFontSize);
                    $pdf->SetTextColor(0, 0, 0);
                    $lineHeight = $ctFontSize * 0.6;
                    $pdf->SetXY($customTextOverlay['x'], $customTextOverlay['y']);
                    $pdf->MultiCell($customTextOverlay['width'], $lineHeight, $customTextOverlay['text'], 0, 'L');
                }
            }
        }

        return $pdf->Output('S');
    } catch (Exception $e) {
        error_log("PDF Overlay Error: " . $e->getMessage());
        throw new Exception("Failed to generate PDF: " . $e->getMessage());
    }
}

function savePDFToFile($pdfContent, $outputPath) {
    try {
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $result = file_put_contents($outputPath, $pdfContent);
        if ($result === false) {
            throw new Exception("Failed to write PDF file");
        }
        return true;
    } catch (Exception $e) {
        error_log("PDF Save Error: " . $e->getMessage());
        return false;
    }
}

function generateProviderDocument($documentId, $providerName, $outputDir, $overrides = []) {
    try {
        $doc = dbFetchOne(
            "SELECT * FROM case_documents WHERE id = ? AND is_provider_template = 1",
            [$documentId]
        );

        if ($doc && !empty($overrides)) {
            $doc = array_merge($doc, $overrides);
        }

        if (!$doc) {
            return ['success' => false, 'error' => 'Template document not found'];
        }

        if (!$doc['provider_name_x'] || !$doc['provider_name_y'] ||
            !$doc['provider_name_width'] || !$doc['provider_name_height']) {
            return ['success' => false, 'error' => 'Template coordinates not configured'];
        }

        $templatePath = STORAGE_PATH . '/' . $doc['file_path'];

        if (!file_exists($templatePath)) {
            return ['success' => false, 'error' => 'Template file not found'];
        }

        $dateOverlay = null;
        if ($doc['use_date_overlay'] && $doc['date_x'] && $doc['date_y'] &&
            $doc['date_width'] && $doc['date_height']) {
            $dateOverlay = [
                'x' => (float)$doc['date_x'],
                'y' => (float)$doc['date_y'],
                'width' => (float)$doc['date_width'],
                'height' => (float)$doc['date_height'],
                'fontSize' => (int)$doc['date_font_size'] ?: 12
            ];
        }

        $customTextOverlay = null;
        if (!empty($doc['use_custom_text_overlay']) && $doc['custom_text_x'] && $doc['custom_text_y'] &&
            $doc['custom_text_width'] && $doc['custom_text_height'] && !empty($doc['custom_text_value'])) {
            $customTextOverlay = [
                'x' => (float)$doc['custom_text_x'],
                'y' => (float)$doc['custom_text_y'],
                'width' => (float)$doc['custom_text_width'],
                'height' => (float)$doc['custom_text_height'],
                'fontSize' => (int)$doc['custom_text_font_size'] ?: 12,
                'text' => $doc['custom_text_value']
            ];
        }

        $pdfContent = generateProviderPDF(
            $templatePath,
            $providerName,
            (float)$doc['provider_name_x'],
            (float)$doc['provider_name_y'],
            (float)$doc['provider_name_width'],
            (float)$doc['provider_name_height'],
            (int)$doc['provider_name_font_size'] ?: 12,
            $dateOverlay,
            $customTextOverlay
        );

        $timestamp = date('YmdHis');
        $safeProviderName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $providerName);
        $outputFilename = $doc['case_id'] . '_' . $safeProviderName . '_' . $timestamp . '.pdf';
        $outputPath = $outputDir . '/' . $outputFilename;

        if (!savePDFToFile($pdfContent, $outputPath)) {
            return ['success' => false, 'error' => 'Failed to save generated PDF'];
        }

        return [
            'success' => true,
            'file_path' => $outputPath,
            'filename' => $outputFilename
        ];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
