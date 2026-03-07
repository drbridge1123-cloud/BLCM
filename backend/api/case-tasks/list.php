<?php
// GET /api/case-tasks?case_id=X - List all tasks for a case
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$caseId = (int)($_GET['case_id'] ?? 0);
if (!$caseId) {
    errorResponse('case_id is required');
}

$tasks = dbFetchAll(
    "SELECT ct.*, COALESCE(u.display_name, u.full_name) AS assigned_name
     FROM case_tasks ct
     LEFT JOIN users u ON u.id = ct.assigned_to
     WHERE ct.case_id = ?
     ORDER BY ct.phase_order, ct.stage_order, ct.sort_order",
    [$caseId]
);

// Build hierarchical structure: phases → stages → tasks
$phases = [];
foreach ($tasks as $t) {
    $phaseKey = $t['phase'];
    $stageKey = $t['stage'];

    if (!isset($phases[$phaseKey])) {
        $phases[$phaseKey] = [
            'phase' => $phaseKey,
            'phase_order' => (int)$t['phase_order'],
            'stages' => [],
        ];
    }
    if (!isset($phases[$phaseKey]['stages'][$stageKey])) {
        $phases[$phaseKey]['stages'][$stageKey] = [
            'stage' => $stageKey,
            'stage_order' => (int)$t['stage_order'],
            'tasks' => [],
        ];
    }

    $phases[$phaseKey]['stages'][$stageKey]['tasks'][] = [
        'id' => (int)$t['id'],
        'parent_task_id' => $t['parent_task_id'] ? (int)$t['parent_task_id'] : null,
        'title' => $t['title'],
        'task_number' => $t['task_number'],
        'status' => $t['status'],
        'priority' => $t['priority'],
        'assigned_to' => $t['assigned_to'] ? (int)$t['assigned_to'] : null,
        'assigned_name' => $t['assigned_name'],
        'is_conditional' => (bool)$t['is_conditional'],
        'condition_value' => $t['condition_value'],
        'condition_answer' => $t['condition_answer'],
        'has_subtasks' => (bool)$t['has_subtasks'],
        'due_date' => $t['due_date'],
        'start_date' => $t['start_date'],
        'end_date' => $t['end_date'],
        'notes' => $t['notes'],
        'completed_at' => $t['completed_at'],
    ];
}

// Re-index stages to arrays
foreach ($phases as &$p) {
    $stageArr = array_values($p['stages']);
    usort($stageArr, fn($a, $b) => $a['stage_order'] - $b['stage_order']);
    $p['stages'] = $stageArr;
}
unset($p);

$phaseArr = array_values($phases);
usort($phaseArr, fn($a, $b) => $a['phase_order'] - $b['phase_order']);

jsonResponse(['success' => true, 'phases' => $phaseArr]);
