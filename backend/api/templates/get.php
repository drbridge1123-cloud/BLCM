<?php
/**
 * GET /api/templates/{id}
 * Get a single letter template by ID
 */
$userId = requireAuth();
$id     = (int)$_GET['id'];

$template = dbFetchOne("
    SELECT lt.*,
           u.full_name AS created_by_name
    FROM letter_templates lt
    LEFT JOIN users u ON u.id = lt.created_by
    WHERE lt.id = ?
", [$id]);

if (!$template) errorResponse('Template not found', 404);

successResponse($template);
