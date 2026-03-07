<?php
/**
 * POST /api/case-adjusters
 * Create/update an adjuster and link to case with coverage_type
 */
$userId = requireAuth();
$input = getInput();

$caseId = (int)($input['case_id'] ?? 0);
if (!$caseId) errorResponse('case_id is required');

$coverageType = $input['coverage_type'] ?? '';
$validTypes = ['3rd_party','um','uim','pip','pd','dv','bi'];
if (!validateEnum($coverageType, $validTypes)) errorResponse('Invalid coverage type');

$coverageIndex = (int)($input['coverage_index'] ?? 1);
if ($coverageIndex < 1) $coverageIndex = 1;

$adjusterId = !empty($input['adjuster_id']) ? (int)$input['adjuster_id'] : null;

// If adjuster_id provided, just link it
if ($adjusterId) {
    $adj = dbFetchOne("SELECT id FROM adjusters WHERE id = ?", [$adjusterId]);
    if (!$adj) errorResponse('Adjuster not found', 404);
} else {
    // Create or update adjuster
    $firstName = trim($input['first_name'] ?? '');
    $lastName = trim($input['last_name'] ?? '');
    if (!$firstName || !$lastName) errorResponse('First name and last name are required');

    $existingId = !empty($input['id']) ? (int)$input['id'] : null;

    $data = [
        'first_name' => sanitizeString($firstName),
        'last_name' => sanitizeString($lastName),
    ];

    if (isset($input['insurance_company_id'])) $data['insurance_company_id'] = $input['insurance_company_id'] ? (int)$input['insurance_company_id'] : null;
    if (isset($input['adjuster_type'])) $data['adjuster_type'] = $input['adjuster_type'] ?: null;

    $stringFields = ['title', 'phone', 'fax', 'email', 'claim_number', 'notes'];
    foreach ($stringFields as $field) {
        if (isset($input[$field])) $data[$field] = sanitizeString($input[$field]);
    }

    if ($existingId) {
        dbUpdate('adjusters', $data, 'id = ?', [$existingId]);
        $adjusterId = $existingId;
    } else {
        $adjusterId = dbInsert('adjusters', $data);
    }
}

// Upsert case_adjusters link
$existing = dbFetchOne(
    "SELECT id FROM case_adjusters WHERE case_id = ? AND coverage_type = ? AND coverage_index = ?",
    [$caseId, $coverageType, $coverageIndex]
);

if ($existing) {
    dbUpdate('case_adjusters', ['adjuster_id' => $adjusterId], 'id = ?', [$existing['id']]);
} else {
    dbInsert('case_adjusters', [
        'case_id' => $caseId,
        'adjuster_id' => $adjusterId,
        'coverage_type' => $coverageType,
        'coverage_index' => $coverageIndex,
    ]);
}

// Get the case_adjuster link id
$link = dbFetchOne(
    "SELECT id FROM case_adjusters WHERE case_id = ? AND coverage_type = ? AND coverage_index = ?",
    [$caseId, $coverageType, $coverageIndex]
);

// Return full adjuster data + link id
$adjuster = dbFetchOne(
    "SELECT a.*, ic.name AS company_name
     FROM adjusters a
     LEFT JOIN insurance_companies ic ON ic.id = a.insurance_company_id
     WHERE a.id = ?",
    [$adjusterId]
);
$adjuster['case_adjuster_id'] = $link['id'] ?? null;

$adjuster['coverage_index'] = $coverageIndex;

logActivity($userId, 'case_adjuster_linked', 'case_adjusters', $caseId, [
    'coverage_type' => $coverageType,
    'coverage_index' => $coverageIndex,
    'adjuster_name' => ($adjuster['first_name'] ?? '') . ' ' . ($adjuster['last_name'] ?? ''),
]);

successResponse($adjuster, 'Adjuster saved');
