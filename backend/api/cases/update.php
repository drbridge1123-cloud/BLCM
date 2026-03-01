<?php
/**
 * PUT /api/cases/{id}
 * Update an existing MR case
 */
$userId = requireAuth();
$id = (int)$_GET['id'];
$input = getInput();

$case = dbFetchOne("SELECT * FROM cases WHERE id = ?", [$id]);
if (!$case) errorResponse('Case not found', 404);

$allowedFields = [
    'case_number', 'client_name', 'client_dob', 'doi',
    'assigned_to', 'status', 'attorney_name', 'ini_completed', 'notes'
];

$data    = [];
$changes = [];

foreach ($allowedFields as $field) {
    if (!array_key_exists($field, $input)) continue;

    $newValue = $input[$field];

    // Sanitize / cast per field
    switch ($field) {
        case 'case_number':
        case 'client_name':
        case 'attorney_name':
        case 'notes':
            $newValue = sanitizeString($newValue);
            break;
        case 'client_dob':
        case 'doi':
            $newValue = sanitizeString($newValue);
            if ($newValue && !validateDate($newValue)) {
                errorResponse("Invalid {$field} date format (YYYY-MM-DD)");
            }
            break;
        case 'assigned_to':
            $newValue = $newValue ? (int)$newValue : null;
            break;
        case 'ini_completed':
            $newValue = (int)$newValue;
            break;
        case 'status':
            $validStatuses = [
                'collecting','verification','completed','rfd',
                'final_verification','disbursement','accounting','closed'
            ];
            if (!validateEnum($newValue, $validStatuses)) {
                errorResponse('Invalid status value');
            }
            break;
    }

    // Track changes for activity log
    $oldValue = $case[$field] ?? null;
    if ((string)$newValue !== (string)$oldValue) {
        $changes[$field] = ['from' => $oldValue, 'to' => $newValue];
    }

    $data[$field] = $newValue;
}

if (empty($data)) errorResponse('No fields to update');

// Duplicate check when case_number or client_dob is being changed
$checkNumber = $data['case_number'] ?? $case['case_number'];
$checkDob    = $data['client_dob'] ?? $case['client_dob'];

if (isset($data['case_number']) || isset($data['client_dob'])) {
    $dup = dbFetchOne(
        "SELECT id FROM cases WHERE case_number = ? AND client_dob = ? AND id != ?",
        [$checkNumber, $checkDob, $id]
    );
    if ($dup) errorResponse('A case with this case number and date of birth already exists');
}

// If ini_completed set to 0, revert not_started providers back to treating (requesting)
if (isset($data['ini_completed']) && (int)$data['ini_completed'] === 0) {
    dbQuery(
        "UPDATE case_providers SET overall_status = 'requesting' WHERE case_id = ? AND overall_status = 'not_started'",
        [$id]
    );
}

dbUpdate('cases', $data, 'id = ?', [$id]);

if (!empty($changes)) {
    logActivity($userId, 'update', 'case', $id, $changes);
}

successResponse(null, 'Case updated successfully');
