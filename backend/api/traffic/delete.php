<?php
/**
 * DELETE /api/traffic/{id}
 * Delete a traffic case
 */
$userId = requireAuth();
requirePermission('traffic');
$user = getCurrentUser();

$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) errorResponse('Case ID required');

$row = dbFetchOne("SELECT * FROM traffic_cases WHERE id = ?", [$caseId]);
if (!$row) errorResponse('Traffic case not found', 404);

// Ownership check
if (!in_array($user['role'], ['admin', 'manager']) && (int)$row['user_id'] !== $userId) {
    errorResponse('Not authorized', 403);
}

// Delete associated files from disk
$files = dbFetchAll("SELECT * FROM traffic_case_files WHERE case_id = ?", [$caseId]);
foreach ($files as $f) {
    $filePath = __DIR__ . '/../../../storage/traffic-files/' . $caseId . '/' . $f['filename'];
    if (file_exists($filePath)) unlink($filePath);
}

// Delete file records and case
dbQuery("DELETE FROM traffic_case_files WHERE case_id = ?", [$caseId]);
dbQuery("DELETE FROM traffic_cases WHERE id = ?", [$caseId]);

logActivity($userId, 'traffic_case_deleted', 'traffic_cases', $caseId);

successResponse(null, 'Traffic case deleted');
