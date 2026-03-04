<?php
/**
 * POST /api/clients/save
 * Create or update a client, optionally link to a case
 */
$userId = requireAuth();
$input = getInput();

$clientId = !empty($input['id']) ? (int)$input['id'] : null;
$caseId = !empty($input['case_id']) ? (int)$input['case_id'] : null;

$name = trim($input['name'] ?? '');
if (!$name) {
    errorResponse('Client name is required');
}

$data = [
    'name'           => $name,
    'dob'            => !empty($input['dob']) ? trim($input['dob']) : null,
    'phone'          => trim($input['phone'] ?? ''),
    'email'          => trim($input['email'] ?? ''),
    'address_street' => trim($input['address_street'] ?? ''),
    'address_city'   => trim($input['address_city'] ?? ''),
    'address_state'  => trim($input['address_state'] ?? ''),
    'address_zip'    => trim($input['address_zip'] ?? ''),
];

if ($clientId) {
    // Update existing client
    $existing = dbFetchOne("SELECT id FROM clients WHERE id = ?", [$clientId]);
    if (!$existing) {
        errorResponse('Client not found', 404);
    }
    dbUpdate('clients', $data, 'id = ?', [$clientId]);
    logActivity($userId, 'client_updated', 'clients', $clientId, ['name' => $name]);
} else {
    // Create new client
    $clientId = dbInsert('clients', $data);
    logActivity($userId, 'client_created', 'clients', $clientId, ['name' => $name]);
}

// Link to case if case_id provided
if ($caseId) {
    $case = dbFetchOne("SELECT id FROM cases WHERE id = ?", [$caseId]);
    if ($case) {
        dbUpdate('cases', ['client_id' => $clientId], 'id = ?', [$caseId]);
    }
}

$client = dbFetchOne("SELECT * FROM clients WHERE id = ?", [$clientId]);
successResponse($client, $clientId ? 'Client saved' : 'Client created');
