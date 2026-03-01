<?php
/**
 * PUT /api/users/{id}/toggle-active
 */
requireAdmin();
$id = (int)$_GET['id'];

$user = dbFetchOne("SELECT id, is_active FROM users WHERE id = ?", [$id]);
if (!$user) errorResponse('User not found', 404);

$newStatus = $user['is_active'] ? 0 : 1;
dbUpdate('users', ['is_active' => $newStatus], 'id = ?', [$id]);

logActivity($_SESSION['user_id'], $newStatus ? 'activate' : 'deactivate', 'user', $id);

successResponse(['is_active' => $newStatus], $newStatus ? 'User activated' : 'User deactivated');
