<?php
/**
 * DELETE /api/templates/{id}
 * Delete a letter template (admin only)
 */
requireAdmin();
$userId = $_SESSION['user_id'];
$id     = (int)$_GET['id'];

$template = dbFetchOne("SELECT * FROM letter_templates WHERE id = ?", [$id]);
if (!$template) errorResponse('Template not found', 404);

dbDelete('letter_templates', 'id = ?', [$id]);

logActivity($userId, 'delete', 'letter_template', $id, [
    'name' => $template['name'],
]);

successResponse(null, 'Template deleted');
