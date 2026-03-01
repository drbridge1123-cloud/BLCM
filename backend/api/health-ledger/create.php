<?php
/**
 * POST /api/health-ledger
 * Create a new health ledger item
 */
$userId = requireAuth();
requirePermission('health_tracker');

$input = getInput();
$errors = validateRequired($input, ['client_name', 'insurance_carrier']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$data = [
    'client_name'       => sanitizeString($input['client_name']),
    'insurance_carrier' => sanitizeString($input['insurance_carrier']),
];

$optionalStr = [
    'case_number', 'claim_number', 'member_id',
    'carrier_contact_email', 'carrier_contact_fax', 'note'
];
foreach ($optionalStr as $field) {
    if (isset($input[$field])) $data[$field] = sanitizeString($input[$field]);
}

if (isset($input['case_id']))     $data['case_id']     = (int)$input['case_id'];
if (isset($input['assigned_to'])) $data['assigned_to'] = (int)$input['assigned_to'];

$newId = dbInsert('health_ledger_items', $data);

logActivity($userId, 'create', 'health_ledger_item', $newId, [
    'client_name' => $data['client_name'],
    'carrier'     => $data['insurance_carrier'],
]);

successResponse(['id' => $newId], 'Health ledger item created successfully');
