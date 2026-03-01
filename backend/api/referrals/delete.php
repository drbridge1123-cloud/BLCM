<?php
/**
 * DELETE /api/referrals/{id}
 * Soft-delete a referral entry
 */
$userId = requireAuth();
requirePermission('referrals');
$user = getCurrentUser();

$refId = (int)($_GET['id'] ?? 0);
if (!$refId) errorResponse('Referral ID required');

$row = dbFetchOne("SELECT * FROM referral_entries WHERE id = ? AND deleted_at IS NULL", [$refId]);
if (!$row) errorResponse('Referral not found', 404);

// Ownership check for non-admin
if (!in_array($user['role'], ['admin', 'manager']) && (int)$row['lead_id'] !== $userId) {
    errorResponse('Not authorized', 403);
}

dbUpdate('referral_entries', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [$refId]);
logActivity($userId, 'referral_deleted', 'referral_entries', $refId);

successResponse(null, 'Referral deleted');
