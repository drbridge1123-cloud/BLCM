<?php
/**
 * GET /api/referrals
 * List referral entries with filters and pagination
 */
$userId = requireAuth();
requirePermission('referrals');
$user = getCurrentUser();

[$page, $perPage, $offset] = getPaginationParams();

$search    = $_GET['search'] ?? null;
$year      = $_GET['year'] ?? null;
$month     = $_GET['month'] ?? null;
$managerId = $_GET['case_manager_id'] ?? null;
$leadId    = $_GET['lead_id'] ?? null;

$where  = 'r.deleted_at IS NULL';
$params = [];

// Non-admin/manager sees only own referrals
if (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND r.lead_id = ?';
    $params[] = $userId;
}

if ($year) {
    $where .= ' AND r.entry_month LIKE ?';
    $params[] = "%. {$year}";
}
if ($month) {
    $where .= ' AND r.entry_month = ?';
    $params[] = $month;
}
if ($managerId && $managerId !== 'all') {
    $where .= ' AND r.case_manager_id = ?';
    $params[] = (int)$managerId;
}
if ($leadId) {
    $where .= ' AND r.lead_id = ?';
    $params[] = (int)$leadId;
}
if ($search) {
    $where .= ' AND (r.client_name LIKE ? OR r.file_number LIKE ? OR r.referred_by LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$total = dbFetchOne(
    "SELECT COUNT(*) AS cnt FROM referral_entries r WHERE {$where}", $params
)['cnt'];

$sql = "SELECT r.*,
            cm.full_name AS case_manager_name,
            cb.full_name AS created_by_name,
            ld.full_name AS lead_name,
            ac.id AS case_id, ac.phase, ac.status AS case_status,
            ac.settled, ac.legal_fee, ac.discounted_legal_fee,
            ac.commission, ac.check_received AS case_check_received,
            ac.reviewed_at AS paid_date
        FROM referral_entries r
        LEFT JOIN users cm ON r.case_manager_id = cm.id
        LEFT JOIN users cb ON r.created_by = cb.id
        LEFT JOIN users ld ON r.lead_id = ld.id
        LEFT JOIN attorney_cases ac ON r.file_number = ac.case_number
            AND r.file_number IS NOT NULL AND r.file_number != ''
            AND ac.deleted_at IS NULL
        WHERE {$where}
        ORDER BY r.signed_date DESC, r.row_number DESC
        LIMIT ? OFFSET ?";

$rows = dbFetchAll($sql, array_merge($params, [$perPage, $offset]));

// Summary stats
$summaryWhere = str_replace('r.', '', $where);
$curMonth = date('M. Y');
$curYear = date('Y');

$summary = dbFetchOne("
    SELECT
        COUNT(*) AS total_entries,
        SUM(r.entry_month = ?) AS month_count
    FROM referral_entries r
    WHERE {$where}
", array_merge([$curMonth], $params));

paginatedResponse($rows, (int)$total, $page, $perPage, ['summary' => [
    'total_entries' => (int)($summary['total_entries'] ?? 0),
    'month_count'   => (int)($summary['month_count'] ?? 0),
]]);
