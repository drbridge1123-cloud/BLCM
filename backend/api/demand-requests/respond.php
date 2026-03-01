<?php
/**
 * PUT /api/demand-requests/{id}
 * Accept or deny a demand request
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$input = getInput();

$reqId = (int)($_GET['id'] ?? 0);
if (!$reqId) errorResponse('Request ID required');

$req = dbFetchOne("SELECT * FROM demand_requests WHERE id = ?", [$reqId]);
if (!$req) errorResponse('Request not found', 404);

$action = $input['action'] ?? '';
if (!in_array($action, ['accept', 'deny'])) {
    errorResponse('Action must be accept or deny');
}

if ($req['status'] !== 'pending') {
    errorResponse('Request already ' . $req['status']);
}

if ($action === 'accept') {
    // Create attorney case from request
    $assignedDate = date('Y-m-d');
    $deadline = calculateDemandDeadline($assignedDate);

    $caseId = dbInsert('attorney_cases', [
        'case_number'      => $req['case_number'] ?: 'REQ-' . $reqId,
        'client_name'      => $req['client_name'],
        'case_type'        => $req['case_type'] ?? 'Auto',
        'attorney_user_id' => $userId,
        'created_by'       => (int)$req['requested_by'],
        'phase'            => 'demand',
        'status'           => 'in_progress',
        'stage'            => 'demand_review',
        'assigned_date'    => $assignedDate,
        'demand_deadline'  => $deadline,
        'note'             => $req['note'] ?? '',
    ]);

    dbUpdate('demand_requests', [
        'status'       => 'accepted',
        'responded_at' => date('Y-m-d H:i:s'),
    ], 'id = ?', [$reqId]);

    dbInsert('notifications', [
        'user_id' => (int)$req['requested_by'],
        'type'    => 'demand_request_accepted',
        'message' => "Demand request for {$req['client_name']} was accepted",
        'is_read' => 0,
    ]);

    logActivity($userId, 'demand_request_accepted', 'demand_requests', $reqId);
    successResponse(['case_id' => $caseId], 'Request accepted, case created');

} else {
    $denyReason = trim($input['deny_reason'] ?? '');
    if (!$denyReason) errorResponse('Deny reason is required');

    dbUpdate('demand_requests', [
        'status'       => 'denied',
        'deny_reason'  => $denyReason,
        'responded_at' => date('Y-m-d H:i:s'),
    ], 'id = ?', [$reqId]);

    dbInsert('notifications', [
        'user_id' => (int)$req['requested_by'],
        'type'    => 'demand_request_denied',
        'message' => "Demand request for {$req['client_name']} denied: {$denyReason}",
        'is_read' => 0,
    ]);

    logActivity($userId, 'demand_request_denied', 'demand_requests', $reqId);
    successResponse(null, 'Request denied');
}
