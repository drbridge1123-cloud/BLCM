<?php
/**
 * POST /api/mbds/{id}/activate-providers
 * Auto-populate provider lines from case_providers that don't already have a line
 */
$userId = requireAuth();
requirePermission('mbds');

$reportId = (int)$_GET['id'];

$report = dbFetchOne("SELECT id, case_id FROM mbds_reports WHERE id = ?", [$reportId]);
if (!$report) errorResponse('MBDS report not found', 404);

$caseId = (int)$report['case_id'];

// Get existing case_provider_ids already linked in this report
$existingIds = dbFetchAll(
    "SELECT case_provider_id FROM mbds_lines WHERE report_id = ? AND case_provider_id IS NOT NULL",
    [$reportId]
);
$existingCpIds = array_column($existingIds, 'case_provider_id');

// Fetch case_providers not yet added
$where  = "cp.case_id = ?";
$params = [$caseId];

if (!empty($existingCpIds)) {
    $placeholders = implode(',', array_fill(0, count($existingCpIds), '?'));
    $where .= " AND cp.id NOT IN ({$placeholders})";
    $params = array_merge($params, $existingCpIds);
}

$newProviders = dbFetchAll(
    "SELECT cp.id AS cp_id, p.name AS provider_name
     FROM case_providers cp
     JOIN providers p ON cp.provider_id = p.id
     WHERE {$where}
     ORDER BY cp.id ASC",
    $params
);

if (empty($newProviders)) {
    successResponse(['created' => 0], 'No new providers to add');
}

// Get current max sort_order
$maxOrder = dbFetchOne(
    "SELECT COALESCE(MAX(sort_order), 0) AS max_order FROM mbds_lines WHERE report_id = ?",
    [$reportId]
);
$sortOrder = (int)$maxOrder['max_order'];

foreach ($newProviders as $prov) {
    $sortOrder++;
    dbInsert('mbds_lines', [
        'report_id'        => $reportId,
        'line_type'        => 'provider',
        'provider_name'    => $prov['provider_name'],
        'case_provider_id' => $prov['cp_id'],
        'sort_order'       => $sortOrder,
    ]);
}

logActivity($userId, 'activate_providers', 'mbds_report', $reportId, [
    'added_count' => count($newProviders),
]);

successResponse(['created' => count($newProviders)], count($newProviders) . ' provider line(s) added');
