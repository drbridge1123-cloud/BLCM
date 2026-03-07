<?php
/**
 * GET /api/dashboard/staff-metrics
 * Staff workload metrics - role-based view
 */
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$user = getCurrentUser();

$isStaff = !in_array($user['role'], ['admin', 'manager']);

if ($isStaff) {
    // Staff view: personal metrics only
    $myMetrics = dbFetchOne("
        SELECT
            u.id,
            COALESCE(u.display_name, u.full_name) AS full_name,
            COUNT(DISTINCT c.id) as my_cases,
            COUNT(DISTINCT CASE
                WHEN cp.overall_status NOT IN ('received_complete', 'verified')
                THEN cp.id
            END) as my_providers,
            COUNT(DISTINCT CASE
                WHEN cp.deadline < CURDATE()
                AND cp.overall_status NOT IN ('received_complete', 'verified')
                THEN cp.id
            END) as my_overdue,
            COUNT(DISTINCT CASE
                WHEN cp.overall_status IN ('requesting', 'follow_up')
                AND EXISTS (
                    SELECT 1 FROM record_requests rr
                    WHERE rr.case_provider_id = cp.id
                    AND rr.next_followup_date <= CURDATE()
                    ORDER BY rr.id DESC LIMIT 1
                )
                THEN cp.id
            END) as my_followup
        FROM users u
        LEFT JOIN cases c ON c.assigned_to = u.id AND c.status NOT IN ('closed')
        LEFT JOIN case_providers cp ON cp.assigned_to = u.id AND cp.assignment_status = 'accepted'
        WHERE u.id = ?
        GROUP BY u.id, COALESCE(u.display_name, u.full_name)
    ", [$userId]);

    // Team averages for comparison
    $teamAvg = dbFetchOne("
        SELECT
            AVG(case_count) as avg_cases,
            AVG(overdue_count) as avg_overdue,
            AVG(followup_count) as avg_followup
        FROM (
            SELECT
                u.id,
                COUNT(DISTINCT c.id) as case_count,
                COUNT(DISTINCT CASE
                    WHEN cp.deadline < CURDATE()
                    AND cp.overall_status NOT IN ('received_complete', 'verified')
                    THEN cp.id
                END) as overdue_count,
                COUNT(DISTINCT CASE
                    WHEN cp.overall_status IN ('requesting', 'follow_up')
                    AND EXISTS (
                        SELECT 1 FROM record_requests rr
                        WHERE rr.case_provider_id = cp.id
                        AND rr.next_followup_date <= CURDATE()
                        ORDER BY rr.id DESC LIMIT 1
                    )
                    THEN cp.id
                END) as followup_count
            FROM users u
            LEFT JOIN cases c ON c.assigned_to = u.id AND c.status NOT IN ('closed')
            LEFT JOIN case_providers cp ON cp.assigned_to = u.id
            WHERE u.is_active = 1 AND u.role NOT IN ('admin', 'manager')
            GROUP BY u.id
        ) t
    ");

    successResponse([
        'view_type' => 'personal',
        'my_metrics' => [
            'id' => (int)($myMetrics['id'] ?? 0),
            'full_name' => $myMetrics['full_name'] ?? '',
            'my_cases' => (int)($myMetrics['my_cases'] ?? 0),
            'my_providers' => (int)($myMetrics['my_providers'] ?? 0),
            'my_overdue' => (int)($myMetrics['my_overdue'] ?? 0),
            'my_followup' => (int)($myMetrics['my_followup'] ?? 0)
        ],
        'team_avg' => [
            'avg_cases' => round((float)($teamAvg['avg_cases'] ?? 0), 1),
            'avg_overdue' => round((float)($teamAvg['avg_overdue'] ?? 0), 1),
            'avg_followup' => round((float)($teamAvg['avg_followup'] ?? 0), 1)
        ]
    ]);

} else {
    // Manager/Admin view: all staff metrics
    $staffMetrics = dbFetchAll("
        SELECT
            u.id,
            COALESCE(u.display_name, u.full_name) AS full_name,
            u.role,
            COUNT(DISTINCT c.id) as case_count,
            COUNT(DISTINCT CASE
                WHEN cp.overall_status NOT IN ('received_complete', 'verified')
                THEN cp.id
            END) as provider_count,
            COUNT(DISTINCT CASE
                WHEN cp.deadline < CURDATE()
                AND cp.overall_status NOT IN ('received_complete', 'verified')
                THEN cp.id
            END) as overdue_count,
            COUNT(DISTINCT CASE
                WHEN cp.overall_status IN ('requesting', 'follow_up')
                AND EXISTS (
                    SELECT 1 FROM record_requests rr
                    WHERE rr.case_provider_id = cp.id
                    AND rr.next_followup_date <= CURDATE()
                    ORDER BY rr.id DESC LIMIT 1
                )
                THEN cp.id
            END) as followup_count
        FROM users u
        LEFT JOIN cases c ON c.assigned_to = u.id AND c.status NOT IN ('closed')
        LEFT JOIN case_providers cp ON cp.assigned_to = u.id AND cp.assignment_status = 'accepted'
        WHERE u.is_active = 1
        GROUP BY u.id, COALESCE(u.display_name, u.full_name), u.role
        ORDER BY u.role, COALESCE(u.display_name, u.full_name)
    ");

    $totals = [
        'total_cases' => 0,
        'total_providers' => 0,
        'total_overdue' => 0,
        'total_followup' => 0
    ];

    foreach ($staffMetrics as &$staff) {
        $staff['case_count'] = (int)$staff['case_count'];
        $staff['provider_count'] = (int)$staff['provider_count'];
        $staff['overdue_count'] = (int)$staff['overdue_count'];
        $staff['followup_count'] = (int)$staff['followup_count'];

        $totals['total_cases'] += $staff['case_count'];
        $totals['total_providers'] += $staff['provider_count'];
        $totals['total_overdue'] += $staff['overdue_count'];
        $totals['total_followup'] += $staff['followup_count'];
    }
    unset($staff);

    successResponse([
        'view_type' => 'team',
        'staff_metrics' => $staffMetrics,
        'totals' => $totals
    ]);
}
