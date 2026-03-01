<?php
/**
 * GET /api/insurance-companies/{id}
 * Get a single insurance company with its adjusters
 */
requireAuth();

$id = (int)$_GET['id'];

$company = dbFetchOne(
    "SELECT id, name, type, phone, fax, email, address, city, state, zip,
            website, notes, created_at, updated_at
     FROM insurance_companies WHERE id = ?",
    [$id]
);

if (!$company) errorResponse('Insurance company not found', 404);

// Get associated adjusters
$company['adjusters'] = dbFetchAll(
    "SELECT id, first_name, last_name, title, adjuster_type, phone, fax, email,
            notes, is_active, created_at, updated_at
     FROM adjusters
     WHERE insurance_company_id = ?
     ORDER BY last_name, first_name",
    [$id]
);

foreach ($company['adjusters'] as &$a) {
    $a['is_active'] = (int)$a['is_active'];
}
unset($a);

successResponse($company);
