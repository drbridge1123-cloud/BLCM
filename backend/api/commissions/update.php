<?php
/**
 * PUT /api/commissions/{id}
 * Update an employee commission entry
 */
$userId = requireAuth();
requirePermission('commissions');
$user = getCurrentUser();
$input = getInput();

$commId = (int)($_GET['id'] ?? 0);
if (!$commId) errorResponse('Commission ID required');

$row = dbFetchOne("SELECT * FROM employee_commissions WHERE id = ? AND deleted_at IS NULL", [$commId]);
if (!$row) errorResponse('Commission not found', 404);

// Ownership check
if (!in_array($user['role'], ['admin', 'manager']) && (int)$row['employee_user_id'] !== $userId) {
    errorResponse('Not authorized', 403);
}

// Employees can only edit in_progress or unpaid
if (!in_array($user['role'], ['admin', 'manager']) && !in_array($row['status'], ['in_progress', 'unpaid'])) {
    errorResponse('Cannot edit a ' . $row['status'] . ' commission');
}

// Check for partial update (toggle only)
$isToggleOnly = isset($input['check_received']) && count($input) === 1;
$isStatusOnly = isset($input['status']) && count($input) === 1;

if ($isToggleOnly) {
    dbUpdate('employee_commissions', ['check_received' => $input['check_received'] ? 1 : 0], 'id = ?', [$commId]);
    successResponse(null, 'Check received updated');
}

if ($isStatusOnly && in_array($user['role'], ['admin', 'manager'])) {
    dbUpdate('employee_commissions', ['status' => $input['status']], 'id = ?', [$commId]);
    successResponse(null, 'Status updated');
}

// Full update — recalculate financials
$employee = dbFetchOne("SELECT commission_rate, uses_presuit_offer FROM users WHERE id = ?", [(int)$row['employee_user_id']]);

$settled      = (float)($input['settled'] ?? $row['settled']);
$presuitOffer = (float)($input['presuit_offer'] ?? $row['presuit_offer']);
$feeRate      = (float)($input['fee_rate'] ?? $row['fee_rate']);
$isMarketing  = isset($input['is_marketing']) ? ($input['is_marketing'] ? 1 : 0) : (int)$row['is_marketing'];
$commRate     = $isMarketing ? 5.0 : (float)$employee['commission_rate'];
$usesPresuit  = (int)$employee['uses_presuit_offer'];

$calc = calculateCaseFinancials($settled, $presuitOffer, $feeRate, $commRate, $usesPresuit);

// Auto-status (preserve rejected/paid)
$currentStatus = $row['status'];
if ($currentStatus === 'rejected' && !in_array($user['role'], ['admin', 'manager'])) {
    $status = 'rejected';
} elseif ($currentStatus === 'paid') {
    $status = 'paid';
} else {
    $status = $settled > 0 ? 'unpaid' : 'in_progress';
}

// Admin can override status
if (in_array($user['role'], ['admin', 'manager']) && isset($input['status'])) {
    $status = $input['status'];
}

$data = [
    'case_number'          => trim($input['case_number'] ?? $row['case_number']),
    'client_name'          => trim($input['client_name'] ?? $row['client_name']),
    'case_type'            => $input['case_type'] ?? $row['case_type'],
    'settled'              => $calc['settled'],
    'presuit_offer'        => $calc['presuit_offer'],
    'difference'           => $calc['difference'],
    'fee_rate'             => $calc['fee_rate'],
    'legal_fee'            => $calc['legal_fee'],
    'discounted_legal_fee' => $calc['discounted_legal_fee'],
    'commission_rate'      => $calc['commission_rate'],
    'commission'           => $calc['commission'],
    'is_marketing'         => $isMarketing,
    'status'               => $status,
    'check_received'       => isset($input['check_received']) ? ($input['check_received'] ? 1 : 0) : (int)$row['check_received'],
    'month'                => $input['month'] ?? $row['month'],
    'note'                 => trim($input['note'] ?? $row['note'] ?? ''),
];

dbUpdate('employee_commissions', $data, 'id = ?', [$commId]);
logActivity($userId, 'commission_updated', 'employee_commissions', $commId);

successResponse(null, 'Commission updated');
