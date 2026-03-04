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
    'client_dob'            => trim($input['client_dob'] ?? $row['client_dob'] ?? '') ?: null,
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

// Auto-create case when case_manager and file_number are set
$fileNumber = trim($data['file_number'] ?? '');
$clientName = trim($data['client_name'] ?? '');
if ($caseManagerId && $fileNumber) {
    $existingCase = dbFetchOne("SELECT id FROM cases WHERE case_number = ?", [$fileNumber]);
    if (!$existingCase) {
        $caseId = dbInsert('cases', [
            'case_number'              => $fileNumber,
            'client_name'              => $clientName,
            'client_dob'               => $data['client_dob'] ?: null,
            'doi'                      => $data['date_of_loss'] ?: date('Y-m-d'),
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
            'referral_id' => $refId, 'client_name' => $clientName,
        ]);
    }
}

// Conflict check (non-blocking warning)
$warnings = [];
$clientName = $data['client_name'];
$clientDob = $data['client_dob'] ?? null;
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
        [$clientName, $clientDob, $refId]
    );
    if ($refConflict) {
        $warnings[] = "A referral already exists for this client name and DOB";
    }
}

$responseData = !empty($warnings) ? ['warnings' => $warnings] : null;
successResponse($responseData, 'Referral updated');
