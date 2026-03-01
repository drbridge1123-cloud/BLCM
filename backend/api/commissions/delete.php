<?php
/**
 * DELETE /api/commissions/{id}
 * Soft-delete an employee commission entry
 */
$userId = requireAuth();
requirePermission('commissions');
$user = getCurrentUser();

$commId = (int)($_GET['id'] ?? 0);
if (!$commId) errorResponse('Commission ID required');

$row = dbFetchOne("SELECT * FROM employee_commissions WHERE id = ? AND deleted_at IS NULL", [$commId]);
if (!$row) errorResponse('Commission not found', 404);

// Ownership check
if (!in_array($user['role'], ['admin', 'manager'])) {
    if ((int)$row['employee_user_id'] !== $userId) {
        errorResponse('Not authorized', 403);
    }
    if (!in_array($row['status'], ['in_progress', 'unpaid'])) {
        errorResponse('Cannot delete a ' . $row['status'] . ' commission');
    }
}

dbUpdate('employee_commissions', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [$commId]);
logActivity($userId, 'commission_deleted', 'employee_commissions', $commId);

successResponse(null, 'Commission deleted');
