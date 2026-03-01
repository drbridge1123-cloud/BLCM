<?php
/**
 * GET /api/mr-fee-payments
 * List payments for a case with optional filters and totals
 */
$userId = requireAuth();
requirePermission('cases');

$caseId = (int)($_GET['case_id'] ?? 0);
if (!$caseId) errorResponse('case_id is required');

$where = 'mfp.case_id = ?';
$params = [$caseId];

if (!empty($_GET['expense_category'])) {
    $valid = ['mr_cost', 'litigation', 'other'];
    if (!validateEnum($_GET['expense_category'], $valid)) errorResponse('Invalid expense_category');
    $where .= ' AND mfp.expense_category = ?';
    $params[] = $_GET['expense_category'];
}

if (!empty($_GET['payment_type'])) {
    $valid = ['check', 'card', 'cash', 'wire', 'other'];
    if (!validateEnum($_GET['payment_type'], $valid)) errorResponse('Invalid payment_type');
    $where .= ' AND mfp.payment_type = ?';
    $params[] = $_GET['payment_type'];
}

$rows = dbFetchAll("
    SELECT mfp.*, u.full_name AS paid_by_name
    FROM mr_fee_payments mfp
    LEFT JOIN users u ON u.id = mfp.paid_by
    WHERE {$where}
    ORDER BY mfp.payment_date DESC
", $params);

$totals = dbFetchOne("
    SELECT COALESCE(SUM(billed_amount), 0) AS total_billed,
           COALESCE(SUM(paid_amount), 0) AS total_paid
    FROM mr_fee_payments mfp
    WHERE {$where}
", $params);

successResponse([
    'payments' => $rows,
    'totals'   => $totals,
]);
