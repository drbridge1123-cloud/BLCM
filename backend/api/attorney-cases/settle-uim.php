<?php
/**
 * POST /api/attorney-cases/settle-uim
 * Settle a case in UIM phase
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$input = getInput();

$errors = validateRequired($input, ['case_id', 'settled']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId = (int)$input['case_id'];
$settled = (float)$input['settled'];
$checkReceived = !empty($input['check_received']) ? 1 : 0;

if ($settled <= 0) errorResponse('Settled amount must be greater than 0');

$case = dbFetchOne("SELECT * FROM attorney_cases WHERE id = ? AND deleted_at IS NULL", [$caseId]);
if (!$case) errorResponse('Case not found', 404);
if ($case['phase'] !== 'uim') errorResponse('Case is not in UIM phase');

// Check if attorney has commission
$attorney = dbFetchOne("SELECT commission_rate FROM users WHERE id = ?", [$case['attorney_user_id']]);
$hasCommission = $attorney && (float)$attorney['commission_rate'] > 0;

$today = date('Y-m-d');

$data = [
    'uim_settled' => $settled,
    'uim_settled_date' => $today,
    'uim_duration_days' => calculateDaysBetween($case['uim_start_date'], $today),
    'phase' => 'settled',
    'status' => 'unpaid',
    'check_received' => $checkReceived,
];

if ($hasCommission) {
    $discountedLegalFee = (float)($input['discounted_legal_fee'] ?? 0);
    $month = sanitizeString($input['month'] ?? date('M. Y'));
    if ($discountedLegalFee <= 0) errorResponse('Discounted legal fee must be greater than 0');

    $calc = calculateChongCommission('uim', null, $settled, 0, $discountedLegalFee);
    $uimCommission = round($discountedLegalFee * 0.05, 2);

    $data['uim_legal_fee'] = $calc['legal_fee'];
    $data['uim_discounted_legal_fee'] = $discountedLegalFee;
    $data['uim_commission'] = $uimCommission;
    $data['month'] = $month;
} else {
    $uimCommission = 0;
}

dbUpdate('attorney_cases', $data, 'id = ?', [$caseId]);

logActivity($userId, 'settle_uim', 'attorney_case', $caseId, [
    'uim_settled' => $settled,
    'uim_commission' => $uimCommission
]);

$totalCommission = (float)$case['commission'] + $uimCommission;

successResponse([
    'uim_commission' => $uimCommission,
    'total_commission' => $totalCommission
], 'UIM settled');
