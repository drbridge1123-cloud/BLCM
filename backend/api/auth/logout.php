<?php
/**
 * POST /api/auth/logout
 */
if ($method !== 'POST') errorResponse('Method not allowed', 405);

startSecureSession();
$userId = $_SESSION['user_id'] ?? null;
if ($userId) logActivity($userId, 'logout', 'user', $userId);
session_destroy();

successResponse(null, 'Logged out successfully');
