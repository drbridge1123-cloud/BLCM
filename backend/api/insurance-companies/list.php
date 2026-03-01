<?php
/**
 * GET /api/insurance-companies
 * List all insurance companies with optional filters, adjuster count, and sorting
 */
requireAuth();

$type = $_GET['type'] ?? null;
$search = $_GET['search'] ?? null;
$sortBy = $_GET['sort_by'] ?? 'name';
$sortDir = strtoupper($_GET['sort_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

$where = '1=1';
$params = [];

if ($type) {
    $validTypes = ['auto','health','workers_comp','liability','um_uim','other'];
    if (!validateEnum($type, $validTypes)) errorResponse('Invalid insurance company type');
    $where .= ' AND ic.type = ?';
    $params[] = $type;
}

if ($search) {
    $where .= ' AND (ic.name LIKE ? OR ic.phone LIKE ? OR ic.email LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$allowedSorts = ['name','type','city','state','created_at'];
if (!in_array($sortBy, $allowedSorts)) $sortBy = 'name';

$companies = dbFetchAll(
    "SELECT ic.id, ic.name, ic.type, ic.phone, ic.fax, ic.email,
            ic.address, ic.city, ic.state, ic.zip, ic.website,
            ic.notes, ic.created_at, ic.updated_at,
            (SELECT COUNT(*) FROM adjusters a WHERE a.insurance_company_id = ic.id) AS adjuster_count
     FROM insurance_companies ic
     WHERE {$where}
     ORDER BY ic.{$sortBy} {$sortDir}",
    $params
);

foreach ($companies as &$c) {
    $c['adjuster_count'] = (int)$c['adjuster_count'];
}
unset($c);

successResponse($companies);
