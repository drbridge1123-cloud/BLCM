<?php
// GET /api/tracker/list - All case_providers with latest request info
$userId = requireAuth();

// Pagination
[$page, $perPage, $offset] = getPaginationParams();

// Build WHERE clauses
$where = ["c.status NOT IN ('closed')"];
$params = [];

// When viewing a specific case, show ALL providers; otherwise hide treating/completed
if (empty($_GET['case_id'])) {
    $where[] = "cp.overall_status != 'treating'";
    if (empty($_GET['status']) || !in_array($_GET['status'], ['received_complete','verified'])) {
        $where[] = "cp.overall_status NOT IN ('received_complete','verified')";
    }
}

// Filter: search (case_number, client_name, provider_name)
if (!empty($_GET['search'])) {
    $term = '%' . sanitizeString($_GET['search']) . '%';
    $where[] = '(c.case_number LIKE ? OR c.client_name LIKE ? OR p.name LIKE ?)';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

// Filter: overall_status
if (!empty($_GET['status'])) {
    $allowed = ['not_started','requesting','follow_up','action_needed','received_partial','on_hold','received_complete','verified'];
    if (in_array($_GET['status'], $allowed)) {
        $where[] = 'cp.overall_status = ?';
        $params[] = $_GET['status'];
    }
}

// Filter: assigned_to
if (!empty($_GET['assigned_to'])) {
    $where[] = 'cp.assigned_to = ?';
    $params[] = (int)$_GET['assigned_to'];
}

// Filter: case_id (from "Manage in Tracker" link)
if (!empty($_GET['case_id'])) {
    $where[] = 'cp.case_id = ?';
    $params[] = (int)$_GET['case_id'];
}

// Filter: escalation tier (deadline-based)
if (!empty($_GET['tier'])) {
    if ($_GET['tier'] === 'action') {
        $where[] = "cp.deadline IS NOT NULL AND cp.deadline <= CURDATE()";
    } elseif ($_GET['tier'] === 'admin') {
        $where[] = "cp.deadline IS NOT NULL AND DATEDIFF(CURDATE(), cp.deadline) >= " . ADMIN_ESCALATION_DAYS_AFTER_DEADLINE;
    }
}

// Special filters
$filter = $_GET['filter'] ?? '';
if ($filter === 'overdue') {
    $where[] = 'cp.deadline < CURDATE()';
    $where[] = "cp.overall_status NOT IN ('received_complete','verified')";
} elseif ($filter === 'followup_due') {
    $where[] = 'lr.next_followup_date <= CURDATE()';
    $where[] = "cp.overall_status IN ('requesting','follow_up','action_needed')";
} elseif ($filter === 'no_request') {
    $where[] = 'lr.request_date IS NULL';
}

$whereClause = implode(' AND ', $where);

// Sorting (whitelist approach)
$sortColumns = [
    'case_number'        => 'c.case_number',
    'client_name'        => 'c.client_name',
    'provider_name'      => 'p.name',
    'overall_status'     => 'cp.overall_status',
    'last_request_date'  => 'lr.request_date',
    'request_count'      => 'request_count',
    'next_followup_date' => 'lr.next_followup_date',
    'deadline'           => 'cp.deadline',
    'days_since_request' => 'days_since_request',
];
$sortBy = $sortColumns[$_GET['sort_by'] ?? ''] ?? 'cp.deadline';
$sortDir = ($_GET['sort_dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

// Handle NULLs for deadline/date sorts - push nulls to end
$nullsLast = in_array($sortBy, ['cp.deadline', 'lr.request_date', 'lr.next_followup_date', 'days_since_request'])
    ? "CASE WHEN {$sortBy} IS NULL THEN 1 ELSE 0 END, "
    : '';
$orderBy = "ORDER BY {$nullsLast}{$sortBy} {$sortDir}";

// Common JOINs
$joins = "
FROM case_providers cp
JOIN cases c ON cp.case_id = c.id
JOIN providers p ON cp.provider_id = p.id
LEFT JOIN users u ON cp.assigned_to = u.id
LEFT JOIN (
    SELECT r1.case_provider_id, r1.request_date, r1.request_method,
           r1.next_followup_date, r1.send_status
    FROM record_requests r1
    INNER JOIN (
        SELECT case_provider_id, MAX(id) AS max_id
        FROM record_requests GROUP BY case_provider_id
    ) r2 ON r1.id = r2.max_id
) lr ON lr.case_provider_id = cp.id
LEFT JOIN (
    SELECT case_provider_id, COUNT(*) AS request_count
    FROM record_requests GROUP BY case_provider_id
) rq ON rq.case_provider_id = cp.id";

// Count query
$countSql = "SELECT COUNT(*) AS cnt {$joins} WHERE {$whereClause}";
$countResult = dbFetchOne($countSql, $params);
$total = (int)$countResult['cnt'];

// Main query
$sql = "SELECT
    cp.id, cp.case_id, cp.overall_status, cp.deadline,
    cp.assigned_to AS assigned_to_id,
    cp.assignment_status, cp.activated_by,
    cp.request_mr, cp.request_bill, cp.request_chart, cp.request_img, cp.request_op,
    cp.record_types_needed,
    c.case_number, c.client_name,
    p.name AS provider_name, p.type AS provider_type,
    p.fax AS provider_fax, p.email AS provider_email,
    u.full_name AS assigned_name,
    lr.request_date AS last_request_date,
    lr.request_method AS last_request_method,
    lr.next_followup_date,
    lr.send_status AS last_send_status,
    COALESCE(rq.request_count, 0) AS request_count,
    DATEDIFF(CURDATE(), lr.request_date) AS days_since_request,
    DATEDIFF(cp.deadline, CURDATE()) AS days_until_deadline
{$joins}
WHERE {$whereClause}
{$orderBy}
LIMIT ? OFFSET ?";

$queryParams = array_merge($params, [$perPage, $offset]);
$rows = dbFetchAll($sql, $queryParams);

// Add computed flags
foreach ($rows as &$row) {
    $row['is_overdue'] = $row['deadline']
        && $row['days_until_deadline'] !== null
        && $row['days_until_deadline'] < 0
        && !in_array($row['overall_status'], ['received_complete', 'verified']);

    $row['is_followup_due'] = $row['next_followup_date']
        && $row['next_followup_date'] <= date('Y-m-d')
        && in_array($row['overall_status'], ['requesting', 'follow_up']);

    $row['request_count'] = (int)$row['request_count'];
    $row['days_since_request'] = $row['days_since_request'] !== null ? (int)$row['days_since_request'] : null;
    $row['days_until_deadline'] = $row['days_until_deadline'] !== null ? (int)$row['days_until_deadline'] : null;

    // Add escalation tier info (deadline-based)
    $daysPastDeadline = $row['deadline'] && $row['days_until_deadline'] !== null
        ? -$row['days_until_deadline']
        : null;
    $esc = getEscalationInfo($daysPastDeadline);
    $row['escalation_tier'] = $esc['tier'];
    $row['escalation_label'] = $esc['label'];
    $row['escalation_css'] = $esc['css'];
    $row['assigned_to_id'] = $row['assigned_to_id'] ?? null;
}
unset($row);

// Summary stats (separate query for accuracy without pagination filters interfering)
$summaryJoins = "
FROM case_providers cp
JOIN cases c ON cp.case_id = c.id
LEFT JOIN (
    SELECT r1.case_provider_id, r1.next_followup_date
    FROM record_requests r1
    INNER JOIN (
        SELECT case_provider_id, MAX(id) AS max_id
        FROM record_requests GROUP BY case_provider_id
    ) r2 ON r1.id = r2.max_id
) slr ON slr.case_provider_id = cp.id";

$summaryResult = dbFetchOne("
    SELECT
        COUNT(CASE WHEN cp.overall_status != 'treating' THEN 1 END) AS total,
        SUM(CASE WHEN cp.deadline < CURDATE()
            AND cp.overall_status NOT IN ('treating','received_complete','verified')
            THEN 1 ELSE 0 END) AS overdue_count,
        SUM(CASE WHEN slr.next_followup_date <= CURDATE()
            AND cp.overall_status IN ('requesting','follow_up','action_needed')
            THEN 1 ELSE 0 END) AS followup_due_count,
        SUM(CASE WHEN cp.overall_status = 'not_started'
            THEN 1 ELSE 0 END) AS not_started_count,
        SUM(CASE WHEN cp.assignment_status = 'pending' AND cp.assigned_to = ?
            THEN 1 ELSE 0 END) AS pending_assignments
    {$summaryJoins}
    WHERE c.status NOT IN ('closed')
", [$userId]);

// Custom response with summary
jsonResponse([
    'success' => true,
    'data' => $rows,
    'pagination' => [
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => (int)ceil($total / $perPage)
    ],
    'summary' => [
        'total' => (int)$summaryResult['total'],
        'overdue' => (int)$summaryResult['overdue_count'],
        'followup_due' => (int)$summaryResult['followup_due_count'],
        'not_started' => (int)$summaryResult['not_started_count'],
        'pending_assignments' => (int)$summaryResult['pending_assignments']
    ]
]);
