<?php
// POST /api/provider-negotiations/{case_id} - Save/update a provider negotiation
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

$id = $data['id'] ?? null;

$fields = [
    'case_id' => $caseId,
    'case_provider_id' => $data['case_provider_id'] ?? null,
    'mbds_line_id' => $data['mbds_line_id'] ?? null,
    'provider_name' => $data['provider_name'] ?? '',
    'original_balance' => (float)($data['original_balance'] ?? 0),
    'requested_reduction' => (float)($data['requested_reduction'] ?? 0),
    'accepted_amount' => (float)($data['accepted_amount'] ?? 0),
    'reduction_percent' => (float)($data['reduction_percent'] ?? 0),
    'status' => $data['status'] ?? 'pending',
    'contact_name' => $data['contact_name'] ?? null,
    'contact_info' => $data['contact_info'] ?? null,
    'notes' => $data['notes'] ?? null,
];

if ($id) {
    dbUpdate('provider_negotiations', $fields, 'id = ? AND case_id = ?', [$id, $caseId]);
} else {
    $fields['created_by'] = $userId;
    $id = dbInsert('provider_negotiations', $fields);
}

logActivity($userId, 'provider_negotiation_save', 'provider_negotiation', $id, [
    'case_id' => $caseId,
    'provider_name' => $fields['provider_name'],
]);

jsonResponse([
    'success' => true,
    'id' => $id,
    'message' => 'Provider negotiation saved',
]);
