<?php
/**
 * POST /api/attorney-cases/settle-litigation
 * Settle a case in litigation phase
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
if ($case['phase'] !== 'litigation') errorResponse('Case is not in litigation phase');

// Check if attorney has commission
$attorney = dbFetchOne("SELECT commission_rate FROM users WHERE id = ?", [$case['attorney_user_id']]);
$hasCommission = $attorney && (float)$attorney['commission_rate'] > 0;

$today = date('Y-m-d');

$data = [
    'settled' => $settled,
    'litigation_settled_date' => $today,
    'litigation_duration_days' => calculateDaysBetween($case['litigation_start_date'], $today),
    'check_received' => $checkReceived,
];

if ($hasCommission) {
    $resolutionType = sanitizeString($input['resolution_type'] ?? '');
    $discountedLegalFee = (float)($input['discounted_legal_fee'] ?? 0);
    $month = sanitizeString($input['month'] ?? date('M. Y'));
    $manualFeeRate = isset($input['manual_fee_rate']) ? (float)$input['manual_fee_rate'] : null;
    $manualCommRate = isset($input['manual_commission_rate']) ? (float)$input['manual_commission_rate'] : null;
    $feeRateOverride = isset($input['fee_rate_override']) ? (float)$input['fee_rate_override'] : null;
    $note = isset($input['note']) ? sanitizeString($input['note']) : null;

    if (!$resolutionType) errorResponse('Resolution type is required');
    if ($discountedLegalFee <= 0) errorResponse('Discounted legal fee must be greater than 0');
    if ($feeRateOverride && !$note) errorResponse('Note is required when fee rate is overridden');

    $presuit = (float)($input['presuit_offer'] ?? $case['presuit_offer'] ?? 0);
    $calc = calculateChongCommission('litigation', $resolutionType, $settled, $presuit,
        $discountedLegalFee, $manualCommRate, $manualFeeRate, $feeRateOverride);

    $commission = round($discountedLegalFee * $calc['commission_rate'] / 100, 2);
    $difference = $settled - $presuit;

    $data['presuit_offer'] = $presuit;
    $data['difference'] = $difference;
    $data['legal_fee'] = $calc['legal_fee'];
    $data['discounted_legal_fee'] = $discountedLegalFee;
    $data['commission'] = $commission;
    $data['commission_type'] = $calc['commission_type'];
    $data['fee_rate'] = $calc['fee_rate'];
    $data['resolution_type'] = $resolutionType;
    $data['month'] = $month;

    if ($note !== null) $data['note'] = $note;
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

$logData = ['settled' => $settled, 'commission' => $commission, 'is_policy_limit' => $isPolicyLimit];
$resData = ['commission' => $commission, 'is_policy_limit' => $isPolicyLimit];
if ($hasCommission) {
    $logData['resolution_type'] = $resolutionType;
    $resData['result'] = $calc;
}

logActivity($userId, 'settle_litigation', 'attorney_case', $caseId, $logData);
successResponse($resData, 'Litigation settled');
