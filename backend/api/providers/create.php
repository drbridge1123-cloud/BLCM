<?php
/**
 * POST /api/providers
 * Create a new provider with optional contacts
 */
requireAuth();

$input = getInput();
$errors = validateRequired($input, ['name', 'type']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$validTypes = ['hospital','er','chiro','imaging','physician','surgery_center','pharmacy','acupuncture','massage','pain_management','pt','other'];
if (!validateEnum($input['type'], $validTypes)) {
    errorResponse('Invalid provider type');
}

if (!empty($input['preferred_method'])) {
    $validMethods = ['email','fax','portal','phone','mail','chartswap','online'];
    if (!validateEnum($input['preferred_method'], $validMethods)) {
        errorResponse('Invalid preferred method');
    }
}

if (!empty($input['difficulty_level'])) {
    $validDifficulty = ['easy','medium','hard'];
    if (!validateEnum($input['difficulty_level'], $validDifficulty)) {
        errorResponse('Invalid difficulty level');
    }
}

$data = [
    'name' => sanitizeString($input['name']),
    'type' => $input['type'],
];

$optionalFields = ['address', 'phone', 'fax', 'email', 'portal_url', 'third_party_name', 'third_party_contact', 'notes'];
foreach ($optionalFields as $field) {
    if (isset($input[$field])) {
        $data[$field] = sanitizeString($input[$field]);
    }
}

if (isset($input['preferred_method'])) $data['preferred_method'] = $input['preferred_method'];
if (isset($input['uses_third_party'])) $data['uses_third_party'] = (int)$input['uses_third_party'];
if (isset($input['charges_record_fee'])) $data['charges_record_fee'] = (int)$input['charges_record_fee'];
if (isset($input['avg_response_days'])) $data['avg_response_days'] = (int)$input['avg_response_days'];
if (isset($input['difficulty_level'])) $data['difficulty_level'] = $input['difficulty_level'];

$id = dbInsert('providers', $data);

// Insert contacts if provided
if (!empty($input['contacts']) && is_array($input['contacts'])) {
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

logActivity($_SESSION['user_id'], 'create', 'provider', $id, ['name' => $input['name']]);

successResponse(['id' => $id], 'Provider created successfully');
