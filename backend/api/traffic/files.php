<?php
/**
 * Traffic Case File Management
 * POST: Upload file
 * GET: List files or download
 * DELETE: Delete file
 */
$userId = requireAuth();
requirePermission('traffic');
$user = getCurrentUser();

$method = $_SERVER['REQUEST_METHOD'];

// ── LIST FILES ──
if ($method === 'GET' && isset($_GET['case_id'])) {
    $caseId = (int)$_GET['case_id'];
    $files = dbFetchAll(
        "SELECT f.*, u.full_name AS uploaded_by_name
         FROM traffic_case_files f
         LEFT JOIN users u ON f.uploaded_by = u.id
         WHERE f.case_id = ?
         ORDER BY f.uploaded_at DESC",
        [$caseId]
    );
    successResponse($files);
}

// ── DOWNLOAD FILE ──
if ($method === 'GET' && isset($_GET['file_id'])) {
    $fileId = (int)$_GET['file_id'];
    $file = dbFetchOne("SELECT * FROM traffic_case_files WHERE id = ?", [$fileId]);
    if (!$file) errorResponse('File not found', 404);

    $filePath = __DIR__ . '/../../../storage/traffic-files/' . $file['case_id'] . '/' . $file['filename'];
    if (!file_exists($filePath)) errorResponse('File not found on disk', 404);

    header('Content-Type: ' . ($file['file_type'] ?: 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
}

// ── UPLOAD FILE ──
if ($method === 'POST') {
    $caseId = (int)($_POST['case_id'] ?? 0);
    if (!$caseId) errorResponse('case_id is required');

    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        errorResponse('No file uploaded or upload error');
    }

    $file = $_FILES['file'];
    $maxSize = 20 * 1024 * 1024; // 20MB
    if ($file['size'] > $maxSize) errorResponse('File too large (max 20MB)');

    $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) errorResponse('File type not allowed');

    $dir = __DIR__ . '/../../../storage/traffic-files/' . $caseId;
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $filename = uniqid() . '_' . time() . '.' . $ext;
    $dest = $dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        errorResponse('Failed to save file', 500);
    }

    $fileId = dbInsert('traffic_case_files', [
        'case_id'       => $caseId,
        'filename'      => $filename,
        'original_name' => $file['name'],
        'file_type'     => $file['type'],
        'file_size'     => $file['size'],
        'uploaded_by'   => $userId,
    ]);

    successResponse([
        'id' => $fileId,
        'filename' => $filename,
        'original_name' => $file['name'],
    ], 'File uploaded');
}

// ── DELETE FILE ──
if ($method === 'DELETE') {
    $input = getInput();
    $fileId = (int)($input['file_id'] ?? $_GET['file_id'] ?? 0);
    if (!$fileId) errorResponse('file_id is required');

    $file = dbFetchOne("SELECT * FROM traffic_case_files WHERE id = ?", [$fileId]);
    if (!$file) errorResponse('File not found', 404);

    $filePath = __DIR__ . '/../../../storage/traffic-files/' . $file['case_id'] . '/' . $file['filename'];
    if (file_exists($filePath)) unlink($filePath);

    dbQuery("DELETE FROM traffic_case_files WHERE id = ?", [$fileId]);

    successResponse(null, 'File deleted');
}

errorResponse('Invalid request', 400);
