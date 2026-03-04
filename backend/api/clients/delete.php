<?php
/**
 * DELETE /api/clients/{id}
 * Delete a client. Prevents deletion if linked to cases.
 */
$userId = requireAuth();

$id = (int)$_GET['id'];

$client = dbFetchOne("SELECT id, name FROM clients WHERE id = ?", [$id]);
if (!$client) errorResponse('Client not found', 404);

// Prevent deletion if client is linked to cases
$caseCount = dbCount('cases', 'client_id = ?', [$id]);
if ($caseCount > 0) {
    errorResponse("Cannot delete client: linked to {$caseCount} case(s)");
}

dbDelete('clients', 'id = ?', [$id]);

logActivity($userId, 'client_deleted', 'clients', $id, ['name' => $client['name']]);

successResponse(null, 'Client deleted successfully');
