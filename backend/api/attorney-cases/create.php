<?php
/**
 * POST /api/attorney-cases
 * Create a new attorney case
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$input = getInput();

$errors = validateRequired($input, ['case_number', 'client_name']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseNumber   = sanitizeString($input['case_number']);
$clientName   = sanitizeString($input['client_name']);
$caseType     = sanitizeString($input['case_type'] ?? 'Auto');
$assignedDate = sanitizeString($input['assigned_date'] ?? date('Y-m-d'));
$phase        = sanitizeString($input['phase'] ?? 'demand');
$attorneyId   = !empty($input['attorney_user_id']) ? (int)$input['attorney_user_id'] : $userId;
$month        = sanitizeString($input['month'] ?? '');
$note         = sanitizeString($input['note'] ?? '');
$presuitOffer = isset($input['presuit_offer']) ? (float)$input['presuit_offer'] : null;

// Validate phase
if (!validateEnum($phase, ['demand', 'litigation', 'uim'])) {
    errorResponse('Invalid phase value');
}

// Validate assigned_date
if ($assignedDate && !validateDate($assignedDate)) {
    errorResponse('Invalid assigned_date format (YYYY-MM-DD)');
}

// Duplicate check
$dup = dbFetchOne(
    "SELECT id FROM attorney_cases WHERE case_number = ? AND deleted_at IS NULL",
    [$caseNumber]
);
if ($dup) errorResponse('An attorney case with this case number already exists');

$demandDeadline = calculateDemandDeadline($assignedDate);

$data = [
    'case_number'     => $caseNumber,
    'client_name'     => $clientName,
    'case_type'       => $caseType,
    'assigned_date'   => $assignedDate,
    'demand_deadline'  => $demandDeadline,
    'phase'           => $phase,
    'status'          => 'in_progress',
    'stage'           => 'demand_review',
    'attorney_user_id' => $attorneyId,
    'created_by'      => $userId,
    'month'           => $month ?: null,
    'note'            => $note ?: null,
    'presuit_offer'   => $presuitOffer,
];

$id = dbInsert('attorney_cases', $data);

logActivity($userId, 'create', 'attorney_case', $id, [
    'case_number' => $caseNumber,
    'client_name' => $clientName,
    'phase'       => $phase,
]);

successResponse(['id' => $id], 'Attorney case created successfully');
