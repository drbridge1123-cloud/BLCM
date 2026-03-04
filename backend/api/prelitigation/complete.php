<?php
// POST /api/prelitigation/complete - Send case to billing
$userId = requireAuth();
requirePermission('prelitigation_tracker');
$input = getInput();

$caseId = (int)($input['case_id'] ?? 0);
if (!$caseId) errorResponse('case_id required', 400);

$case = dbFetchOne("SELECT * FROM cases WHERE id = ? AND status = 'ini'", [$caseId]);
if (!$case) errorResponse('Case not found or not in treatment (INI) status', 404);

$newOwner = STATUS_OWNER_MAP['rec'] ?? $case['assigned_to'];

dbUpdate('cases', [
    'status' => 'rec',
    'assigned_to' => $newOwner,
    'sent_to_billing_date' => date('Y-m-d'),
    'treatment_status' => 'treatment_done',
    'treatment_end_date' => $case['treatment_end_date'] ?? date('Y-m-d'),
], 'id = ?', [$caseId]);

// Create notification for billing owner
if ($newOwner) {
    dbInsert('notifications', [
        'user_id' => $newOwner,
        'type' => 'status_change',
        'message' => "Case {$case['case_number']} ({$case['client_name']}) treatment completed. Ready for billing (records collection).",
    ]);
}

logActivity($userId, 'change_status', 'case', $caseId, [
    'from' => 'ini',
    'to' => 'rec',
    'note' => sanitizeString($input['note'] ?? 'Treatment completed, sent to billing'),
]);

successResponse(null, 'Case sent to billing');
