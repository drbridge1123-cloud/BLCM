<?php
/**
 * GET /api/demand-requests
 * List demand case requests
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$user = getCurrentUser();

$status = $_GET['status'] ?? null;

$where  = '1=1';
$params = [];

// Attorney sees assigned-to-them, others see their sent requests
if ($user['role'] === 'attorney') {
    $where .= ' AND dr.assigned_to = ?';
    $params[] = $userId;
} elseif (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND dr.requested_by = ?';
    $params[] = $userId;
}

if ($status && $status !== 'all') {
    $where .= ' AND dr.status = ?';
    $params[] = $status;
}

$rows = dbFetchAll("
    SELECT dr.*, rb.full_name AS requested_by_name, at.full_name AS assigned_to_name
    FROM demand_requests dr
    LEFT JOIN users rb ON dr.requested_by = rb.id
    LEFT JOIN users at ON dr.assigned_to = at.id
    WHERE {$where}
    ORDER BY dr.created_at DESC
", $params);

$pendingCount = 0;
if ($user['role'] === 'attorney') {
    $pc = dbFetchOne("SELECT COUNT(*) AS cnt FROM demand_requests WHERE assigned_to = ? AND status = 'pending'", [$userId]);
    $pendingCount = (int)$pc['cnt'];
} elseif (in_array($user['role'], ['admin', 'manager'])) {
    $pc = dbFetchOne("SELECT COUNT(*) AS cnt FROM demand_requests WHERE status = 'pending'");
    $pendingCount = (int)$pc['cnt'];
}

successResponse([
    'requests' => $rows,
    'pending_count' => $pendingCount,
]);
