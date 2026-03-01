<?php
/**
 * PUT /api/case-providers/{id}/update-deadline
 * Update the deadline for a case-provider and log the change
 */
$userId = requireAuth();
$id     = (int)$_GET['id'];
$input  = getInput();

$errors = validateRequired($input, ['deadline', 'reason']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

if (!validateDate($input['deadline'])) errorResponse('Invalid deadline date');

$cp = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$id]);
if (!$cp) errorResponse('Case-provider not found', 404);

$oldDeadline = $cp['deadline'] ?? date('Y-m-d');
$newDeadline = $input['deadline'];
$reason      = sanitizeString($input['reason']);

// Record change in deadline_changes table
dbInsert('deadline_changes', [
    'case_provider_id' => $id,
    'old_deadline'     => $oldDeadline,
    'new_deadline'     => $newDeadline,
    'reason'           => $reason,
    'changed_by'       => $userId,
]);

// Update the deadline
dbUpdate('case_providers', ['deadline' => $newDeadline], 'id = ?', [$id]);

logActivity($userId, 'update_deadline', 'case_provider', $id, [
    'old_deadline' => $oldDeadline,
    'new_deadline' => $newDeadline,
    'reason'       => $reason,
]);

successResponse(null, 'Deadline updated');
