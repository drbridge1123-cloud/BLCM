<?php
/**
 * PUT /api/case-providers/{id}/assign
 * Assign a user to a case-provider
 */
$userId = requireAuth();
$id     = (int)$_GET['id'];
$input  = getInput();

$errors = validateRequired($input, ['assigned_to']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$cp = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$id]);
if (!$cp) errorResponse('Case-provider not found', 404);

$assigneeId = (int)$input['assigned_to'];

// Validate user is active
$assignee = dbFetchOne("SELECT id, full_name, is_active FROM users WHERE id = ?", [$assigneeId]);
if (!$assignee) errorResponse('User not found', 404);
if (!$assignee['is_active']) errorResponse('User is not active');

dbUpdate('case_providers', [
    'assigned_to'       => $assigneeId,
    'assignment_status' => 'pending',
], 'id = ?', [$id]);

// Notify assignee
$caseInfo = dbFetchOne("SELECT case_number, client_name FROM cases WHERE id = ?", [$cp['case_id']]);
$provInfo = dbFetchOne("SELECT name FROM providers WHERE id = ?", [$cp['provider_id']]);

dbInsert('notifications', [
    'user_id'          => $assigneeId,
    'case_provider_id' => $id,
    'type'             => 'assignment',
    'message'          => "You have been assigned to {$provInfo['name']} on case {$caseInfo['case_number']}",
]);

logActivity($userId, 'assign', 'case_provider', $id, [
    'assigned_to' => $assigneeId,
    'assignee_name' => $assignee['full_name'],
]);

successResponse(null, 'Provider assigned');
