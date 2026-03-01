<?php
/**
 * GET /api/performance
 * Get performance snapshots
 */
$userId = requireAuth();
requirePermission('goals');
$user = getCurrentUser();

$employeeId = $_GET['employee_id'] ?? null;
$months = (int)($_GET['months'] ?? 12);

$where  = '1=1';
$params = [];

if (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND p.employee_id = ?';
    $params[] = $userId;
} elseif ($employeeId) {
    $where .= ' AND p.employee_id = ?';
    $params[] = (int)$employeeId;
}

$snapshots = dbFetchAll("
    SELECT p.*, u.full_name AS employee_name
    FROM performance_snapshots p
    JOIN users u ON p.employee_id = u.id
    WHERE {$where}
    ORDER BY p.snapshot_month DESC
    LIMIT ?
", array_merge($params, [$months]));

successResponse($snapshots);
