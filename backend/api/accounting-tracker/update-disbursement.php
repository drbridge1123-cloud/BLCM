<?php
// PUT /api/accounting-tracker/update-disbursement
$userId = requireAuth();
requirePermission('accounting_tracker');
$input = getInput();

$id = (int)($input['id'] ?? 0);
if (!$id) errorResponse('Disbursement ID required', 400);

$disb = dbFetchOne("SELECT * FROM accounting_disbursements WHERE id = ?", [$id]);
if (!$disb) errorResponse('Disbursement not found', 404);

$data = [];
if (array_key_exists('status', $input)) $data['status'] = sanitizeString($input['status']);
if (array_key_exists('check_number', $input)) $data['check_number'] = sanitizeString($input['check_number']);
if (array_key_exists('payment_date', $input)) $data['payment_date'] = $input['payment_date'];
if (array_key_exists('payment_method', $input)) $data['payment_method'] = sanitizeString($input['payment_method']);
if (array_key_exists('amount', $input)) $data['amount'] = (float)$input['amount'];
if (array_key_exists('payee_name', $input)) $data['payee_name'] = sanitizeString($input['payee_name']);
if (array_key_exists('notes', $input)) $data['notes'] = sanitizeString($input['notes']);

if (!empty($data)) {
    $data['processed_by'] = $userId;
    dbUpdate('accounting_disbursements', $data, 'id = ?', [$id]);
}

logActivity($userId, 'update_disbursement', 'case', $disb['case_id'], [
    'disbursement_id' => $id,
    'changes' => array_keys($data),
]);

successResponse(null, 'Disbursement updated');
