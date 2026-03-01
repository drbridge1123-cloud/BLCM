<?php
/**
 * PUT /api/commissions/{id}/toggle-check
 * Toggle check_received status
 */
$userId = requireAuth();
requirePermission('commissions');
$user = getCurrentUser();

$input = getInput();
$commId = (int)($input['case_id'] ?? 0);
if (!$commId) errorResponse('case_id is required');

$row = dbFetchOne("SELECT * FROM employee_commissions WHERE id = ? AND deleted_at IS NULL", [$commId]);
if (!$row) errorResponse('Commission not found', 404);

// Ownership check for non-admin
if (!in_array($user['role'], ['admin', 'manager']) && (int)$row['employee_user_id'] !== $userId) {
    errorResponse('Not authorized', 403);
}

$newVal = (int)$row['check_received'] ? 0 : 1;
dbUpdate('employee_commissions', ['check_received' => $newVal], 'id = ?', [$commId]);

successResponse(['check_received' => $newVal], 'Check received toggled');
