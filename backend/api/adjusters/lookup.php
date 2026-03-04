<?php
/**
 * GET /api/adjusters/lookup?first_name=X&last_name=Y&adjuster_type=Z
 * Auto-match adjuster by name and optional type
 */
$userId = requireAuth();

$firstName = trim($_GET['first_name'] ?? '');
$lastName = trim($_GET['last_name'] ?? '');
$adjusterType = trim($_GET['adjuster_type'] ?? '');

if (!$firstName && !$lastName) {
    errorResponse('At least one name field is required');
}

$where = '1=1';
$params = [];

if ($firstName) {
    $where .= ' AND LOWER(a.first_name) = LOWER(?)';
    $params[] = $firstName;
}
if ($lastName) {
    $where .= ' AND LOWER(a.last_name) = LOWER(?)';
    $params[] = $lastName;
}
if ($adjusterType) {
    $where .= ' AND a.adjuster_type = ?';
    $params[] = $adjusterType;
}

$adjuster = dbFetchOne(
    "SELECT a.*, ic.name AS company_name
     FROM adjusters a
     LEFT JOIN insurance_companies ic ON ic.id = a.insurance_company_id
     WHERE {$where} LIMIT 1",
    $params
);

successResponse($adjuster);
