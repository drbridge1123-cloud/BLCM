<?php
/**
 * POST /api/users
 * Create a new user (admin only)
 */
requireAdmin();
$input = getInput();
$errors = validateRequired($input, ['username', 'full_name', 'password', 'role']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$validRoles = ['admin', 'manager', 'accounting', 'staff', 'attorney'];
if (!validateEnum($input['role'], $validRoles)) {
    errorResponse('Invalid role');
}

$existing = dbFetchOne("SELECT id FROM users WHERE username = ?", [$input['username']]);
if ($existing) errorResponse('Username already exists');

$permissions = isset($input['permissions'])
    ? $input['permissions']
    : getDefaultPermissions($input['role']);

$id = dbInsert('users', [
    'username' => sanitizeString($input['username']),
    'full_name' => sanitizeString($input['full_name']),
    'display_name' => sanitizeString($input['display_name'] ?? $input['full_name']),
    'email' => sanitizeString($input['email'] ?? ''),
    'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT),
    'job_title' => sanitizeString($input['job_title'] ?? ''),
    'card_last4' => sanitizeString($input['card_last4'] ?? ''),
    'team' => sanitizeString($input['team'] ?? ''),
    'role' => $input['role'],
    'commission_rate' => (float)($input['commission_rate'] ?? COMMISSION_RATE_DEFAULT),
    'uses_presuit_offer' => (int)($input['uses_presuit_offer'] ?? 1),
    'permissions' => json_encode($permissions),
]);

logActivity($_SESSION['user_id'], 'create', 'user', $id, ['username' => $input['username']]);

successResponse(['id' => $id], 'User created successfully');
