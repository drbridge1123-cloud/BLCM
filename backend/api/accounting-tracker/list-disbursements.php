<?php
// GET /api/accounting-tracker/list-disbursements?case_id=X or ?attorney_case_id=X
$userId = requireAuth();
requirePermission('accounting_tracker');

$caseId = (int)($_GET['case_id'] ?? 0);
$attorneyCaseId = (int)($_GET['attorney_case_id'] ?? 0);

if (!$caseId && !$attorneyCaseId) errorResponse('case_id or attorney_case_id required', 400);

$where = $attorneyCaseId ? 'ad.attorney_case_id = ?' : 'ad.case_id = ?';
$param = $attorneyCaseId ?: $caseId;

$disbursements = dbFetchAll(
    "SELECT ad.*, u.full_name AS created_by_name, u2.full_name AS processed_by_name
     FROM accounting_disbursements ad
     LEFT JOIN users u ON u.id = ad.created_by
     LEFT JOIN users u2 ON u2.id = ad.processed_by
     WHERE {$where}
     ORDER BY ad.created_at DESC",
    [$param]
);

successResponse($disbursements);
