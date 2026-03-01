<?php
/**
 * DELETE /api/bank-reconciliation/delete-batch
 * Delete all unmatched entries in a batch
 */
requireAuth();
requirePermission('bank_reconciliation');

$input = getInput();
$errors = validateRequired($input, ['batch_id']);
if ($errors) errorResponse($errors[0]);

$batchId = $input['batch_id'];

// Verify batch exists
$batch = dbFetchOne(
    "SELECT COUNT(*) as cnt FROM bank_statement_entries WHERE batch_id = ?", [$batchId]
);
if (!$batch || (int)$batch['cnt'] === 0) errorResponse('Batch not found', 404);

// Only delete unmatched entries (protect matched ones)
$deleted = dbDelete(
    'bank_statement_entries',
    "batch_id = ? AND reconciliation_status = 'unmatched'",
    [$batchId]
);

$user = getCurrentUser();

logActivity($user['id'], 'delete_batch', 'bank_reconciliation', null, [
    'batch_id' => $batchId, 'deleted_count' => $deleted
]);

successResponse(['deleted' => $deleted], "Deleted {$deleted} unmatched entries from batch");
