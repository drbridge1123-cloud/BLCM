<?php
/**
 * GET /api/activity-log
 * List activity log entries (admin only)
 */
$userId = requireAuth();
requirePermission('activity_log');

[$page, $perPage, $offset] = getPaginationParams();

$where = '1=1';
$params = [];

// Search
$search = trim($_GET['search'] ?? '');
if ($search) {
    $where .= ' AND (a.action LIKE ? OR a.entity_type LIKE ? OR u.full_name LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

// Entity type filter
$entityType = trim($_GET['entity_type'] ?? '');
if ($entityType) {
    $where .= ' AND a.entity_type = ?';
    $params[] = $entityType;
}

$total = dbFetchOne("
    SELECT COUNT(*) as cnt
    FROM activity_log a
    LEFT JOIN users u ON a.user_id = u.id
    WHERE {$where}
", $params)['cnt'];

$rows = dbFetchAll("
    SELECT a.*, u.full_name
    FROM activity_log a
    LEFT JOIN users u ON a.user_id = u.id
    WHERE {$where}
    ORDER BY a.created_at DESC
    LIMIT {$perPage} OFFSET {$offset}
", $params);

paginatedResponse($rows, $total, $page, $perPage);
