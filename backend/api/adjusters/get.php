<?php
/**
 * GET /api/adjusters/{id}
 * Fetch a single adjuster by ID with company name
 */
$userId = requireAuth();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    errorResponse('Adjuster ID is required');
}

$adjuster = dbFetchOne(
    "SELECT a.*, ic.name AS company_name
     FROM adjusters a
     LEFT JOIN insurance_companies ic ON ic.id = a.insurance_company_id
     WHERE a.id = ?",
    [$id]
);

if (!$adjuster) {
    errorResponse('Adjuster not found', 404);
}

successResponse($adjuster);
