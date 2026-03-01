<?php
/**
 * GET /api/goals
 * List employee goals
 */
$userId = requireAuth();
requirePermission('goals');
$user = getCurrentUser();

$year = (int)($_GET['year'] ?? date('Y'));
$employeeId = $_GET['user_id'] ?? null;

$where  = '1=1';
$params = [];

if ($year) {
    $where .= ' AND g.year = ?';
    $params[] = $year;
}

if (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND g.user_id = ?';
    $params[] = $userId;
} elseif ($employeeId) {
    $where .= ' AND g.user_id = ?';
    $params[] = (int)$employeeId;
}

$goals = dbFetchAll("
    SELECT g.*, u.full_name AS employee_name
    FROM employee_goals g
    JOIN users u ON g.user_id = u.id
    WHERE {$where}
    ORDER BY u.full_name
", $params);

successResponse($goals);
