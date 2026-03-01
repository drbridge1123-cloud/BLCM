<?php
/**
 * PUT /api/deadline-requests/{id}
 * Approve or reject a deadline extension request (admin/manager)
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$user = getCurrentUser();
$input = getInput();

if (!in_array($user['role'], ['admin', 'manager'])) {
    errorResponse('Not authorized', 403);
}

$reqId = (int)($_GET['id'] ?? 0);
if (!$reqId) errorResponse('Request ID required');

$req = dbFetchOne("SELECT * FROM deadline_extension_requests WHERE id = ?", [$reqId]);
if (!$req) errorResponse('Request not found', 404);

if ($req['status'] !== 'pending') {
    errorResponse('Request already ' . $req['status']);
}

$action = $input['action'] ?? '';
if (!in_array($action, ['approve', 'reject'])) {
    errorResponse('Action must be approve or reject');
}

$newStatus = $action === 'approve' ? 'approved' : 'rejected';

dbUpdate('deadline_extension_requests', [
    'status'      => $newStatus,
    'admin_note'  => trim($input['admin_note'] ?? ''),
    'reviewed_by' => $userId,
    'reviewed_at' => date('Y-m-d H:i:s'),
], 'id = ?', [$reqId]);

// If approved, update the case deadline
if ($action === 'approve' && $req['case_id']) {
    dbUpdate('attorney_cases', [
        'demand_deadline' => $req['requested_deadline'],
    ], 'id = ?', [(int)$req['case_id']]);
}

// Notify requester
dbInsert('notifications', [
    'user_id' => (int)$req['user_id'],
    'type'    => 'deadline_request_' . $newStatus,
    'message' => "Your deadline extension request was {$newStatus}",
    'is_read' => 0,
]);

logActivity($userId, 'deadline_request_' . $action . 'd', 'deadline_extension_requests', $reqId);

successResponse(['status' => $newStatus], "Request {$newStatus}");
