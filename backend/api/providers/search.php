<?php
/**
 * GET /api/providers/search?q=term
 * Autocomplete search for providers. Returns up to 10 matches.
 */
requireAuth();

$q = $_GET['q'] ?? '';
if (strlen(trim($q)) === 0) {
    successResponse([]);
}

$providers = dbFetchAll(
    "SELECT id, name, type
     FROM providers
     WHERE name LIKE ?
     ORDER BY name ASC
     LIMIT 10",
    ["%{$q}%"]
);

successResponse($providers);
