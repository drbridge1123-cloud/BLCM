<?php
// POST /api/prelitigation-tracker/log-followup
$userId = requireAuth();
requirePermission('prelitigation_tracker');
$input = getInput();

$errors = validateRequired($input, ['case_id', 'followup_date', 'followup_type', 'contact_result']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId = (int)$input['case_id'];
$case = dbFetchOne("SELECT * FROM cases WHERE id = ? AND status = 'prelitigation'", [$caseId]);
if (!$case) errorResponse('Case not found or not in prelitigation status', 404);

$followupDate = $input['followup_date'];
$nextDate = $input['next_followup_date'] ?? date('Y-m-d', strtotime($followupDate . ' +' . PRELITIGATION_FOLLOWUP_DAYS . ' days'));

$id = dbInsert('prelitigation_followups', [
    'case_id' => $caseId,
    'followup_date' => $followupDate,
    'followup_type' => sanitizeString($input['followup_type']),
    'contact_result' => sanitizeString($input['contact_result']),
    'treatment_status_update' => sanitizeString($input['treatment_status_update'] ?? ''),
    'next_followup_date' => $nextDate,
    'notes' => sanitizeString($input['notes'] ?? ''),
    'created_by' => $userId,
]);

// Update treatment status if provided
if (!empty($input['treatment_status_update']) && $input['treatment_status_update'] === 'treatment_done') {
    dbUpdate('cases', [
        'treatment_status' => 'treatment_done',
        'treatment_end_date' => $followupDate,
    ], 'id = ?', [$caseId]);
}

logActivity($userId, 'log_followup', 'case', $caseId, [
    'followup_date' => $followupDate,
    'type' => $input['followup_type'],
    'result' => $input['contact_result'],
]);

successResponse(['id' => $id, 'next_followup_date' => $nextDate], 'Follow-up logged');
