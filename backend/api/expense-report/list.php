<?php
// GET /api/expense-report/list
$userId = requireAuth();
requirePermission('expense_report');

$conditions = [];
$params = [];

if (!empty($_GET['date_from'])) {
    $conditions[] = 'p.payment_date >= ?';
    $params[] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $conditions[] = 'p.payment_date <= ?';
    $params[] = $_GET['date_to'];
}
if (!empty($_GET['staff_id'])) {
    $conditions[] = 'p.paid_by = ?';
    $params[] = (int)$_GET['staff_id'];
}
if (!empty($_GET['category'])) {
    $conditions[] = 'p.expense_category = ?';
    $params[] = sanitizeString($_GET['category']);
}
if (!empty($_GET['payment_type'])) {
    $conditions[] = 'p.payment_type = ?';
    $params[] = sanitizeString($_GET['payment_type']);
}
if (!empty($_GET['search'])) {
    $search = '%' . sanitizeString($_GET['search']) . '%';
    $conditions[] = '(c.case_number LIKE ? OR c.client_name LIKE ? OR p.provider_name LIKE ? OR prov.name LIKE ? OR p.description LIKE ? OR p.check_number LIKE ?)';
    $params = array_merge($params, [$search, $search, $search, $search, $search, $search]);
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Sorting
$allowedSorts = ['payment_date', 'paid_amount', 'billed_amount', 'provider_name', 'case_number', 'expense_category', 'payment_type', 'created_at'];
$sortBy = in_array($_GET['sort_by'] ?? '', $allowedSorts) ? $_GET['sort_by'] : 'payment_date';
$sortDir = ($_GET['sort_dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

$sortCol = $sortBy === 'case_number' ? 'c.case_number' : "p.{$sortBy}";

$rows = dbFetchAll(
    "SELECT p.*,
            c.case_number,
            c.client_name,
            u_paid.full_name AS paid_by_name,
            u_created.full_name AS created_by_name,
            prov.name AS linked_provider_name
     FROM mr_fee_payments p
     LEFT JOIN cases c ON p.case_id = c.id
     LEFT JOIN users u_paid ON p.paid_by = u_paid.id
     LEFT JOIN users u_created ON p.created_by = u_created.id
     LEFT JOIN case_providers cp ON p.case_provider_id = cp.id
     LEFT JOIN providers prov ON cp.provider_id = prov.id
     {$whereClause}
     ORDER BY {$sortCol} {$sortDir}, p.id DESC",
    $params
);

// Summary aggregates
$summaryRow = dbFetchOne(
    "SELECT COALESCE(SUM(p.billed_amount), 0) AS total_billed,
            COALESCE(SUM(p.paid_amount), 0) AS total_paid,
            COUNT(*) AS total_count
     FROM mr_fee_payments p
     LEFT JOIN cases c ON p.case_id = c.id
     {$whereClause}",
    $params
);

// Per-category breakdown
$categoryBreakdown = dbFetchAll(
    "SELECT p.expense_category,
            COALESCE(SUM(p.billed_amount), 0) AS total_billed,
            COALESCE(SUM(p.paid_amount), 0) AS total_paid,
            COUNT(*) AS count
     FROM mr_fee_payments p
     LEFT JOIN cases c ON p.case_id = c.id
     {$whereClause}
     GROUP BY p.expense_category",
    $params
);

// Per-staff breakdown
$staffWhere = !empty($conditions) ? $whereClause . ' AND p.paid_by IS NOT NULL' : 'WHERE p.paid_by IS NOT NULL';
$staffBreakdown = dbFetchAll(
    "SELECT p.paid_by,
            u.full_name AS staff_name,
            COALESCE(SUM(p.paid_amount), 0) AS total_paid,
            COUNT(*) AS count
     FROM mr_fee_payments p
     LEFT JOIN cases c ON p.case_id = c.id
     LEFT JOIN users u ON p.paid_by = u.id
     {$staffWhere}
     GROUP BY p.paid_by, u.full_name
     ORDER BY total_paid DESC",
    $params
);

// Per-payment-type breakdown
$typeWhere = !empty($conditions) ? $whereClause . ' AND p.payment_type IS NOT NULL' : 'WHERE p.payment_type IS NOT NULL';
$typeBreakdown = dbFetchAll(
    "SELECT p.payment_type,
            COALESCE(SUM(p.paid_amount), 0) AS total_paid,
            COUNT(*) AS count
     FROM mr_fee_payments p
     LEFT JOIN cases c ON p.case_id = c.id
     {$typeWhere}
     GROUP BY p.payment_type
     ORDER BY total_paid DESC",
    $params
);

// Staff list for filter dropdown
$staff = dbFetchAll(
    "SELECT DISTINCT u.id, u.full_name
     FROM users u
     INNER JOIN mr_fee_payments p ON p.paid_by = u.id
     ORDER BY u.full_name"
);

jsonResponse([
    'success' => true,
    'data' => $rows,
    'total' => count($rows),
    'summary' => [
        'total_billed' => round((float)$summaryRow['total_billed'], 2),
        'total_paid' => round((float)$summaryRow['total_paid'], 2),
        'total_count' => (int)$summaryRow['total_count'],
        'by_category' => $categoryBreakdown,
        'by_staff' => $staffBreakdown,
        'by_payment_type' => $typeBreakdown,
    ],
    'staff' => $staff,
]);
