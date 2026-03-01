<?php
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$requestId = (int)($_GET['id'] ?? 0);
if (!$requestId) {
    errorResponse('Request ID is required');
}

$input = getInput();

$letterData = getRequestLetterData($requestId);
if (!$letterData) {
    errorResponse('Request not found', 404);
}

if (!in_array($letterData['request_method'], ['email', 'fax'])) {
    errorResponse('Only email and fax requests can be sent through the system');
}

if ($letterData['send_status'] === 'sent') {
    errorResponse('This request has already been sent successfully');
}

// Determine recipient
$recipient = !empty($input['recipient'])
    ? sanitizeString($input['recipient'])
    : ($letterData['sent_to'] ?: ($letterData['request_method'] === 'email'
        ? $letterData['provider_email']
        : $letterData['provider_fax']));

if (empty($recipient)) {
    $label = $letterData['request_method'] === 'email' ? 'email address' : 'fax number';
    errorResponse("No recipient {$label} specified");
}

// Add sender info for template placeholders
$senderInfo = dbFetchOne("SELECT full_name, smtp_email FROM users WHERE id = ?", [$userId]);
if ($senderInfo) {
    $letterData['sender_name'] = $senderInfo['full_name'] ?? '';
    $letterData['sender_email'] = $senderInfo['smtp_email'] ?? '';
}

// Check if user sent edited letter content
$userEditedHtml = !empty($input['letter_html']) ? $input['letter_html'] : null;
$userEditedSubject = isset($input['subject']) ? sanitizeString($input['subject']) : null;
$isEdited = false;

if ($userEditedHtml) {
    // Use user-edited HTML (sanitized)
    $html = sanitizeLetterHtml($userEditedHtml);
    $subject = $userEditedSubject ?: '';
    $isEdited = true;
} else {
    // Render letter from template (existing behavior)
    $subject = '';
    if (!empty($letterData['template_id'])) {
        $result = renderLetterFromTemplate($letterData['template_id'], $letterData);
        $html = $result['html'];
        $subject = $result['subject'];
    } else {
        $html = renderRequestLetter($letterData);
    }
    // Allow subject override even without body edit
    if ($userEditedSubject !== null) {
        $subject = $userEditedSubject;
        $isEdited = true;
    }
}

// Load attachments
$attachments = [];

// Load additional document attachments (HIPAA, releases, etc.)
$attachmentRecords = dbFetchAll(
    "SELECT cd.file_path, cd.original_file_name
     FROM request_attachments ra
     JOIN case_documents cd ON ra.case_document_id = cd.id
     WHERE ra.record_request_id = ?",
    [$requestId]
);

foreach ($attachmentRecords as $att) {
    $fullPath = __DIR__ . '/../../../storage/' . $att['file_path'];
    if (file_exists($fullPath)) {
        $attachments[] = [
            'path' => $fullPath,
            'name' => $att['original_file_name']
        ];
    }
}

// Auto-attach Balance Verification Form for balance_verification templates
if (!empty($letterData['template_id'])) {
    $tplInfo = dbFetchOne("SELECT template_type FROM letter_templates WHERE id = ?", [$letterData['template_id']]);
    if ($tplInfo && $tplInfo['template_type'] === 'balance_verification') {
        $bvFormPath = __DIR__ . '/../../../storage/templates/balance_verification_form.pdf';
        if (file_exists($bvFormPath)) {
            $attachments[] = [
                'path' => $bvFormPath,
                'name' => 'Balance_Verification_Form.pdf'
            ];
        }
    }
}

// Mark as sending
dbUpdate('record_requests', [
    'send_status' => 'sending',
    'sent_to' => $recipient
], 'id = ?', [$requestId]);

// Send
$result = ['success' => false, 'error' => 'Unknown method'];

