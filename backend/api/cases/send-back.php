<?php
/**
 * POST /api/cases/{id}/send-back
 * Send a case back to a previous status (backward transition)
 */
$userId = requireAuth();
$id = (int)$_GET['id'];
$input = getInput();

$errors = validateRequired($input, ['new_status', 'note']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$case = dbFetchOne("SELECT * FROM cases WHERE id = ?", [$id]);
if (!$case) errorResponse('Case not found', 404);

$newStatus     = sanitizeString($input['new_status']);
$note          = sanitizeString($input['note']);
$currentStatus = $case['status'];

// Allowed backward paths (current => allowed targets)
$backwardPaths = [
    'collecting'         => ['prelitigation'],
    'verification'       => ['collecting'],
    'completed'          => ['verification'],
    'rfd'                => ['completed'],
    'final_verification' => ['rfd'],
    'disbursement'       => ['final_verification'],
    'accounting'         => ['disbursement'],
];

$allowed = $backwardPaths[$currentStatus] ?? [];
if (!in_array($newStatus, $allowed)) {
    errorResponse("Cannot send case back from '{$currentStatus}' to '{$newStatus}'");
}

// Auto-assign owner from STATUS_OWNER_MAP
$newOwner = STATUS_OWNER_MAP[$newStatus] ?? $case['assigned_to'];

dbUpdate('cases', [
    'status'      => $newStatus,
    'assigned_to' => $newOwner,
], 'id = ?', [$id]);

// Log activity
logActivity($userId, 'send_back', 'case', $id, [
    'from' => $currentStatus,
    'to'   => $newStatus,
    'note' => $note,
]);

// Create notification for new owner
if ($newOwner) {
    dbInsert('notifications', [
        'user_id' => $newOwner,
        'type'    => 'send_back',
        'message' => "Case {$case['case_number']} ({$case['client_name']}) sent back to {$newStatus}: {$note}",
    ]);
}

successResponse(null, "Case sent back to {$newStatus}");
