<?php
/**
 * POST /api/performance
 * Generate performance snapshot for current month (admin only)
 */
$userId = requireAuth();
requirePermission('goals');
$user = getCurrentUser();

if (!in_array($user['role'], ['admin', 'manager'])) {
    errorResponse('Not authorized', 403);
}

$input = getInput();
$month = $input['month'] ?? date('Y-m');

// Get all employees who have attorney_cases permission or commissions
$employees = dbFetchAll("SELECT id, full_name FROM users WHERE is_active = 1");

$generated = 0;
foreach ($employees as $emp) {
    $empId = (int)$emp['id'];

    // Check if snapshot already exists
    $existing = dbFetchOne(
        "SELECT id FROM performance_snapshots WHERE employee_id = ? AND snapshot_month = ?",
        [$empId, $month]
    );

    // Attorney case stats for this month
    $acStats = dbFetchOne("
        SELECT
            SUM(phase = 'settled') AS cases_settled,
            SUM(phase = 'settled' AND commission_type LIKE '%demand%') AS demand_settled,
            SUM(phase = 'settled' AND commission_type LIKE '%litigation%') AS litigation_settled,
            COALESCE(SUM(CASE WHEN phase = 'settled' THEN COALESCE(commission, 0) + COALESCE(uim_commission, 0) END), 0) AS total_commission,
            SUM(phase = 'demand' AND DATE_FORMAT(assigned_date, '%Y-%m') = ?) AS new_cases,
            AVG(CASE WHEN demand_duration_days > 0 THEN demand_duration_days END) AS avg_demand_days,
            AVG(CASE WHEN litigation_duration_days > 0 THEN litigation_duration_days END) AS avg_litigation_days
        FROM attorney_cases
        WHERE attorney_user_id = ? AND deleted_at IS NULL
    ", [$month, $empId]);

    // Employee commission stats
    $ecStats = dbFetchOne("
        SELECT COALESCE(SUM(commission), 0) AS ec_commission
        FROM employee_commissions
        WHERE employee_user_id = ? AND deleted_at IS NULL AND status = 'paid'
    ", [$empId]);

    $totalComm = round((float)($acStats['total_commission'] ?? 0) + (float)($ecStats['ec_commission'] ?? 0), 2);

    $snapData = [
        'employee_id'          => $empId,
        'snapshot_month'       => $month,
        'cases_settled'        => (int)($acStats['cases_settled'] ?? 0),
        'demand_settled'       => (int)($acStats['demand_settled'] ?? 0),
        'litigation_settled'   => (int)($acStats['litigation_settled'] ?? 0),
        'total_commission'     => $totalComm,
        'new_cases_received'   => (int)($acStats['new_cases'] ?? 0),
        'avg_demand_days'      => round((float)($acStats['avg_demand_days'] ?? 0), 1),
        'avg_litigation_days'  => round((float)($acStats['avg_litigation_days'] ?? 0), 1),
    ];

    if ($existing) {
        dbUpdate('performance_snapshots', $snapData, 'id = ?', [(int)$existing['id']]);
    } else {
        dbInsert('performance_snapshots', $snapData);
    }
    $generated++;
}

logActivity($userId, 'performance_generated', 'performance_snapshots', null, [
    'month' => $month, 'count' => $generated
]);

successResponse(['generated' => $generated], "Performance snapshots generated for {$month}");
