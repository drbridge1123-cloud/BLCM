<?php
/**
 * GET /api/adjusters
 * List all adjusters with optional filters. Includes company name via JOIN.
 */
requireAuth();

$insuranceCompanyId = $_GET['insurance_company_id'] ?? null;
$adjusterType = $_GET['adjuster_type'] ?? null;
$isActive = $_GET['is_active'] ?? null;
$search = $_GET['search'] ?? null;
$sortBy = $_GET['sort_by'] ?? 'last_name';
$sortDir = strtoupper($_GET['sort_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

$where = '1=1';
$params = [];

if ($insuranceCompanyId) {
    $where .= ' AND a.insurance_company_id = ?';
    $params[] = (int) $insuranceCompanyId;
}

if ($adjusterType) {
    $validTypes = ['pip', 'um', 'uim', '3rd_party', 'liability', 'pd', 'bi'];
    if (!validateEnum($adjusterType, $validTypes))
        errorResponse('Invalid adjuster type');
    $where .= ' AND a.adjuster_type = ?';
    $params[] = $adjusterType;
}

if ($isActive !== null && $isActive !== '') {
    $where .= ' AND a.is_active = ?';
    $params[] = (int) $isActive;
}

if ($search) {
    $where .= ' AND (a.first_name LIKE ? OR a.last_name LIKE ? OR a.email LIKE ? OR a.phone LIKE ? OR ic.name LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$allowedSorts = ['last_name', 'first_name', 'adjuster_type', 'is_active', 'created_at', 'company_name'];
if (!in_array($sortBy, $allowedSorts))
    $sortBy = 'last_name';
$orderCol = $sortBy === 'company_name' ? 'ic.name' : "a.{$sortBy}";

$adjusters = dbFetchAll(
    "SELECT a.id, a.insurance_company_id, a.first_name, a.last_name, a.title,
            a.adjuster_type, a.phone, a.fax, a.email, a.notes,
            a.is_active, a.created_at, a.updated_at,
            ic.name AS company_name,
            (SELECT COUNT(*) FROM cases c WHERE c.adjuster_3rd_id = a.id OR c.adjuster_um_id = a.id) AS case_count
     FROM adjusters a
     LEFT JOIN insurance_companies ic ON ic.id = a.insurance_company_id
     WHERE {$where}
     ORDER BY {$orderCol} {$sortDir}",
    $params
);

foreach ($adjusters as &$a) {
    $a['is_active'] = (int) $a['is_active'];
    $a['case_count'] = (int) $a['case_count'];
}
unset($a);

successResponse($adjusters);
