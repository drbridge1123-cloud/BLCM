<?php
/**
 * POST /api/commissions
 * Create a new employee commission entry
 */
$userId = requireAuth();
requirePermission('commissions');
$user = getCurrentUser();
$input = getInput();

$caseNumber = trim($input['case_number'] ?? '');
$clientName = trim($input['client_name'] ?? '');

if (!$caseNumber || !$clientName) {
    errorResponse('Case number and client name are required');
}

// Determine target employee
$employeeId = $userId;
if (in_array($user['role'], ['admin', 'manager']) && !empty($input['employee_user_id'])) {
    $employeeId = (int)$input['employee_user_id'];
}

// Get employee commission rate
$employee = dbFetchOne("SELECT commission_rate, uses_presuit_offer FROM users WHERE id = ?", [$employeeId]);
if (!$employee) {
    errorResponse('Employee not found', 404);
}

$settled       = (float)($input['settled'] ?? 0);
$presuitOffer  = (float)($input['presuit_offer'] ?? 0);
$feeRate       = (float)($input['fee_rate'] ?? FEE_RATE_STANDARD);
$isMarketing   = !empty($input['is_marketing']) ? 1 : 0;
$commRate      = $isMarketing ? 5.0 : (float)$employee['commission_rate'];
$usesPresuit   = (int)$employee['uses_presuit_offer'];

// Calculate financials
$calc = calculateCaseFinancials($settled, $presuitOffer, $feeRate, $commRate, $usesPresuit);

// Auto-status
$status = $settled > 0 ? 'unpaid' : 'in_progress';

$id = dbInsert('employee_commissions', [
    'case_number'          => $caseNumber,
    'client_name'          => $clientName,
    'case_type'            => $input['case_type'] ?? 'Auto',
    'employee_user_id'     => $employeeId,
    'created_by'           => $userId,
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
    'check_received'       => !empty($input['check_received']) ? 1 : 0,
    'month'                => $input['month'] ?? 'TBD',
    'note'                 => trim($input['note'] ?? ''),
]);

logActivity($userId, 'commission_created', 'employee_commissions', $id, [
    'case_number' => $caseNumber, 'commission' => $calc['commission']
]);

successResponse(['id' => $id], 'Commission created');
