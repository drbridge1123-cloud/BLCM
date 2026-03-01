<?php
/**
 * PUT /api/commissions/{id}/approve
 * Approve or reject a single commission (admin/manager only)
 */
$userId = requireAuth();
requirePermission('commission_admin');
$input = getInput();

$commId = (int)($input['case_id'] ?? 0);
$action = $input['action'] ?? '';

if (!$commId) errorResponse('case_id is required');
if (!in_array($action, ['approve', 'reject'])) {
    errorResponse('Action must be approve or reject');
}

$row = dbFetchOne("SELECT * FROM employee_commissions WHERE id = ? AND deleted_at IS NULL", [$commId]);
if (!$row) errorResponse('Commission not found', 404);

if ($row['status'] !== 'unpaid') {
    errorResponse('Only unpaid commissions can be approved/rejected');
}

if ($action === 'approve' && !(int)$row['check_received']) {
    errorResponse('Check must be received before approving');
}

$newStatus = $action === 'approve' ? 'paid' : 'rejected';

dbUpdate('employee_commissions', [
    'status'      => $newStatus,
    'reviewed_at' => date('Y-m-d H:i:s'),
    'reviewed_by' => $userId,
], 'id = ?', [$commId]);

logActivity($userId, 'commission_' . $action . 'd', 'employee_commissions', $commId);

successResponse(['status' => $newStatus], 'Commission ' . $action . 'd');
