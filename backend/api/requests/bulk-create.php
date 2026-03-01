<?php
// POST /api/requests/bulk-create - Create multiple follow-up requests at once
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$input = getInput();

// Validate required fields
$errors = validateRequired($input, ['requests', 'request_date', 'request_method', 'request_type']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors), 400);
}

if (!is_array($input['requests']) || empty($input['requests'])) {
    errorResponse('Requests must be a non-empty array', 400);
}

if (!validateDate($input['request_date'])) {
    errorResponse('Invalid request_date format', 400);
}

// Validate request_method
$allowedMethods = ['email', 'fax', 'portal', 'phone', 'mail'];
if (!validateEnum($input['request_method'], $allowedMethods)) {
    errorResponse('Invalid request_method. Allowed: ' . implode(', ', $allowedMethods), 422);
}

// Validate request_type
$allowedTypes = ['initial', 'follow_up', 're_request', 'rfd'];
if (!validateEnum($input['request_type'], $allowedTypes)) {
    errorResponse('Invalid request_type. Allowed: ' . implode(', ', $allowedTypes), 422);
}

// Extract case_provider_ids
$caseProviderIds = [];
$recipientMap = [];
foreach ($input['requests'] as $req) {
    if (empty($req['case_provider_id'])) {
        errorResponse('Each request must have a case_provider_id', 400);
    }
    $cpId = (int)$req['case_provider_id'];
    $caseProviderIds[] = $cpId;

    // Store custom recipient if provided
    if (!empty($req['recipient'])) {
        $recipientMap[$cpId] = sanitizeString($req['recipient']);
    }
}

