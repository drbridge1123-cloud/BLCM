<?php
// GET /api/health-ledger/list - List items with latest request info
$userId = requireAuth();

[$page, $perPage, $offset] = getPaginationParams();

$where = ['1=1'];
$params = [];

if (!empty($_GET['search'])) {
    $term = '%' . sanitizeString($_GET['search']) . '%';
    $where[] = '(hli.client_name LIKE ? OR hli.case_number LIKE ? OR hli.insurance_carrier LIKE ?)';
    $params = array_merge($params, [$term, $term, $term]);
}

if (!empty($_GET['status'])) {
    $allowed = ['not_started', 'requesting', 'follow_up', 'received', 'done'];
    if (in_array($_GET['status'], $allowed)) {
        $where[] = 'hli.overall_status = ?';
        $params[] = $_GET['status'];
    }
}

if (!empty($_GET['assigned_to'])) {
    $where[] = 'hli.assigned_to = ?';
    $params[] = (int)$_GET['assigned_to'];
}

if (!empty($_GET['case_id'])) {
    $where[] = 'hli.case_id = ?';
    $params[] = (int)$_GET['case_id'];
}

// Health ledger has no deadline-based escalation tiers

// Special filters
$filter = $_GET['filter'] ?? '';
if ($filter === 'followup_due') {
    $where[] = 'lr.next_followup_date <= CURDATE()';
    $where[] = "hli.overall_status IN ('requesting','follow_up')";
}

$whereClause = implode(' AND ', $where);

// Sorting
$sortColumns = [
    'client_name'       => 'hli.client_name',
    'case_number'       => 'hli.case_number',
    'insurance_carrier' => 'hli.insurance_carrier',
    'overall_status'    => 'hli.overall_status',
    'last_request_date' => 'lr.request_date',
    'request_count'     => 'request_count',
    'next_followup_date'=> 'lr.next_followup_date',
    'days_since_request'=> 'days_since_request',
    'assigned_name'     => 'u.full_name',
    'created_at'        => 'hli.created_at',
];
$sortBy = $sortColumns[$_GET['sort_by'] ?? ''] ?? 'hli.created_at';
$sortDir = ($_GET['sort_dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

$nullsLast = in_array($sortBy, ['lr.request_date', 'lr.next_followup_date', 'days_since_request'])
    ? "CASE WHEN {$sortBy} IS NULL THEN 1 ELSE 0 END, "
    : '';
$orderBy = "ORDER BY {$nullsLast}{$sortBy} {$sortDir}";

$joins = "
FROM health_ledger_items hli
LEFT JOIN users u ON hli.assigned_to = u.id
LEFT JOIN (
    SELECT r1.item_id, r1.request_date, r1.request_method,
           r1.next_followup_date, r1.send_status
    FROM hl_requests r1
    INNER JOIN (
        SELECT item_id, MAX(id) AS max_id
        FROM hl_requests GROUP BY item_id
    ) r2 ON r1.id = r2.max_id
) lr ON lr.item_id = hli.id
LEFT JOIN (
    SELECT item_id, COUNT(*) AS request_count
    FROM hl_requests GROUP BY item_id
) rc ON rc.item_id = hli.id";

// Count
$countSql = "SELECT COUNT(*) AS cnt {$joins} WHERE {$whereClause}";
$total = (int)dbFetchOne($countSql, $params)['cnt'];

// Main query
$sql = "SELECT
    hli.id, hli.case_id, hli.case_number, hli.client_name,
    hli.insurance_carrier, hli.claim_number, hli.member_id,
    hli.carrier_contact_email, hli.carrier_contact_fax,
    hli.overall_status, hli.assigned_to, hli.note, hli.created_at,
    u.full_name AS assigned_name,
    lr.request_date AS last_request_date,
    lr.request_method AS last_request_method,
    lr.next_followup_date,
    lr.send_status AS last_send_status,
    COALESCE(rc.request_count, 0) AS request_count,
    DATEDIFF(CURDATE(), lr.request_date) AS days_since_request
{$joins}
WHERE {$whereClause}
{$orderBy}
LIMIT ? OFFSET ?";

$rows = dbFetchAll($sql, array_merge($params, [$perPage, $offset]));

// Computed flags
foreach ($rows as &$row) {
    $row['request_count'] = (int)$row['request_count'];
    $row['days_since_request'] = $row['days_since_request'] !== null ? (int)$row['days_since_request'] : null;
    $row['is_followup_due'] = $row['next_followup_date']
        && $row['next_followup_date'] <= date('Y-m-d')
        && in_array($row['overall_status'], ['requesting', 'follow_up']);

    // No deadline-based escalation for health ledger
    $row['escalation_tier'] = 'normal';
    $row['escalation_label'] = 'Normal';
    $row['escalation_css'] = 'escalation-normal';
}
unset($row);

// Summary
$summaryResult = dbFetchOne("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN hli.overall_status = 'not_started' THEN 1 ELSE 0 END) AS not_started_count,
        SUM(CASE WHEN hli.overall_status = 'requesting' THEN 1 ELSE 0 END) AS requesting_count,
        SUM(CASE WHEN hli.overall_status = 'follow_up' THEN 1 ELSE 0 END) AS followup_count,
        SUM(CASE WHEN hli.overall_status = 'received' THEN 1 ELSE 0 END) AS received_count,
        SUM(CASE WHEN hli.overall_status = 'done' THEN 1 ELSE 0 END) AS done_count
    FROM health_ledger_items hli
", []);

jsonResponse([
    'success' => true,
    'data' => $rows,
    'pagination' => [
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => (int)ceil($total / max($perPage, 1))
    ],
    'summary' => [
        'total' => (int)$summaryResult['total'],
        'not_started' => (int)$summaryResult['not_started_count'],
        'requesting' => (int)$summaryResult['requesting_count'],
        'follow_up' => (int)$summaryResult['followup_count'],
        'received' => (int)$summaryResult['received_count'],
        'done' => (int)$summaryResult['done_count']
    ]
]);
