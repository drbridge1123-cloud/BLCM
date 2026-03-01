<?php
/**
 * POST /api/attorney-cases/to-litigation
 * Move a case from demand to litigation phase
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$input = getInput();

$errors = validateRequired($input, ['case_id']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId = (int)$input['case_id'];
$litigationStartDate = sanitizeString($input['litigation_start_date'] ?? date('Y-m-d'));
$presuitOffer = (float)($input['presuit_offer'] ?? 0);
$note = isset($input['note']) ? sanitizeString($input['note']) : '';

$case = dbFetchOne("SELECT * FROM attorney_cases WHERE id = ? AND deleted_at IS NULL", [$caseId]);
if (!$case) errorResponse('Case not found', 404);
if ($case['phase'] !== 'demand') errorResponse('Case is not in demand phase');

$data = [
    'phase' => 'litigation',
    'litigation_start_date' => $litigationStartDate,
    'presuit_offer' => $presuitOffer,
];

// Append note if provided
if ($note) {
    $existing = trim($case['note'] ?? '');
    $data['note'] = $existing ? $existing . "\n" . $note : $note;
}

dbUpdate('attorney_cases', $data, 'id = ?', [$caseId]);

logActivity($userId, 'to_litigation', 'attorney_case', $caseId, [
    'litigation_start_date' => $litigationStartDate,
    'presuit_offer' => $presuitOffer
]);

successResponse(null, 'Case moved to litigation');
