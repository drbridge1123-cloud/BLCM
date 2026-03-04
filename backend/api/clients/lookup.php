<?php
/**
 * GET /api/clients/lookup?name=X&dob=Y
 * Auto-match client by name + dob
 */
$userId = requireAuth();

$name = trim($_GET['name'] ?? '');
$dob = trim($_GET['dob'] ?? '');

if (!$name) {
    errorResponse('Name is required');
}

$where = "LOWER(name) = LOWER(?)";
$params = [$name];

if ($dob) {
    $where .= " AND dob = ?";
    $params[] = $dob;
}

$client = dbFetchOne("SELECT * FROM clients WHERE {$where} LIMIT 1", $params);

successResponse($client);
