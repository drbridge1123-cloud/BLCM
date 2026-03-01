<?php
/**
 * PUT /api/attorney-cases/{id}
 * Update an existing attorney case
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$id = (int)$_GET['id'];
$input = getInput();

$case = dbFetchOne("SELECT * FROM attorney_cases WHERE id = ? AND deleted_at IS NULL", [$id]);
if (!$case) errorResponse('Attorney case not found', 404);

$allowedFields = [
    'case_number', 'client_name', 'case_type', 'month', 'note', 'status',
    'stage', 'check_received', 'is_marketing', 'attorney_user_id', 'assigned_date',
    'demand_out_date', 'negotiate_date', 'demand_deadline', 'top_offer_date',
    'settled', 'fee_rate', 'discounted_legal_fee', 'commission',
    'uim_settled', 'uim_discounted_legal_fee', 'uim_commission'
];

$data    = [];
$changes = [];

foreach ($allowedFields as $field) {
    if (!array_key_exists($field, $input)) continue;
    $newValue = $input[$field];

    switch ($field) {
        case 'case_number':
        case 'client_name':
        case 'case_type':
        case 'month':
        case 'note':
        case 'stage':
        case 'status':
            $newValue = sanitizeString($newValue);
            break;
        case 'settled':
        case 'fee_rate':
        case 'discounted_legal_fee':
        case 'commission':
        case 'uim_settled':
        case 'uim_discounted_legal_fee':
        case 'uim_commission':
            $newValue = (float)$newValue;
            break;
        case 'assigned_date':
        case 'demand_out_date':
        case 'negotiate_date':
        case 'demand_deadline':
        case 'top_offer_date':
            $newValue = sanitizeString($newValue);
            if ($newValue && !validateDate($newValue)) {
                errorResponse("Invalid {$field} format (YYYY-MM-DD)");
            }
            if (!$newValue) $newValue = null;
            break;
        case 'attorney_user_id':
            $newValue = $newValue ? (int)$newValue : null;
            break;
        case 'check_received':
        case 'is_marketing':
            $newValue = (int)(bool)$newValue;
            break;
    }

    $oldValue = $case[$field] ?? null;
    if ((string)$newValue !== (string)$oldValue) {
        $changes[$field] = ['from' => $oldValue, 'to' => $newValue];
    }
    $data[$field] = $newValue;
}

if (empty($data)) errorResponse('No fields to update');

// Recalculate demand_deadline when assigned_date changes (only if not manually set)
if (isset($data['assigned_date']) && $data['assigned_date'] && !isset($data['demand_deadline'])) {
    $data['demand_deadline'] = calculateDemandDeadline($data['assigned_date']);
    $changes['demand_deadline'] = [
        'from' => $case['demand_deadline'],
        'to'   => $data['demand_deadline'],
    ];
}

dbUpdate('attorney_cases', $data, 'id = ?', [$id]);

if (!empty($changes)) {
    logActivity($userId, 'update', 'attorney_case', $id, $changes);
}

successResponse(null, 'Attorney case updated successfully');
