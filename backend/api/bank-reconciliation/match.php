<?php
/**
 * PUT /api/bank-reconciliation/{id}/match
 * Match a bank statement entry to an MR fee payment
 */
requireAuth();
requirePermission('bank_reconciliation');

$id = (int)$_GET['id'];
$input = getInput();

$errors = validateRequired($input, ['payment_id']);
if ($errors) errorResponse($errors[0]);

$paymentId = (int)$input['payment_id'];

// Verify entry exists
$entry = dbFetchOne("SELECT id, reconciliation_status FROM bank_statement_entries WHERE id = ?", [$id]);
if (!$entry) errorResponse('Bank statement entry not found', 404);

// Verify payment exists
$payment = dbFetchOne("SELECT id FROM mr_fee_payments WHERE id = ?", [$paymentId]);
if (!$payment) errorResponse('Payment not found', 404);

// Check payment is not already matched to another entry
$existing = dbFetchOne(
    "SELECT id FROM bank_statement_entries WHERE matched_payment_id = ? AND id != ?",
    [$paymentId, $id]
);
if ($existing) errorResponse('This payment is already matched to another bank entry');

$user = getCurrentUser();

dbUpdate('bank_statement_entries', [
    'matched_payment_id'    => $paymentId,
    'matched_by'            => $user['id'],
    'matched_at'            => date('Y-m-d H:i:s'),
    'reconciliation_status' => 'matched',
], 'id = ?', [$id]);

logActivity($user['id'], 'match', 'bank_reconciliation', $id, [
    'payment_id' => $paymentId
]);

successResponse(null, 'Entry matched to payment successfully');
