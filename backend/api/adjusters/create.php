<?php
/**
 * POST /api/adjusters
 * Create a new adjuster
 */
requireAuth();

$input = getInput();
$errors = validateRequired($input, ['first_name', 'last_name']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

if (!empty($input['adjuster_type'])) {
    $validTypes = ['pip','um','uim','3rd_party','liability','pd','bi'];
    if (!validateEnum($input['adjuster_type'], $validTypes)) {
        errorResponse('Invalid adjuster type');
    }
}

// Validate insurance_company_id if provided
if (!empty($input['insurance_company_id'])) {
    $company = dbFetchOne("SELECT id FROM insurance_companies WHERE id = ?", [(int)$input['insurance_company_id']]);
    if (!$company) errorResponse('Insurance company not found');
}

$data = [
    'first_name' => sanitizeString($input['first_name']),
    'last_name' => sanitizeString($input['last_name']),
];

if (isset($input['insurance_company_id'])) $data['insurance_company_id'] = $input['insurance_company_id'] ? (int)$input['insurance_company_id'] : null;
if (isset($input['adjuster_type'])) $data['adjuster_type'] = $input['adjuster_type'];

$stringFields = ['title', 'phone', 'fax', 'email', 'notes'];
foreach ($stringFields as $field) {
    if (isset($input[$field])) {
        $data[$field] = sanitizeString($input[$field]);
    }
}

$id = dbInsert('adjusters', $data);

logActivity($_SESSION['user_id'], 'create', 'adjuster', $id, [
    'name' => $input['first_name'] . ' ' . $input['last_name']
]);

successResponse(['id' => $id], 'Adjuster created successfully');
