<?php
/**
 * PUT /api/users/{id}/reset-password
 */
requireAdmin();
$id = (int)$_GET['id'];
$input = getInput();

$errors = validateRequired($input, ['password']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$user = dbFetchOne("SELECT id FROM users WHERE id = ?", [$id]);
if (!$user) errorResponse('User not found', 404);

dbUpdate('users', [
    'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT)
], 'id = ?', [$id]);

logActivity($_SESSION['user_id'], 'reset_password', 'user', $id);

successResponse(null, 'Password reset successfully');
