<?php
// POST /api/accounting-tracker/create-disbursement
$userId = requireAuth();
requirePermission('accounting_tracker');
$input = getInput();

$errors = validateRequired($input, ['disbursement_type', 'payee_name', 'amount']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId = !empty($input['case_id']) ? (int)$input['case_id'] : null;
$attorneyCaseId = !empty($input['attorney_case_id']) ? (int)$input['attorney_case_id'] : null;

if (!$caseId && !$attorneyCaseId) {
    errorResponse('Either case_id or attorney_case_id is required');
}

// Validate the case exists and is in accounting
if ($attorneyCaseId) {
    $attCase = dbFetchOne("SELECT * FROM attorney_cases WHERE id = ? AND status = 'accounting' AND deleted_at IS NULL", [$attorneyCaseId]);
    if (!$attCase) errorResponse('Attorney case not found or not in accounting status', 404);
} else {
    $case = dbFetchOne("SELECT * FROM cases WHERE id = ? AND status = 'accounting'", [$caseId]);
    if (!$case) errorResponse('Case not found or not in accounting status', 404);
}

$data = [
    'disbursement_type' => sanitizeString($input['disbursement_type']),
    'payee_name' => sanitizeString($input['payee_name']),
    'amount' => (float)$input['amount'],
    'check_number' => sanitizeString($input['check_number'] ?? ''),
    'payment_method' => !empty($input['payment_method']) ? sanitizeString($input['payment_method']) : null,
    'payment_date' => $input['payment_date'] ?? null,
    'status' => sanitizeString($input['status'] ?? 'pending'),
    'notes' => sanitizeString($input['notes'] ?? ''),
    'created_by' => $userId,
];

if ($attorneyCaseId) {
    $data['attorney_case_id'] = $attorneyCaseId;
} else {
    $data['case_id'] = $caseId;
}

$id = dbInsert('accounting_disbursements', $data);

$logType = $attorneyCaseId ? 'attorney_case' : 'case';
$logId = $attorneyCaseId ?: $caseId;

logActivity($userId, 'create_disbursement', $logType, $logId, [
    'disbursement_id' => $id,
    'type' => $input['disbursement_type'],
    'amount' => $input['amount'],
    'payee' => $input['payee_name'],
]);

successResponse(['id' => $id], 'Disbursement created');
