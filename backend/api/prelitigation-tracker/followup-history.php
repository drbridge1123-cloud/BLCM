<?php
// GET /api/prelitigation-tracker/followup-history?case_id=X
$userId = requireAuth();
requirePermission('prelitigation_tracker');
$caseId = (int)($_GET['case_id'] ?? 0);
if (!$caseId) errorResponse('case_id required', 400);

$history = dbFetchAll(
    "SELECT pf.*, u.full_name AS created_by_name
     FROM prelitigation_followups pf
     LEFT JOIN users u ON u.id = pf.created_by
     WHERE pf.case_id = ?
     ORDER BY pf.followup_date DESC, pf.id DESC",
    [$caseId]
);

successResponse($history);
