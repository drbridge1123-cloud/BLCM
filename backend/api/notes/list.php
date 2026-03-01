<?php
/**
 * GET /api/notes
 * List case notes by ?case_id. Optional: case_provider_id, note_type filters.
 */
$userId = requireAuth();

$caseId = (int)($_GET['case_id'] ?? 0);
if (!$caseId) errorResponse('case_id is required');

$case = dbFetchOne("SELECT id FROM cases WHERE id = ?", [$caseId]);
if (!$case) errorResponse('Case not found', 404);

$where  = 'cn.case_id = ?';
$params = [$caseId];

// Optional: filter by case_provider_id
if (!empty($_GET['case_provider_id'])) {
    $where .= ' AND cn.case_provider_id = ?';
    $params[] = (int)$_GET['case_provider_id'];
}

// Optional: filter by note_type
if (!empty($_GET['note_type'])) {
    $validTypes = ['general','follow_up','issue','handoff'];
    if (validateEnum($_GET['note_type'], $validTypes)) {
        $where .= ' AND cn.note_type = ?';
        $params[] = $_GET['note_type'];
    }
}

$rows = dbFetchAll("
    SELECT cn.*,
           u.full_name AS author_name,
           p.name AS provider_name
    FROM case_notes cn
    JOIN users u ON u.id = cn.user_id
    LEFT JOIN case_providers cp ON cp.id = cn.case_provider_id
    LEFT JOIN providers p ON p.id = cp.provider_id
    WHERE {$where}
    ORDER BY cn.contact_date DESC, cn.created_at DESC
", $params);

successResponse($rows);
