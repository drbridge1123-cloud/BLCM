<?php
/**
 * DELETE /api/mr-fee-payments/{id}
 * Delete an MR fee payment
 */
$userId = requireAuth();
requirePermission('cases');

$paymentId = (int)($_GET['id'] ?? 0);
if (!$paymentId) errorResponse('Payment ID is required');

$payment = dbFetchOne(
    "SELECT id, case_id, expense_category, provider_name FROM mr_fee_payments WHERE id = ?",
    [$paymentId]
);
if (!$payment) errorResponse('Payment not found', 404);

dbDelete('mr_fee_payments', 'id = ?', [$paymentId]);

logActivity($userId, 'delete', 'mr_fee_payment', $paymentId, [
    'case_id'  => $payment['case_id'],
    'category' => $payment['expense_category'],
    'provider' => $payment['provider_name'],
]);

successResponse(null, 'Payment deleted successfully');
