<?php
/**
 * GET /api/attorney-cases
 * List attorney cases with pagination, filters, and summary counts
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$user = getCurrentUser();
[$page, $perPage, $offset] = getPaginationParams();

$phase = $_GET['phase'] ?? null;
$status = $_GET['status'] ?? null;
$month = $_GET['month'] ?? null;
$year = $_GET['year'] ?? null;
$attorneyId = $_GET['attorney_user_id'] ?? null;
$search = $_GET['search'] ?? null;

$where = 'ac.deleted_at IS NULL';
$params = [];

if (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND ac.attorney_user_id = ?';
    $params[] = $userId;
} elseif ($attorneyId) {
    $where .= ' AND ac.attorney_user_id = ?';
    $params[] = (int)$attorneyId;
}
if ($phase)  { $where .= ' AND ac.phase = ?';  $params[] = $phase; }
if ($status) { $where .= ' AND ac.status = ?'; $params[] = $status; }
if ($month)  { $where .= ' AND ac.month = ?';  $params[] = $month; }
if ($year)   { $where .= ' AND YEAR(ac.assigned_date) = ?'; $params[] = (int)$year; }
if ($search) {
    $where .= ' AND (ac.client_name LIKE ? OR ac.case_number LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$total = dbFetchOne("SELECT COUNT(*) as cnt FROM attorney_cases ac WHERE {$where}", $params)['cnt'];
$orderBy = ($phase === 'demand') ? 'ac.demand_deadline ASC' : 'ac.submitted_at DESC';

$sql = "SELECT ac.*, u.full_name AS attorney_name,
            oa.full_name AS top_offer_assignee_name,
            DATEDIFF(ac.demand_deadline, CURDATE()) AS deadline_days_remaining
        FROM attorney_cases ac
        LEFT JOIN users u ON ac.attorney_user_id = u.id
        LEFT JOIN users oa ON ac.top_offer_assignee_id = oa.id
        WHERE {$where} ORDER BY {$orderBy} LIMIT ? OFFSET ?";

$cases = dbFetchAll($sql, array_merge($params, [$perPage, $offset]));

foreach ($cases as &$row) {
    $row['deadline_days_remaining'] = $row['deadline_days_remaining'] !== null
        ? (int)$row['deadline_days_remaining'] : null;
}
unset($row);

// Summary counts (respects same user filter)
$bw = 'ac.deleted_at IS NULL';
$bp = [];
if (!in_array($user['role'], ['admin', 'manager'])) {
    $bw .= ' AND ac.attorney_user_id = ?';
    $bp[] = $userId;
}
$s = dbFetchOne("SELECT
    SUM(ac.phase='demand' AND ac.status='in_progress') AS demand_count,
    SUM(ac.phase='litigation' AND ac.status='in_progress') AS litigation_count,
    SUM(ac.phase='uim' AND ac.status='in_progress') AS uim_count,
    SUM(ac.phase='settled') AS settled_count,
    SUM(ac.phase!='settled' AND ac.status='in_progress') AS active_count
    FROM attorney_cases ac WHERE {$bw}", $bp);

$summaryData = [
    'demand'     => (int)($s['demand_count'] ?? 0),
    'litigation' => (int)($s['litigation_count'] ?? 0),
    'uim'        => (int)($s['uim_count'] ?? 0),
    'settled'    => (int)($s['settled_count'] ?? 0),
    'active'     => (int)($s['active_count'] ?? 0),
];

paginatedResponse($cases, (int)$total, $page, $perPage, ['summary' => $summaryData]);
