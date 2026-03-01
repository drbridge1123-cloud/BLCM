<?php
/**
 * GET /api/cases
 * List cases with pagination, filters, provider progress stats, and summary counts
 */
$userId = requireAuth();

[$page, $perPage, $offset] = getPaginationParams();

// Filters
$status    = $_GET['status'] ?? null;
$assignedTo = $_GET['assigned_to'] ?? null;
$search    = $_GET['search'] ?? null;
$sortBy    = $_GET['sort_by'] ?? 'c.created_at';
$sortDir   = strtoupper($_GET['sort_dir'] ?? 'DESC');

// Whitelist sort columns
$allowedSorts = [
    'c.id', 'c.case_number', 'c.client_name', 'c.client_dob', 'c.doi',
    'c.status', 'c.created_at', 'c.updated_at', 'assigned_name'
];
if (!in_array($sortBy, $allowedSorts)) $sortBy = 'c.created_at';
if (!in_array($sortDir, ['ASC', 'DESC'])) $sortDir = 'DESC';

$where  = '1=1';
$params = [];

// Multi-select status filter (comma-separated)
if ($status) {
    $statuses = array_map('trim', explode(',', $status));
    $placeholders = implode(',', array_fill(0, count($statuses), '?'));
    $where .= " AND c.status IN ({$placeholders})";
    $params = array_merge($params, $statuses);
}

if ($assignedTo) {
    $where .= ' AND c.assigned_to = ?';
    $params[] = (int)$assignedTo;
}

if ($search) {
    $where .= ' AND (c.client_name LIKE ? OR c.case_number LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

// Count total for pagination
$total = dbFetchOne(
    "SELECT COUNT(*) as cnt FROM cases c WHERE {$where}",
    $params
)['cnt'];

// Main query with assigned user name and provider progress subqueries
$orderClause = $sortBy === 'assigned_name'
    ? "u.full_name {$sortDir}"
    : "{$sortBy} {$sortDir}";

$sql = "SELECT c.*,
            u.full_name AS assigned_name,
            (SELECT COUNT(*) FROM case_providers cp WHERE cp.case_id = c.id) AS provider_total,
            (SELECT COUNT(*) FROM case_providers cp WHERE cp.case_id = c.id AND cp.overall_status = 'verified') AS provider_done,
            (SELECT COUNT(*) FROM case_providers cp WHERE cp.case_id = c.id AND cp.deadline IS NOT NULL AND cp.deadline < CURDATE() AND cp.overall_status NOT IN ('received_complete','verified')) AS provider_overdue,
            (SELECT COUNT(*) FROM case_providers cp
                JOIN record_requests rr ON rr.case_provider_id = cp.id
                WHERE cp.case_id = c.id
                AND rr.next_followup_date IS NOT NULL
                AND rr.next_followup_date <= CURDATE()
                AND cp.overall_status NOT IN ('received_complete','verified')
                AND rr.id = (SELECT MAX(rr2.id) FROM record_requests rr2 WHERE rr2.case_provider_id = cp.id)
            ) AS provider_followup
        FROM cases c
        LEFT JOIN users u ON c.assigned_to = u.id
        WHERE {$where}
        ORDER BY {$orderClause}
        LIMIT ? OFFSET ?";

$queryParams = array_merge($params, [$perPage, $offset]);
$cases = dbFetchAll($sql, $queryParams);

// Cast numeric fields
foreach ($cases as &$row) {
    $row['provider_total']    = (int)$row['provider_total'];
    $row['provider_done']     = (int)$row['provider_done'];
    $row['provider_overdue']  = (int)$row['provider_overdue'];
    $row['provider_followup'] = (int)$row['provider_followup'];
}
unset($row);

// Summary counts (unfiltered, for sidebar/badges)
$summary = dbFetchOne("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'collecting') AS collecting,
        SUM(status = 'verification') AS verification,
        SUM(status = 'completed') AS completed,
        SUM(status = 'rfd') AS rfd,
        SUM(status = 'final_verification') AS final_verification,
        SUM(status = 'disbursement') AS disbursement,
        SUM(status = 'accounting') AS accounting,
        SUM(status = 'closed') AS closed,
        SUM(status NOT IN ('closed')) AS active
    FROM cases
");

// Overdue and not-started provider counts across all open cases
$providerCounts = dbFetchOne("
    SELECT
        SUM(CASE WHEN cp.deadline IS NOT NULL AND cp.deadline < CURDATE()
            AND cp.overall_status NOT IN ('received_complete','verified') THEN 1 ELSE 0 END) AS overdue_providers,
        SUM(CASE WHEN cp.overall_status = 'not_started' THEN 1 ELSE 0 END) AS not_started_providers
    FROM case_providers cp
    JOIN cases c ON c.id = cp.case_id AND c.status NOT IN ('closed')
");

$summaryData = [
    'total'                => (int)$summary['total'],
    'active'               => (int)$summary['active'],
    'collecting'           => (int)$summary['collecting'],
    'verification'         => (int)$summary['verification'],
    'completed'            => (int)($summary['completed'] ?? 0),
    'rfd'                  => (int)($summary['rfd'] ?? 0),
    'final_verification'   => (int)($summary['final_verification'] ?? 0),
    'disbursement'         => (int)($summary['disbursement'] ?? 0),
    'accounting'           => (int)($summary['accounting'] ?? 0),
    'closed'               => (int)$summary['closed'],
    'overdue_providers'    => (int)($providerCounts['overdue_providers'] ?? 0),
    'not_started_providers'=> (int)($providerCounts['not_started_providers'] ?? 0),
];

paginatedResponse($cases, (int)$total, $page, $perPage, ['summary' => $summaryData]);
