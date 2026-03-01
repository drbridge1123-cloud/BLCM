<?php
/**
 * PUT /api/insurance-companies/{id}
 * Update an insurance company
 */
requireAuth();

$id = (int)$_GET['id'];
$input = getInput();

$company = dbFetchOne("SELECT * FROM insurance_companies WHERE id = ?", [$id]);
if (!$company) errorResponse('Insurance company not found', 404);

$data = [];

if (isset($input['name'])) $data['name'] = sanitizeString($input['name']);

if (isset($input['type'])) {
    $validTypes = ['auto','health','workers_comp','liability','um_uim','other'];
    if (!validateEnum($input['type'], $validTypes)) errorResponse('Invalid insurance company type');
    $data['type'] = $input['type'];
}

$stringFields = ['phone', 'fax', 'email', 'address', 'city', 'state', 'zip', 'website', 'notes'];
foreach ($stringFields as $field) {
    if (isset($input[$field])) {
        $data[$field] = sanitizeString($input[$field]);
    }
}

if (empty($data)) errorResponse('No fields to update');

dbUpdate('insurance_companies', $data, 'id = ?', [$id]);

logActivity($_SESSION['user_id'], 'update', 'insurance_company', $id, $data);

successResponse(null, 'Insurance company updated successfully');
