<?php
/**
 * GET /api/clients/{id}
 * Fetch a single client by ID
 */
$userId = requireAuth();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    errorResponse('Client ID is required');
}

$client = dbFetchOne("SELECT * FROM clients WHERE id = ?", [$id]);
if (!$client) {
    errorResponse('Client not found', 404);
}

successResponse($client);
