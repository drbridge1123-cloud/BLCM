<?php
// DELETE /api/messages/{id}
$userId = requireAuth();

$msgId = (int)($_GET['id'] ?? 0);
if (!$msgId) {
    errorResponse('Message ID is required', 400);
}

$msg = dbFetchOne("SELECT * FROM messages WHERE id = ?", [$msgId]);
if (!$msg) {
    errorResponse('Message not found', 404);
}

// Only recipient can delete
if ((int)$msg['to_user_id'] !== $userId) {
    errorResponse('Only the recipient can delete a message', 403);
}

dbQuery("DELETE FROM messages WHERE id = ?", [$msgId]);

logActivity($userId, 'deleted_message', 'message', $msgId, [
    'subject' => $msg['subject']
]);

successResponse(null, 'Message deleted');
