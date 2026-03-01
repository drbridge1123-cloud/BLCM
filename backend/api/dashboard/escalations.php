<?php
/**
 * GET /api/dashboard/escalations
 * Returns escalated items (past deadline, grouped by tier)
 */
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$user = getCurrentUser();

// Generate escalation notifications for today
generateEscalationNotifications();

$items = getEscalatedItems($user['role'], $userId);

successResponse($items);
