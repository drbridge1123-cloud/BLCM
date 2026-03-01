<?php
/**
 * PUT /api/attorney-cases/edit-uim
 * Edit UIM fields (admin)
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

if (isset($input['uim_start_date'])) {
    $data['uim_start_date'] = sanitizeString($input['uim_start_date']);
}
if (isset($input['settled'])) {
    $data['settled'] = (float)$input['settled'];
}
if (isset($input['commission'])) {
    $data['commission'] = (float)$input['commission'];
}
if (isset($input['uim_demand_out_date'])) {
    $data['uim_demand_out_date'] = sanitizeString($input['uim_demand_out_date']) ?: null;
}
if (isset($input['uim_negotiate_date'])) {
    $data['uim_negotiate_date'] = sanitizeString($input['uim_negotiate_date']) ?: null;
}
if (isset($input['note'])) {
    $data['note'] = sanitizeString($input['note']);
}

if (empty($data)) errorResponse('No fields to update');

dbUpdate('attorney_cases', $data, 'id = ?', [$caseId]);

logActivity($userId, 'edit_uim', 'attorney_case', $caseId, $data);

successResponse(null, 'UIM updated');
