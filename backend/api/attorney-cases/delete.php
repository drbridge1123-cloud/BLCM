<?php
/**
 * DELETE /api/attorney-cases/{id}
 * Soft-delete an attorney case (sets deleted_at)
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$user = getCurrentUser();
$id = (int)$_GET['id'];

$case = dbFetchOne(
    "SELECT id, case_number, client_name, status, attorney_user_id
     FROM attorney_cases WHERE id = ? AND deleted_at IS NULL",
    [$id]
);
if (!$case) errorResponse('Attorney case not found', 404);

// Non-admin can only delete in_progress or unpaid cases
if (!in_array($user['role'], ['admin', 'manager'])) {
    if (!in_array($case['status'], ['in_progress', 'unpaid'])) {
        errorResponse('Only in-progress or unpaid cases can be deleted', 403);
    }
    if ((int)$case['attorney_user_id'] !== $userId) {
        errorResponse('Access denied', 403);
    }
}

dbUpdate('attorney_cases', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);

logActivity($userId, 'delete', 'attorney_case', $id, [
    'case_number' => $case['case_number'],
    'client_name' => $case['client_name'],
]);

successResponse(null, 'Attorney case deleted successfully');
