<?php
/**
 * GET /api/attorney-cases/stats
 * Attorney case statistics, commission totals, breakdowns
 * Supports ?year=YYYY for filtering settled cases
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$user = getCurrentUser();

$where  = 'deleted_at IS NULL';
$params = [];

// Non-admin/manager only sees own stats
if (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND attorney_user_id = ?';
    $params[] = $userId;
}

// ── Active counts (always unfiltered by year) ──
$stats = dbFetchOne("
    SELECT
        SUM(phase != 'settled' AND status = 'in_progress') AS total_active,
        SUM(phase = 'demand') AS demand_count,
        SUM(phase = 'litigation') AS litigation_count,
        SUM(phase = 'uim') AS uim_count,
        SUM(phase = 'settled') AS settled_count,
        SUM(phase = 'demand' AND demand_deadline < CURDATE() AND top_offer_date IS NULL) AS overdue_count
    FROM attorney_cases
    WHERE {$where}
", $params);

// ── Year-filtered queries for reports ──
$year = $_GET['year'] ?? date('Y');
$yearWhere = $where . ' AND phase = ? AND month LIKE ?';
$yearParams = array_merge($params, ['settled', '%' . (int)$year]);

// Totals for settled cases in the selected year
$totals = dbFetchOne("
    SELECT
        COALESCE(SUM(settled), 0) AS total_settled,
        COALESCE(SUM(COALESCE(legal_fee, 0) + COALESCE(uim_legal_fee, 0)), 0) AS total_legal_fee,
        COALESCE(SUM(COALESCE(commission, 0) + COALESCE(uim_commission, 0)), 0) AS total_commission
    FROM attorney_cases
    WHERE {$yearWhere}
", $yearParams);

// Average demand days for the selected year
$avgDays = dbFetchOne("
    SELECT AVG(demand_duration_days) AS avg_demand_days
    FROM attorney_cases
    WHERE {$yearWhere} AND demand_duration_days IS NOT NULL
", $yearParams);

// ── By Phase breakdown ──
$byPhase = dbFetchAll("
    SELECT
        CASE
            WHEN uim_settled > 0 THEN 'uim'
            WHEN litigation_settled_date IS NOT NULL THEN 'litigation'
            ELSE 'demand'
        END AS phase,
        COUNT(*) AS count,
        COALESCE(SUM(COALESCE(commission, 0) + COALESCE(uim_commission, 0)), 0) AS commission
    FROM attorney_cases
    WHERE {$yearWhere}
    GROUP BY phase
    ORDER BY count DESC
", $yearParams);

// ── By Attorney breakdown ──
$byAttorney = dbFetchAll("
    SELECT u.full_name AS attorney_name,
        COUNT(*) AS cases,
        COALESCE(SUM(COALESCE(ac.commission, 0) + COALESCE(ac.uim_commission, 0)), 0) AS commission
    FROM attorney_cases ac
    JOIN users u ON ac.attorney_user_id = u.id
    WHERE ac.{$yearWhere}
    GROUP BY ac.attorney_user_id
    ORDER BY commission DESC
", $yearParams);

// ── Monthly commission ──
$curMonth = date('Y-m');
$monthComm = dbFetchOne("
    SELECT COALESCE(SUM(COALESCE(commission, 0) + COALESCE(uim_commission, 0)), 0) AS total
    FROM attorney_cases
    WHERE {$where} AND (
        DATE_FORMAT(demand_settled_date, '%Y-%m') = ?
        OR DATE_FORMAT(litigation_settled_date, '%Y-%m') = ?
        OR DATE_FORMAT(uim_settled_date, '%Y-%m') = ?
    )
", array_merge($params, [$curMonth, $curMonth, $curMonth]));

successResponse([
    'total_active'     => (int)($stats['total_active'] ?? 0),
    'demand_count'     => (int)($stats['demand_count'] ?? 0),
    'litigation_count' => (int)($stats['litigation_count'] ?? 0),
    'uim_count'        => (int)($stats['uim_count'] ?? 0),
    'settled_count'    => (int)($stats['settled_count'] ?? 0),
    'overdue_count'    => (int)($stats['overdue_count'] ?? 0),
    'month_commission' => round((float)$monthComm['total'], 2),
    'total_settled'    => round((float)($totals['total_settled'] ?? 0), 2),
    'total_legal_fee'  => round((float)($totals['total_legal_fee'] ?? 0), 2),
    'total_commission' => round((float)($totals['total_commission'] ?? 0), 2),
    'avg_demand_days'  => round((float)($avgDays['avg_demand_days'] ?? 0), 1),
    'by_phase'         => $byPhase,
    'by_attorney'      => $byAttorney,
]);
