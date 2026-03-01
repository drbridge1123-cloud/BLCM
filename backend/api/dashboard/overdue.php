<?php
/**
 * GET /api/dashboard/overdue
 * Returns case_providers past their deadline
 */
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$rows = dbFetchAll(
    "SELECT cp.id, cp.case_id, cp.deadline, cp.overall_status,
            c.case_number, c.client_name,
            p.name AS provider_name, p.type AS provider_type,
            u.full_name AS assigned_name,
            DATEDIFF(CURDATE(), cp.deadline) AS days_overdue,
            DATEDIFF(CURDATE(), cp.deadline) AS days_past_deadline
     FROM case_providers cp
     JOIN cases c ON cp.case_id = c.id
     JOIN providers p ON cp.provider_id = p.id
     LEFT JOIN users u ON cp.assigned_to = u.id
     WHERE cp.deadline < CURDATE()
       AND cp.overall_status NOT IN ('received_complete', 'verified')
     ORDER BY cp.deadline ASC"
);

foreach ($rows as &$row) {
    $daysPast = $row['days_past_deadline'] !== null ? (int)$row['days_past_deadline'] : null;
    $esc = getEscalationInfo($daysPast);
    $row['escalation_tier'] = $esc['tier'];
    $row['escalation_label'] = $esc['label'];
    $row['escalation_css'] = $esc['css'];
}
unset($row);

successResponse($rows);
