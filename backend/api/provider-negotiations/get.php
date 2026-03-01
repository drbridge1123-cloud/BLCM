<?php
// GET /api/provider-negotiations/{case_id} - Get provider negotiations for a case
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('case_id is required');
}

$negotiations = dbFetchAll(
    "SELECT pn.*, ml.balance AS mbds_balance, ml.charges AS mbds_charges
     FROM provider_negotiations pn
     LEFT JOIN mbds_lines ml ON pn.mbds_line_id = ml.id
     WHERE pn.case_id = ?
     ORDER BY pn.provider_name",
    [$caseId]
);

jsonResponse([
    'success' => true,
    'negotiations' => $negotiations,
]);
