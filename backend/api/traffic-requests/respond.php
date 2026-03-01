<?php
/**
 * PUT /api/traffic-requests/{id}
 * Accept or deny a traffic request (attorney)
 */
$userId = requireAuth();
requirePermission('traffic');
$input = getInput();

$reqId = (int)($_GET['id'] ?? 0);
if (!$reqId) errorResponse('Request ID required');

$req = dbFetchOne("SELECT * FROM traffic_requests WHERE id = ?", [$reqId]);
if (!$req) errorResponse('Request not found', 404);

$action = $input['action'] ?? '';
if (!in_array($action, ['accept', 'deny'])) {
    errorResponse('Action must be accept or deny');
}

if ($req['status'] !== 'pending') {
    errorResponse('Request already ' . $req['status']);
}

if ($action === 'accept') {
    // Create traffic case from request
    $caseId = dbInsert('traffic_cases', [
        'user_id'              => $userId,
        'client_name'          => $req['client_name'],
        'client_phone'         => $req['client_phone'] ?? '',
        'client_email'         => $req['client_email'] ?? '',
        'court'                => $req['court'] ?? '',
        'court_date'           => $req['court_date'],
        'charge'               => $req['charge'] ?? '',
        'case_number'          => $req['case_number'] ?? '',
        'note'                 => $req['note'] ?? '',
        'citation_issued_date' => $req['citation_issued_date'],
        'referral_source'      => $req['referral_source'] ?? '',
        'disposition'          => 'pending',
        'commission'           => 0,
        'status'               => 'active',
        'paid'                 => 0,
        'request_id'           => $reqId,
        'requested_by'         => (int)$req['requested_by'],
    ]);

    dbUpdate('traffic_requests', [
        'status'       => 'accepted',
        'responded_at' => date('Y-m-d H:i:s'),
    ], 'id = ?', [$reqId]);

    // Notify requester
    dbInsert('notifications', [
        'user_id'  => (int)$req['requested_by'],
        'type'     => 'traffic_request_accepted',
        'message'  => "Your traffic request for {$req['client_name']} was accepted",
        'is_read'  => 0,
    ]);

    logActivity($userId, 'traffic_request_accepted', 'traffic_requests', $reqId);

    successResponse(['case_id' => $caseId], 'Request accepted, case created');

} else {
    // Deny
    $denyReason = trim($input['deny_reason'] ?? '');
    if (!$denyReason) errorResponse('Deny reason is required');

    dbUpdate('traffic_requests', [
        'status'       => 'denied',
        'deny_reason'  => $denyReason,
        'responded_at' => date('Y-m-d H:i:s'),
    ], 'id = ?', [$reqId]);

    // Notify requester
    dbInsert('notifications', [
        'user_id'  => (int)$req['requested_by'],
        'type'     => 'traffic_request_denied',
        'message'  => "Your traffic request for {$req['client_name']} was denied: {$denyReason}",
        'is_read'  => 0,
    ]);

    logActivity($userId, 'traffic_request_denied', 'traffic_requests', $reqId);

    successResponse(null, 'Request denied');
}
