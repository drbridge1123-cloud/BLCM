<?php
/**
 * PUT /api/referrals/{id}
 * Update a referral entry
 */
$userId = requireAuth();
requirePermission('referrals');
$user = getCurrentUser();
$input = getInput();

$refId = (int)($_GET['id'] ?? 0);
if (!$refId) errorResponse('Referral ID required');

$row = dbFetchOne("SELECT * FROM referral_entries WHERE id = ? AND deleted_at IS NULL", [$refId]);
if (!$row) errorResponse('Referral not found', 404);

// Ownership check for non-admin
if (!in_array($user['role'], ['admin', 'manager']) && (int)$row['lead_id'] !== $userId) {
    errorResponse('Not authorized', 403);
}

$signedDate = $input['signed_date'] ?? $row['signed_date'];
$entryMonth = date('M. Y', strtotime($signedDate));

// Employee restrictions
$caseManagerId = isset($input['case_manager_id']) ? (int)$input['case_manager_id'] : (int)$row['case_manager_id'];
if (!in_array($user['role'], ['admin', 'manager'])) {
    $caseManagerId = $userId;
}

$data = [
    'signed_date'           => $signedDate,
    'file_number'           => trim($input['file_number'] ?? $row['file_number']),
    'client_name'           => trim($input['client_name'] ?? $row['client_name']),
    'status'                => trim($input['status'] ?? $row['status'] ?? ''),
    'date_of_loss'          => $input['date_of_loss'] ?? $row['date_of_loss'],
    'referred_by'           => trim($input['referred_by'] ?? $row['referred_by'] ?? ''),
    'referred_to_provider'  => trim($input['referred_to_provider'] ?? $row['referred_to_provider'] ?? ''),
    'referred_to_body_shop' => trim($input['referred_to_body_shop'] ?? $row['referred_to_body_shop'] ?? ''),
    'referral_type'         => trim($input['referral_type'] ?? $row['referral_type'] ?? ''),
    'lead_id'               => isset($input['lead_id']) ? (int)$input['lead_id'] : $row['lead_id'],
    'case_manager_id'       => $caseManagerId,
    'remark'                => trim($input['remark'] ?? $row['remark'] ?? ''),
    'entry_month'           => $entryMonth,
];

dbUpdate('referral_entries', $data, 'id = ?', [$refId]);
logActivity($userId, 'referral_updated', 'referral_entries', $refId);

successResponse(null, 'Referral updated');
