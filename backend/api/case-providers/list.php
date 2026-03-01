<?php
/**
 * GET /api/case-providers
 * List case-providers by ?case_id with enriched data
 */
require_once __DIR__ . '/../../helpers/date.php';
require_once __DIR__ . '/../../helpers/escalation.php';

$userId = requireAuth();

$caseId = (int)($_GET['case_id'] ?? 0);
if (!$caseId) errorResponse('case_id is required');

$case = dbFetchOne("SELECT id FROM cases WHERE id = ?", [$caseId]);
if (!$case) errorResponse('Case not found', 404);

$sort = $_GET['sort'] ?? 'cp.id';
$dir  = strtoupper($_GET['dir'] ?? 'ASC');
if (!in_array($dir, ['ASC', 'DESC'])) $dir = 'ASC';

$allowedSorts = [
    'id' => 'cp.id',
    'provider_name' => 'p.name',
    'overall_status' => 'cp.overall_status',
    'deadline' => 'cp.deadline',
    'first_request_date' => 'first_request_date',
    'last_request_date' => 'last_request_date',
    'request_count' => 'request_count',
];
$orderCol = $allowedSorts[$sort] ?? 'cp.id';

$rows = dbFetchAll("
    SELECT cp.*,
           p.name AS provider_name,
           p.type AS provider_type,
           p.phone AS provider_phone,
           p.fax AS provider_fax,
           p.email AS provider_email,
           p.preferred_method,
           u.full_name AS assigned_name,
           (SELECT MIN(rr.request_date) FROM record_requests rr WHERE rr.case_provider_id = cp.id) AS first_request_date,
           (SELECT MAX(rr.request_date) FROM record_requests rr WHERE rr.case_provider_id = cp.id) AS last_request_date,
           (SELECT COUNT(*) FROM record_requests rr WHERE rr.case_provider_id = cp.id) AS request_count,
           (SELECT COUNT(*) FROM record_requests rr WHERE rr.case_provider_id = cp.id AND rr.request_type = 'follow_up') AS followup_count
    FROM case_providers cp
    JOIN providers p ON p.id = cp.provider_id
    LEFT JOIN users u ON u.id = cp.assigned_to
    WHERE cp.case_id = ?
    ORDER BY {$orderCol} {$dir}
", [$caseId]);

foreach ($rows as &$row) {
    // Calculate days_since_request
    $row['days_since_request'] = $row['last_request_date'] ? daysElapsed($row['last_request_date']) : null;

    // Calculate overdue / deadline info
    $row['is_overdue'] = isOverdue($row['deadline']);
    $row['days_until_deadline'] = daysUntil($row['deadline']);

    // Escalation
    $daysPast = $row['deadline'] ? -daysUntil($row['deadline']) : null;
    $esc = getEscalationInfo($daysPast);
    $row['escalation_tier']  = $esc['tier'];
    $row['escalation_label'] = $esc['label'];
    $row['escalation_css']   = $esc['css'];

    // Load contacts for this provider
    $row['contacts'] = dbFetchAll(
        "SELECT id, department, contact_type, contact_value, is_primary, notes
         FROM provider_contacts WHERE provider_id = ? ORDER BY is_primary DESC",
        [$row['provider_id']]
    );

    // Aggregate received record types from receipts
    $receipts = dbFetchAll(
        "SELECT has_medical_records, has_billing, has_chart, has_imaging, has_op_report
         FROM record_receipts WHERE case_provider_id = ?",
        [$row['id']]
    );
    $received = [];
    foreach ($receipts as $rc) {
        if ($rc['has_medical_records']) $received['medical_records'] = true;
        if ($rc['has_billing'])         $received['billing'] = true;
        if ($rc['has_chart'])           $received['chart'] = true;
        if ($rc['has_imaging'])         $received['imaging'] = true;
        if ($rc['has_op_report'])       $received['op_report'] = true;
    }
    $row['received_record_types'] = array_keys($received);
}
unset($row);

successResponse($rows);
