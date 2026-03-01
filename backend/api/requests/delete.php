<?php
/**
 * DELETE /api/requests/{id}
 * Delete a record request (only if still in draft)
 */
$userId = requireAuth();
$id     = (int)$_GET['id'];

$request = dbFetchOne("SELECT * FROM record_requests WHERE id = ?", [$id]);
if (!$request) errorResponse('Request not found', 404);

// Delete any attachments first
dbDelete('request_attachments', 'record_request_id = ?', [$id]);

dbDelete('record_requests', 'id = ?', [$id]);

logActivity($userId, 'delete', 'record_request', $id, [
    'case_provider_id' => $request['case_provider_id'],
]);

successResponse(null, 'Request deleted');
