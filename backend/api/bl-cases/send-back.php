<?php
/**
 * POST /api/bl-cases/{id}/send-back
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

// Allow backward transition to any previous status
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

if (!isset($statusOrder[$newStatus])) {
    errorResponse('Invalid target status');
}

if (!isset($statusOrder[$currentStatus])) {
    errorResponse('Current case status is invalid');
}

if ($statusOrder[$newStatus] >= $statusOrder[$currentStatus]) {
    errorResponse("Cannot send case back from '{$currentStatus}' to '{$newStatus}'. Use change-status for forward transitions.");
}

// Staff assignment required
if (empty($input['assign_to'])) {
    errorResponse('Staff assignment is required');
}
$assignTo = (int)$input['assign_to'];
$staff = dbFetchOne("SELECT id FROM users WHERE id = ? AND is_active = 1", [$assignTo]);
if (!$staff) errorResponse('Invalid staff member');

dbUpdate('cases', [
    'status'                     => $newStatus,
    'assigned_to'                => $assignTo,
    'assignment_status'          => 'pending',
    'assignment_assigned_by'     => $userId,
    'assignment_declined_reason' => null,
], 'id = ?', [$id]);

// Log activity
logActivity($userId, 'send_back', 'case', $id, [
    'from'        => $currentStatus,
    'to'          => $newStatus,
    'assigned_to' => $assignTo,
    'note'        => $note,
]);

// Create notification for assigned staff
dbInsert('notifications', [
    'user_id' => $assignTo,
    'type'    => 'case_assignment',
    'message' => "Case {$case['case_number']} ({$case['client_name']}) sent back to {$newStatus}: {$note}. Please accept or decline.",
]);

successResponse(null, "Case sent back to {$newStatus}");
