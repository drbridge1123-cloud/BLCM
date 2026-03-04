<?php
/**
 * GET /api/templates
 * List all letter templates. Optional: template_type, is_active filter.
 */
$userId = requireAuth();

$where  = '1=1';
$params = [];

// Optional: filter by template_type (accept both 'type' and 'template_type')
$typeParam = $_GET['type'] ?? $_GET['template_type'] ?? '';
if (!empty($typeParam)) {
    $validTypes = ['medical_records','health_ledger','bulk_request','custom','balance_verification','discount_request'];
    if (validateEnum($typeParam, $validTypes)) {
        $where .= ' AND lt.template_type = ?';
        $params[] = $typeParam;
    }
}

// Optional: filter by is_active (accept both 'active_only' and 'is_active')
$activeParam = $_GET['active_only'] ?? $_GET['is_active'] ?? '';
if ($activeParam !== '') {
    $where .= ' AND lt.is_active = ?';
    $params[] = (int)$activeParam;
}

$rows = dbFetchAll("
    SELECT lt.*,
           COALESCE(u.display_name, u.full_name) AS created_by_name
    FROM letter_templates lt
    LEFT JOIN users u ON u.id = lt.created_by
    WHERE {$where}
    ORDER BY lt.sort_order, lt.name
", $params);

successResponse($rows);
