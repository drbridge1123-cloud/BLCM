<?php
/**
 * GET /api/mr-fee-payments/expense-report
 * Expense report aggregation (admin view)
 */
$userId = requireAuth();
requirePermission('expense_report');

$dateWhere = '1=1';
$params = [];

if (!empty($_GET['date_from'])) {
    $dateWhere .= ' AND mfp.payment_date >= ?';
    $params[] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $dateWhere .= ' AND mfp.payment_date <= ?';
    $params[] = $_GET['date_to'];
}

// 1. Total by category
$byCategory = dbFetchAll("
    SELECT expense_category,
           COALESCE(SUM(billed_amount), 0) AS total_billed,
           COALESCE(SUM(paid_amount), 0) AS total_paid,
           COUNT(*) AS payment_count
    FROM mr_fee_payments mfp
    WHERE {$dateWhere}
    GROUP BY expense_category
", $params);

// 2. Total by staff (paid_by)
$byStaff = dbFetchAll("
    SELECT mfp.paid_by, u.full_name AS staff_name,
           COALESCE(SUM(mfp.billed_amount), 0) AS total_billed,
           COALESCE(SUM(mfp.paid_amount), 0) AS total_paid,
           COUNT(*) AS payment_count
    FROM mr_fee_payments mfp
    LEFT JOIN users u ON u.id = mfp.paid_by
    WHERE {$dateWhere}
    GROUP BY mfp.paid_by, u.full_name
", $params);

// 3. Total by payment type
$byPaymentType = dbFetchAll("
    SELECT payment_type,
           COALESCE(SUM(billed_amount), 0) AS total_billed,
           COALESCE(SUM(paid_amount), 0) AS total_paid,
           COUNT(*) AS payment_count
    FROM mr_fee_payments mfp
    WHERE {$dateWhere}
    GROUP BY payment_type
", $params);

successResponse([
    'by_category'     => $byCategory,
    'by_staff'        => $byStaff,
    'by_payment_type' => $byPaymentType,
]);
