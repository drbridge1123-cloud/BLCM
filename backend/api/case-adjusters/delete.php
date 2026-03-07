<?php
/**
 * DELETE /api/case-adjusters/{id}
 * Remove adjuster link from case (does not delete the adjuster itself)
 */
$userId = requireAuth();

$id = (int)($_GET['id'] ?? 0);
if (!$id) errorResponse('id is required');

$link = dbFetchOne("SELECT * FROM case_adjusters WHERE id = ?", [$id]);
if (!$link) errorResponse('Link not found', 404);

dbDelete('case_adjusters', 'id = ?', [$id]);

logActivity($userId, 'case_adjuster_removed', 'case_adjusters', $link['case_id'], [
    'coverage_type' => $link['coverage_type'],
]);

successResponse(null, 'Adjuster removed from case');
