<?php
/**
 * DELETE /api/adjusters/{id}
 * Delete an adjuster
 */
requireAdmin();

$id = (int)$_GET['id'];

$adjuster = dbFetchOne("SELECT id, first_name, last_name FROM adjusters WHERE id = ?", [$id]);
if (!$adjuster) errorResponse('Adjuster not found', 404);

dbDelete('adjusters', 'id = ?', [$id]);

logActivity($_SESSION['user_id'], 'delete', 'adjuster', $id, [
    'name' => $adjuster['first_name'] . ' ' . $adjuster['last_name']
]);

successResponse(null, 'Adjuster deleted successfully');
