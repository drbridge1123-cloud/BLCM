<?php
/**
 * PUT /api/settlement/{id}
 * Save settlement settings for a case
 */
$userId = requireAuth();
requirePermission('cases');

$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) errorResponse('Case ID is required');

$case = dbFetchOne("SELECT id FROM cases WHERE id = ?", [$caseId]);
if (!$case) errorResponse('Case not found', 404);

$input = getInput();

$allowedFields = [
    'settlement_amount', 'attorney_fee_percent', 'coverage_3rd_party',
    'coverage_um', 'coverage_uim', 'policy_limit', 'um_uim_limit',
    'pip_subrogation_amount', 'pip_insurance_company', 'settlement_method'
];

$data = [];
$changes = [];

foreach ($allowedFields as $field) {
    if (!array_key_exists($field, $input)) continue;
    $newValue = $input[$field];

    if ($field === 'pip_insurance_company' || $field === 'settlement_method') {
        $newValue = $newValue !== null ? sanitizeString($newValue) : null;
    } else {
        $newValue = $newValue !== null && $newValue !== '' ? (float)$newValue : null;
    }

    $data[$field] = $newValue;
}

if (empty($data)) errorResponse('No fields to update');

dbUpdate('cases', $data, 'id = ?', [$caseId]);
logActivity($userId, 'update', 'settlement', $caseId, $data);

successResponse(null, 'Settlement updated successfully');
