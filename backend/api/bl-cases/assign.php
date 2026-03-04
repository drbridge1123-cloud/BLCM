<?php
/**
 * POST /api/bl-cases/{id}/assign
 * Assign (or reassign) a case to a staff member
 */
$userId = requireAuth();

$id = (int)$_GET['id'];
$input = getInput();

$errors = validateRequired($input, ['assigned_to', 'note']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$case = dbFetchOne("SELECT * FROM cases WHERE id = ?", [$id]);
if (!$case) errorResponse('Case not found', 404);

$assigneeId = (int)$input['assigned_to'];
$note = sanitizeString($input['note']);

$assignee = dbFetchOne("SELECT id, COALESCE(display_name, full_name) AS full_name, is_active FROM users WHERE id = ?", [$assigneeId]);
if (!$assignee) errorResponse('User not found', 404);
if (!$assignee['is_active']) errorResponse('User is not active');

dbUpdate('cases', [
    'assigned_to'                => $assigneeId,
    'assignment_status'          => 'pending',
    'assignment_assigned_by'     => $userId,
    'assignment_declined_reason' => null,
], 'id = ?', [$id]);

// Set prelitigation_start_date if not already set
if (!$case['prelitigation_start_date']) {
    dbUpdate('cases', [
        'prelitigation_start_date' => date('Y-m-d'),
    ], 'id = ?', [$id]);
}

dbInsert('notifications', [
    'user_id' => $assigneeId,
    'type'    => 'case_assignment',
    'message' => "Case {$case['case_number']} ({$case['client_name']}) reassigned to you: {$note}. Please accept or decline.",
    'is_read' => 0,
]);

logActivity($userId, 'case_reassign', 'case', $id, [
    'assigned_to'   => $assigneeId,
    'assignee_name' => $assignee['full_name'],
    'note'          => $note,
]);

successResponse(null, 'Case reassigned');
