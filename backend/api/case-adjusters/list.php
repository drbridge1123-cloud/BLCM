<?php
/**
 * GET /api/case-adjusters?case_id=X
 * List all adjusters linked to a case
 */
$userId = requireAuth();

$caseId = (int)($_GET['case_id'] ?? 0);
if (!$caseId) errorResponse('case_id is required');

$rows = dbFetchAll(
    "SELECT ca.id, ca.case_id, ca.coverage_type, ca.coverage_index, ca.adjuster_id,
            a.first_name, a.last_name, a.title, a.phone, a.fax, a.email,
            a.claim_number, a.adjuster_type, a.insurance_company_id,
            ic.name AS company_name
     FROM case_adjusters ca
     JOIN adjusters a ON a.id = ca.adjuster_id
     LEFT JOIN insurance_companies ic ON ic.id = a.insurance_company_id
     WHERE ca.case_id = ?
     ORDER BY FIELD(ca.coverage_type, '3rd_party','um','uim','pip','liability','pd','bi','dv'), ca.coverage_index",
    [$caseId]
);

successResponse($rows);
