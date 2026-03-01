<?php
/**
 * POST /api/requests
 * Create a new record request
 */
require_once __DIR__ . '/../../helpers/date.php';

$userId = requireAuth();
$input  = getInput();

$errors = validateRequired($input, ['case_provider_id', 'request_date', 'request_method']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$cpId = (int)$input['case_provider_id'];
$cp   = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$cpId]);
if (!$cp) errorResponse('Case-provider not found', 404);

if (!validateDate($input['request_date'])) errorResponse('Invalid request_date');

$validMethods = ['email','fax','portal','phone','mail','chartswap','online'];
if (!validateEnum($input['request_method'], $validMethods)) {
    errorResponse('Invalid request_method');
}

$data = [
    'case_provider_id'  => $cpId,
    'request_date'      => $input['request_date'],
    'request_method'    => $input['request_method'],
    'send_status'       => 'draft',
    'requested_by'      => $userId,
    'next_followup_date'=> calculateNextFollowup($input['request_date']),
];

// Optional fields
if (!empty($input['request_type'])) {
    $validTypes = ['initial','follow_up','re_request','rfd'];
    if (!validateEnum($input['request_type'], $validTypes)) errorResponse('Invalid request_type');
    $data['request_type'] = $input['request_type'];
}
if (!empty($input['template_id']))        $data['template_id'] = (int)$input['template_id'];
if (!empty($input['sent_to']))            $data['sent_to'] = sanitizeString($input['sent_to']);
if (!empty($input['department']))         $data['department'] = sanitizeString($input['department']);
if (isset($input['authorization_sent']))  $data['authorization_sent'] = (int)$input['authorization_sent'];
if (isset($input['notes']))              $data['notes'] = sanitizeString($input['notes']);
if (!empty($input['letter_html']))        $data['letter_html'] = $input['letter_html'];
if (!empty($input['template_data']))      $data['template_data'] = json_encode($input['template_data']);

$id = dbInsert('record_requests', $data);

// Update case_provider status to 'requesting' if not already further along
$advanceStatuses = ['not_started'];
if (in_array($cp['overall_status'], $advanceStatuses)) {
    dbUpdate('case_providers', ['overall_status' => 'requesting'], 'id = ?', [$cpId]);
}

logActivity($userId, 'create', 'record_request', $id, [
    'case_provider_id' => $cpId,
    'method'           => $input['request_method'],
]);

successResponse(['id' => $id], 'Request created');
