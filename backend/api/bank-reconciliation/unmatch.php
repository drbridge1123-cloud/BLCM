<?php
/**
 * PUT /api/bank-reconciliation/{id}/unmatch
 * Remove match from a bank statement entry
 */
requireAuth();
requirePermission('bank_reconciliation');

$id = (int)$_GET['id'];

$entry = dbFetchOne("SELECT id, matched_payment_id FROM bank_statement_entries WHERE id = ?", [$id]);
if (!$entry) errorResponse('Bank statement entry not found', 404);

$user = getCurrentUser();

dbUpdate('bank_statement_entries', [
    'matched_payment_id'    => null,
    'matched_by'            => null,
    'matched_at'            => null,
    'reconciliation_status' => 'unmatched',
], 'id = ?', [$id]);

logActivity($user['id'], 'unmatch', 'bank_reconciliation', $id, [
    'previous_payment_id' => $entry['matched_payment_id']
]);

successResponse(null, 'Entry unmatched successfully');
