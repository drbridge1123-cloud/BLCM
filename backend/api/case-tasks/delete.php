<?php
// DELETE /api/case-tasks/{id} - Delete a task (and its subtasks via CASCADE)
if ($method !== 'DELETE') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$taskId = (int)($_GET['id'] ?? 0);
if (!$taskId) {
    errorResponse('Task ID is required');
}

$task = dbFetchOne("SELECT id FROM case_tasks WHERE id = ?", [$taskId]);
if (!$task) {
    errorResponse('Task not found', 404);
}

dbQuery("DELETE FROM case_tasks WHERE id = ?", [$taskId]);

jsonResponse(['success' => true]);
