<?php
/**
 * PUT /api/case-providers/{id}
 * Generic update endpoint - delegates to update-status for status changes
 */
$input = getInput();

// If overall_status is provided, delegate to update-status handler
if (!empty($input['overall_status'])) {
    require __DIR__ . '/update-status.php';
    return;
}

// For other fields, handle generic update
$userId = requireAuth();
$id = (int)$_GET['id'];

$cp = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$id]);
if (!$cp) errorResponse('Case-provider not found', 404);

$data = [];
$updatableFields = ['assigned_to', 'treatment_start_date', 'treatment_end_date', 'record_types_needed', 'notes', 'deadline'];

foreach ($updatableFields as $field) {
    if (isset($input[$field])) {
        $data[$field] = $input[$field] === '' ? null : sanitizeString((string)$input[$field]);
    }
}

if (empty($data)) {
    errorResponse('No valid fields to update', 400);
}

dbUpdate('case_providers', $data, 'id = ?', [$id]);

logActivity($userId, 'update', 'case_provider', $id, $data);

successResponse(null, 'Case provider updated');
