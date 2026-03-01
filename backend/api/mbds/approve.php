<?php
/**
 * PUT /api/mbds/{id}/approve
 * Approve a completed MBDS report
 */
$userId = requireAuth();
requirePermission('mbds');

$id = (int)$_GET['id'];

$report = dbFetchOne("SELECT id, status FROM mbds_reports WHERE id = ?", [$id]);
if (!$report) errorResponse('MBDS report not found', 404);

if ($report['status'] !== 'completed') {
    errorResponse('Only completed reports can be approved');
}

dbUpdate('mbds_reports', [
    'status'      => 'approved',
    'approved_by' => $userId,
    'approved_at' => date('Y-m-d H:i:s'),
], 'id = ?', [$id]);

logActivity($userId, 'approve', 'mbds_report', $id);

successResponse(null, 'MBDS report approved');
