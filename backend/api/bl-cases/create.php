<?php
/**
 * POST /api/bl-cases
 * Create a new MR case
 */
$userId = requireAuth();
$user = getCurrentUser();
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

// Determine if admin/manager is assigning to a staff member (assignment workflow)
$isAdminOrManager = in_array($user['role'], ['admin', 'manager']);
$assignmentStatus = 'unassigned';
$status = 'ini';
$actualAssignedTo = STATUS_OWNER_MAP[$status] ?? $assignedTo;

if ($isAdminOrManager && $assignedTo) {
    // Validate assignee
    $assignee = dbFetchOne("SELECT id, full_name, is_active FROM users WHERE id = ?", [$assignedTo]);
    if ($assignee && $assignee['is_active']) {
        $assignmentStatus = 'pending';
        $actualAssignedTo = $assignedTo;
    }
}

$data = [
    'case_number'              => $caseNumber,
    'client_name'              => $clientName,
    'client_dob'               => $clientDob,
    'doi'                      => $doi,
    'assigned_to'              => $actualAssignedTo,
    'assignment_status'        => $assignmentStatus,
    'assignment_assigned_by'   => ($assignmentStatus === 'pending') ? $userId : null,
    'status'                   => $status,
    'prelitigation_start_date' => date('Y-m-d'),
    'attorney_name'            => sanitizeString($input['attorney_name'] ?? ''),
    'notes'                    => sanitizeString($input['notes'] ?? ''),
];

$id = dbInsert('cases', $data);

// Create notification for pending assignment
if ($assignmentStatus === 'pending') {
    dbInsert('notifications', [
        'user_id' => $assignedTo,
        'type'    => 'case_assignment',
        'message' => "You have been assigned case {$caseNumber} ({$clientName}). Please accept or decline.",
        'is_read' => 0,
    ]);
}

logActivity($userId, 'create', 'case', $id, [
    'case_number' => $caseNumber,
    'client_name' => $clientName,
    'status'      => $status,
]);

successResponse(['id' => $id], 'Case created successfully');
