<?php
/**
 * PUT /api/commissions/bulk-approve
 * Bulk approve or reject commissions (admin/manager only)
 */
$userId = requireAuth();
requirePermission('commission_admin');
$input = getInput();

$caseIds = $input['case_ids'] ?? [];
$action  = $input['action'] ?? '';

if (empty($caseIds) || !is_array($caseIds)) {
    errorResponse('case_ids array is required');
}
if (count($caseIds) > 100) {
    errorResponse('Maximum 100 cases per bulk operation');
}
if (!in_array($action, ['approve', 'reject'])) {
    errorResponse('Action must be approve or reject');
}

$newStatus = $action === 'approve' ? 'paid' : 'rejected';
$updated = 0;
$skipped = 0;

$pdo = getDbConnection();
$pdo->beginTransaction();

try {
    foreach ($caseIds as $id) {
        $id = (int)$id;
        $row = dbFetchOne("SELECT status, check_received FROM employee_commissions WHERE id = ? AND deleted_at IS NULL", [$id]);

        if (!$row || $row['status'] !== 'unpaid') {
            $skipped++;
            continue;
        }

        if ($action === 'approve' && !(int)$row['check_received']) {
            $skipped++;
            continue;
        }

        dbUpdate('employee_commissions', [
            'status'      => $newStatus,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'reviewed_by' => $userId,
        ], 'id = ?', [$id]);
        $updated++;
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Bulk operation failed: ' . $e->getMessage(), 500);
}

$response = ['updated' => $updated, 'skipped' => $skipped];
if ($skipped > 0) {
    $response['warning'] = "{$skipped} case(s) skipped (not unpaid or check not received)";
}

logActivity($userId, 'commission_bulk_' . $action, 'employee_commissions', null, $response);

successResponse($response, "Bulk {$action} completed");
