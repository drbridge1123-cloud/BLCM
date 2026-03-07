<?php
// GET /api/case-tasks/summary?case_id=X - Progress summary per phase
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$caseId = (int)($_GET['case_id'] ?? 0);
if (!$caseId) {
    errorResponse('case_id is required');
}

$rows = dbFetchAll(
    "SELECT phase, phase_order,
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS done,
            SUM(CASE WHEN status = 'na' THEN 1 ELSE 0 END) AS na,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress
     FROM case_tasks
     WHERE case_id = ? AND parent_task_id IS NULL
     GROUP BY phase, phase_order
     ORDER BY phase_order",
    [$caseId]
);

$totalAll = 0;
$doneAll = 0;
$naAll = 0;
$phases = [];

foreach ($rows as $r) {
    $effective = (int)$r['total'] - (int)$r['na'];
    $pct = $effective > 0 ? round((int)$r['done'] / $effective * 100) : 0;
    $phases[] = [
        'phase' => $r['phase'],
        'phase_order' => (int)$r['phase_order'],
        'total' => (int)$r['total'],
        'done' => (int)$r['done'],
        'na' => (int)$r['na'],
        'in_progress' => (int)$r['in_progress'],
        'percent' => $pct,
    ];
    $totalAll += (int)$r['total'];
    $doneAll += (int)$r['done'];
    $naAll += (int)$r['na'];
}

$effectiveAll = $totalAll - $naAll;
$overallPct = $effectiveAll > 0 ? round($doneAll / $effectiveAll * 100) : 0;

jsonResponse([
    'success' => true,
    'initialized' => !empty($phases),
    'overall' => ['total' => $totalAll, 'done' => $doneAll, 'na' => $naAll, 'percent' => $overallPct],
    'phases' => $phases,
]);
