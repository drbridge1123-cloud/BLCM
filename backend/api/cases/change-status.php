<?php
/**
 * POST /api/cases/{id}/change-status
 * Forward-only status transition with auto-assignment and notification
 */
$userId = requireAuth();
$id = (int)$_GET['id'];
$input = getInput();

$errors = validateRequired($input, ['new_status', 'note']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$case = dbFetchOne("SELECT * FROM cases WHERE id = ?", [$id]);
if (!$case) errorResponse('Case not found', 404);

$newStatus = sanitizeString($input['new_status']);
$note      = sanitizeString($input['note']);

// Define forward-only transition order
$statusOrder = [
    'prelitigation'      => 0,
    'collecting'         => 1,
    'verification'       => 2,
    'completed'          => 3,
    'rfd'                => 4,
    'final_verification' => 5,
    'disbursement'       => 6,
    'accounting'         => 7,
    'closed'             => 8,
];

$currentStatus = $case['status'];

if (!isset($statusOrder[$newStatus])) {
    errorResponse('Invalid status');
}

if (!isset($statusOrder[$currentStatus])) {
    errorResponse('Current case status is invalid');
}

if ($statusOrder[$newStatus] <= $statusOrder[$currentStatus]) {
    errorResponse("Cannot move forward from '{$currentStatus}' to '{$newStatus}'. Use send-back for backward transitions.");
}

// Auto-assign owner from STATUS_OWNER_MAP
$newOwner = STATUS_OWNER_MAP[$newStatus] ?? $case['assigned_to'];

$updateData = [
    'status'      => $newStatus,
    'assigned_to' => $newOwner,
];

// Log workflow date for phase transitions
$dateColumns = [
    'prelitigation'      => 'prelitigation_start_date',
    'collecting'         => 'sent_to_billing_date',
    'rfd'                => 'sent_to_attorney_date',
    'final_verification' => 'sent_to_billing_final_date',
    'accounting'         => 'sent_to_accounting_date',
    'closed'             => 'closed_date',
];
if (isset($dateColumns[$newStatus])) {
    $updateData[$dateColumns[$newStatus]] = date('Y-m-d');
}

dbUpdate('cases', $updateData, 'id = ?', [$id]);

// Log activity
logActivity($userId, 'change_status', 'case', $id, [
    'from'  => $currentStatus,
    'to'    => $newStatus,
    'note'  => $note,
]);

// Create notification for new owner
if ($newOwner) {
    dbInsert('notifications', [
        'user_id' => $newOwner,
        'type'    => 'status_change',
        'message' => "Case {$case['case_number']} ({$case['client_name']}) moved to {$newStatus}: {$note}",
    ]);
}

successResponse(null, "Case status changed to {$newStatus}");
