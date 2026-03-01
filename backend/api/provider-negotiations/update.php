<?php
/**
 * PUT /api/provider-negotiations/{id}
 * Update a provider negotiation
 */
$userId = requireAuth();
requirePermission('cases');

$id = (int)$_GET['id'];
$input = getInput();

$row = dbFetchOne("SELECT * FROM provider_negotiations WHERE id = ?", [$id]);
if (!$row) errorResponse('Provider negotiation not found', 404);

$allowed = [
    'original_balance', 'requested_reduction', 'accepted_amount',
    'reduction_percent', 'status', 'contact_name', 'contact_info', 'notes'
];
$validStatuses = ['pending', 'negotiating', 'accepted', 'rejected', 'waived'];

$data = [];
$changes = [];

foreach ($allowed as $field) {
    if (!array_key_exists($field, $input)) continue;
    $val = $input[$field];

    if (in_array($field, ['original_balance', 'requested_reduction', 'accepted_amount', 'reduction_percent'])) {
        $val = $val !== null && $val !== '' ? (float)$val : null;
    } elseif ($field === 'status') {
        if (!validateEnum($val, $validStatuses)) errorResponse('Invalid status');
    } else {
        $val = sanitizeString((string)$val);
    }

    if ((string)($val ?? '') !== (string)($row[$field] ?? '')) {
        $changes[$field] = ['from' => $row[$field], 'to' => $val];
    }
    $data[$field] = $val;
}

if (empty($data)) errorResponse('No fields to update');

// Auto-recalc reduction_percent if balance or accepted changed
$ob = $data['original_balance'] ?? (float)$row['original_balance'];
$aa = $data['accepted_amount'] ?? ($row['accepted_amount'] !== null ? (float)$row['accepted_amount'] : null);
if ($ob > 0 && $aa !== null && (isset($data['original_balance']) || isset($data['accepted_amount']))) {
    $data['reduction_percent'] = round(($ob - $aa) / $ob * 100, 2);
}

dbUpdate('provider_negotiations', $data, 'id = ?', [$id]);

if (!empty($changes)) {
    logActivity($userId, 'update', 'provider_negotiation', $id, $changes);
}

successResponse(null, 'Provider negotiation updated');
