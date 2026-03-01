<?php
/**
 * POST /api/mbds/{id}/add-line
 * Add a new line item to an MBDS report
 */
$userId = requireAuth();
requirePermission('mbds');

$reportId = (int)$_GET['id'];
$input    = getInput();

$report = dbFetchOne("SELECT id, status FROM mbds_reports WHERE id = ?", [$reportId]);
if (!$report) errorResponse('MBDS report not found', 404);

$errors = validateRequired($input, ['line_type']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$validTypes = ['provider', 'bridge_law', 'wage_loss', 'essential_service',
               'health_subrogation', 'health_subrogation2', 'rx'];
if (!validateEnum($input['line_type'], $validTypes)) {
    errorResponse('Invalid line_type');
}

// Auto-calculate sort_order
$maxOrder = dbFetchOne(
    "SELECT COALESCE(MAX(sort_order), 0) AS max_order FROM mbds_lines WHERE report_id = ?",
    [$reportId]
);

$lineId = dbInsert('mbds_lines', [
    'report_id'            => $reportId,
    'line_type'            => $input['line_type'],
    'provider_name'        => sanitizeString($input['provider_name'] ?? ''),
    'case_provider_id'     => !empty($input['case_provider_id']) ? (int)$input['case_provider_id'] : null,
    'charges'              => (float)($input['charges'] ?? 0),
    'pip1_amount'          => (float)($input['pip1_amount'] ?? 0),
    'pip2_amount'          => (float)($input['pip2_amount'] ?? 0),
    'health1_amount'       => (float)($input['health1_amount'] ?? 0),
    'health2_amount'       => (float)($input['health2_amount'] ?? 0),
    'health3_amount'       => (float)($input['health3_amount'] ?? 0),
    'discount'             => (float)($input['discount'] ?? 0),
    'office_paid'          => (float)($input['office_paid'] ?? 0),
    'client_paid'          => (float)($input['client_paid'] ?? 0),
    'balance'              => (float)($input['balance'] ?? 0),
    'treatment_dates'      => sanitizeString($input['treatment_dates'] ?? ''),
    'visits'               => sanitizeString($input['visits'] ?? ''),
    'note'                 => sanitizeString($input['note'] ?? ''),
    'record_types_needed'  => sanitizeString($input['record_types_needed'] ?? ''),
    'ini_status'           => ($input['ini_status'] ?? 'pending') === 'complete' ? 'complete' : 'pending',
    'sort_order'           => (int)$maxOrder['max_order'] + 1,
]);

$line = dbFetchOne("SELECT * FROM mbds_lines WHERE id = ?", [$lineId]);

successResponse($line, 'Line added successfully');
