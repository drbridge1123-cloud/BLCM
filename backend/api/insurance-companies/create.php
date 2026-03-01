<?php
/**
 * POST /api/insurance-companies
 * Create a new insurance company
 */
requireAuth();

$input = getInput();
$errors = validateRequired($input, ['name']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

if (!empty($input['type'])) {
    $validTypes = ['auto','health','workers_comp','liability','um_uim','other'];
    if (!validateEnum($input['type'], $validTypes)) {
        errorResponse('Invalid insurance company type');
    }
}

$data = [
    'name' => sanitizeString($input['name']),
];

if (isset($input['type'])) $data['type'] = $input['type'];

$stringFields = ['phone', 'fax', 'email', 'address', 'city', 'state', 'zip', 'website', 'notes'];
foreach ($stringFields as $field) {
    if (isset($input[$field])) {
        $data[$field] = sanitizeString($input[$field]);
    }
}

$id = dbInsert('insurance_companies', $data);

logActivity($_SESSION['user_id'], 'create', 'insurance_company', $id, ['name' => $input['name']]);

successResponse(['id' => $id], 'Insurance company created successfully');
