<?php
/**
 * DELETE /api/provider-negotiations/{id}
 * Delete a provider negotiation
 */
$userId = requireAuth();
requirePermission('cases');

$id = (int)$_GET['id'];

$row = dbFetchOne(
    "SELECT id, case_id, provider_name FROM provider_negotiations WHERE id = ?",
    [$id]
);
if (!$row) errorResponse('Provider negotiation not found', 404);

dbDelete('provider_negotiations', 'id = ?', [$id]);

logActivity($userId, 'delete', 'provider_negotiation', $id, [
    'case_id'       => $row['case_id'],
    'provider_name' => $row['provider_name'],
]);

successResponse(null, 'Provider negotiation deleted');
