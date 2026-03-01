<?php
/**
 * POST /api/case-providers
 * Link a provider to a case
 */
require_once __DIR__ . '/../../helpers/date.php';

$userId = requireAuth();
$input  = getInput();
$errors = validateRequired($input, ['case_id', 'provider_id']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId     = (int)$input['case_id'];
$providerId = (int)$input['provider_id'];

// Validate case exists
$case = dbFetchOne("SELECT id FROM cases WHERE id = ?", [$caseId]);
if (!$case) errorResponse('Case not found', 404);

// Validate provider exists
$provider = dbFetchOne("SELECT id, name FROM providers WHERE id = ?", [$providerId]);
if (!$provider) errorResponse('Provider not found', 404);

// Check duplicate link
$existing = dbFetchOne(
    "SELECT id FROM case_providers WHERE case_id = ? AND provider_id = ?",
    [$caseId, $providerId]
);
if ($existing) errorResponse('Provider is already linked to this case');

// Build insert data
$data = [
    'case_id'        => $caseId,
    'provider_id'    => $providerId,
    'overall_status' => 'not_started',
    'activated_by'   => $userId,
];

if (!empty($input['treatment_start_date'])) {
    if (!validateDate($input['treatment_start_date'])) errorResponse('Invalid treatment_start_date');
    $data['treatment_start_date'] = $input['treatment_start_date'];
}
if (!empty($input['treatment_end_date'])) {
    if (!validateDate($input['treatment_end_date'])) errorResponse('Invalid treatment_end_date');
    $data['treatment_end_date'] = $input['treatment_end_date'];
}
if (isset($input['record_types_needed'])) {
    $data['record_types_needed'] = sanitizeString($input['record_types_needed']);
}
if (!empty($input['assigned_to'])) {
    $data['assigned_to'] = (int)$input['assigned_to'];
}
if (!empty($input['deadline'])) {
    if (!validateDate($input['deadline'])) errorResponse('Invalid deadline');
    $data['deadline'] = $input['deadline'];
} else {
    $data['deadline'] = calculateDeadline();
}
if (isset($input['notes'])) {
    $data['notes'] = sanitizeString($input['notes']);
}

$id = dbInsert('case_providers', $data);

// Auto-create MBDS line if report exists
$mbdsReport = dbFetchOne("SELECT id FROM mbds_reports WHERE case_id = ?", [$caseId]);
if ($mbdsReport) {
    $maxSort = dbFetchOne(
        "SELECT COALESCE(MAX(sort_order), 0) AS max_sort FROM mbds_lines WHERE report_id = ?",
        [$mbdsReport['id']]
    );
    dbInsert('mbds_lines', [
        'report_id'          => $mbdsReport['id'],
        'line_type'          => 'provider',
        'provider_name'      => $provider['name'],
        'case_provider_id'   => $id,
        'record_types_needed'=> $data['record_types_needed'] ?? null,
        'sort_order'         => ($maxSort['max_sort'] ?? 0) + 1,
    ]);
}

logActivity($userId, 'create', 'case_provider', $id, [
    'case_id'     => $caseId,
    'provider_id' => $providerId,
]);

successResponse(['id' => $id], 'Provider linked to case');
