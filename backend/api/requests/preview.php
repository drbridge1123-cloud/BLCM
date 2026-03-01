<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$requestId = (int)($_GET['id'] ?? 0);
if (!$requestId) {
    errorResponse('Request ID is required');
}

$letterData = getRequestLetterData($requestId);
if (!$letterData) {
    errorResponse('Request not found', 404);
}

if (!in_array($letterData['request_method'], ['email', 'fax'])) {
    errorResponse('Only email and fax requests can be previewed');
}

// Add sender info for template placeholders
$sender = dbFetchOne("SELECT full_name, smtp_email FROM users WHERE id = ?", [$userId]);
if ($sender) {
    $letterData['sender_name'] = $sender['full_name'] ?? '';
    $letterData['sender_email'] = $sender['smtp_email'] ?? '';
}

// Use database template if specified, otherwise use hardcoded template
$subject = '';
if (!empty($letterData['template_id'])) {
    $result = renderLetterFromTemplate($letterData['template_id'], $letterData);
    $html = $result['html'];
    $subject = $result['subject'];
} else {
    $html = renderRequestLetter($letterData);
    // Generate default subject for hardcoded template
    $subject = 'Medical Records Request - ' . $letterData['client_name'];
}

$recipient = $letterData['sent_to']
    ?: ($letterData['request_method'] === 'email'
        ? $letterData['provider_email']
        : $letterData['provider_fax']);

successResponse([
    'request_id'    => (int)$requestId,
    'method'        => $letterData['request_method'],
    'recipient'     => $recipient ?: '',
    'provider_name' => $letterData['provider_name'],
    'client_name'   => $letterData['client_name'],
    'send_status'   => $letterData['send_status'],
    'subject'       => $subject,
    'letter_html'   => $html
]);
