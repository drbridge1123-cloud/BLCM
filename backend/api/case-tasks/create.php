<?php
// POST /api/case-tasks - Create a custom task
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$input = json_decode(file_get_contents('php://input'), true);

$caseId = (int)($input['case_id'] ?? 0);
$title = trim($input['title'] ?? '');
$phase = trim($input['phase'] ?? '');
$stage = trim($input['stage'] ?? '');

if (!$caseId || !$title || !$phase || !$stage) {
    errorResponse('case_id, title, phase, and stage are required');
}

// Get phase_order and stage_order from existing tasks
$ref = dbFetchOne(
    "SELECT phase_order, stage_order, COALESCE(MAX(sort_order), 0) + 1 AS next_sort
     FROM case_tasks WHERE case_id = ? AND phase = ? AND stage = ?",
    [$caseId, $phase, $stage]
);

$phaseOrder = $ref ? (int)$ref['phase_order'] : 0;
$stageOrder = $ref ? (int)$ref['stage_order'] : 0;
$sortOrder = $ref ? (int)$ref['next_sort'] : 1;

dbQuery(
    "INSERT INTO case_tasks (case_id, parent_task_id, title, phase, stage, phase_order, stage_order, sort_order, assigned_to)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
    [
        $caseId,
        $input['parent_task_id'] ?? null,
        $title,
        $phase,
        $stage,
        $phaseOrder,
        $stageOrder,
        $sortOrder,
        $input['assigned_to'] ?? null,
    ]
);

$newId = getDBConnection()->lastInsertId();

jsonResponse(['success' => true, 'id' => (int)$newId]);
