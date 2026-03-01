<?php
/**
 * POST /api/attorney-cases/toggle-date
 * Toggle inline date fields (demand_out_date, negotiate_date, etc.)
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$input = getInput();

$errors = validateRequired($input, ['case_id', 'field']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId = (int)$input['case_id'];
$field  = sanitizeString($input['field']);
$date   = isset($input['date']) && $input['date'] !== '' ? sanitizeString($input['date']) : null;

// Whitelist allowed date fields
$allowedFields = [
    'demand_out_date', 'negotiate_date',
    'uim_demand_out_date', 'uim_negotiate_date',
];
if (!in_array($field, $allowedFields)) {
    errorResponse('Invalid field');
}

$case = dbFetchOne(
    "SELECT id, case_number FROM attorney_cases WHERE id = ? AND deleted_at IS NULL",
    [$caseId]
);
if (!$case) errorResponse('Attorney case not found', 404);

// Validate date format if provided
if ($date && !validateDate($date)) {
    errorResponse('Invalid date format (YYYY-MM-DD)');
}

$data = [$field => $date];

// Auto-update stage when setting (not clearing) date fields
if ($date) {
    if ($field === 'demand_out_date')     $data['stage'] = 'demand_sent';
    if ($field === 'negotiate_date')       $data['stage'] = 'negotiate';
}

dbUpdate('attorney_cases', $data, 'id = ?', [$caseId]);

$action = $date ? 'set' : 'cleared';
logActivity($userId, 'toggle_date', 'attorney_case', $caseId, [
    'field'  => $field,
    'action' => $action,
    'date'   => $date,
]);

successResponse(null, ucfirst(str_replace('_', ' ', $field)) . " {$action} successfully");
