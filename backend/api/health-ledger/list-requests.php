<?php
/**
 * GET /api/health-ledger/{id}/requests
 * List all requests for a health ledger item
 */
$userId = requireAuth();
requirePermission('health_tracker');

$itemId = (int)($_GET['id'] ?? 0);
if (!$itemId) errorResponse('Item ID is required');

$item = dbFetchOne("SELECT id FROM health_ledger_items WHERE id = ?", [$itemId]);
if (!$item) errorResponse('Health ledger item not found', 404);

$rows = dbFetchAll("
    SELECT hr.*, u.full_name AS created_by_name
    FROM hl_requests hr
    LEFT JOIN users u ON u.id = hr.created_by
    WHERE hr.item_id = ?
    ORDER BY hr.created_at DESC
", [$itemId]);

successResponse($rows);
