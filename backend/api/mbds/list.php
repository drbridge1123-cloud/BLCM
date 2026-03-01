<?php
/**
 * GET /api/mbds
 * List MBDS reports with pagination, search, and filters
 */
$userId = requireAuth();
requirePermission('mbds');

[$page, $perPage, $offset] = getPaginationParams();

$status  = $_GET['status'] ?? null;
$search  = $_GET['search'] ?? null;
$sortBy  = $_GET['sort_by'] ?? 'r.created_at';
$sortDir = strtoupper($_GET['sort_dir'] ?? 'DESC');

$allowedSorts = ['r.created_at', 'c.case_number', 'r.status', 'r.updated_at'];
if (!in_array($sortBy, $allowedSorts)) $sortBy = 'r.created_at';
if (!in_array($sortDir, ['ASC', 'DESC'])) $sortDir = 'DESC';

$where  = '1=1';
$params = [];

if ($status) {
    $statuses = array_map('trim', explode(',', $status));
    $placeholders = implode(',', array_fill(0, count($statuses), '?'));
    $where .= " AND r.status IN ({$placeholders})";
    $params = array_merge($params, $statuses);
}

if ($search) {
    $where .= ' AND (c.case_number LIKE ? OR c.client_name LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$total = dbFetchOne(
    "SELECT COUNT(*) as cnt FROM mbds_reports r JOIN cases c ON r.case_id = c.id WHERE {$where}",
    $params
)['cnt'];

$sql = "SELECT r.*, c.case_number, c.client_name, u.full_name AS assigned_name,
            cb.full_name AS completed_by_name, ab.full_name AS approved_by_name
        FROM mbds_reports r
        JOIN cases c ON r.case_id = c.id
        LEFT JOIN users u ON c.assigned_to = u.id
        LEFT JOIN users cb ON r.completed_by = cb.id
        LEFT JOIN users ab ON r.approved_by = ab.id
        WHERE {$where}
        ORDER BY {$sortBy} {$sortDir}
        LIMIT ? OFFSET ?";

$reports = dbFetchAll($sql, array_merge($params, [$perPage, $offset]));

// Summary counts
$summary = dbFetchOne("
    SELECT COUNT(*) AS total,
        SUM(status = 'draft') AS draft,
        SUM(status = 'completed') AS completed,
        SUM(status = 'approved') AS approved
    FROM mbds_reports
");

$summaryData = [
    'total'     => (int)($summary['total'] ?? 0),
    'draft'     => (int)($summary['draft'] ?? 0),
    'completed' => (int)($summary['completed'] ?? 0),
    'approved'  => (int)($summary['approved'] ?? 0),
];

paginatedResponse($reports, (int)$total, $page, $perPage, ['summary' => $summaryData]);
