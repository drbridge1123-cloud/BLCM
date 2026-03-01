<?php
/**
 * GET /api/deadline-requests
 * List deadline extension requests
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$user = getCurrentUser();

$status = $_GET['status'] ?? null;

$where  = '1=1';
$params = [];

if (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND dr.user_id = ?';
    $params[] = $userId;
}

if ($status && $status !== 'all') {
    $where .= ' AND dr.status = ?';
    $params[] = $status;
}

$rows = dbFetchAll("
    SELECT dr.*, u.full_name AS requested_by_name, r.full_name AS reviewed_by_name,
           ac.case_number, ac.client_name
    FROM deadline_extension_requests dr
    JOIN users u ON dr.user_id = u.id
    LEFT JOIN users r ON dr.reviewed_by = r.id
    LEFT JOIN attorney_cases ac ON dr.case_id = ac.id
    WHERE {$where}
    ORDER BY dr.created_at DESC
", $params);

successResponse($rows);
