<?php
/**
 * POST /api/mr-fee-payments
 * Create a new MR fee payment
 */
$userId = requireAuth();
requirePermission('cases');

$input = getInput();
$errors = validateRequired($input, ['case_id', 'expense_category']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$validCategories = ['mr_cost', 'litigation', 'other'];
if (!validateEnum($input['expense_category'], $validCategories)) {
    errorResponse('Invalid expense_category');
}

if (!empty($input['payment_type'])) {
    $validTypes = ['check', 'card', 'cash', 'wire', 'other'];
    if (!validateEnum($input['payment_type'], $validTypes)) {
        errorResponse('Invalid payment_type');
    }
}

$data = [
    'case_id'          => (int)$input['case_id'],
    'expense_category' => $input['expense_category'],
    'created_by'       => $userId,
];

$optionalStr = ['provider_name', 'description', 'check_number', 'notes'];
foreach ($optionalStr as $field) {
    if (isset($input[$field])) $data[$field] = sanitizeString($input[$field]);
}

$optionalNum = ['case_provider_id', 'paid_by', 'receipt_document_id'];
foreach ($optionalNum as $field) {
    if (isset($input[$field])) $data[$field] = (int)$input[$field];
}

if (isset($input['billed_amount'])) $data['billed_amount'] = (float)$input['billed_amount'];
if (isset($input['paid_amount']))   $data['paid_amount']   = (float)$input['paid_amount'];
if (isset($input['payment_type']))  $data['payment_type']  = $input['payment_type'];
if (isset($input['payment_date']))  $data['payment_date']  = sanitizeString($input['payment_date']);

$newId = dbInsert('mr_fee_payments', $data);
logActivity($userId, 'create', 'mr_fee_payment', $newId, [
    'case_id' => $data['case_id'], 'category' => $data['expense_category']
]);

successResponse(['id' => $newId], 'Payment created successfully');
