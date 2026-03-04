<?php
/**
 * POST /api/bl-cases/{id}/activate-providers
 * Transition treating providers to not_started with staff assignment
 */
require_once __DIR__ . '/../../helpers/date.php';

$userId = requireAuth();
$caseId = (int)$_GET['id'];
$input  = getInput();

$case = dbFetchOne("SELECT * FROM cases WHERE id = ?", [$caseId]);
if (!$case) errorResponse('Case not found', 404);

$assignedTo = (int)($input['assigned_to'] ?? 0);
if (!$assignedTo) errorResponse('assigned_to is required', 400);

$assignee = dbFetchOne("SELECT id, is_active FROM users WHERE id = ?", [$assignedTo]);
if (!$assignee || !$assignee['is_active']) errorResponse('Invalid or inactive staff member', 400);

// Determine which providers to activate (treatment_complete or treating)
$providerIds = $input['provider_ids'] ?? [];
if (!empty($providerIds)) {
    $placeholders = implode(',', array_fill(0, count($providerIds), '?'));
    $providers = dbFetchAll(
        "SELECT id FROM case_providers WHERE case_id = ? AND id IN ({$placeholders}) AND overall_status IN ('treating', 'treatment_complete')",
        array_merge([$caseId], $providerIds)
    );
} else {
    $providers = dbFetchAll(
        "SELECT id FROM case_providers WHERE case_id = ? AND overall_status IN ('treating', 'treatment_complete')",
        [$caseId]
    );
}

if (empty($providers)) {
    errorResponse('No providers found to activate', 400);
}

$deadline = calculateDeadline();
$notes = isset($input['notes']) ? trim($input['notes']) : null;
$activatedCount = 0;

foreach ($providers as $prov) {
    $updateData = [
        'overall_status'    => 'not_started',
        'assigned_to'       => $assignedTo,
        'assignment_status' => 'pending',
        'activated_by'      => $userId,
        'deadline'          => $deadline,
        'request_mr'        => !empty($input['request_mr']) ? 1 : 0,
        'request_bill'      => !empty($input['request_bill']) ? 1 : 0,
        'request_chart'     => !empty($input['request_chart']) ? 1 : 0,
        'request_img'       => !empty($input['request_img']) ? 1 : 0,
        'request_op'        => !empty($input['request_op']) ? 1 : 0,
    ];
    if ($notes !== null) {
        $updateData['notes'] = $notes;
    }

    dbUpdate('case_providers', $updateData, 'id = ?', [$prov['id']]);
    $activatedCount++;
}

// Create notification for the assigned billing staff
$currentUser = dbFetchOne("SELECT COALESCE(display_name, full_name) AS full_name FROM users WHERE id = ?", [$userId]);
dbInsert('notifications', [
    'user_id' => $assignedTo,
    'type'    => 'billing_assignment',
    'message' => "{$currentUser['full_name']} assigned {$activatedCount} provider(s) from case {$case['case_number']} ({$case['client_name']}) for billing. Please accept.",
    'is_read' => 0,
]);

// Only mark treating completed if no treating/treatment_complete providers remain
$remainingTreating = dbFetchOne(
    "SELECT COUNT(*) AS cnt FROM case_providers WHERE case_id = ? AND overall_status IN ('treating', 'treatment_complete')",
    [$caseId]
);
if ((int)$remainingTreating['cnt'] === 0) {
    dbUpdate('cases', ['ini_completed' => 1], 'id = ?', [$caseId]);
}

logActivity($userId, 'activate_providers', 'case', $caseId, [
    'activated_count' => $activatedCount,
    'assigned_to'     => $assignedTo,
]);

successResponse(['activated' => $activatedCount], "{$activatedCount} provider(s) activated for billing");
