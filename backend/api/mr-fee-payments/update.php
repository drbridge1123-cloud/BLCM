<?php
/**
 * PUT /api/mr-fee-payments/{id}
 * Update an existing MR fee payment
 */
$userId = requireAuth();
requirePermission('cases');

$paymentId = (int)($_GET['id'] ?? 0);
if (!$paymentId) errorResponse('Payment ID is required');

$payment = dbFetchOne("SELECT * FROM mr_fee_payments WHERE id = ?", [$paymentId]);
if (!$payment) errorResponse('Payment not found', 404);

$input = getInput();

$data = [];

if (isset($input['expense_category'])) {
    $valid = ['mr_cost', 'litigation', 'other'];
    if (!validateEnum($input['expense_category'], $valid)) errorResponse('Invalid expense_category');
    $data['expense_category'] = $input['expense_category'];
}

if (isset($input['payment_type'])) {
    $valid = ['check', 'card', 'cash', 'wire', 'other'];
    if (!validateEnum($input['payment_type'], $valid)) errorResponse('Invalid payment_type');
    $data['payment_type'] = $input['payment_type'];
}

$strFields = ['provider_name', 'description', 'check_number', 'notes'];
foreach ($strFields as $field) {
    if (isset($input[$field])) $data[$field] = sanitizeString($input[$field]);
}

$numFields = ['case_provider_id', 'paid_by'];
foreach ($numFields as $field) {
    if (isset($input[$field])) $data[$field] = $input[$field] ? (int)$input[$field] : null;
}

if (isset($input['billed_amount'])) $data['billed_amount'] = (float)$input['billed_amount'];
if (isset($input['paid_amount']))   $data['paid_amount']   = (float)$input['paid_amount'];
if (isset($input['payment_date']))  $data['payment_date']  = sanitizeString($input['payment_date']);

if (empty($data)) errorResponse('No fields to update');

dbUpdate('mr_fee_payments', $data, 'id = ?', [$paymentId]);
logActivity($userId, 'update', 'mr_fee_payment', $paymentId, $data);

successResponse(null, 'Payment updated successfully');
