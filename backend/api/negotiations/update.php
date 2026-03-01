<?php
/**
 * PUT /api/negotiations/{id}
 * Update a case negotiation
 */
$userId = requireAuth();
requirePermission('cases');

$id = (int)$_GET['id'];
$input = getInput();

$row = dbFetchOne("SELECT * FROM case_negotiations WHERE id = ?", [$id]);
if (!$row) errorResponse('Negotiation not found', 404);

$allowed = [
    'demand_date', 'demand_amount', 'offer_date', 'offer_amount',
    'insurance_company', 'party', 'adjuster_phone', 'adjuster_fax',
    'adjuster_email', 'claim_number', 'status', 'notes'
];

$data = [];
$changes = [];

foreach ($allowed as $field) {
    if (!array_key_exists($field, $input)) continue;
    $val = $input[$field];

    if (in_array($field, ['demand_amount', 'offer_amount'])) {
        $val = $val !== null && $val !== '' ? (float)$val : null;
    } elseif ($field === 'status') {
        if (!validateEnum($val, ['pending','countered','accepted','rejected'])) {
            errorResponse('Invalid status');
        }
    } else {
        $val = sanitizeString((string)$val);
    }

    if ((string)($val ?? '') !== (string)($row[$field] ?? '')) {
        $changes[$field] = ['from' => $row[$field], 'to' => $val];
    }
    $data[$field] = $val;
}

if (empty($data)) errorResponse('No fields to update');

dbUpdate('case_negotiations', $data, 'id = ?', [$id]);

if (!empty($changes)) {
    logActivity($userId, 'update', 'case_negotiation', $id, $changes);
}

successResponse(null, 'Negotiation updated');
