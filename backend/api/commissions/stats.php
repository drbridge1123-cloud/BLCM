<?php
/**
 * GET /api/commissions/stats
 * Commission statistics and summary
 * Supports ?year=YYYY for filtering
 */
$userId = requireAuth();
requirePermission('commissions');
$user = getCurrentUser();

$where  = 'deleted_at IS NULL';
$params = [];

if (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND employee_user_id = ?';
    $params[] = $userId;
}

$year = $_GET['year'] ?? null;
if ($year) {
    $where .= ' AND month LIKE ?';
    $params[] = '%' . (int)$year;
}

// Counts by status
$counts = dbFetchOne("
    SELECT
        COUNT(*) AS total_cases,
        SUM(status = 'in_progress') AS in_progress_count,
        SUM(status = 'unpaid') AS unpaid_count,
        SUM(status = 'paid') AS paid_count,
        SUM(status = 'rejected') AS rejected_count,
        COALESCE(SUM(settled), 0) AS total_settled,
        COALESCE(SUM(commission), 0) AS total_commission,
        COALESCE(SUM(CASE WHEN status = 'paid' THEN commission END), 0) AS paid_commission,
        COALESCE(SUM(CASE WHEN status = 'unpaid' THEN commission END), 0) AS unpaid_commission
    FROM employee_commissions
    WHERE {$where}
", $params);

// Monthly breakdown
$monthly = [];
$byEmployee = [];
if (in_array($user['role'], ['admin', 'manager'])) {
    $monthly = dbFetchAll("
        SELECT month,
            COUNT(*) AS cases,
            COALESCE(SUM(settled), 0) AS settled,
            COALESCE(SUM(legal_fee), 0) AS legal_fee,
            COALESCE(SUM(commission), 0) AS commission,
            SUM(status = 'paid') AS paid_count,
            SUM(status = 'unpaid') AS unpaid_count
        FROM employee_commissions
        WHERE {$where} AND month IS NOT NULL AND month != '' AND month != 'TBD'
        GROUP BY month
        ORDER BY month DESC
        LIMIT 24
    ", $params);

    // By employee
    $byEmployee = dbFetchAll("
        SELECT c.employee_user_id, u.full_name AS employee_name,
            COUNT(*) AS cases,
            COALESCE(SUM(c.commission), 0) AS total_commission,
            COALESCE(SUM(c.settled), 0) AS total_settled,
            COALESCE(SUM(CASE WHEN c.status = 'paid' THEN c.commission END), 0) AS paid,
            COALESCE(SUM(CASE WHEN c.status = 'unpaid' THEN c.commission END), 0) AS unpaid
        FROM employee_commissions c
        JOIN users u ON c.employee_user_id = u.id
        WHERE c.{$where}
        GROUP BY c.employee_user_id
        ORDER BY total_commission DESC
    ", $params);
}

successResponse([
    'total_cases'       => (int)($counts['total_cases'] ?? 0),
    'in_progress_count' => (int)($counts['in_progress_count'] ?? 0),
    'unpaid_count'      => (int)($counts['unpaid_count'] ?? 0),
    'paid_count'        => (int)($counts['paid_count'] ?? 0),
    'rejected_count'    => (int)($counts['rejected_count'] ?? 0),
    'total_settled'     => round((float)($counts['total_settled'] ?? 0), 2),
    'total_commission'  => round((float)($counts['total_commission'] ?? 0), 2),
    'paid_commission'   => round((float)($counts['paid_commission'] ?? 0), 2),
    'unpaid_commission' => round((float)($counts['unpaid_commission'] ?? 0), 2),
    'monthly'           => $monthly,
    'by_employee'       => $byEmployee,
]);
