<?php
/**
 * DELETE /api/health-ledger/{id}
 * Delete a health ledger item and its associated requests
 */
$userId = requireAuth();
requirePermission('health_tracker');

$itemId = (int)($_GET['id'] ?? 0);
if (!$itemId) errorResponse('Item ID is required');

$item = dbFetchOne(
    "SELECT id, client_name, insurance_carrier FROM health_ledger_items WHERE id = ?",
    [$itemId]
);
if (!$item) errorResponse('Health ledger item not found', 404);

// Delete associated requests first
dbDelete('hl_requests', 'item_id = ?', [$itemId]);
dbDelete('health_ledger_items', 'id = ?', [$itemId]);

logActivity($userId, 'delete', 'health_ledger_item', $itemId, [
    'client_name' => $item['client_name'],
    'carrier'     => $item['insurance_carrier'],
]);

successResponse(null, 'Health ledger item deleted successfully');
