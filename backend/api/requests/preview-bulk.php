<?php
// POST /api/requests/preview-bulk - Preview letter HTML for bulk requests without creating records
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

requireAuth();
$input = getInput();

// Validate required fields
$errors = validateRequired($input, ['requests', 'request_date', 'request_method', 'request_type']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors), 400);
}

if (!is_array($input['requests']) || empty($input['requests'])) {
    errorResponse('Requests must be a non-empty array', 400);
}

// Extract case_provider_ids
$caseProviderIds = [];
foreach ($input['requests'] as $req) {
    if (empty($req['case_provider_id'])) {
        errorResponse('Each request must have a case_provider_id', 400);
    }
    $caseProviderIds[] = (int)$req['case_provider_id'];
}

// Load all case_provider records with full data needed for letter
$placeholders = implode(',', array_fill(0, count($caseProviderIds), '?'));
$caseProviders = dbFetchAll(
    "SELECT cp.id, cp.case_id, cp.provider_id, cp.treatment_start_date, cp.treatment_end_date,
            cp.record_types_needed,
            c.case_number, c.client_name, c.client_dob, c.doi, c.attorney_name,
            p.name AS provider_name, p.address AS provider_address,
            p.city AS provider_city, p.state AS provider_state, p.zip AS provider_zip,
            p.email AS provider_email, p.fax AS provider_fax
     FROM case_providers cp
     JOIN cases c ON c.id = cp.case_id
     JOIN providers p ON p.id = cp.provider_id
     WHERE cp.id IN ({$placeholders})",
    $caseProviderIds
);

if (count($caseProviders) !== count($caseProviderIds)) {
    errorResponse('One or more case_provider records not found', 404);
}

// Validate all belong to same provider
$providerIds = array_unique(array_column($caseProviders, 'provider_id'));
if (count($providerIds) > 1) {
    $providerNames = array_unique(array_column($caseProviders, 'provider_name'));
    errorResponse('Selected cases must be from same provider. Found: ' . implode(', ', $providerNames), 422);
}

// Build provider address
$providerAddress = $caseProviders[0]['provider_address'] ?? '';
if ($caseProviders[0]['provider_city'] || $caseProviders[0]['provider_state'] || $caseProviders[0]['provider_zip']) {
    $cityStateZip = trim(($caseProviders[0]['provider_city'] ?? '') . ', ' . ($caseProviders[0]['provider_state'] ?? '') . ' ' . ($caseProviders[0]['provider_zip'] ?? ''));
    if ($providerAddress) {
        $providerAddress .= "\n" . $cityStateZip;
    } else {
        $providerAddress = $cityStateZip;
    }
}

// Prepare case data for combined letter
$casesData = [];
foreach ($caseProviders as $cp) {
    $casesData[] = [
        'case_number' => $cp['case_number'],
        'client_name' => $cp['client_name'],
        'doi' => $cp['doi'],
        'treatment_start_date' => $cp['treatment_start_date'],
        'treatment_end_date' => $cp['treatment_end_date'],
        'provider_name' => $cp['provider_name'],
        'provider_address' => $providerAddress,
        'attorney_name' => $cp['attorney_name']
    ];
}

// Get sender info
$sender = dbFetchOne("SELECT full_name, smtp_email FROM users WHERE id = ?", [$_SESSION['user_id']]);

$commonData = [
    'request_date' => $input['request_date'],
    'request_type' => $input['request_type'],
    'request_method' => $input['request_method'] ?? 'email',
    'authorization_sent' => !empty($input['authorization_sent']),
    'notes' => $input['notes'] ?? null,
    'sender_name' => $sender['full_name'] ?? '',
    'sender_email' => $sender['smtp_email'] ?? '',
];

// Render ONE combined letter for preview
$templateId = !empty($input['template_id']) ? (int)$input['template_id'] : null;
$html = renderBulkRequestLetter($casesData, $commonData, $templateId);

successResponse([
    'letter_html' => $html,
    'provider_name' => $caseProviders[0]['provider_name'],
    'case_count' => count($casesData)
], 'Generated combined preview for ' . count($casesData) . ' case(s)');
