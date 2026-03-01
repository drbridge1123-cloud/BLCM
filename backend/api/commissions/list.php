<?php
/**
 * GET /api/commissions
 * List employee commissions with filters and pagination
 */
$userId = requireAuth();
requirePermission('commissions');
$user = getCurrentUser();

[$page, $perPage, $offset] = getPaginationParams();

$search   = $_GET['search'] ?? null;
$status   = $_GET['status'] ?? null;
$month    = $_GET['month'] ?? null;
$year     = $_GET['year'] ?? null;
$employee = $_GET['employee_id'] ?? null;

$where  = 'c.deleted_at IS NULL';
$params = [];

// Non-admin/manager sees only own commissions
if (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND c.employee_user_id = ?';
    $params[] = $userId;
} elseif ($employee) {
    $where .= ' AND c.employee_user_id = ?';
    $params[] = (int)$employee;
}

if ($status) {
    $where .= ' AND c.status = ?';
    $params[] = $status;
}
if ($month) {
    $where .= ' AND c.month = ?';
    $params[] = $month;
}
if ($year) {
    $where .= ' AND c.month LIKE ?';
    $params[] = "%{$year}";
}
if ($search) {
    $where .= ' AND (c.case_number LIKE ? OR c.client_name LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$total = dbFetchOne(
    "SELECT COUNT(*) AS cnt FROM employee_commissions c WHERE {$where}", $params
)['cnt'];

$sql = "SELECT c.*, u.full_name AS employee_name, u.commission_rate AS user_commission_rate,
            r.full_name AS reviewed_by_name
        FROM employee_commissions c
        JOIN users u ON c.employee_user_id = u.id
        LEFT JOIN users r ON c.reviewed_by = r.id
        WHERE {$where}
        ORDER BY c.submitted_at DESC
        LIMIT ? OFFSET ?";

$rows = dbFetchAll($sql, array_merge($params, [$perPage, $offset]));

paginatedResponse($rows, (int)$total, $page, $perPage);
