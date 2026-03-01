<?php
/**
 * DELETE /api/negotiations/{id}
 * Delete a case negotiation
 */
$userId = requireAuth();
requirePermission('cases');

$id = (int)$_GET['id'];

$row = dbFetchOne(
    "SELECT id, case_id, coverage_type, round_number FROM case_negotiations WHERE id = ?",
    [$id]
);
if (!$row) errorResponse('Negotiation not found', 404);

dbDelete('case_negotiations', 'id = ?', [$id]);

logActivity($userId, 'delete', 'case_negotiation', $id, [
    'case_id'       => $row['case_id'],
    'coverage_type' => $row['coverage_type'],
    'round_number'  => $row['round_number'],
]);

successResponse(null, 'Negotiation deleted');
