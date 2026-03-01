<?php
/**
 * GET /api/bank-reconciliation/search-payments
 * Search MR fee payments for potential matches
 */
requireAuth();
requirePermission('bank_reconciliation');

$amount      = $_GET['amount'] ?? null;
$checkNumber = $_GET['check_number'] ?? null;
$dateFrom    = $_GET['date_from'] ?? null;
$dateTo      = $_GET['date_to'] ?? null;

$where = "p.id NOT IN (SELECT matched_payment_id FROM bank_statement_entries WHERE matched_payment_id IS NOT NULL)";
$params = [];

if ($amount !== null && $amount !== '') {
    $amt = (float)$amount;
    $where .= ' AND p.paid_amount BETWEEN ? AND ?';
    $params[] = $amt - 0.01;
    $params[] = $amt + 0.01;
}

if ($checkNumber) {
    $where .= ' AND p.check_number LIKE ?';
    $params[] = "%{$checkNumber}%";
}

if ($dateFrom) {
    $where .= ' AND p.payment_date >= ?';
    $params[] = $dateFrom;
}

if ($dateTo) {
    $where .= ' AND p.payment_date <= ?';
    $params[] = $dateTo;
}

$sql = "SELECT p.*, c.case_number, c.client_name, u.full_name AS paid_by_name
        FROM mr_fee_payments p
        LEFT JOIN cases c ON p.case_id = c.id
        LEFT JOIN users u ON p.paid_by = u.id
        WHERE {$where}
        ORDER BY p.payment_date DESC
        LIMIT 20";

$payments = dbFetchAll($sql, $params);

successResponse($payments);
