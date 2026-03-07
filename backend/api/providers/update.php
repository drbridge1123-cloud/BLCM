<?php
/**
 * PUT /api/providers/{id}
 * Update a provider. If contacts array is provided, replaces all contacts.
 */
requireAuth();

$id = (int)$_GET['id'];
$input = getInput();

$provider = dbFetchOne("SELECT * FROM providers WHERE id = ?", [$id]);
if (!$provider) errorResponse('Provider not found', 404);

$data = [];

if (isset($input['name'])) $data['name'] = sanitizeString($input['name']);

if (isset($input['type'])) {
    $validTypes = ['hospital','er','chiro','imaging','physician','surgery_center','pharmacy','acupuncture','massage','pain_management','pt','other'];
    if (!validateEnum($input['type'], $validTypes)) errorResponse('Invalid provider type');
    $data['type'] = $input['type'];
}

if (isset($input['preferred_method'])) {
    $validMethods = ['email','fax','portal','phone','mail','chartswap','online'];
    if (!validateEnum($input['preferred_method'], $validMethods)) errorResponse('Invalid preferred method');
    $data['preferred_method'] = $input['preferred_method'];
}

if (isset($input['difficulty_level'])) {
    $validDifficulty = ['easy','medium','hard'];
    if (!validateEnum($input['difficulty_level'], $validDifficulty)) errorResponse('Invalid difficulty level');
    $data['difficulty_level'] = $input['difficulty_level'];
}

$stringFields = ['address', 'phone', 'fax', 'email', 'portal_url', 'third_party_name', 'third_party_contact', 'notes'];
foreach ($stringFields as $field) {
    if (isset($input[$field])) {
        $data[$field] = sanitizeString($input[$field]);
    }
}

if (isset($input['uses_third_party'])) $data['uses_third_party'] = (int)$input['uses_third_party'];
if (isset($input['charges_record_fee'])) $data['charges_record_fee'] = (int)$input['charges_record_fee'];
if (isset($input['avg_response_days'])) $data['avg_response_days'] = (int)$input['avg_response_days'];

if (empty($data) && !isset($input['contacts'])) errorResponse('No fields to update');

if (!empty($data)) {
    dbUpdate('providers', $data, 'id = ?', [$id]);
}

// If contacts array is provided, delete old and re-insert
if (isset($input['contacts']) && is_array($input['contacts'])) {
    dbDelete('provider_contacts', 'provider_id = ?', [$id]);
    foreach ($input['contacts'] as $contact) {
        dbInsert('provider_contacts', [
            'provider_id' => $id,
            'department' => sanitizeString($contact['department'] ?? ''),
            'contact_type' => $contact['contact_type'],
            'contact_value' => sanitizeString($contact['contact_value']),
            'is_primary' => (int)($contact['is_primary'] ?? 0),
        ]);
    }
}

logActivity($_SESSION['user_id'], 'update', 'provider', $id, $data);

successResponse(null, 'Provider updated successfully');
