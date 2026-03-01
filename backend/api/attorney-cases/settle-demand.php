<?php
/**
 * POST /api/attorney-cases/settle-demand
 * Settle a case in demand phase
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$input = getInput();

$errors = validateRequired($input, ['case_id', 'settled']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId = (int)$input['case_id'];
$settled = (float)$input['settled'];
$checkReceived = !empty($input['check_received']) ? 1 : 0;
$isPolicyLimit = !empty($input['is_policy_limit']) ? 1 : 0;

if ($settled <= 0) errorResponse('Settled amount must be greater than 0');

$case = dbFetchOne("SELECT * FROM attorney_cases WHERE id = ? AND deleted_at IS NULL", [$caseId]);
if (!$case) errorResponse('Case not found', 404);
if ($case['phase'] !== 'demand') errorResponse('Case is not in demand phase');

// Check if attorney has commission
$attorney = dbFetchOne("SELECT commission_rate FROM users WHERE id = ?", [$case['attorney_user_id']]);
$hasCommission = $attorney && (float)$attorney['commission_rate'] > 0;

$today = date('Y-m-d');

$data = [
    'settled' => $settled,
    'demand_settled_date' => $today,
    'demand_duration_days' => calculateDaysBetween($case['assigned_date'], $today),
    'check_received' => $checkReceived,
];

if ($hasCommission) {
    $discountedLegalFee = (float)($input['discounted_legal_fee'] ?? 0);
    $month = sanitizeString($input['month'] ?? date('M. Y'));
    if ($discountedLegalFee <= 0) errorResponse('Discounted legal fee must be greater than 0');

    $calc = calculateChongCommission('demand', null, $settled, 0, $discountedLegalFee);
    $commission = round($discountedLegalFee * 0.05, 2);

    $data['discounted_legal_fee'] = $discountedLegalFee;
    $data['legal_fee'] = $calc['legal_fee'];
    $data['commission'] = $commission;
    $data['commission_type'] = $calc['commission_type'];
    $data['month'] = $month;
} else {
    $commission = 0;
}

if ($isPolicyLimit) {
    $data['phase'] = 'uim';
    $data['uim_start_date'] = $today;
} else {
    $data['phase'] = 'settled';
    $data['status'] = 'unpaid';
}

dbUpdate('attorney_cases', $data, 'id = ?', [$caseId]);

logActivity($userId, 'settle_demand', 'attorney_case', $caseId, [
    'settled' => $settled,
    'commission' => $commission,
    'is_policy_limit' => $isPolicyLimit
]);

successResponse([
    'commission' => $commission,
    'is_policy_limit' => $isPolicyLimit
], 'Demand settled');
