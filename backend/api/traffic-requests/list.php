<?php
/**
 * GET /api/traffic-requests
 * List traffic requests
 */
$userId = requireAuth();
requirePermission('traffic');
$user = getCurrentUser();

$status = $_GET['status'] ?? null;

$where  = '1=1';
$params = [];

// Attorney sees only their assigned requests
if ($user['role'] === 'attorney') {
    $where .= ' AND tr.assigned_to = ?';
    $params[] = $userId;
} elseif (!in_array($user['role'], ['admin', 'manager'])) {
    // Staff sees only their own sent requests
    $where .= ' AND tr.requested_by = ?';
    $params[] = $userId;
}

if ($status && $status !== 'all') {
    $where .= ' AND tr.status = ?';
    $params[] = $status;
}

$sql = "SELECT tr.*, rb.full_name AS requested_by_name, at.full_name AS assigned_to_name
        FROM traffic_requests tr
        LEFT JOIN users rb ON tr.requested_by = rb.id
        LEFT JOIN users at ON tr.assigned_to = at.id
        WHERE {$where}
        ORDER BY tr.created_at DESC";

$rows = dbFetchAll($sql, $params);

// Pending count for badge
$pendingCount = 0;
if ($user['role'] === 'attorney') {
    $pc = dbFetchOne("SELECT COUNT(*) AS cnt FROM traffic_requests WHERE assigned_to = ? AND status = 'pending'", [$userId]);
    $pendingCount = (int)$pc['cnt'];
} elseif (in_array($user['role'], ['admin', 'manager'])) {
    $pc = dbFetchOne("SELECT COUNT(*) AS cnt FROM traffic_requests WHERE status = 'pending'");
    $pendingCount = (int)$pc['cnt'];
}

successResponse([
    'requests' => $rows,
    'pending_count' => $pendingCount,
]);
