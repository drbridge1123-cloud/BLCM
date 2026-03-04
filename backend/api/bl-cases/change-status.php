<?php
/**
 * POST /api/bl-cases/{id}/change-status
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
    'ini'                => 1,
    'rec'                => 2,
    'verification'       => 3,
    'rfd'                => 4,
    'neg'                => 5,
    'lit'                => 6,
    'final_verification' => 7,
    'accounting'         => 8,
    'closed'             => 9,
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

// Manual staff assignment required
if (empty($input['assign_to'])) {
    errorResponse('Staff assignment is required for forward transitions');
}
$assignTo = (int)$input['assign_to'];
$staff = dbFetchOne("SELECT id FROM users WHERE id = ? AND is_active = 1", [$assignTo]);
if (!$staff) errorResponse('Invalid staff member');

$updateData = [
    'status'                 => $newStatus,
    'assigned_to'            => $assignTo,
    'assignment_status'      => 'pending',
    'assignment_assigned_by' => $userId,
];

// Log workflow date for phase transitions
$dateColumns = [
    'ini'                => 'prelitigation_start_date',
    'rec'                => 'sent_to_billing_date',
    'rfd'                => 'sent_to_attorney_date',
    'lit'                => 'sent_to_litigation_date',
    'neg'                => 'sent_to_billing_final_date',
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

// Create notification for assigned staff
dbInsert('notifications', [
    'user_id' => $assignTo,
    'type'    => 'status_change',
    'message' => "Case {$case['case_number']} ({$case['client_name']}) moved to {$newStatus}: {$note}",
]);

successResponse(null, "Case status changed to {$newStatus}");
