<?php
// PUT /api/notifications/read-all - Mark all notifications as read
$userId = requireAuth();

dbQuery("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0", [$userId]);

successResponse(null, 'All notifications marked as read');
