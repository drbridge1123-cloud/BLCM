<?php
/**
 * GET /api/dashboard/summary
 * Unified dashboard summary — role-based data aggregation
 */
$userId = requireAuth();
requirePermission('dashboard');
$user = getCurrentUser();
$permissions = $user['permissions'] ?? [];
$isAdmin = in_array($user['role'], ['admin', 'manager']);

$data = [];

// ── MR Cases (if has cases permission) ──
if (in_array('cases', $permissions) || $isAdmin) {
    $mrWhere = "status != 'closed'";
    $mrParams = [];
    if (!$isAdmin) {
        $mrWhere .= ' AND assigned_to = ?';
        $mrParams[] = $userId;
    }

    $mrStats = dbFetchOne("
        SELECT
            COUNT(*) AS total_active,
            SUM(status = 'collecting') AS collecting_count,
            SUM(status = 'verification') AS verification_count
        FROM cases WHERE {$mrWhere}
    ", $mrParams);

    $data['mr_cases'] = [
        'total_active'    => (int)($mrStats['total_active'] ?? 0),
        'new_count'       => (int)($mrStats['collecting_count'] ?? 0),
        'in_progress'     => (int)($mrStats['verification_count'] ?? 0),
    ];
}

// ── Attorney Cases (if has attorney_cases permission) ──
if (in_array('attorney_cases', $permissions) || $isAdmin) {
    $acWhere = 'deleted_at IS NULL';
    $acParams = [];
    if (!$isAdmin) {
        $acWhere .= ' AND attorney_user_id = ?';
        $acParams[] = $userId;
    }

    $acStats = dbFetchOne("
        SELECT
            SUM(phase != 'settled' AND status = 'in_progress') AS active_count,
            SUM(phase = 'demand') AS demand_count,
            SUM(phase = 'litigation') AS litigation_count,
            SUM(phase = 'uim') AS uim_count,
            SUM(phase = 'demand' AND demand_deadline < CURDATE() AND top_offer_date IS NULL) AS overdue_count,
            COALESCE(SUM(CASE WHEN phase = 'settled' THEN COALESCE(commission, 0) + COALESCE(uim_commission, 0) END), 0) AS total_commission
        FROM attorney_cases WHERE {$acWhere}
    ", $acParams);

    $data['attorney_cases'] = [
        'active_count'     => (int)($acStats['active_count'] ?? 0),
        'demand_count'     => (int)($acStats['demand_count'] ?? 0),
        'litigation_count' => (int)($acStats['litigation_count'] ?? 0),
        'uim_count'        => (int)($acStats['uim_count'] ?? 0),
        'overdue_count'    => (int)($acStats['overdue_count'] ?? 0),
        'total_commission' => round((float)($acStats['total_commission'] ?? 0), 2),
    ];

    // Upcoming deadlines (next 14 days)
    $deadlineWhere = $acWhere . " AND phase = 'demand' AND demand_deadline IS NOT NULL AND demand_deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)";
    $data['upcoming_deadlines'] = dbFetchAll("
        SELECT id, case_number, client_name, demand_deadline,
               DATEDIFF(demand_deadline, CURDATE()) AS days_remaining
        FROM attorney_cases WHERE {$deadlineWhere}
        ORDER BY demand_deadline ASC LIMIT 10
    ", $acParams);
}

// ── Employee Commissions (if has commissions permission) ──
if (in_array('commissions', $permissions) || $isAdmin) {
    $ecWhere = 'deleted_at IS NULL';
    $ecParams = [];
    if (!$isAdmin) {
        $ecWhere .= ' AND employee_user_id = ?';
        $ecParams[] = $userId;
    }

    $ecStats = dbFetchOne("
        SELECT
            COUNT(*) AS total_cases,
            SUM(status = 'unpaid') AS pending_count,
            COALESCE(SUM(commission), 0) AS total_commission,
            COALESCE(SUM(CASE WHEN status = 'paid' THEN commission END), 0) AS paid_commission,
            COALESCE(SUM(CASE WHEN status = 'unpaid' THEN commission END), 0) AS unpaid_commission
        FROM employee_commissions WHERE {$ecWhere}
    ", $ecParams);

    $data['commissions'] = [
        'total_cases'      => (int)($ecStats['total_cases'] ?? 0),
        'pending_count'    => (int)($ecStats['pending_count'] ?? 0),
        'total_commission' => round((float)($ecStats['total_commission'] ?? 0), 2),
        'paid_commission'  => round((float)($ecStats['paid_commission'] ?? 0), 2),
        'unpaid_commission' => round((float)($ecStats['unpaid_commission'] ?? 0), 2),
    ];
}

// ── Traffic (if has traffic permission) ──
if (in_array('traffic', $permissions) || $isAdmin) {
    $trWhere = '1=1';
    $trParams = [];
    if (!$isAdmin) {
        $trWhere .= ' AND user_id = ?';
        $trParams[] = $userId;
    }

    $trStats = dbFetchOne("
        SELECT
            SUM(status = 'active') AS active_count,
            SUM(status = 'resolved') AS resolved_count,
            COALESCE(SUM(commission), 0) AS total_commission
        FROM traffic_cases WHERE {$trWhere}
    ", $trParams);

    $data['traffic'] = [
        'active_count'     => (int)($trStats['active_count'] ?? 0),
        'resolved_count'   => (int)($trStats['resolved_count'] ?? 0),
        'total_commission' => round((float)($trStats['total_commission'] ?? 0), 2),
    ];

    // Pending traffic requests
    $pendReq = dbFetchOne("SELECT COUNT(*) AS cnt FROM traffic_requests WHERE status = 'pending'" .
        (!$isAdmin ? " AND assigned_to = ?" : ""), !$isAdmin ? [$userId] : []);
    $data['traffic']['pending_requests'] = (int)($pendReq['cnt'] ?? 0);
}

// ── Referrals (if has referrals permission) ──
if (in_array('referrals', $permissions) || $isAdmin) {
    $refWhere = 'deleted_at IS NULL';
    $refParams = [];
    if (!$isAdmin) {
        $refWhere .= ' AND lead_id = ?';
        $refParams[] = $userId;
    }

    $curMonth = date('M. Y');
    $refStats = dbFetchOne("
        SELECT
            COUNT(*) AS total_entries,
            SUM(entry_month = ?) AS month_count
        FROM referral_entries WHERE {$refWhere}
    ", array_merge([$curMonth], $refParams));

    $data['referrals'] = [
        'total_entries' => (int)($refStats['total_entries'] ?? 0),
        'month_count'   => (int)($refStats['month_count'] ?? 0),
    ];
}

// ── Pending Requests (admin only) ──
if ($isAdmin) {
    $demandReq = dbFetchOne("SELECT COUNT(*) AS cnt FROM demand_requests WHERE status = 'pending'");
    $deadlineReq = dbFetchOne("SELECT COUNT(*) AS cnt FROM deadline_extension_requests WHERE status = 'pending'");

    $data['pending_requests'] = [
        'demand_requests'   => (int)($demandReq['cnt'] ?? 0),
        'deadline_requests' => (int)($deadlineReq['cnt'] ?? 0),
    ];
}

successResponse($data);
