<?php
/**
 * PUT /api/case-providers/{id}/update-status
 * Update the overall_status of a case-provider
 */
$userId = requireAuth();
$id     = (int)$_GET['id'];
$input  = getInput();

$errors = validateRequired($input, ['overall_status']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$allowed = [
    'treating','not_started','requesting','follow_up','action_needed',
    'received_partial','on_hold','no_records','received_complete','verified'
];
if (!validateEnum($input['overall_status'], $allowed)) {
    errorResponse('Invalid overall_status');
}

$cp = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$id]);
if (!$cp) errorResponse('Case-provider not found', 404);

$newStatus = $input['overall_status'];
$data = ['overall_status' => $newStatus];

// Handle no_records specifics
if ($newStatus === 'no_records') {
    if (!empty($input['no_records_reason'])) {
        $data['no_records_reason'] = sanitizeString($input['no_records_reason']);
    }
    if (!empty($input['no_records_detail'])) {
        $data['no_records_detail'] = sanitizeString($input['no_records_detail']);
    }
}

// Handle on_hold
if ($newStatus === 'on_hold') {
    $data['is_on_hold'] = 1;
    if (!empty($input['hold_reason'])) {
        $data['hold_reason'] = sanitizeString($input['hold_reason']);
    }
} else {
    $data['is_on_hold'] = 0;
    $data['hold_reason'] = null;
}

dbUpdate('case_providers', $data, 'id = ?', [$id]);

// Notify admin on received_complete
if ($newStatus === 'received_complete') {
    $caseInfo = dbFetchOne("SELECT case_number, client_name FROM cases WHERE id = ?", [$cp['case_id']]);
    $provInfo = dbFetchOne("SELECT name FROM providers WHERE id = ?", [$cp['provider_id']]);
    $admins = dbFetchAll("SELECT id FROM users WHERE role = 'admin' AND is_active = 1");
    foreach ($admins as $admin) {
        dbInsert('notifications', [
            'user_id'          => $admin['id'],
            'case_provider_id' => $id,
            'type'             => 'records_complete',
            'message'          => "Records received complete for {$provInfo['name']} on case {$caseInfo['case_number']}",
        ]);
    }
}

// Auto-advance case when all providers done (ini -> rec)
$doneStatuses = ['received_complete','verified','no_records'];
if (in_array($newStatus, $doneStatuses)) {
    $caseRow = dbFetchOne("SELECT id, status FROM cases WHERE id = ?", [$cp['case_id']]);
    if ($caseRow && $caseRow['status'] === 'ini') {
        $totalProviders = dbCount('case_providers', 'case_id = ?', [$cp['case_id']]);
        $doneProviders  = dbCount(
            'case_providers',
            "case_id = ? AND overall_status IN ('received_complete','verified','no_records')",
            [$cp['case_id']]
        );
        if ($totalProviders > 0 && $doneProviders >= $totalProviders) {
            $newOwner = STATUS_OWNER_MAP['rec'] ?? null;
            $caseUpdate = ['status' => 'rec'];
            if ($newOwner) $caseUpdate['assigned_to'] = $newOwner;
            dbUpdate('cases', $caseUpdate, 'id = ?', [$cp['case_id']]);
            logActivity($userId, 'auto_advance', 'case', $cp['case_id'], [
                'from' => 'ini',
                'to'   => 'rec',
            ]);
        }
    }
}

logActivity($userId, 'update_status', 'case_provider', $id, [
    'from' => $cp['overall_status'],
    'to'   => $newStatus,
]);

successResponse(null, 'Status updated');
