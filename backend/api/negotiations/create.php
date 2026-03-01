<?php
/**
 * POST /api/negotiations
 * Create a new negotiation round
 */
$userId = requireAuth();
requirePermission('cases');
$input = getInput();

$errors = validateRequired($input, ['case_id', 'coverage_type']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId = (int)$input['case_id'];
$coverageType = sanitizeString($input['coverage_type']);

$validTypes = ['3rd_party', 'um', 'uim', 'dv'];
if (!validateEnum($coverageType, $validTypes)) errorResponse('Invalid coverage_type');

// Auto-calculate round_number
$max = dbFetchOne(
    "SELECT COALESCE(MAX(round_number), 0) AS max_round
     FROM case_negotiations WHERE case_id = ? AND coverage_type = ?",
    [$caseId, $coverageType]
);
$roundNumber = (int)$max['max_round'] + 1;

$data = [
    'case_id'           => $caseId,
    'coverage_type'     => $coverageType,
    'round_number'      => $roundNumber,
    'insurance_company' => sanitizeString($input['insurance_company'] ?? ''),
    'demand_date'       => $input['demand_date'] ?? null,
    'demand_amount'     => isset($input['demand_amount']) ? (float)$input['demand_amount'] : null,
    'offer_date'        => $input['offer_date'] ?? null,
    'offer_amount'      => isset($input['offer_amount']) ? (float)$input['offer_amount'] : null,
    'party'             => sanitizeString($input['party'] ?? ''),
    'adjuster_phone'    => sanitizeString($input['adjuster_phone'] ?? ''),
    'adjuster_fax'      => sanitizeString($input['adjuster_fax'] ?? ''),
    'adjuster_email'    => sanitizeString($input['adjuster_email'] ?? ''),
    'claim_number'      => sanitizeString($input['claim_number'] ?? ''),
    'status'            => validateEnum($input['status'] ?? 'pending', ['pending','countered','accepted','rejected'])
                           ? ($input['status'] ?? 'pending') : 'pending',
    'notes'             => sanitizeString($input['notes'] ?? ''),
    'created_by'        => $userId,
];

$id = dbInsert('case_negotiations', $data);

logActivity($userId, 'create', 'case_negotiation', $id, [
    'case_id' => $caseId, 'coverage_type' => $coverageType, 'round' => $roundNumber
]);

successResponse(['id' => $id, 'round_number' => $roundNumber], 'Negotiation round created');