// Load all case_provider records with provider and case info
$placeholders = implode(',', array_fill(0, count($caseProviderIds), '?'));
$caseProviders = dbFetchAll(
    "SELECT cp.id, cp.case_id, cp.provider_id, cp.overall_status,
            cp.treatment_start_date, cp.treatment_end_date, cp.record_types_needed,
            c.case_number, c.client_name, c.client_dob, c.doi, c.attorney_name,
            p.name AS provider_name, p.email AS provider_email, p.fax AS provider_fax,
            p.address AS provider_address, p.city AS provider_city,
            p.state AS provider_state, p.zip AS provider_zip
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

$providerName = $caseProviders[0]['provider_name'];
$defaultEmail = $caseProviders[0]['provider_email'];
$defaultFax = $caseProviders[0]['provider_fax'];

// Determine recipient - use first custom recipient or default
$recipient = null;
if (!empty($recipientMap)) {
    $recipient = reset($recipientMap); // Get first custom recipient
} else {
    // Auto-detect from provider
    if ($input['request_method'] === 'email') {
        $recipient = $defaultEmail;
    } elseif ($input['request_method'] === 'fax') {
        $recipient = $defaultFax;
    }
}

// Calculate next followup date if provided
$nextFollowupDate = null;
if (!empty($input['next_followup_date'])) {
    if (!validateDate($input['next_followup_date'])) {
        errorResponse('Invalid next_followup_date format', 400);
    }
    $nextFollowupDate = $input['next_followup_date'];
} else {
    $nextFollowupDate = calculateNextFollowup($input['request_date']);
}

// Validate followup date >= request date
if ($nextFollowupDate && $nextFollowupDate < $input['request_date']) {
    errorResponse('next_followup_date must be >= request_date', 400);
}

$pdo = getDBConnection();
$pdo->beginTransaction();

try {
    $createdRequestIds = [];
    $autoSend = !empty($input['auto_send']);

    // Create individual request records for each case
    foreach ($caseProviders as $cp) {
        $cpId = (int)$cp['id'];

        $requestData = [
            'case_provider_id' => $cpId,
            'request_date' => $input['request_date'],
            'request_method' => $input['request_method'],
            'request_type' => $input['request_type'],
            'requested_by' => $userId,
            'send_status' => 'draft',
            'next_followup_date' => $nextFollowupDate
        ];

        if ($recipient) {
            $requestData['sent_to'] = $recipient;
        }

        if (!empty($input['notes'])) {
            $requestData['notes'] = sanitizeString($input['notes']);
        }

        if (!empty($input['authorization_sent'])) {
            $requestData['authorization_sent'] = 1;
        }

        $requestId = dbInsert('record_requests', $requestData);
        $createdRequestIds[] = $requestId;

        // Update case_provider status
        $newStatus = ($input['request_type'] === 'follow_up') ? 'follow_up' : 'requesting';
        dbUpdate('case_providers', ['overall_status' => $newStatus], 'id = ?', [$cpId]);

        // Log activity for creation
        logActivity($userId, 'bulk_request_created', 'record_request', $requestId, [
            'case_provider_id' => $cpId,
            'case_number' => $cp['case_number'],
            'provider_name' => $providerName
        ]);
    }

    // Auto-send if requested AND method is email/fax
    $sentSuccess = false;
    $sendError = null;
    $externalId = null;

    if ($autoSend && in_array($input['request_method'], ['email', 'fax']) && $recipient) {
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

        $commonData = [
            'request_date' => $input['request_date'],
            'request_type' => $input['request_type'],
            'authorization_sent' => !empty($input['authorization_sent']),
            'notes' => $input['notes'] ?? null
        ];

        // Render ONE combined letter
        $templateId = !empty($input['template_id']) ? (int)$input['template_id'] : null;
        $html = renderBulkRequestLetter($casesData, $commonData, $templateId);

        // Update all requests to sending status
        foreach ($createdRequestIds as $reqId) {
            dbUpdate('record_requests', ['send_status' => 'sending'], 'id = ?', [$reqId]);
        }

        // Send ONE email/fax
        $result = ['success' => false, 'error' => 'Unknown method'];

        if ($input['request_method'] === 'email') {
            $subject = 'Medical Records Request - Multiple Cases - ' . $providerName;

            $emailOptions = [];
            $sender = dbFetchOne("SELECT full_name, smtp_email, smtp_app_password FROM users WHERE id = ?", [$userId]);
            if ($sender && !empty($sender['smtp_email']) && !empty($sender['smtp_app_password'])) {
                $emailOptions['smtp_email'] = $sender['smtp_email'];
                $emailOptions['smtp_password'] = $sender['smtp_app_password'];
                $emailOptions['from_name'] = $sender['full_name'];
            }
            $result = sendEmail($recipient, $subject, $html, $emailOptions);
        } elseif ($input['request_method'] === 'fax') {
            $result = sendFax($recipient, $html);
        }

        $sentSuccess = $result['success'];
        $sendError = $result['error'] ?? null;
        $externalId = $result['message_id'] ?? $result['fax_id'] ?? null;

        // Log to send_log for EACH request (all point to same external_id)
        foreach ($createdRequestIds as $reqId) {
            dbInsert('send_log', [
                'record_request_id' => $reqId,
                'send_method' => $input['request_method'],
                'recipient' => $recipient,
                'status' => $sentSuccess ? 'success' : 'failed',
                'external_id' => $externalId,
                'error_message' => $sendError,
                'sent_by' => $userId
            ]);
        }

        // Update all requests based on result
        if ($sentSuccess) {
            foreach ($createdRequestIds as $reqId) {
                dbUpdate('record_requests', [
                    'send_status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s'),
                    'send_error' => null,
                    'send_attempts' => 1,
                    'letter_html' => $html
                ], 'id = ?', [$reqId]);

                logActivity($userId, 'bulk_request_sent', 'record_request', $reqId, [
                    'method' => $input['request_method'],
                    'recipient' => $recipient,
                    'combined' => true,
                    'total_cases' => count($createdRequestIds)
                ]);
            }
        } else {
            foreach ($createdRequestIds as $reqId) {
                dbUpdate('record_requests', [
                    'send_status' => 'failed',
                    'send_error' => $sendError,
                    'send_attempts' => 1,
                    'letter_html' => $html
                ], 'id = ?', [$reqId]);

                logActivity($userId, 'bulk_request_send_failed', 'record_request', $reqId, [
                    'method' => $input['request_method'],
                    'recipient' => $recipient,
                    'error' => $sendError,
                    'combined' => true
                ]);
            }
        }
    }

    $pdo->commit();

    $message = "Created " . count($createdRequestIds) . " " . $input['request_type'] . " request(s) for {$providerName}";
    if ($autoSend) {
        if ($sentSuccess) {
            $message .= ". Successfully sent 1 combined " . $input['request_method'];
        } else {
            $message .= ". Failed to send: " . ($sendError ?? 'Unknown error');
        }
    }

    successResponse([
        'created_count' => count($createdRequestIds),
        'sent_count' => $sentSuccess ? 1 : 0,
        'failed_count' => $sentSuccess ? 0 : 1,
        'request_ids' => $createdRequestIds,
        'provider_name' => $providerName,
        'combined_send' => true
    ], $message);

} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Failed to create bulk requests: ' . $e->getMessage(), 500);
}
