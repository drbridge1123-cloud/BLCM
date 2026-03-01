<?php
/**
 * GET /api/referrals/report
 * Referral analytics and reports
 */
$userId = requireAuth();
requirePermission('referrals');
$user = getCurrentUser();

$year = (int)($_GET['year'] ?? date('Y'));

$where  = 'r.deleted_at IS NULL AND r.entry_month LIKE ?';
$params = ["%. {$year}"];

if (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND r.lead_id = ?';
    $params[] = $userId;
}

// Total referrals
$total = dbFetchOne(
    "SELECT COUNT(*) AS cnt FROM referral_entries r WHERE {$where}", $params
)['cnt'];

// By personal referrer (referred_by)
$byPersonal = dbFetchAll("
    SELECT r.referred_by AS name, COUNT(*) AS referral_count,
           MAX(r.signed_date) AS last_referral,
           SUM(ac.phase = 'settled') AS settled_count,
           COALESCE(SUM(ac.commission), 0) AS total_commission
    FROM referral_entries r
    LEFT JOIN attorney_cases ac ON r.file_number = ac.case_number
        AND r.file_number IS NOT NULL AND r.file_number != '' AND ac.deleted_at IS NULL
    WHERE {$where} AND r.referred_by IS NOT NULL AND r.referred_by != ''
    GROUP BY r.referred_by
    ORDER BY referral_count DESC
    LIMIT 20
", $params);

// By provider
$byProvider = dbFetchAll("
    SELECT r.referred_to_provider AS name, COUNT(*) AS referral_count,
           MAX(r.signed_date) AS last_referral,
           SUM(ac.phase = 'settled') AS settled_count,
           COALESCE(SUM(ac.commission), 0) AS total_commission
    FROM referral_entries r
    LEFT JOIN attorney_cases ac ON r.file_number = ac.case_number
        AND r.file_number IS NOT NULL AND r.file_number != '' AND ac.deleted_at IS NULL
    WHERE {$where} AND r.referred_to_provider IS NOT NULL AND r.referred_to_provider != ''
    GROUP BY r.referred_to_provider
    ORDER BY referral_count DESC
    LIMIT 20
", $params);

// By body shop
$byDestination = dbFetchAll("
    SELECT r.referred_to_body_shop AS name, COUNT(*) AS referral_count,
           MAX(r.signed_date) AS last_referral
    FROM referral_entries r
    WHERE {$where} AND r.referred_to_body_shop IS NOT NULL AND r.referred_to_body_shop != ''
    GROUP BY r.referred_to_body_shop
    ORDER BY referral_count DESC
    LIMIT 20
", $params);

// By status
$byStatus = dbFetchAll("
    SELECT r.status, COUNT(*) AS count
    FROM referral_entries r
    WHERE {$where} AND r.status IS NOT NULL AND r.status != ''
    GROUP BY r.status
    ORDER BY count DESC
", $params);

// By month (1-12)
$byMonth = dbFetchAll("
    SELECT MONTH(r.signed_date) AS month, COUNT(*) AS count
    FROM referral_entries r
    WHERE {$where} AND r.signed_date IS NOT NULL
    GROUP BY MONTH(r.signed_date)
    ORDER BY month
", $params);

successResponse([
    'total_referrals' => (int)$total,
    'by_personal'     => $byPersonal,
    'by_provider'     => $byProvider,
    'by_destination'  => $byDestination,
    'by_status'       => $byStatus,
    'by_month'        => $byMonth,
]);
