<?php
// PUT /api/notifications/{id}/read - Mark notification as read
$userId = requireAuth();
$id = (int)($_GET['id'] ?? 0);

if (!$id) errorResponse('Notification ID required', 400);

$notif = dbFetchOne("SELECT * FROM notifications WHERE id = ? AND user_id = ?", [$id, $userId]);
if (!$notif) errorResponse('Notification not found', 404);

dbUpdate('notifications', ['is_read' => 1], 'id = ?', [$id]);

successResponse(null, 'Marked as read');
