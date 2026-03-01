<?php
/**
 * GET /api/documents
 * List case documents by ?case_id. Optional: case_provider_id, document_type.
 */
$userId = requireAuth();

$caseId = (int)($_GET['case_id'] ?? 0);
if (!$caseId) errorResponse('case_id is required');

$case = dbFetchOne("SELECT id FROM cases WHERE id = ?", [$caseId]);
if (!$case) errorResponse('Case not found', 404);

$where  = 'cd.case_id = ?';
$params = [$caseId];

// Optional: filter by case_provider_id
if (!empty($_GET['case_provider_id'])) {
    $where .= ' AND cd.case_provider_id = ?';
    $params[] = (int)$_GET['case_provider_id'];
}

// Optional: filter by document_type
if (!empty($_GET['document_type'])) {
    $validTypes = ['hipaa_authorization','signed_release','other'];
    if (validateEnum($_GET['document_type'], $validTypes)) {
        $where .= ' AND cd.document_type = ?';
        $params[] = $_GET['document_type'];
    }
}

$rows = dbFetchAll("
    SELECT cd.*,
           u.full_name AS uploaded_by_name
    FROM case_documents cd
    LEFT JOIN users u ON u.id = cd.uploaded_by
    WHERE {$where}
    ORDER BY cd.created_at DESC
", $params);

successResponse($rows);
