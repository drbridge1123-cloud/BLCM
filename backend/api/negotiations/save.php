<?php
// POST /api/negotiations/{case_id} - Save negotiation round
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('case_id is required');
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    errorResponse('Invalid request body');
}

$coverageType = $data['coverage_type'] ?? '3rd_party';
$allowedTypes = ['3rd_party', 'um', 'uim', 'dv'];
if (!in_array($coverageType, $allowedTypes)) {
    errorResponse('Invalid coverage type');
}

// If saving adjuster info for a coverage type (updates all rounds)
if (isset($data['adjuster_info'])) {
    $adj = $data['adjuster_info'];
    $adjFields = [
        'insurance_company' => $adj['insurance_company'] ?? null,
        'party' => $adj['party'] ?? null,
        'adjuster_phone' => $adj['adjuster_phone'] ?? null,
        'adjuster_fax' => $adj['adjuster_fax'] ?? null,
        'adjuster_email' => $adj['adjuster_email'] ?? null,
        'claim_number' => $adj['claim_number'] ?? null,
    ];
    dbUpdate('case_negotiations', $adjFields, 'case_id = ? AND coverage_type = ?', [$caseId, $coverageType]);

    logActivity($userId, 'negotiation_adjuster_update', 'case', $caseId, [
        'coverage_type' => $coverageType,
    ]);

    jsonResponse([
        'success' => true,
        'message' => 'Adjuster info updated',
    ]);
}

// If saving a single round (add/edit)
if (isset($data['round'])) {
    $round = $data['round'];
    $roundId = $round['id'] ?? null;

    $fields = [
        'case_id' => $caseId,
        'coverage_type' => $coverageType,
        'round_number' => (int)($round['round_number'] ?? 1),
        'demand_date' => $round['demand_date'] ?: null,
        'demand_amount' => (float)($round['demand_amount'] ?? 0),
        'offer_date' => $round['offer_date'] ?: null,
        'offer_amount' => (float)($round['offer_amount'] ?? 0),
        'insurance_company' => $round['insurance_company'] ?? null,
        'party' => $round['party'] ?? null,
        'adjuster_phone' => $round['adjuster_phone'] ?? null,
        'adjuster_fax' => $round['adjuster_fax'] ?? null,
        'adjuster_email' => $round['adjuster_email'] ?? null,
        'claim_number' => $round['claim_number'] ?? null,
        'status' => $round['status'] ?? 'pending',
        'notes' => $round['notes'] ?? null,
    ];

    if ($roundId) {
        // Update existing
        dbUpdate('case_negotiations', $fields, 'id = ? AND case_id = ?', [$roundId, $caseId]);
        $id = $roundId;
    } else {
        // Get next round number
        $maxRound = dbFetchOne(
            "SELECT MAX(round_number) AS max_round FROM case_negotiations WHERE case_id = ? AND coverage_type = ?",
            [$caseId, $coverageType]
        );
        $fields['round_number'] = ($maxRound['max_round'] ?? 0) + 1;
        $fields['created_by'] = $userId;
        $id = dbInsert('case_negotiations', $fields);
    }

    logActivity($userId, 'negotiation_save', 'case_negotiation', $id, [
        'case_id' => $caseId,
        'coverage_type' => $coverageType,
        'round_number' => $fields['round_number'],
    ]);

    jsonResponse([
        'success' => true,
        'id' => $id,
        'message' => 'Negotiation round saved',
    ]);
}

errorResponse('Invalid request - must include round data');
