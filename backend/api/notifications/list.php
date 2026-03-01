<?php
// GET /api/notifications - List notifications for current user
$userId = requireAuth();

$unreadOnly = isset($_GET['unread_only']);
$limit = (int)($_GET['limit'] ?? 50);
if ($limit > 100) $limit = 100;

$where = 'user_id = ?';
$params = [$userId];

if ($unreadOnly) {
    $where .= ' AND is_read = 0';
}

$notifications = dbFetchAll(
    "SELECT * FROM notifications WHERE {$where} ORDER BY created_at DESC LIMIT {$limit}",
    $params
);

$unreadCount = dbCount('notifications', 'user_id = ? AND is_read = 0', [$userId]);

jsonResponse([
    'success' => true,
    'data' => $notifications,
    'unread_count' => (int)$unreadCount
]);
