<?php
/**
 * PUT /api/health-ledger/{id}
 * Update a health ledger item
 */
$userId = requireAuth();
requirePermission('health_tracker');

$itemId = (int)($_GET['id'] ?? 0);
if (!$itemId) errorResponse('Item ID is required');

$item = dbFetchOne("SELECT id FROM health_ledger_items WHERE id = ?", [$itemId]);
if (!$item) errorResponse('Health ledger item not found', 404);

$input = getInput();
$data = [];

$strFields = [
    'client_name', 'case_number', 'insurance_carrier', 'claim_number',
    'member_id', 'carrier_contact_email', 'carrier_contact_fax', 'note'
];
foreach ($strFields as $field) {
    if (isset($input[$field])) $data[$field] = sanitizeString($input[$field]);
}

if (isset($input['case_id']))     $data['case_id']     = $input['case_id'] ? (int)$input['case_id'] : null;
if (isset($input['assigned_to'])) $data['assigned_to'] = $input['assigned_to'] ? (int)$input['assigned_to'] : null;

if (isset($input['overall_status'])) {
    $valid = ['not_started', 'requesting', 'follow_up', 'received', 'done'];
    if (!validateEnum($input['overall_status'], $valid)) errorResponse('Invalid overall_status');
    $data['overall_status'] = $input['overall_status'];
}

if (empty($data)) errorResponse('No fields to update');

dbUpdate('health_ledger_items', $data, 'id = ?', [$itemId]);
logActivity($userId, 'update', 'health_ledger_item', $itemId, $data);

successResponse(null, 'Health ledger item updated successfully');
