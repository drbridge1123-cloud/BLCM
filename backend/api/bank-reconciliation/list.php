<?php
/**
 * GET /api/bank-reconciliation
 * List bank statement entries with filters, search, pagination, and summary
 */
requireAuth();
requirePermission('bank_reconciliation');

[$page, $perPage, $offset] = getPaginationParams();

$search = $_GET['search'] ?? null;
$status = $_GET['reconciliation_status'] ?? null;
$batchId = $_GET['batch_id'] ?? null;
$dateFrom = $_GET['date_from'] ?? null;
$dateTo = $_GET['date_to'] ?? null;

$where = '1=1';
$params = [];

if ($search) {
    $where .= ' AND (b.description LIKE ? OR b.check_number LIKE ? OR b.reference_number LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($status) {
    $where .= ' AND b.reconciliation_status = ?';
    $params[] = $status;
}
if ($batchId) {
    $where .= ' AND b.batch_id = ?';
    $params[] = $batchId;
}
if ($dateFrom) {
    $where .= ' AND b.transaction_date >= ?';
    $params[] = $dateFrom;
}
if ($dateTo) {
    $where .= ' AND b.transaction_date <= ?';
    $params[] = $dateTo;
}

$total = dbFetchOne(
    "SELECT COUNT(*) as cnt FROM bank_statement_entries b WHERE {$where}", $params
)['cnt'];

$sql = "SELECT b.*, u.full_name AS imported_by_name,
            p.paid_amount AS payment_amount, p.check_number AS payment_check_number,
            p.payment_date AS payment_date, p.provider_name AS payment_provider
        FROM bank_statement_entries b
        JOIN users u ON b.imported_by = u.id
        LEFT JOIN mr_fee_payments p ON b.matched_payment_id = p.id
        WHERE {$where}
        ORDER BY b.transaction_date DESC
        LIMIT ? OFFSET ?";

$entries = dbFetchAll($sql, array_merge($params, [$perPage, $offset]));

$summary = dbFetchOne("
    SELECT
        COUNT(*) AS total_entries,
        SUM(reconciliation_status = 'unmatched') AS unmatched_count,
        SUM(reconciliation_status = 'matched') AS matched_count,
        SUM(reconciliation_status = 'ignored') AS ignored_count,
        COALESCE(SUM(CASE WHEN reconciliation_status = 'unmatched' THEN amount END), 0) AS unmatched_sum,
        COALESCE(SUM(CASE WHEN reconciliation_status = 'matched' THEN amount END), 0) AS matched_sum,
        COALESCE(SUM(CASE WHEN reconciliation_status = 'ignored' THEN amount END), 0) AS ignored_sum
    FROM bank_statement_entries b
    WHERE {$where}
", $params);

paginatedResponse($entries, (int)$total, $page, $perPage, ['summary' => [
    'total_entries'  => (int)$summary['total_entries'],
    'unmatched_count'=> (int)$summary['unmatched_count'],
    'matched_count'  => (int)$summary['matched_count'],
    'ignored_count'  => (int)$summary['ignored_count'],
    'unmatched_sum'  => (float)$summary['unmatched_sum'],
    'matched_sum'    => (float)$summary['matched_sum'],
    'ignored_sum'    => (float)$summary['ignored_sum'],
]]);
