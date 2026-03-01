<?php
// POST /api/messages - Send a new message
$userId = requireAuth();

$input = getInput();
$errors = validateRequired($input, ['to_user_id', 'subject', 'message']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

$toUserId = (int)$input['to_user_id'];
$subject = sanitizeString($input['subject']);
$message = sanitizeString($input['message']);

if (strlen($subject) > 200) {
    errorResponse('Subject must be 200 characters or less');
}
if (strlen($message) > 5000) {
    errorResponse('Message must be 5000 characters or less');
}

// Verify recipient exists and is active
$recipient = dbFetchOne("SELECT id, full_name FROM users WHERE id = ? AND is_active = 1", [$toUserId]);
if (!$recipient) {
    errorResponse('Recipient not found', 404);
}

// Cannot send to self
if ($toUserId === $userId) {
    errorResponse('Cannot send message to yourself');
}

$newId = dbInsert('messages', [
    'from_user_id' => $userId,
    'to_user_id' => $toUserId,
    'subject' => $subject,
    'message' => $message
]);

logActivity($userId, 'sent_message', 'message', $newId, [
    'to' => $recipient['full_name'],
    'subject' => $subject
]);

successResponse(['id' => $newId], 'Message sent');
