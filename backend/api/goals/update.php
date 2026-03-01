<?php
/**
 * PUT /api/goals/{id}
 * Update an employee goal
 */
$userId = requireAuth();
requirePermission('goals');
$user = getCurrentUser();
$input = getInput();

$goalId = (int)($_GET['id'] ?? 0);
if (!$goalId) errorResponse('Goal ID required');

$row = dbFetchOne("SELECT * FROM employee_goals WHERE id = ?", [$goalId]);
if (!$row) errorResponse('Goal not found', 404);

if (!in_array($user['role'], ['admin', 'manager']) && (int)$row['user_id'] !== $userId) {
    errorResponse('Not authorized', 403);
}

dbUpdate('employee_goals', [
    'target_cases'     => (int)($input['target_cases'] ?? $row['target_cases']),
    'target_legal_fee' => (float)($input['target_legal_fee'] ?? $row['target_legal_fee']),
    'notes'            => trim($input['notes'] ?? $row['notes'] ?? ''),
], 'id = ?', [$goalId]);

logActivity($userId, 'goal_updated', 'employee_goals', $goalId);

successResponse(null, 'Goal updated');
