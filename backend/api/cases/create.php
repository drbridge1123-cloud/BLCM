<?php
/**
 * POST /api/cases
 * Create a new MR case
 */
$userId = requireAuth();
$input = getInput();

$errors = validateRequired($input, ['case_number', 'client_name', 'client_dob', 'doi', 'assigned_to']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseNumber = sanitizeString($input['case_number']);
$clientName = sanitizeString($input['client_name']);
$clientDob  = sanitizeString($input['client_dob']);
$doi        = sanitizeString($input['doi']);
$assignedTo = (int)$input['assigned_to'];

// Validate dates
if (!validateDate($clientDob)) errorResponse('Invalid client_dob date format (YYYY-MM-DD)');
if (!validateDate($doi)) errorResponse('Invalid doi date format (YYYY-MM-DD)');

// Duplicate check on case_number + client_dob
$dup = dbFetchOne(
    "SELECT id FROM cases WHERE case_number = ? AND client_dob = ?",
    [$caseNumber, $clientDob]
);
if ($dup) errorResponse('A case with this case number and date of birth already exists');

// Determine default status
$status = 'collecting';

// Auto-assign owner from STATUS_OWNER_MAP
$autoAssigned = STATUS_OWNER_MAP[$status] ?? $assignedTo;

$data = [
    'case_number'   => $caseNumber,
    'client_name'   => $clientName,
    'client_dob'    => $clientDob,
    'doi'           => $doi,
    'assigned_to'   => $autoAssigned,
    'status'        => $status,
    'attorney_name' => sanitizeString($input['attorney_name'] ?? ''),
    'notes'         => sanitizeString($input['notes'] ?? ''),
];

$id = dbInsert('cases', $data);

logActivity($userId, 'create', 'case', $id, [
    'case_number' => $caseNumber,
    'client_name' => $clientName,
    'status'      => $status,
]);

successResponse(['id' => $id], 'Case created successfully');
