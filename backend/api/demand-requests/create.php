<?php
/**
 * POST /api/demand-requests
 * Create a new demand case request
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$input = getInput();

$caseNumber = trim($input['case_number'] ?? '');
$clientName = trim($input['client_name'] ?? '');

if (!$clientName) errorResponse('Client name is required');

// Auto-assign to first active attorney
$assignedTo = !empty($input['assigned_to']) ? (int)$input['assigned_to'] : null;
if (!$assignedTo) {
    $attorney = dbFetchOne("SELECT id FROM users WHERE role = 'attorney' AND is_active = 1 ORDER BY id LIMIT 1");
    $assignedTo = $attorney ? (int)$attorney['id'] : null;
}

$id = dbInsert('demand_requests', [
    'requested_by' => $userId,
    'assigned_to'  => $assignedTo,
    'case_number'  => $caseNumber,
    'client_name'  => $clientName,
    'case_type'    => $input['case_type'] ?? 'Auto',
    'note'         => trim($input['note'] ?? ''),
    'status'       => 'pending',
]);

if ($assignedTo) {
    dbInsert('notifications', [
        'user_id' => $assignedTo,
        'type'    => 'demand_request',
        'message' => "New demand request for {$clientName}",
        'is_read' => 0,
    ]);
}

logActivity($userId, 'demand_request_created', 'demand_requests', $id);

successResponse(['id' => $id], 'Demand request created');
