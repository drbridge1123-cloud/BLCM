<?php
/**
 * POST /api/attorney-cases/to-uim
 * Move a case from demand to UIM phase
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$input = getInput();

$errors = validateRequired($input, ['case_id']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId = (int)$input['case_id'];
$note = isset($input['note']) ? sanitizeString($input['note']) : '';

$case = dbFetchOne("SELECT * FROM attorney_cases WHERE id = ? AND deleted_at IS NULL", [$caseId]);
if (!$case) errorResponse('Case not found', 404);
if ($case['phase'] !== 'demand') errorResponse('Case is not in demand phase');

$data = [
    'phase' => 'uim',
];

if ($note) {
    $existing = trim($case['note'] ?? '');
    $data['note'] = $existing ? $existing . "\n" . $note : $note;
}

dbUpdate('attorney_cases', $data, 'id = ?', [$caseId]);

logActivity($userId, 'to_uim', 'attorney_case', $caseId, []);

successResponse(null, 'Case moved to UIM');
