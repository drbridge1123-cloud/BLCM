<?php
// POST /api/accounting-tracker/complete - Close case
$userId = requireAuth();
requirePermission('accounting_tracker');
$input = getInput();

$caseId = (int)($input['case_id'] ?? 0);
$attorneyCaseId = (int)($input['attorney_case_id'] ?? 0);

if (!$caseId && !$attorneyCaseId) errorResponse('case_id or attorney_case_id required', 400);

$fileLocation = sanitizeString($input['file_location'] ?? '');
if (empty($fileLocation)) errorResponse('File location is required to close a case', 400);

if ($attorneyCaseId) {
    // Close attorney case
    $attCase = dbFetchOne("SELECT * FROM attorney_cases WHERE id = ? AND status = 'accounting' AND deleted_at IS NULL", [$attorneyCaseId]);
    if (!$attCase) errorResponse('Attorney case not found or not in accounting status', 404);

    dbUpdate('attorney_cases', [
        'status' => 'closed',
    ], 'id = ?', [$attorneyCaseId]);

    logActivity($userId, 'change_status', 'attorney_case', $attorneyCaseId, [
        'from' => 'accounting',
        'to' => 'closed',
        'file_location' => $fileLocation,
        'note' => sanitizeString($input['note'] ?? 'Case closed from accounting'),
    ]);
} else {
    // Close regular case
    $case = dbFetchOne("SELECT * FROM cases WHERE id = ? AND status = 'accounting'", [$caseId]);
    if (!$case) errorResponse('Case not found or not in accounting status', 404);

    dbUpdate('cases', [
        'status' => 'closed',
        'closed_date' => date('Y-m-d'),
        'file_location' => $fileLocation,
    ], 'id = ?', [$caseId]);

    logActivity($userId, 'change_status', 'case', $caseId, [
        'from' => 'accounting',
        'to' => 'closed',
        'file_location' => $fileLocation,
        'note' => sanitizeString($input['note'] ?? 'Case closed from accounting'),
    ]);
}

successResponse(null, 'Case closed');
