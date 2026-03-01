<?php
/**
 * PUT /api/bank-reconciliation/{id}/ignore
 * Mark a bank statement entry as ignored
 */
requireAuth();
requirePermission('bank_reconciliation');

$id = (int)$_GET['id'];
$input = getInput();

$entry = dbFetchOne("SELECT id FROM bank_statement_entries WHERE id = ?", [$id]);
if (!$entry) errorResponse('Bank statement entry not found', 404);

$user = getCurrentUser();
$notes = trim($input['notes'] ?? '');

$data = ['reconciliation_status' => 'ignored'];
if ($notes) $data['notes'] = $notes;

dbUpdate('bank_statement_entries', $data, 'id = ?', [$id]);

logActivity($user['id'], 'ignore', 'bank_reconciliation', $id, [
    'notes' => $notes ?: null
]);

successResponse(null, 'Entry marked as ignored');
