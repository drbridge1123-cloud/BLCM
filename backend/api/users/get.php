<?php
/**
 * GET /api/users/{id}
 */
requireAdmin();
$id = (int)$_GET['id'];

$user = dbFetchOne(
    "SELECT id, username, full_name, display_name, role, email,
            commission_rate, uses_presuit_offer, permissions,
            is_active, created_at
     FROM users WHERE id = ?",
    [$id]
);

if (!$user) errorResponse('User not found', 404);

$user['permissions'] = $user['permissions']
    ? json_decode($user['permissions'], true)
    : getDefaultPermissions($user['role']);
$user['commission_rate'] = (float)$user['commission_rate'];

successResponse($user);
