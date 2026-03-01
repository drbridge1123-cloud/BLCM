<?php
/**
 * POST /api/referrals
 * Create a new referral entry
 */
$userId = requireAuth();
requirePermission('referrals');
$user = getCurrentUser();
$input = getInput();

$clientName = trim($input['client_name'] ?? '');
if (!$clientName) {
    errorResponse('Client name is required');
}

$signedDate = $input['signed_date'] ?? date('Y-m-d');
$entryMonth = date('M. Y', strtotime($signedDate));

// Auto-increment row_number per month
$maxRow = dbFetchOne(
    "SELECT MAX(row_number) AS mx FROM referral_entries WHERE entry_month = ? AND deleted_at IS NULL",
    [$entryMonth]
);
$rowNumber = ((int)($maxRow['mx'] ?? 0)) + 1;

// Employee restrictions
$caseManagerId = !empty($input['case_manager_id']) ? (int)$input['case_manager_id'] : null;
$leadId = !empty($input['lead_id']) ? (int)$input['lead_id'] : null;

if (!in_array($user['role'], ['admin', 'manager'])) {
    $caseManagerId = $userId;
    if (!$leadId) $leadId = $userId;
}

$id = dbInsert('referral_entries', [
    'row_number'            => $rowNumber,
    'signed_date'           => $signedDate,
    'file_number'           => trim($input['file_number'] ?? ''),
    'client_name'           => $clientName,
    'status'                => trim($input['status'] ?? ''),
    'date_of_loss'          => $input['date_of_loss'] ?: null,
    'referred_by'           => trim($input['referred_by'] ?? ''),
    'referred_to_provider'  => trim($input['referred_to_provider'] ?? ''),
    'referred_to_body_shop' => trim($input['referred_to_body_shop'] ?? ''),
    'referral_type'         => trim($input['referral_type'] ?? ''),
    'lead_id'               => $leadId,
    'case_manager_id'       => $caseManagerId,
    'remark'                => trim($input['remark'] ?? ''),
    'entry_month'           => $entryMonth,
    'created_by'            => $userId,
]);

logActivity($userId, 'referral_created', 'referral_entries', $id, [
    'client_name' => $clientName
]);

successResponse(['id' => $id], 'Referral created');
