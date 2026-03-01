<?php
/**
 * GET /api/templates
 * List all letter templates. Optional: template_type, is_active filter.
 */
$userId = requireAuth();

$where  = '1=1';
$params = [];

// Optional: filter by template_type
if (!empty($_GET['template_type'])) {
    $validTypes = ['medical_records','health_ledger','bulk_request','custom','balance_verification'];
    if (validateEnum($_GET['template_type'], $validTypes)) {
        $where .= ' AND lt.template_type = ?';
        $params[] = $_GET['template_type'];
    }
}

// Optional: filter by is_active
if (isset($_GET['is_active']) && $_GET['is_active'] !== '') {
    $where .= ' AND lt.is_active = ?';
    $params[] = (int)$_GET['is_active'];
}

$rows = dbFetchAll("
    SELECT lt.*,
           u.full_name AS created_by_name
    FROM letter_templates lt
    LEFT JOIN users u ON u.id = lt.created_by
    WHERE {$where}
    ORDER BY lt.template_type, lt.name
", $params);

successResponse($rows);
