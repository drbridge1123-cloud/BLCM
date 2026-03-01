<?php
/**
 * POST /api/goals
 * Create or update employee goals (admin/manager)
 */
$userId = requireAuth();
requirePermission('goals');
$user = getCurrentUser();
$input = getInput();

$targetUserId = (int)($input['user_id'] ?? 0);
$year = (int)($input['year'] ?? date('Y'));

if (!$targetUserId) errorResponse('user_id is required');

// Only admin/manager can set others' goals
if (!in_array($user['role'], ['admin', 'manager']) && $targetUserId !== $userId) {
    errorResponse('Not authorized', 403);
}

// Upsert — insert or update if exists
$existing = dbFetchOne(
    "SELECT id FROM employee_goals WHERE user_id = ? AND year = ?",
    [$targetUserId, $year]
);

$data = [
    'user_id'          => $targetUserId,
    'year'             => $year,
    'target_cases'     => (int)($input['target_cases'] ?? 50),
    'target_legal_fee' => (float)($input['target_legal_fee'] ?? 500000),
    'notes'            => trim($input['notes'] ?? ''),
    'created_by'       => $userId,
];

if ($existing) {
    dbUpdate('employee_goals', $data, 'id = ?', [(int)$existing['id']]);
    $id = (int)$existing['id'];
} else {
    $id = dbInsert('employee_goals', $data);
}

logActivity($userId, 'goal_saved', 'employee_goals', $id);

successResponse(['id' => $id], 'Goal saved');
