<?php
// POST /api/case-tasks/initialize - Initialize tasks from template for a case
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$input = json_decode(file_get_contents('php://input'), true);
$caseId = (int)($input['case_id'] ?? 0);
if (!$caseId) {
    errorResponse('case_id is required');
}

// Check case exists
$case = dbFetchOne("SELECT id FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

// Check if already initialized
$existing = dbFetchOne("SELECT COUNT(*) AS cnt FROM case_tasks WHERE case_id = ?", [$caseId]);
if ((int)$existing['cnt'] > 0) {
    errorResponse('Tasks already initialized for this case. Delete existing tasks first.');
}

// Load template
$templatePath = __DIR__ . '/../../data/checklist-template.json';
$template = json_decode(file_get_contents($templatePath), true);
if (!$template) {
    errorResponse('Failed to load checklist template');
}

try {
    dbTransaction(function ($pdo) use ($template, $caseId) {
        foreach ($template as $phase) {
            $phaseName = $phase['phase'];
            $phaseOrder = (int)$phase['phase_order'];

            foreach ($phase['stages'] as $stage) {
                $stageName = $stage['stage'];
                $stageOrder = (int)$stage['stage_order'];
                $sortOrder = 0;

                foreach ($stage['tasks'] as $task) {
                    $sortOrder++;
                    _clInsertTask($pdo, $caseId, null, $task, $phaseName, $stageName, $phaseOrder, $stageOrder, $sortOrder);
                }
            }
        }
    });

    jsonResponse(['success' => true, 'message' => 'Checklist initialized']);
} catch (Exception $e) {
    errorResponse('Failed to initialize: ' . $e->getMessage(), 500);
}

function _clInsertTask($pdo, $caseId, $parentId, $task, $phase, $stage, $phaseOrder, $stageOrder, &$sortOrder) {
    $isConditional = !empty($task['is_conditional']) ? 1 : 0;
    $hasSubtasks = !empty($task['has_subtasks']) ? 1 : 0;
    if ($isConditional) $hasSubtasks = 1;

    $stmt = $pdo->prepare(
        "INSERT INTO case_tasks (case_id, parent_task_id, title, task_number, phase, stage, phase_order, stage_order, sort_order, is_conditional, has_subtasks)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $caseId, $parentId, $task['task_name'], $task['task_number'] ?? null,
        $phase, $stage, $phaseOrder, $stageOrder, $sortOrder, $isConditional, $hasSubtasks,
    ]);
    $taskId = $pdo->lastInsertId();

    // Non-conditional subtasks (always-visible children)
    if (!$isConditional && !empty($task['has_subtasks']) && isset($task['subtasks']) && is_array($task['subtasks'])) {
        foreach ($task['subtasks'] as $sub) {
            $sortOrder++;
            _clInsertTask($pdo, $caseId, $taskId, $sub, $phase, $stage, $phaseOrder, $stageOrder, $sortOrder);
        }
    }

    // Conditional subtasks — yes/no branches with condition_value
    if ($isConditional && isset($task['subtasks']) && is_array($task['subtasks'])) {
        foreach (['yes', 'no'] as $branch) {
            if (!empty($task['subtasks'][$branch])) {
                foreach ($task['subtasks'][$branch] as $sub) {
                    $sortOrder++;
                    _clInsertCondChild($pdo, $caseId, $taskId, $sub, $branch, $phase, $stage, $phaseOrder, $stageOrder, $sortOrder);
                }
            }
        }
    }
}

function _clInsertCondChild($pdo, $caseId, $parentId, $sub, $branch, $phase, $stage, $phaseOrder, $stageOrder, &$sortOrder) {
    $subIsConditional = !empty($sub['is_conditional']) ? 1 : 0;
    $subHasSubtasks = !empty($sub['has_subtasks']) ? 1 : 0;
    if ($subIsConditional) $subHasSubtasks = 1;

    $stmt = $pdo->prepare(
        "INSERT INTO case_tasks (case_id, parent_task_id, title, task_number, phase, stage, phase_order, stage_order, sort_order, is_conditional, has_subtasks, condition_value)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $caseId, $parentId, $sub['task_name'], $sub['task_number'] ?? null,
        $phase, $stage, $phaseOrder, $stageOrder, $sortOrder, $subIsConditional, $subHasSubtasks, $branch,
    ]);
    $subId = $pdo->lastInsertId();

    // Nested conditional subtasks (e.g., Wage Loss? inside PIP)
    if ($subIsConditional && isset($sub['subtasks']) && is_array($sub['subtasks'])) {
        foreach (['yes', 'no'] as $b2) {
            if (!empty($sub['subtasks'][$b2])) {
                foreach ($sub['subtasks'][$b2] as $sub2) {
                    $sortOrder++;
                    $nestedStmt = $pdo->prepare(
                        "INSERT INTO case_tasks (case_id, parent_task_id, title, task_number, phase, stage, phase_order, stage_order, sort_order, condition_value)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                    $nestedStmt->execute([
                        $caseId, $subId, $sub2['task_name'], $sub2['task_number'] ?? null,
                        $phase, $stage, $phaseOrder, $stageOrder, $sortOrder, $b2,
                    ]);
                }
            }
        }
    }

    // Nested non-conditional subtasks
    if (!$subIsConditional && !empty($sub['has_subtasks']) && isset($sub['subtasks']) && is_array($sub['subtasks'])) {
        foreach ($sub['subtasks'] as $sub2) {
            $sortOrder++;
            $nestedStmt = $pdo->prepare(
                "INSERT INTO case_tasks (case_id, parent_task_id, title, task_number, phase, stage, phase_order, stage_order, sort_order, condition_value)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $nestedStmt->execute([
                $caseId, $subId, $sub2['task_name'], $sub2['task_number'] ?? null,
                $phase, $stage, $phaseOrder, $stageOrder, $sortOrder, $branch,
            ]);
        }
    }
}
