<?php
/**
 * POST /api/documents/upload
 * Upload document to case
 */
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

require_once __DIR__ . '/../../helpers/file-upload.php';

// Validate required fields
if (!isset($_POST['case_id']) || empty($_POST['case_id'])) {
    errorResponse('case_id is required', 400);
}

$caseId = (int)$_POST['case_id'];

// Verify case exists
$case = dbFetchOne("SELECT id, case_number FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
    errorResponse('No file uploaded', 400);
}

$file = $_FILES['file'];

// Get optional fields
$documentType = $_POST['document_type'] ?? 'other';
$caseProviderId = !empty($_POST['case_provider_id']) ? (int)$_POST['case_provider_id'] : null;
$notes = $_POST['notes'] ?? null;

// Provider template fields
$isProviderTemplate = isset($_POST['is_provider_template']) && $_POST['is_provider_template'] == '1';
$providerNameX = !empty($_POST['provider_name_x']) ? (float)$_POST['provider_name_x'] : null;
$providerNameY = !empty($_POST['provider_name_y']) ? (float)$_POST['provider_name_y'] : null;
$providerNameWidth = !empty($_POST['provider_name_width']) ? (float)$_POST['provider_name_width'] : null;
$providerNameHeight = !empty($_POST['provider_name_height']) ? (float)$_POST['provider_name_height'] : null;
$providerNameFontSize = !empty($_POST['provider_name_font_size']) ? (int)$_POST['provider_name_font_size'] : 12;

// Date overlay fields
$useDateOverlay = isset($_POST['use_date_overlay']) && $_POST['use_date_overlay'] == '1';
$dateX = !empty($_POST['date_x']) ? (float)$_POST['date_x'] : null;
$dateY = !empty($_POST['date_y']) ? (float)$_POST['date_y'] : null;
$dateWidth = !empty($_POST['date_width']) ? (float)$_POST['date_width'] : null;
$dateHeight = !empty($_POST['date_height']) ? (float)$_POST['date_height'] : null;
$dateFontSize = !empty($_POST['date_font_size']) ? (int)$_POST['date_font_size'] : 12;

// Custom text overlay fields
$useCustomTextOverlay = isset($_POST['use_custom_text_overlay']) && $_POST['use_custom_text_overlay'] == '1';
$customTextValue = !empty($_POST['custom_text_value']) ? sanitizeString($_POST['custom_text_value']) : null;
$customTextX = !empty($_POST['custom_text_x']) ? (float)$_POST['custom_text_x'] : null;
$customTextY = !empty($_POST['custom_text_y']) ? (float)$_POST['custom_text_y'] : null;
$customTextWidth = !empty($_POST['custom_text_width']) ? (float)$_POST['custom_text_width'] : null;
$customTextHeight = !empty($_POST['custom_text_height']) ? (float)$_POST['custom_text_height'] : null;
$customTextFontSize = !empty($_POST['custom_text_font_size']) ? (int)$_POST['custom_text_font_size'] : 12;

// Validate document type
$allowedTypes = ['hipaa_authorization', 'signed_release', 'other'];
if (!in_array($documentType, $allowedTypes)) {
    errorResponse('Invalid document_type. Allowed: ' . implode(', ', $allowedTypes), 422);
}

// Determine subdirectory based on document type
$subdir = 'other';
if ($documentType === 'hipaa_authorization') {
    $subdir = 'hipaa';
} elseif ($documentType === 'signed_release') {
    $subdir = 'releases';
}

// Store the file
$result = storeUploadedFile($file, $subdir, $caseId);

if (!$result['success']) {
    errorResponse($result['error'], 422);
}

// Get file info
$finfo = new finfo(FILEINFO_MIME_TYPE);
$tempPath = STORAGE_PATH . '/' . $result['file_path'];
$mimeType = file_exists($tempPath) ? $finfo->file($tempPath) : getMimeTypeFromExtension(pathinfo($file['name'], PATHINFO_EXTENSION));

// Insert document record
$documentData = [
    'case_id' => $caseId,
    'case_provider_id' => $caseProviderId,
    'document_type' => $documentType,
    'file_name' => $result['file_name'],
    'original_file_name' => sanitizeString($file['name']),
    'file_path' => $result['file_path'],
    'file_size' => $file['size'],
    'mime_type' => $mimeType,
    'uploaded_by' => $userId,
    'notes' => $notes ? sanitizeString($notes) : null,
    'is_provider_template' => $isProviderTemplate ? 1 : 0,
    'provider_name_x' => $providerNameX,
    'provider_name_y' => $providerNameY,
    'provider_name_width' => $providerNameWidth,
    'provider_name_height' => $providerNameHeight,
    'provider_name_font_size' => $providerNameFontSize,
    'use_date_overlay' => $useDateOverlay ? 1 : 0,
    'date_x' => $dateX,
    'date_y' => $dateY,
    'date_width' => $dateWidth,
    'date_height' => $dateHeight,
    'date_font_size' => $dateFontSize,
    'use_custom_text_overlay' => $useCustomTextOverlay ? 1 : 0,
    'custom_text_value' => $customTextValue,
    'custom_text_x' => $customTextX,
    'custom_text_y' => $customTextY,
    'custom_text_width' => $customTextWidth,
    'custom_text_height' => $customTextHeight,
    'custom_text_font_size' => $customTextFontSize
];

$documentId = dbInsert('case_documents', $documentData);

logActivity($userId, 'document_uploaded', 'case_document', $documentId, [
    'case_id' => $caseId,
    'case_number' => $case['case_number'],
    'file_name' => $file['name'],
    'file_size' => formatBytes($file['size'])
]);

successResponse([
    'id' => $documentId,
    'file_name' => $result['file_name'],
    'original_file_name' => $file['name'],
    'file_size' => $file['size'],
    'file_size_formatted' => formatBytes($file['size'])
], 'Document uploaded successfully');
