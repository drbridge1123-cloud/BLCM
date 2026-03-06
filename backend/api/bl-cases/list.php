<?php
/**
 * GET /api/bl-cases
 * List cases with pagination, filters, provider progress stats, and summary counts
 */
$userId = requireAuth();

[$page, $perPage, $offset] = getPaginationParams();

// Filters
$status = $_GET['status'] ?? null;
$assignedTo = $_GET['assigned_to'] ?? null;
$search = $_GET['search'] ?? null;
$sortBy = $_GET['sort_by'] ?? 'c.created_at';
$sortDir = strtoupper($_GET['sort_dir'] ?? 'DESC');

// Whitelist sort columns
$allowedSorts = [
    'c.id',
    'c.case_number',
    'c.client_name',
    'c.client_dob',
    'c.doi',
    'c.status',
    'c.created_at',
    'c.updated_at',
    'assigned_name'
];
if (!in_array($sortBy, $allowedSorts))
    $sortBy = 'c.created_at';
if (!in_array($sortDir, ['ASC', 'DESC']))
    $sortDir = 'DESC';

$where = '1=1';
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
    $params[] = (int) $assignedTo;
}

if ($search) {
    $where .= ' AND (c.client_name LIKE ? OR c.case_number LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$clientId = $_GET['client_id'] ?? null;
$providerId = $_GET['provider_id'] ?? null;
$adjusterId = $_GET['adjuster_id'] ?? null;
$insuranceCompanyId = $_GET['insurance_company_id'] ?? null;

if ($clientId) {
    $where .= ' AND c.client_id = ?';
    $params[] = (int) $clientId;
}

if ($providerId) {
    $where .= ' AND EXISTS (SELECT 1 FROM case_providers cp WHERE cp.case_id = c.id AND cp.provider_id = ?)';
    $params[] = (int) $providerId;
}

if ($adjusterId) {
    $where .= ' AND c.adjuster_id = ?';
    $params[] = (int) $adjusterId;
}

if ($insuranceCompanyId) {
    $where .= ' AND EXISTS (SELECT 1 FROM adjusters a WHERE a.id = c.adjuster_id AND a.insurance_company_id = ?)';
    $params[] = (int) $insuranceCompanyId;
}

// Count total for pagination
$total = dbFetchOne(
    "SELECT COUNT(*) as cnt FROM cases c WHERE {$where}",
    $params
)['cnt'];

// Main query with assigned user name and provider progress subqueries
$orderClause = $sortBy === 'assigned_name'
    ? "COALESCE(u.display_name, u.full_name) {$sortDir}"
    : "{$sortBy} {$sortDir}";

$sql = "SELECT c.*,
            COALESCE(u.display_name, u.full_name) AS assigned_name,
            COALESCE(pstats.provider_total, 0) AS provider_total,
            COALESCE(pstats.provider_done, 0) AS provider_done,
            COALESCE(pstats.provider_overdue, 0) AS provider_overdue,
            COALESCE(pstats.provider_followup, 0) AS provider_followup
        FROM cases c
        LEFT JOIN users u ON c.assigned_to = u.id
        LEFT JOIN (
            SELECT cp.case_id,
                COUNT(*) AS provider_total,
                SUM(cp.overall_status = 'verified') AS provider_done,
                SUM(cp.deadline IS NOT NULL AND cp.deadline < CURDATE()
                    AND cp.overall_status NOT IN ('received_complete','verified')) AS provider_overdue,
                SUM(lr.next_followup_date IS NOT NULL AND lr.next_followup_date <= CURDATE()
                    AND cp.overall_status NOT IN ('received_complete','verified')) AS provider_followup
            FROM case_providers cp
            LEFT JOIN (
                SELECT rr.case_provider_id, rr.next_followup_date
                FROM record_requests rr
                INNER JOIN (
                    SELECT case_provider_id, MAX(id) AS max_id
                    FROM record_requests GROUP BY case_provider_id
                ) rm ON rr.id = rm.max_id
            ) lr ON lr.case_provider_id = cp.id
            GROUP BY cp.case_id
        ) pstats ON pstats.case_id = c.id
        WHERE {$where}
        ORDER BY {$orderClause}
        LIMIT ? OFFSET ?";

$queryParams = array_merge($params, [$perPage, $offset]);
$cases = dbFetchAll($sql, $queryParams);

// Cast numeric fields
foreach ($cases as &$row) {
    $row['provider_total'] = (int) $row['provider_total'];
    $row['provider_done'] = (int) $row['provider_done'];
    $row['provider_overdue'] = (int) $row['provider_overdue'];
    $row['provider_followup'] = (int) $row['provider_followup'];
}
unset($row);

// Summary counts (unfiltered, for sidebar/badges)
$summary = dbFetchOne("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'ini') AS ini,
        SUM(status = 'rec') AS rec,
        SUM(status = 'verification') AS verification,
        SUM(status = 'rfd') AS rfd,
        SUM(status = 'neg') AS neg,
        SUM(status = 'lit') AS lit,
        SUM(status = 'final_verification') AS final_verification,
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
    'total' => (int) $summary['total'],
    'active' => (int) $summary['active'],
    'ini' => (int) ($summary['ini'] ?? 0),
    'rec' => (int) ($summary['rec'] ?? 0),
    'verification' => (int) ($summary['verification'] ?? 0),
    'rfd' => (int) ($summary['rfd'] ?? 0),
    'neg' => (int) ($summary['neg'] ?? 0),
    'lit' => (int) ($summary['lit'] ?? 0),
    'final_verification' => (int) ($summary['final_verification'] ?? 0),
    'accounting' => (int) ($summary['accounting'] ?? 0),
    'closed' => (int) $summary['closed'],
    'overdue_providers' => (int) ($providerCounts['overdue_providers'] ?? 0),
    'not_started_providers' => (int) ($providerCounts['not_started_providers'] ?? 0),
];

paginatedResponse($cases, (int) $total, $page, $perPage, ['summary' => $summaryData]);
