<?php
/**
 * POST /api/deadline-requests
 * Create a deadline extension request
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$input = getInput();

$caseId = (int)($input['case_id'] ?? 0);
if (!$caseId) errorResponse('case_id is required');

$ac = dbFetchOne("SELECT * FROM attorney_cases WHERE id = ? AND deleted_at IS NULL", [$caseId]);
if (!$ac) errorResponse('Attorney case not found', 404);

$requestedDeadline = $input['requested_deadline'] ?? '';
if (!$requestedDeadline) errorResponse('requested_deadline is required');

$reason = trim($input['reason'] ?? '');
if (!$reason) errorResponse('reason is required');

$id = dbInsert('deadline_extension_requests', [
    'case_id'            => $caseId,
    'user_id'            => $userId,
    'current_deadline'   => $ac['demand_deadline'],
    'requested_deadline' => $requestedDeadline,
    'reason'             => $reason,
    'status'             => 'pending',
]);

// Notify admins
$admins = dbFetchAll("SELECT id FROM users WHERE role IN ('admin', 'manager') AND is_active = 1");
foreach ($admins as $admin) {
    dbInsert('notifications', [
        'user_id' => (int)$admin['id'],
        'type'    => 'deadline_extension_request',
        'message' => "Deadline extension request for {$ac['case_number']}",
        'is_read' => 0,
    ]);
}

logActivity($userId, 'deadline_request_created', 'deadline_extension_requests', $id);

successResponse(['id' => $id], 'Deadline extension request submitted');
