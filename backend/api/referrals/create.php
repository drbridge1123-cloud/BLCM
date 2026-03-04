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
$clientDob = trim($input['client_dob'] ?? '') ?: null;

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
    'client_dob'            => $clientDob,
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

// Auto-create case when case_manager and file_number are set
$fileNumber = trim($input['file_number'] ?? '');
if ($caseManagerId && $fileNumber) {
    $existingCase = dbFetchOne("SELECT id FROM cases WHERE case_number = ?", [$fileNumber]);
    if (!$existingCase) {
        $caseId = dbInsert('cases', [
            'case_number'              => $fileNumber,
            'client_name'              => $clientName,
            'client_dob'               => $clientDob ?: null,
            'doi'                      => $input['date_of_loss'] ?: date('Y-m-d'),
            'assigned_to'              => $caseManagerId,
            'status'                   => 'ini',
            'assignment_status'        => 'pending',
            'assignment_assigned_by'   => $userId,
            'prelitigation_start_date' => date('Y-m-d'),
        ]);

        dbInsert('notifications', [
            'user_id' => $caseManagerId,
            'type'    => 'case_assignment',
            'message' => "New case from referral: {$fileNumber} ({$clientName}). Please accept or decline.",
            'is_read' => 0,
        ]);

        logActivity($userId, 'case_created_from_referral', 'case', $caseId, [
            'referral_id' => $id, 'client_name' => $clientName,
        ]);
    }
}

// Conflict check (non-blocking warning)
$warnings = [];
if ($clientDob && $clientName) {
    $caseConflict = dbFetchOne(
        "SELECT id, case_number FROM cases WHERE client_name = ? AND client_dob = ?",
        [$clientName, $clientDob]
    );
    if ($caseConflict) {
        $warnings[] = "A case already exists for this client (Case #" . $caseConflict['case_number'] . ")";
    }
    $refConflict = dbFetchOne(
        "SELECT id FROM referral_entries WHERE client_name = ? AND client_dob = ? AND id != ? AND deleted_at IS NULL",
        [$clientName, $clientDob, $id]
    );
    if ($refConflict) {
        $warnings[] = "A referral already exists for this client name and DOB";
    }
}

$responseData = ['id' => $id];
if (!empty($warnings)) {
    $responseData['warnings'] = $warnings;
}
successResponse($responseData, 'Referral created');
