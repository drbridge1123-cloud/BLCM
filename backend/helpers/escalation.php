<?php

function getEscalationTier($daysPastDeadline) {
    if ($daysPastDeadline !== null && $daysPastDeadline >= ADMIN_ESCALATION_DAYS_AFTER_DEADLINE) return 'admin';
    if ($daysPastDeadline !== null && $daysPastDeadline >= 0) return 'action_needed';
    return 'normal';
}

function getEscalationInfo($daysPastDeadline) {
    $tier = getEscalationTier($daysPastDeadline);
    $info = [
        'normal' => ['tier' => 'normal', 'label' => 'Normal', 'css' => 'escalation-normal'],
        'action_needed' => ['tier' => 'action_needed', 'label' => 'Action Needed', 'css' => 'escalation-action-needed'],
        'admin' => ['tier' => 'admin', 'label' => 'Admin Escalation', 'css' => 'escalation-admin'],
    ];
    return $info[$tier];
}

function generateEscalationNotifications() {
    $today = date('Y-m-d');
    $created = 0;

    // 1. Deadline reached (0+ days past) → notify managers, set action_needed
    $actionNeededItems = dbFetchAll("
        SELECT cp.id, cp.assigned_to, cp.case_id, cp.overall_status, cp.deadline,
               c.case_number, c.client_name,
               p.name AS provider_name,
               DATEDIFF(CURDATE(), cp.deadline) AS days_past_deadline
        FROM case_providers cp
        JOIN cases c ON c.id = cp.case_id
        JOIN providers p ON p.id = cp.provider_id
        WHERE cp.overall_status NOT IN ('action_needed', 'received_complete', 'verified')
          AND c.status NOT IN ('closed')
          AND cp.deadline IS NOT NULL
          AND cp.deadline <= CURDATE()
    ");

    foreach ($actionNeededItems as $item) {
        $provName = $item['provider_name'];
        $clientInfo = "{$item['client_name']} ({$item['case_number']})";

        // Auto-set status to action_needed
        dbUpdate('case_providers', ['overall_status' => 'action_needed'], 'id = ?', [$item['id']]);

        $managers = dbFetchAll("SELECT id FROM users WHERE role = 'manager' AND is_active = 1");
        foreach ($managers as $mgr) {
            $subject = "[System] Action Needed: {$provName} — Case #{$item['case_number']}";
            $exists = dbFetchOne(
                "SELECT id FROM messages WHERE to_user_id = ? AND subject = ? AND DATE(created_at) = ?",
                [$mgr['id'], $subject, $today]
            );
            if (!$exists) {
                dbInsert('messages', [
                    'from_user_id' => (int)$mgr['id'],
                    'to_user_id' => (int)$mgr['id'],
                    'subject' => $subject,
                    'message' => "Action Needed (deadline reached): {$provName} for {$clientInfo}.\n\nThis provider has reached the 30-day deadline with no records received."
                ]);
                $created++;
            }
        }
    }

    // 2. Deadline + 14 days overdue → notify admins
    $adminItems = dbFetchAll("
        SELECT cp.id, cp.assigned_to, cp.case_id, cp.deadline,
               c.case_number, c.client_name,
               p.name AS provider_name,
               DATEDIFF(CURDATE(), cp.deadline) AS days_past_deadline
        FROM case_providers cp
        JOIN cases c ON c.id = cp.case_id
        JOIN providers p ON p.id = cp.provider_id
        WHERE cp.overall_status NOT IN ('received_complete', 'verified')
          AND c.status NOT IN ('closed')
          AND cp.deadline IS NOT NULL
          AND DATEDIFF(CURDATE(), cp.deadline) >= " . ADMIN_ESCALATION_DAYS_AFTER_DEADLINE
    );

    foreach ($adminItems as $item) {
        $provName = $item['provider_name'];
        $clientInfo = "{$item['client_name']} ({$item['case_number']})";
        $daysPast = (int)$item['days_past_deadline'];

        $admins = dbFetchAll("SELECT id FROM users WHERE role = 'admin' AND is_active = 1");
        foreach ($admins as $adm) {
            $subject = "[System] Admin Escalation ({$daysPast}d): {$provName} — Case #{$item['case_number']}";
            $exists = dbFetchOne(
                "SELECT id FROM messages WHERE to_user_id = ? AND subject = ? AND DATE(created_at) = ?",
                [$adm['id'], $subject, $today]
            );
            if (!$exists) {
                dbInsert('messages', [
                    'from_user_id' => (int)$adm['id'],
                    'to_user_id' => (int)$adm['id'],
                    'subject' => $subject,
                    'message' => "Admin Escalation: {$provName} for {$clientInfo} is {$daysPast} days past deadline.\n\nImmediate admin attention required."
                ]);
                $created++;
            }
        }
    }

    return $created;
}

function getEscalatedItems($role, $userId = null) {
    $baseQuery = "
        SELECT cp.id, cp.case_id, cp.deadline, cp.overall_status, cp.assigned_to,
               c.case_number, c.client_name,
               p.name AS provider_name, p.type AS provider_type,
               u.full_name AS assigned_name,
               DATEDIFF(CURDATE(), cp.deadline) AS days_past_deadline
        FROM case_providers cp
        JOIN cases c ON c.id = cp.case_id
        JOIN providers p ON p.id = cp.provider_id
        LEFT JOIN users u ON cp.assigned_to = u.id
        WHERE cp.overall_status NOT IN ('received_complete', 'verified')
          AND c.status NOT IN ('closed')
          AND cp.deadline IS NOT NULL
          AND cp.deadline <= CURDATE()";

    $params = [];

    // Staff: only see their own assigned items
    if ($role === 'staff') {
        $baseQuery .= " AND cp.assigned_to = ?";
        $params[] = $userId;
    }

    $baseQuery .= " ORDER BY days_past_deadline DESC";
    $rows = dbFetchAll($baseQuery, $params);

    foreach ($rows as &$row) {
        $daysPast = $row['days_past_deadline'] !== null ? (int)$row['days_past_deadline'] : null;
        $esc = getEscalationInfo($daysPast);
        $row['escalation_tier'] = $esc['tier'];
        $row['escalation_label'] = $esc['label'];
        $row['escalation_css'] = $esc['css'];
    }
    unset($row);

    return $rows;
}
