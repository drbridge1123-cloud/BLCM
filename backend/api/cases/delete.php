<?php
/**
 * DELETE /api/cases/{id}
 * Delete a case (admin only). Cascade handled by FK constraints.
 */
requireAdmin();
$id = (int)$_GET['id'];

$case = dbFetchOne("SELECT id, case_number, client_name FROM cases WHERE id = ?", [$id]);
if (!$case) errorResponse('Case not found', 404);

dbDelete('cases', 'id = ?', [$id]);

logActivity($_SESSION['user_id'], 'delete', 'case', $id, [
    'case_number' => $case['case_number'],
    'client_name' => $case['client_name'],
]);

successResponse(null, 'Case deleted successfully');
