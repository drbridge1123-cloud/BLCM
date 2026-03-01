<?php
// PUT /api/case-providers/{id}/respond
// Accept or decline an assignment
$userId = requireAuth();

$cpId = (int)($_GET['id'] ?? 0);
if (!$cpId) {
    errorResponse('Case provider ID is required', 400);
}

$input = getInput();
$action = $input['action'] ?? '';
if (!in_array($action, ['accept', 'decline'])) {
    errorResponse('Action must be accept or decline', 400);
}

// Fetch case_provider with case and provider info
$cp = dbFetchOne(
    "SELECT cp.*, c.case_number, c.client_name, p.name AS provider_name
     FROM case_providers cp
     JOIN cases c ON c.id = cp.case_id
     JOIN providers p ON p.id = cp.provider_id
     WHERE cp.id = ?",
    [$cpId]
);
if (!$cp) {
    errorResponse('Case provider not found', 404);
}

if ($cp['assignment_status'] !== 'pending') {
    errorResponse('This assignment is not pending', 400);
}

if ((int)$cp['assigned_to'] !== $userId) {
    errorResponse('You are not assigned to this provider', 403);
}

$currentUser = dbFetchOne("SELECT full_name FROM users WHERE id = ?", [$userId]);

if ($action === 'accept') {
    dbUpdate('case_providers', [
        'assignment_status' => 'accepted'
    ], 'id = ?', [$cpId]);

    // Send message to activator
    if ($cp['activated_by']) {
        dbInsert('messages', [
            'from_user_id' => $userId,
            'to_user_id' => (int)$cp['activated_by'],
            'subject' => "[System] Assignment Accepted: {$cp['provider_name']}",
            'message' => "{$currentUser['full_name']} accepted the assignment for {$cp['provider_name']} on case {$cp['case_number']} ({$cp['client_name']}). Deadline: " . date('M j, Y', strtotime($cp['deadline']))
        ]);
    }

    logActivity($userId, 'accepted_assignment', 'case_provider', $cpId, [
        'provider_name' => $cp['provider_name'],
        'case_number' => $cp['case_number']
    ]);

    successResponse(['status' => 'accepted'], 'Assignment accepted');

} else {
    // Decline
    $reason = trim($input['reason'] ?? '');
    if (!$reason) {
        errorResponse('Decline reason is required', 400);
    }

    dbUpdate('case_providers', [
        'assignment_status' => 'declined',
        'assigned_to' => null
    ], 'id = ?', [$cpId]);

    // Send message to activator
    if ($cp['activated_by']) {
        dbInsert('messages', [
            'from_user_id' => $userId,
            'to_user_id' => (int)$cp['activated_by'],
            'subject' => "[System] Assignment Declined: {$cp['provider_name']}",
            'message' => "{$currentUser['full_name']} declined the assignment for {$cp['provider_name']} on case {$cp['case_number']} ({$cp['client_name']}).\n\nReason: {$reason}"
        ]);
    }

    logActivity($userId, 'declined_assignment', 'case_provider', $cpId, [
        'provider_name' => $cp['provider_name'],
        'case_number' => $cp['case_number'],
        'reason' => $reason
    ]);

    successResponse(['status' => 'declined'], 'Assignment declined');
}
