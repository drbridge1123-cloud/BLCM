<?php
// PUT /api/case-tasks/{id} - Update a task
if ($method !== 'PUT') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$taskId = (int)($_GET['id'] ?? 0);
if (!$taskId) {
    errorResponse('Task ID is required');
}

$input = json_decode(file_get_contents('php://input'), true);
$task = dbFetchOne("SELECT * FROM case_tasks WHERE id = ?", [$taskId]);
if (!$task) {
    errorResponse('Task not found', 404);
}

$sets = [];
$params = [];

// Status update
if (isset($input['status'])) {
    $valid = ['not_started', 'in_progress', 'done', 'na'];
    if (!in_array($input['status'], $valid)) {
        errorResponse('Invalid status');
    }
    $sets[] = "status = ?";
    $params[] = $input['status'];

    if ($input['status'] === 'done') {
        $sets[] = "completed_at = NOW()";
        $sets[] = "completed_by = ?";
        $params[] = $userId;
        // Auto-set end_date if not already set
        if (!$task['end_date']) {
            $sets[] = "end_date = CURDATE()";
        }
    } else {
        $sets[] = "completed_at = NULL";
        $sets[] = "completed_by = NULL";
    }
    // Auto-set start_date when moving to in_progress
    if ($input['status'] === 'in_progress' && !$task['start_date']) {
        $sets[] = "start_date = CURDATE()";
    }
}

// Conditional answer (yes/no)
if (isset($input['condition_answer'])) {
    $sets[] = "condition_answer = ?";
    $params[] = $input['condition_answer'] ?: null;
}

// Assigned to
if (array_key_exists('assigned_to', $input)) {
    $sets[] = "assigned_to = ?";
    $params[] = $input['assigned_to'] ? (int)$input['assigned_to'] : null;
}

// Priority
if (isset($input['priority'])) {
    $valid = ['low', 'normal', 'high', 'urgent'];
    if (!in_array($input['priority'], $valid)) {
        errorResponse('Invalid priority');
    }
    $sets[] = "priority = ?";
    $params[] = $input['priority'];
}

// Due date
if (array_key_exists('due_date', $input)) {
    $sets[] = "due_date = ?";
    $params[] = $input['due_date'] ?: null;
}

// Start date
if (array_key_exists('start_date', $input)) {
    $sets[] = "start_date = ?";
    $params[] = $input['start_date'] ?: null;
}

// End date
if (array_key_exists('end_date', $input)) {
    $sets[] = "end_date = ?";
    $params[] = $input['end_date'] ?: null;
}

// Notes
if (array_key_exists('notes', $input)) {
    $sets[] = "notes = ?";
    $params[] = $input['notes'] ?: null;
}

if (empty($sets)) {
    errorResponse('No fields to update');
}

$params[] = $taskId;
dbQuery("UPDATE case_tasks SET " . implode(', ', $sets) . " WHERE id = ?", $params);

jsonResponse(['success' => true]);
