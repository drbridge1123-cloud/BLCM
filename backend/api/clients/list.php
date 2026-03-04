<?php
/**
 * GET /api/clients
 * List all clients with optional search, sorting, and linked case count
 */
requireAuth();

$search = $_GET['search'] ?? null;
$sortBy = $_GET['sort_by'] ?? 'name';
$sortDir = strtoupper($_GET['sort_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

$where = '1=1';
$params = [];

if ($search) {
    $where .= ' AND (cl.name LIKE ? OR cl.phone LIKE ? OR cl.email LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$allowedSorts = ['name', 'dob', 'phone', 'email', 'created_at'];
if (!in_array($sortBy, $allowedSorts)) $sortBy = 'name';

$clients = dbFetchAll(
    "SELECT cl.*,
            (SELECT COUNT(*) FROM cases c WHERE c.client_id = cl.id) AS case_count
     FROM clients cl
     WHERE {$where}
     ORDER BY cl.{$sortBy} {$sortDir}",
    $params
);

foreach ($clients as &$row) {
    $row['case_count'] = (int)$row['case_count'];
}
unset($row);

successResponse($clients);
