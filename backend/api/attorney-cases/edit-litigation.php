<?php
/**
 * PUT /api/attorney-cases/edit-litigation
 * Edit litigation fields (admin)
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$input = getInput();

$errors = validateRequired($input, ['case_id']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId = (int)$input['case_id'];
$case = dbFetchOne("SELECT * FROM attorney_cases WHERE id = ? AND deleted_at IS NULL", [$caseId]);
if (!$case) errorResponse('Case not found', 404);

$data = [];
$changes = [];

if (isset($input['litigation_start_date'])) {
    $data['litigation_start_date'] = sanitizeString($input['litigation_start_date']);
}
if (isset($input['assigned_date'])) {
    $data['assigned_date'] = sanitizeString($input['assigned_date']);
    $data['demand_deadline'] = calculateDemandDeadline($data['assigned_date']);
}
if (isset($input['presuit_offer'])) {
    $data['presuit_offer'] = (float)$input['presuit_offer'];
}
if (isset($input['resolution_type'])) {
    $data['resolution_type'] = sanitizeString($input['resolution_type']);
}
if (isset($input['note'])) {
    $data['note'] = sanitizeString($input['note']);
}

if (empty($data)) errorResponse('No fields to update');

dbUpdate('attorney_cases', $data, 'id = ?', [$caseId]);

logActivity($userId, 'edit_litigation', 'attorney_case', $caseId, $data);

successResponse(null, 'Litigation updated');
