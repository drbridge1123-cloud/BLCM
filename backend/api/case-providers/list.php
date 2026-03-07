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
           p.charges_record_fee,
           COALESCE(u.display_name, u.full_name) AS assigned_name,
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

// Batch fetch contacts for all providers (instead of N+1 queries)
$providerIds = array_unique(array_column($rows, 'provider_id'));
$contactsMap = [];
if ($providerIds) {
    $ph = implode(',', array_fill(0, count($providerIds), '?'));
    $allContacts = dbFetchAll(
        "SELECT id, provider_id, department, contact_type, contact_value, is_primary, notes
         FROM provider_contacts WHERE provider_id IN ({$ph}) ORDER BY is_primary DESC",
        array_values($providerIds)
    );
    foreach ($allContacts as $c) {
        $contactsMap[$c['provider_id']][] = $c;
    }
}

// Batch fetch receipts for all case providers (instead of N+1 queries)
$cpIds = array_column($rows, 'id');
$receiptsMap = [];
if ($cpIds) {
    $ph = implode(',', array_fill(0, count($cpIds), '?'));
    $allReceipts = dbFetchAll(
        "SELECT case_provider_id, has_medical_records, has_billing, has_chart, has_imaging, has_op_report
         FROM record_receipts WHERE case_provider_id IN ({$ph})",
        array_values($cpIds)
    );
    foreach ($allReceipts as $r) {
        $receiptsMap[$r['case_provider_id']][] = $r;
    }
}

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

    // Use batch-loaded contacts
    $row['contacts'] = $contactsMap[$row['provider_id']] ?? [];

    // Use batch-loaded receipts
    $received = [];
    foreach (($receiptsMap[$row['id']] ?? []) as $rc) {
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
