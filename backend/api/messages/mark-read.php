<?php
// PUT /api/messages/{id}/read  OR  PUT /api/messages/read-all
$userId = requireAuth();

$id = $_GET['id'] ?? null;

if ($id === 'read-all') {
    // Mark all received messages as read
    dbQuery(
        "UPDATE messages SET is_read = 1, read_at = NOW() WHERE to_user_id = ? AND is_read = 0",
        [$userId]
    );
    successResponse(null, 'All messages marked as read');
} else {
    $msgId = (int)$id;
    if (!$msgId) {
        errorResponse('Message ID is required', 400);
    }

    $msg = dbFetchOne("SELECT * FROM messages WHERE id = ?", [$msgId]);
    if (!$msg) {
        errorResponse('Message not found', 404);
    }

    // Only recipient can mark as read
    if ((int)$msg['to_user_id'] !== $userId) {
        errorResponse('Not authorized', 403);
    }

    dbUpdate('messages', [
        'is_read' => 1,
        'read_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$msgId]);

    successResponse(null, 'Message marked as read');
}
