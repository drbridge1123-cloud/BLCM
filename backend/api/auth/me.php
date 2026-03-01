<?php
/**
 * GET /api/auth/me
 * Get current authenticated user info
 */
$userId = requireAuth();

$user = dbFetchOne(
    "SELECT id, username, full_name, display_name, role, permissions,
            commission_rate, uses_presuit_offer, is_active, created_at
     FROM users WHERE id = ?",
    [$userId]
);

if (!$user) errorResponse('User not found', 404);

$unreadMessages = dbCount('messages', 'to_user_id = ? AND is_read = 0', [$userId]);
$user['unread_messages'] = $unreadMessages;
$user['permissions'] = $user['permissions']
    ? json_decode($user['permissions'], true)
    : getDefaultPermissions($user['role']);
$user['commission_rate'] = (float)$user['commission_rate'];

successResponse($user);
