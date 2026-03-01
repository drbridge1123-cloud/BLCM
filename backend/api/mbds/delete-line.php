<?php
/**
 * DELETE /api/mbds/{id}/delete-line
 * Delete an MBDS line item
 */
$userId = requireAuth();
requirePermission('mbds');

$input = getInput();

$lineId = (int)($input['line_id'] ?? $_GET['line_id'] ?? 0);
if (!$lineId) errorResponse('line_id is required');

$line = dbFetchOne("SELECT * FROM mbds_lines WHERE id = ?", [$lineId]);
if (!$line) errorResponse('Line not found', 404);

// Verify parent report exists
$report = dbFetchOne("SELECT id FROM mbds_reports WHERE id = ?", [$line['report_id']]);
if (!$report) errorResponse('MBDS report not found', 404);

dbDelete('mbds_lines', 'id = ?', [$lineId]);

logActivity($userId, 'delete', 'mbds_line', $lineId, [
    'report_id'     => $line['report_id'],
    'line_type'     => $line['line_type'],
    'provider_name' => $line['provider_name'],
]);

successResponse(null, 'Line deleted successfully');