if ($letterData['request_method'] === 'email') {
    // Use template subject if available, otherwise generate default
    if (empty($subject)) {
        $doiFormatted = !empty($letterData['doi']) ? date('m/d/Y', strtotime($letterData['doi'])) : '';
        $subject = 'Medical Records Request - ' . $letterData['client_name'];
        if ($doiFormatted) {
            $subject .= ' (DOI: ' . $doiFormatted . ')';
        }
    }
    // Use per-user SMTP if configured
    $emailOptions = [];
    $sender = dbFetchOne("SELECT full_name, smtp_email, smtp_app_password FROM users WHERE id = ?", [$userId]);
    if ($sender && !empty($sender['smtp_email']) && !empty($sender['smtp_app_password'])) {
        $emailOptions['smtp_email'] = $sender['smtp_email'];
        $emailOptions['smtp_password'] = $sender['smtp_app_password'];
        $emailOptions['from_name'] = $sender['full_name'];
    }
    // Add attachments if any
    if (!empty($attachments)) {
        $emailOptions['attachments'] = $attachments;
    }
    $result = sendEmail($recipient, $subject, $html, $emailOptions);
} elseif ($letterData['request_method'] === 'fax') {
    // Generate PDF version of the letter for fax (fax needs PDF, not HTML)
    require_once __DIR__ . '/../../helpers/pdf-generator.php';
    $letterPdfPath = saveLetterPDF($html, $letterData['case_number'] ?? '', $letterData['provider_name'] ?? '');
    $faxAttachments = [];
    if ($letterPdfPath) {
        $faxAttachments[] = ['path' => $letterPdfPath, 'name' => 'Medical_Records_Request.pdf'];
    }
    $faxAttachments = array_merge($faxAttachments, $attachments);
    $result = sendFax($recipient, $html, ['attachments' => $faxAttachments]);
}

// Log the attempt
dbInsert('send_log', [
    'record_request_id' => $requestId,
    'send_method'       => $letterData['request_method'],
    'recipient'         => $recipient,
    'status'            => $result['success'] ? 'success' : 'failed',
    'external_id'       => $result['message_id'] ?? $result['fax_id'] ?? null,
    'error_message'     => $result['error'] ?? null,
    'sent_by'           => $userId
]);

// Get current attempts count
$current = dbFetchOne("SELECT send_attempts FROM record_requests WHERE id = ?", [$requestId]);
$attempts = (($current['send_attempts'] ?? 0) + 1);

if ($result['success']) {
    dbUpdate('record_requests', [
        'send_status'   => 'sent',
        'sent_at'       => date('Y-m-d H:i:s'),
        'send_error'    => null,
        'send_attempts' => $attempts,
        'letter_html'   => $html
    ], 'id = ?', [$requestId]);

    logActivity($userId, 'request_delivered', 'record_request', $requestId, [
        'method'    => $letterData['request_method'],
        'recipient' => $recipient,
        'edited'    => $isEdited
    ]);

    // Auto-update provider status based on request type
    if (!empty($letterData['case_provider_id'])) {
        $autoStatus = match($letterData['request_type'] ?? '') {
            'initial' => 'requesting',
            'follow_up', 're_request', 'rfd' => 'follow_up',
            default => null,
        };
        if ($autoStatus) {
            dbUpdate('case_providers', ['overall_status' => $autoStatus], 'id = ?', [$letterData['case_provider_id']]);
        }
    }

    successResponse([
        'send_status' => 'sent',
        'sent_at'     => date('Y-m-d H:i:s')
    ], 'Request sent successfully via ' . $letterData['request_method']);
} else {
    dbUpdate('record_requests', [
        'send_status'   => 'failed',
        'send_error'    => $result['error'],
        'send_attempts' => $attempts,
        'letter_html'   => $html
    ], 'id = ?', [$requestId]);

    logActivity($userId, 'request_send_failed', 'record_request', $requestId, [
        'method'    => $letterData['request_method'],
        'recipient' => $recipient,
        'error'     => $result['error'],
        'edited'    => $isEdited
    ]);

    errorResponse('Failed to send: ' . $result['error'], 422);
}
