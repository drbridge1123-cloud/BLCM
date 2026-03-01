<?php
/**
 * POST /api/notes
 * Create a case note
 */
$userId = requireAuth();
$input  = getInput();

$errors = validateRequired($input, ['case_id', 'content']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId = (int)$input['case_id'];
$case   = dbFetchOne("SELECT id FROM cases WHERE id = ?", [$caseId]);
if (!$case) errorResponse('Case not found', 404);

$data = [
    'case_id' => $caseId,
    'user_id' => $userId,
    'content' => sanitizeString($input['content']),
];

// Optional: case_provider_id
if (!empty($input['case_provider_id'])) {
    $cpId = (int)$input['case_provider_id'];
    $cp   = dbFetchOne("SELECT id FROM case_providers WHERE id = ? AND case_id = ?", [$cpId, $caseId]);
    if (!$cp) errorResponse('Case-provider not found for this case', 404);
    $data['case_provider_id'] = $cpId;
}

// Optional: note_type
if (!empty($input['note_type'])) {
    $validTypes = ['general','follow_up','issue','handoff'];
    if (!validateEnum($input['note_type'], $validTypes)) errorResponse('Invalid note_type');
    $data['note_type'] = $input['note_type'];
}

// Optional: contact_method
if (!empty($input['contact_method'])) {
    $validMethods = ['phone','fax','email','portal','mail','in_person','other'];
    if (!validateEnum($input['contact_method'], $validMethods)) errorResponse('Invalid contact_method');
    $data['contact_method'] = $input['contact_method'];
}

// Optional: contact_date
if (!empty($input['contact_date'])) {
    $data['contact_date'] = $input['contact_date'];
} else {
    $data['contact_date'] = date('Y-m-d H:i:s');
}

$id = dbInsert('case_notes', $data);

logActivity($userId, 'create', 'case_note', $id, [
    'case_id' => $caseId,
    'type'    => $data['note_type'] ?? 'general',
]);

successResponse(['id' => $id], 'Note created');
