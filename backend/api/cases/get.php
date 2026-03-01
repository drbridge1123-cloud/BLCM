<?php
/**
 * GET /api/cases/{id}
 * Get a single case with assigned user name and provider counts
 */
$userId = requireAuth();
$id = (int)$_GET['id'];

$case = dbFetchOne(
    "SELECT c.*,
            u.full_name AS assigned_name,
            (SELECT COUNT(*) FROM case_providers cp WHERE cp.case_id = c.id) AS provider_count,
            (SELECT COUNT(*) FROM case_providers cp WHERE cp.case_id = c.id AND cp.overall_status NOT IN ('received_complete','verified')) AS pending_count
     FROM cases c
     LEFT JOIN users u ON c.assigned_to = u.id
     WHERE c.id = ?",
    [$id]
);

if (!$case) errorResponse('Case not found', 404);

$case['provider_count'] = (int)$case['provider_count'];
$case['pending_count']  = (int)$case['pending_count'];

successResponse($case);
