<?php
/**
 * GET /api/requests
 * List record requests by ?case_provider_id
 */
$userId = requireAuth();

$cpId = (int)($_GET['case_provider_id'] ?? 0);
if (!$cpId) errorResponse('case_provider_id is required');

$cp = dbFetchOne("SELECT id FROM case_providers WHERE id = ?", [$cpId]);
if (!$cp) errorResponse('Case-provider not found', 404);

$rows = dbFetchAll("
    SELECT rr.*,
           u.full_name AS requested_by_name
    FROM record_requests rr
    LEFT JOIN users u ON u.id = rr.requested_by
    WHERE rr.case_provider_id = ?
    ORDER BY rr.request_date DESC
", [$cpId]);

successResponse($rows);
