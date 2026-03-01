<?php
/**
 * GET /api/provider-negotiations?case_id=X
 * List provider negotiations for a case
 */
$userId = requireAuth();
requirePermission('cases');

$caseId = (int)($_GET['case_id'] ?? 0);
if (!$caseId) errorResponse('case_id is required');

$rows = dbFetchAll(
    "SELECT pn.*,
            ml.balance AS mbds_balance,
            ml.charges AS mbds_charges
     FROM provider_negotiations pn
     LEFT JOIN mbds_lines ml ON pn.mbds_line_id = ml.id
     WHERE pn.case_id = ?
     ORDER BY pn.provider_name ASC",
    [$caseId]
);

// Cast numeric fields
foreach ($rows as &$r) {
    $r['original_balance']    = $r['original_balance'] !== null ? (float)$r['original_balance'] : null;
    $r['requested_reduction'] = $r['requested_reduction'] !== null ? (float)$r['requested_reduction'] : null;
    $r['accepted_amount']     = $r['accepted_amount'] !== null ? (float)$r['accepted_amount'] : null;
    $r['reduction_percent']   = $r['reduction_percent'] !== null ? (float)$r['reduction_percent'] : null;
    $r['mbds_balance']        = $r['mbds_balance'] !== null ? (float)$r['mbds_balance'] : null;
    $r['mbds_charges']        = $r['mbds_charges'] !== null ? (float)$r['mbds_charges'] : null;
}
unset($r);

successResponse($rows);
