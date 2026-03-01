<?php
/**
 * DELETE /api/documents/{id}
 * Delete a case document and its file from disk
 */
$userId = requireAuth();
$id     = (int)$_GET['id'];

$doc = dbFetchOne("SELECT * FROM case_documents WHERE id = ?", [$id]);
if (!$doc) errorResponse('Document not found', 404);

// Delete file from disk
$filePath = $doc['file_path'];
if ($filePath) {
    // Resolve relative paths from storage base
    if (strpos($filePath, '/') !== 0 && strpos($filePath, ':') === false) {
        $filePath = STORAGE_PATH . '/documents/' . $filePath;
    }
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

dbDelete('case_documents', 'id = ?', [$id]);

logActivity($userId, 'delete', 'case_document', $id, [
    'case_id'   => $doc['case_id'],
    'file_name' => $doc['original_file_name'],
]);

successResponse(null, 'Document deleted');
