<?php
// PUT /api/case-providers/bulk-respond
// Bulk accept or decline multiple provider assignments at once
$userId = requireAuth();

$input = getInput();
$action = $input['action'] ?? '';
if (!in_array($action, ['accept', 'decline'])) {
    errorResponse('Action must be accept or decline', 400);
}

$providerIds = $input['provider_ids'] ?? [];
if (!is_array($providerIds) || empty($providerIds)) {
    errorResponse('provider_ids array is required', 400);
}
$providerIds = array_map('intval', $providerIds);

if ($action === 'decline') {
    $reason = trim($input['reason'] ?? '');
    if (!$reason) {
        errorResponse('Decline reason is required', 400);
    }
}

// Fetch all matching case_providers
$placeholders = implode(',', array_fill(0, count($providerIds), '?'));
$rows = dbFetchAll(
    "SELECT cp.*, c.case_number, c.client_name, p.name AS provider_name
     FROM case_providers cp
     JOIN cases c ON c.id = cp.case_id
     JOIN providers p ON p.id = cp.provider_id
     WHERE cp.id IN ($placeholders)",
    $providerIds
);

if (empty($rows)) {
    errorResponse('No matching case providers found', 404);
}

// Validate all rows belong to this user and are pending
foreach ($rows as $cp) {
    if ((int)$cp['assigned_to'] !== $userId) {
        errorResponse("You are not assigned to provider: {$cp['provider_name']}", 403);
    }
    if ($cp['assignment_status'] !== 'pending') {
        errorResponse("Assignment for {$cp['provider_name']} is not pending", 400);
    }
}

$currentUser = dbFetchOne("SELECT COALESCE(display_name, full_name) AS full_name FROM users WHERE id = ?", [$userId]);
$processed = 0;

$pdo = getDbConnection();
$pdo->beginTransaction();
try {
    foreach ($rows as $cp) {
        $cpId = (int)$cp['id'];

        if ($action === 'accept') {
            dbUpdate('case_providers', [
                'assignment_status' => 'accepted'
            ], 'id = ?', [$cpId]);

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
                'case_number' => $cp['case_number'],
                'bulk' => true
            ]);
        } else {
            dbUpdate('case_providers', [
                'assignment_status' => 'declined',
                'assigned_to' => null
            ], 'id = ?', [$cpId]);

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
                'reason' => $reason,
                'bulk' => true
            ]);
        }
        $processed++;
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Failed to process assignments: ' . $e->getMessage(), 500);
}

$label = $action === 'accept' ? 'accepted' : 'declined';
successResponse(['status' => $label, 'processed' => $processed], "$processed assignment(s) $label");
