<?php
/**
 * POST /api/auth/login
 * Authenticate user and create session
 */
if ($method !== 'POST') errorResponse('Method not allowed', 405);

$input = getInput();
$errors = validateRequired($input, ['username', 'password']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$user = dbFetchOne(
    "SELECT * FROM users WHERE username = ? AND is_active = 1",
    [$input['username']]
);

if (!$user || !password_verify($input['password'], $user['password_hash'])) {
    errorResponse('Invalid username or password', 401);
}

startSecureSession();
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_permissions'] = $user['permissions']
    ? json_decode($user['permissions'], true)
    : getDefaultPermissions($user['role']);

logActivity($user['id'], 'login', 'user', $user['id']);

successResponse([
    'id' => $user['id'],
    'username' => $user['username'],
    'full_name' => $user['full_name'],
    'role' => $user['role'],
    'permissions' => $_SESSION['user_permissions'],
    'commission_rate' => (float)$user['commission_rate'],
], 'Login successful');
