<?php
/**
 * GET /api/attorney-cases/{id}
 * Get a single attorney case with full details
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$id = (int)$_GET['id'];

$case = dbFetchOne(
    "SELECT ac.*,
            u.full_name AS attorney_name,
            oa.full_name AS top_offer_assignee_name,
            cb.full_name AS created_by_name,
            DATEDIFF(ac.demand_deadline, CURDATE()) AS deadline_days_remaining
     FROM attorney_cases ac
     LEFT JOIN users u ON ac.attorney_user_id = u.id
     LEFT JOIN users oa ON ac.top_offer_assignee_id = oa.id
     LEFT JOIN users cb ON ac.created_by = cb.id
     WHERE ac.id = ? AND ac.deleted_at IS NULL",
    [$id]
);

if (!$case) errorResponse('Attorney case not found', 404);

// Non-admin/manager can only view own cases
$user = getCurrentUser();
if (!in_array($user['role'], ['admin', 'manager']) && (int)$case['attorney_user_id'] !== $userId) {
    errorResponse('Access denied', 403);
}

$case['deadline_days_remaining'] = $case['deadline_days_remaining'] !== null
    ? (int)$case['deadline_days_remaining'] : null;

successResponse($case);
