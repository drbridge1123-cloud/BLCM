<?php
/**
 * PUT /api/adjusters/{id}
 * Update an adjuster
 */
requireAuth();

$id = (int)$_GET['id'];
$input = getInput();

$adjuster = dbFetchOne("SELECT * FROM adjusters WHERE id = ?", [$id]);
if (!$adjuster) errorResponse('Adjuster not found', 404);

$data = [];

if (isset($input['first_name'])) $data['first_name'] = sanitizeString($input['first_name']);
if (isset($input['last_name'])) $data['last_name'] = sanitizeString($input['last_name']);

if (isset($input['insurance_company_id'])) {
    if ($input['insurance_company_id']) {
        $company = dbFetchOne("SELECT id FROM insurance_companies WHERE id = ?", [(int)$input['insurance_company_id']]);
        if (!$company) errorResponse('Insurance company not found');
        $data['insurance_company_id'] = (int)$input['insurance_company_id'];
    } else {
        $data['insurance_company_id'] = null;
    }
}

if (isset($input['adjuster_type'])) {
    if ($input['adjuster_type']) {
        $validTypes = ['pip','um','uim','3rd_party','liability','pd','bi'];
        if (!validateEnum($input['adjuster_type'], $validTypes)) errorResponse('Invalid adjuster type');
        $data['adjuster_type'] = $input['adjuster_type'];
    } else {
        $data['adjuster_type'] = null;
    }
}

$stringFields = ['title', 'phone', 'fax', 'email', 'notes'];
foreach ($stringFields as $field) {
    if (isset($input[$field])) {
        $data[$field] = sanitizeString($input[$field]);
    }
}

if (isset($input['is_active'])) $data['is_active'] = (int)$input['is_active'];

if (empty($data)) errorResponse('No fields to update');

dbUpdate('adjusters', $data, 'id = ?', [$id]);

logActivity($_SESSION['user_id'], 'update', 'adjuster', $id, $data);

successResponse(null, 'Adjuster updated successfully');
