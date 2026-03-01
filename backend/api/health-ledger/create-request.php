<?php
/**
 * POST /api/health-ledger/{id}/requests
 * Create a new HL request for an item
 */
$userId = requireAuth();
requirePermission('health_tracker');

$itemId = (int)($_GET['id'] ?? 0);
if (!$itemId) errorResponse('Item ID is required');

$item = dbFetchOne("SELECT id FROM health_ledger_items WHERE id = ?", [$itemId]);
if (!$item) errorResponse('Health ledger item not found', 404);

$input = getInput();
$errors = validateRequired($input, ['request_type', 'request_method', 'request_date']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$validTypes = ['initial', 'follow_up', 're_request'];
if (!validateEnum($input['request_type'], $validTypes)) errorResponse('Invalid request_type');

$validMethods = ['fax', 'email', 'portal', 'phone', 'mail'];
if (!validateEnum($input['request_method'], $validMethods)) errorResponse('Invalid request_method');

$data = [
    'item_id'        => $itemId,
    'request_type'   => $input['request_type'],
    'request_method' => $input['request_method'],
    'request_date'   => sanitizeString($input['request_date']),
    'created_by'     => $userId,
];

$optionalStr = ['sent_to', 'letter_html', 'next_followup_date', 'notes'];
foreach ($optionalStr as $field) {
    if (isset($input[$field])) $data[$field] = sanitizeString($input[$field]);
}

if (isset($input['template_id']))   $data['template_id']   = (int)$input['template_id'];
if (isset($input['template_data'])) $data['template_data'] = json_encode($input['template_data']);

$newId = dbInsert('hl_requests', $data);

// Update parent item overall_status based on request type
$newStatus = $input['request_type'] === 'initial' ? 'requesting' : 'follow_up';
dbUpdate('health_ledger_items', ['overall_status' => $newStatus], 'id = ?', [$itemId]);

logActivity($userId, 'create', 'hl_request', $newId, [
    'item_id' => $itemId, 'type' => $input['request_type']
]);

successResponse(['id' => $newId], 'Request created successfully');
