<?php
// GET /api/tracker/pending-assignments
// Returns case_providers with assignment_status='pending' for the current user
$userId = requireAuth();

$rows = dbFetchAll(
    "SELECT cp.id, cp.case_id, cp.overall_status, cp.deadline, cp.assignment_status,
            cp.request_mr, cp.request_bill, cp.request_chart, cp.request_img, cp.request_op,
            c.case_number, c.client_name,
            p.name AS provider_name, p.type AS provider_type,
            ab.full_name AS activated_by_name
     FROM case_providers cp
     JOIN cases c ON c.id = cp.case_id
     JOIN providers p ON p.id = cp.provider_id
     LEFT JOIN users ab ON ab.id = cp.activated_by
     WHERE cp.assigned_to = ? AND cp.assignment_status = 'pending'
     ORDER BY cp.created_at DESC",
    [$userId]
);

successResponse($rows);
