<?php
/**
 * POST /api/adjusters/save
 * Create or update an adjuster, optionally link to a case
 */
$userId = requireAuth();
$input = getInput();

$adjusterId = !empty($input['id']) ? (int)$input['id'] : null;
$caseId = !empty($input['case_id']) ? (int)$input['case_id'] : null;
$linkField = !empty($input['link_field']) ? $input['link_field'] : null;

$firstName = trim($input['first_name'] ?? '');
$lastName = trim($input['last_name'] ?? '');
if (!$firstName || !$lastName) {
    errorResponse('First name and last name are required');
}

// Validate link_field whitelist
if ($linkField && !in_array($linkField, ['adjuster_3rd_id', 'adjuster_um_id'])) {
    errorResponse('Invalid link field');
}

// Validate adjuster_type if provided
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
    'first_name' => sanitizeString($firstName),
    'last_name' => sanitizeString($lastName),
];

if (isset($input['insurance_company_id'])) $data['insurance_company_id'] = $input['insurance_company_id'] ? (int)$input['insurance_company_id'] : null;
if (isset($input['adjuster_type'])) $data['adjuster_type'] = $input['adjuster_type'] ?: null;

$stringFields = ['title', 'phone', 'fax', 'email', 'notes'];
foreach ($stringFields as $field) {
    if (isset($input[$field])) {
        $data[$field] = sanitizeString($input[$field]);
    }
}

if ($adjusterId) {
    // Update existing
    $existing = dbFetchOne("SELECT id FROM adjusters WHERE id = ?", [$adjusterId]);
    if (!$existing) errorResponse('Adjuster not found', 404);
    dbUpdate('adjusters', $data, 'id = ?', [$adjusterId]);
    logActivity($userId, 'adjuster_updated', 'adjusters', $adjusterId, ['name' => $firstName . ' ' . $lastName]);
} else {
    // Create new
    $adjusterId = dbInsert('adjusters', $data);
    logActivity($userId, 'adjuster_created', 'adjusters', $adjusterId, ['name' => $firstName . ' ' . $lastName]);
}

// Link to case if case_id and link_field provided
if ($caseId && $linkField) {
    $case = dbFetchOne("SELECT id FROM cases WHERE id = ?", [$caseId]);
    if ($case) {
        dbUpdate('cases', [$linkField => $adjusterId], 'id = ?', [$caseId]);
    }
}

$adjuster = dbFetchOne(
    "SELECT a.*, ic.name AS company_name
     FROM adjusters a
     LEFT JOIN insurance_companies ic ON ic.id = a.insurance_company_id
     WHERE a.id = ?",
    [$adjusterId]
);

successResponse($adjuster, $adjusterId ? 'Adjuster saved' : 'Adjuster created');
