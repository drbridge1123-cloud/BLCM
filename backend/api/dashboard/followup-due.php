<?php
/**
 * GET /api/dashboard/followup-due
 * Returns case_providers that are due for follow-up
 */
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$rows = dbFetchAll(
    "SELECT cp.id, cp.case_id, cp.overall_status,
            c.case_number, c.client_name,
            p.name AS provider_name, p.type AS provider_type,
            u.full_name AS assigned_name,
            lr.request_date AS last_request_date,
            lr.next_followup_date,
            DATEDIFF(CURDATE(), lr.request_date) AS days_since_request
     FROM case_providers cp
     JOIN cases c ON cp.case_id = c.id
     JOIN providers p ON cp.provider_id = p.id
     LEFT JOIN users u ON cp.assigned_to = u.id
     INNER JOIN (
         SELECT r1.case_provider_id, r1.request_date, r1.next_followup_date
         FROM record_requests r1
         WHERE r1.id = (
             SELECT r2.id FROM record_requests r2
             WHERE r2.case_provider_id = r1.case_provider_id
             ORDER BY r2.request_date DESC, r2.created_at DESC
             LIMIT 1
         )
     ) lr ON lr.case_provider_id = cp.id
     WHERE cp.overall_status IN ('requesting', 'follow_up')
       AND lr.next_followup_date <= CURDATE()
     ORDER BY lr.next_followup_date ASC"
);

successResponse($rows);
