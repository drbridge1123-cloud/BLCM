<?php
/**
 * DELETE /api/negotiations/group/{case_id}/{coverage_type}/{coverage_index}
 * Delete all rounds in a coverage group (removes a tab)
 */
$userId = requireAuth();
requirePermission('cases');

$caseId = (int)($_GET['case_id'] ?? 0);
$coverageType = $_GET['coverage_type'] ?? '';
$coverageIndex = (int)($_GET['coverage_index'] ?? 0);

if (!$caseId || !$coverageType || !$coverageIndex) {
    errorResponse('case_id, coverage_type, and coverage_index are required');
}

$allowedTypes = ['3rd_party', 'um', 'uim', 'pip', 'pd', 'dv', 'bi'];
if (!in_array($coverageType, $allowedTypes)) {
    errorResponse('Invalid coverage type');
}

$count = dbFetchOne(
    "SELECT COUNT(*) AS cnt FROM case_negotiations WHERE case_id = ? AND coverage_type = ? AND coverage_index = ?",
    [$caseId, $coverageType, $coverageIndex]
);

if ((int)$count['cnt'] === 0) {
    errorResponse('No rounds found for this group', 404);
}

dbDelete('case_negotiations', 'case_id = ? AND coverage_type = ? AND coverage_index = ?', [$caseId, $coverageType, $coverageIndex]);

logActivity($userId, 'delete_group', 'case_negotiation', $caseId, [
    'coverage_type' => $coverageType,
    'coverage_index' => $coverageIndex,
    'rounds_deleted' => (int)$count['cnt'],
]);

successResponse(null, 'Coverage group deleted');
