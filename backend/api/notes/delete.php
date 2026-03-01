<?php
/**
 * DELETE /api/notes/{id}
 * Delete a case note (author or admin only)
 */
$userId = requireAuth();
$id     = (int)$_GET['id'];

$note = dbFetchOne("SELECT * FROM case_notes WHERE id = ?", [$id]);
if (!$note) errorResponse('Note not found', 404);

// Only the author or admin can delete
if ($note['user_id'] !== $userId && $_SESSION['user_role'] !== 'admin') {
    errorResponse('You can only delete your own notes', 403);
}

dbDelete('case_notes', 'id = ?', [$id]);

logActivity($userId, 'delete', 'case_note', $id, [
    'case_id' => $note['case_id'],
]);

successResponse(null, 'Note deleted');
