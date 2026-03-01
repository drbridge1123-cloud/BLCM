<?php
// GET /api/mbds/{case_id} - Get MBDS report with all lines
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireAuth();

$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('Case ID is required', 400);
}

$report = dbFetchOne(
    "SELECT r.*, c.case_number, c.client_name, c.doi, c.status AS case_status,
            u1.full_name AS completed_by_name, u2.full_name AS approved_by_name
     FROM mbds_reports r
     JOIN cases c ON c.id = r.case_id
     LEFT JOIN users u1 ON r.completed_by = u1.id
     LEFT JOIN users u2 ON r.approved_by = u2.id
     WHERE r.case_id = ?",
    [$caseId]
);

if (!$report) {
    errorResponse('Medical Balance report not found', 404);
}

$lines = dbFetchAll(
    "SELECT ml.*, p.type AS provider_type
     FROM mbds_lines ml
     LEFT JOIN case_providers cp ON ml.case_provider_id = cp.id
     LEFT JOIN providers p ON cp.provider_id = p.id
     WHERE ml.report_id = ?
     ORDER BY ml.sort_order, ml.id",
    [$report['id']]
);

// Cast numeric fields
foreach ($lines as &$line) {
    $line['charges'] = (float)$line['charges'];
    $line['pip1_amount'] = (float)$line['pip1_amount'];
    $line['pip2_amount'] = (float)$line['pip2_amount'];
    $line['health1_amount'] = (float)$line['health1_amount'];
    $line['health2_amount'] = (float)$line['health2_amount'];
    $line['health3_amount'] = (float)$line['health3_amount'];
    $line['discount'] = (float)$line['discount'];
    $line['office_paid'] = (float)$line['office_paid'];
    $line['client_paid'] = (float)$line['client_paid'];
    $line['balance'] = (float)$line['balance'];
    $line['sort_order'] = (int)$line['sort_order'];
}
unset($line);

$report['has_wage_loss'] = (int)$report['has_wage_loss'];
$report['has_essential_service'] = (int)$report['has_essential_service'];
$report['has_health_subrogation'] = (int)$report['has_health_subrogation'];
$report['has_health_subrogation2'] = (int)$report['has_health_subrogation2'];
$report['lines'] = $lines;

successResponse($report);
