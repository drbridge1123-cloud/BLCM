<?php
/**
 * GET /api/insurance-companies/search?q=...&type=...
 * Lightweight search for autocomplete
 */
requireAuth();

$q = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? null;

if (strlen($q) < 1) {
    successResponse([]);
}

$where = 'name LIKE ?';
$params = ["%{$q}%"];

if ($type) {
    $validTypes = ['auto','health','workers_comp','liability','um_uim','other'];
    if (validateEnum($type, $validTypes)) {
        $where .= ' AND type = ?';
        $params[] = $type;
    }
}

$results = dbFetchAll(
    "SELECT id, name, type FROM insurance_companies WHERE {$where} ORDER BY name ASC LIMIT 20",
    $params
);

successResponse($results);
