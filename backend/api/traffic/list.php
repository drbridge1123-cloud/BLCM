<?php
/**
 * GET /api/traffic
 * List traffic cases with filters
 */
$userId = requireAuth();
requirePermission('traffic');
$user = getCurrentUser();

[$page, $perPage, $offset] = getPaginationParams();

$status = $_GET['status'] ?? null;
$search = $_GET['search'] ?? null;
$paid   = $_GET['paid'] ?? null;

$where  = '1=1';
$params = [];

// Non-admin sees own cases only
if (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND t.user_id = ?';
    $params[] = $userId;
}

if ($status && $status !== 'all') {
    $where .= ' AND t.status = ?';
    $params[] = $status;
}
if ($paid !== null && $paid !== '') {
    $where .= ' AND t.paid = ?';
    $params[] = (int)$paid;
}
if ($search) {
    $where .= ' AND (t.client_name LIKE ? OR t.case_number LIKE ? OR t.court LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$total = dbFetchOne(
    "SELECT COUNT(*) AS cnt FROM traffic_cases t WHERE {$where}", $params
)['cnt'];

$sql = "SELECT t.*, u.full_name AS attorney_name, rb.full_name AS requested_by_name
        FROM traffic_cases t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN users rb ON t.requested_by = rb.id
        WHERE {$where}
        ORDER BY t.created_at DESC
        LIMIT ? OFFSET ?";

$rows = dbFetchAll($sql, array_merge($params, [$perPage, $offset]));

// Summary
$summary = dbFetchOne("
    SELECT
        SUM(t.status = 'active') AS active_count,
        SUM(t.status = 'resolved') AS resolved_count,
        COALESCE(SUM(t.commission), 0) AS total_commission,
        COALESCE(SUM(CASE WHEN t.paid = 1 THEN t.commission END), 0) AS paid_commission,
        COALESCE(SUM(CASE WHEN t.paid = 0 AND t.commission > 0 THEN t.commission END), 0) AS unpaid_commission
    FROM traffic_cases t
    WHERE {$where}
", $params);

paginatedResponse($rows, (int)$total, $page, $perPage, ['summary' => [
    'active_count'    => (int)($summary['active_count'] ?? 0),
    'resolved_count'  => (int)($summary['resolved_count'] ?? 0),
    'total_commission' => round((float)($summary['total_commission'] ?? 0), 2),
    'paid_commission'  => round((float)($summary['paid_commission'] ?? 0), 2),
    'unpaid_commission' => round((float)($summary['unpaid_commission'] ?? 0), 2),
]]);
