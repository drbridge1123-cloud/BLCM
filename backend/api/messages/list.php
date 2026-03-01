<?php
// GET /api/messages - List messages for current user
$userId = requireAuth();

$filter = $_GET['filter'] ?? 'all'; // all, unread, sent

if ($filter === 'sent') {
    $rows = dbFetchAll(
        "SELECT m.*, uf.full_name AS from_name, ut.full_name AS to_name, 'sent' AS direction
         FROM messages m
         JOIN users uf ON uf.id = m.from_user_id
         JOIN users ut ON ut.id = m.to_user_id
         WHERE m.from_user_id = ?
         ORDER BY m.created_at DESC",
        [$userId]
    );
} elseif ($filter === 'unread') {
    $rows = dbFetchAll(
        "SELECT m.*, uf.full_name AS from_name, ut.full_name AS to_name, 'received' AS direction
         FROM messages m
         JOIN users uf ON uf.id = m.from_user_id
         JOIN users ut ON ut.id = m.to_user_id
         WHERE m.to_user_id = ? AND m.is_read = 0
         ORDER BY m.created_at DESC",
        [$userId]
    );
} else {
    // All: merge received + sent, avoid duplicates when from=to
    $received = dbFetchAll(
        "SELECT m.*, uf.full_name AS from_name, ut.full_name AS to_name, 'received' AS direction
         FROM messages m
         JOIN users uf ON uf.id = m.from_user_id
         JOIN users ut ON ut.id = m.to_user_id
         WHERE m.to_user_id = ?
         ORDER BY m.created_at DESC",
        [$userId]
    );
    $sent = dbFetchAll(
        "SELECT m.*, uf.full_name AS from_name, ut.full_name AS to_name, 'sent' AS direction
         FROM messages m
         JOIN users uf ON uf.id = m.from_user_id
         JOIN users ut ON ut.id = m.to_user_id
         WHERE m.from_user_id = ? AND m.to_user_id != ?
         ORDER BY m.created_at DESC",
        [$userId, $userId]
    );
    $rows = array_merge($received, $sent);
    usort($rows, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
}

// Unread count (always)
$unreadResult = dbFetchOne(
    "SELECT COUNT(*) AS cnt FROM messages WHERE to_user_id = ? AND is_read = 0",
    [$userId]
);

jsonResponse([
    'success' => true,
    'data' => $rows,
    'unread_count' => (int)$unreadResult['cnt']
]);
