<?php
/**
 * POST /api/traffic-requests
 * Create a new traffic request
 */
$userId = requireAuth();
requirePermission('traffic');
$input = getInput();

$clientName = trim($input['client_name'] ?? '');
if (!$clientName) {
    errorResponse('Client name is required');
}

// Auto-assign to first active attorney if not specified
$assignedTo = !empty($input['assigned_to']) ? (int)$input['assigned_to'] : null;
if (!$assignedTo) {
    $attorney = dbFetchOne("SELECT id FROM users WHERE role = 'attorney' AND is_active = 1 ORDER BY id LIMIT 1");
    $assignedTo = $attorney ? (int)$attorney['id'] : null;
}

if (!$assignedTo) {
    errorResponse('No attorney available to assign');
}

$id = dbInsert('traffic_requests', [
    'requested_by'         => $userId,
    'assigned_to'          => $assignedTo,
    'client_name'          => $clientName,
    'client_phone'         => trim($input['client_phone'] ?? ''),
    'client_email'         => trim($input['client_email'] ?? ''),
    'court'                => trim($input['court'] ?? ''),
    'court_date'           => ($input['court_date'] ?? '') ?: null,
    'charge'               => trim($input['charge'] ?? ''),
    'case_number'          => trim($input['case_number'] ?? ''),
    'note'                 => trim($input['note'] ?? ''),
    'citation_issued_date' => ($input['citation_issued_date'] ?? '') ?: null,
    'referral_source'      => trim($input['referral_source'] ?? ''),
    'status'               => 'pending',
]);

// Notify assigned attorney
dbInsert('notifications', [
    'user_id'  => $assignedTo,
    'type'     => 'traffic_request',
    'message'  => "New traffic case request for {$clientName}",
    'is_read'  => 0,
]);

logActivity($userId, 'traffic_request_created', 'traffic_requests', $id, [
    'client_name' => $clientName, 'assigned_to' => $assignedTo
]);

successResponse(['id' => $id], 'Traffic request created');
