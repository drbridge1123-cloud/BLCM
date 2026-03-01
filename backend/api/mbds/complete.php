<?php
/**
 * PUT /api/mbds/{id}/complete
 * Mark MBDS report as completed
 */
$userId = requireAuth();
requirePermission('mbds');

$id = (int)$_GET['id'];

$report = dbFetchOne("SELECT id, status FROM mbds_reports WHERE id = ?", [$id]);
if (!$report) errorResponse('MBDS report not found', 404);

if ($report['status'] !== 'draft') {
    errorResponse('Only draft reports can be marked as completed');
}

dbUpdate('mbds_reports', [
    'status'       => 'completed',
    'completed_by' => $userId,
    'completed_at' => date('Y-m-d H:i:s'),
], 'id = ?', [$id]);

logActivity($userId, 'complete', 'mbds_report', $id);

successResponse(null, 'MBDS report marked as completed');
