<?php
/**
 * POST /api/receipts
 * Log a record receipt for a case-provider
 */
$userId = requireAuth();
$input  = getInput();

$errors = validateRequired($input, ['case_provider_id', 'received_date', 'received_method']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$cpId = (int)$input['case_provider_id'];
$cp   = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$cpId]);
if (!$cp) errorResponse('Case-provider not found', 404);

if (!validateDate($input['received_date'])) errorResponse('Invalid received_date');

$validMethods = ['email','fax','portal','mail','in_person'];
if (!validateEnum($input['received_method'], $validMethods)) {
    errorResponse('Invalid received_method');
}

$data = [
    'case_provider_id' => $cpId,
    'received_date'    => $input['received_date'],
    'received_method'  => $input['received_method'],
    'received_by'      => $userId,
];

// Record type flags
$flags = ['has_medical_records','has_billing','has_chart','has_imaging','has_op_report'];
foreach ($flags as $flag) {
    $data[$flag] = (int)($input[$flag] ?? 0);
}

// Determine completeness
if (isset($input['is_complete'])) {
    $data['is_complete'] = (int)$input['is_complete'];
} else {
    // Auto-determine from record_types_needed
    $needed = $cp['record_types_needed'] ? explode(',', $cp['record_types_needed']) : [];
    if (!empty($needed)) {
        $flagMap = [
            'medical_records' => 'has_medical_records',
            'billing'         => 'has_billing',
            'chart'           => 'has_chart',
            'imaging'         => 'has_imaging',
            'op_report'       => 'has_op_report',
        ];
        // Check all previous receipts + this one
        $prevReceipts = dbFetchAll(
            "SELECT has_medical_records, has_billing, has_chart, has_imaging, has_op_report
             FROM record_receipts WHERE case_provider_id = ?",
            [$cpId]
        );
        $received = [];
        foreach ($prevReceipts as $pr) {
            if ($pr['has_medical_records']) $received['medical_records'] = true;
            if ($pr['has_billing'])         $received['billing'] = true;
            if ($pr['has_chart'])           $received['chart'] = true;
            if ($pr['has_imaging'])         $received['imaging'] = true;
            if ($pr['has_op_report'])       $received['op_report'] = true;
        }
        // Include current receipt
        if ($data['has_medical_records']) $received['medical_records'] = true;
        if ($data['has_billing'])         $received['billing'] = true;
        if ($data['has_chart'])           $received['chart'] = true;
        if ($data['has_imaging'])         $received['imaging'] = true;
        if ($data['has_op_report'])       $received['op_report'] = true;

        $allReceived = true;
        foreach ($needed as $type) {
            $type = trim($type);
            if (!isset($received[$type])) {
                $allReceived = false;
                break;
            }
        }
        $data['is_complete'] = $allReceived ? 1 : 0;
    } else {
        $data['is_complete'] = 0;
    }
}

if (!empty($input['incomplete_reason'])) {
    $data['incomplete_reason'] = sanitizeString($input['incomplete_reason']);
}
if (isset($input['notes'])) {
    $data['notes'] = sanitizeString($input['notes']);
}

$id = dbInsert('record_receipts', $data);

// Update case_provider status
$newStatus = $data['is_complete'] ? 'received_complete' : 'received_partial';
dbUpdate('case_providers', [
    'overall_status' => $newStatus,
    'received_date'  => $input['received_date'],
], 'id = ?', [$cpId]);

// Notify admin on complete receipt
if ($data['is_complete']) {
    $caseInfo = dbFetchOne("SELECT case_number, client_name FROM cases WHERE id = ?", [$cp['case_id']]);
    $provInfo = dbFetchOne("SELECT name FROM providers WHERE id = ?", [$cp['provider_id']]);
    $admins   = dbFetchAll("SELECT id FROM users WHERE role = 'admin' AND is_active = 1");
    foreach ($admins as $admin) {
        dbInsert('notifications', [
            'user_id'          => $admin['id'],
            'case_provider_id' => $cpId,
            'type'             => 'records_complete',
            'message'          => "Records received complete for {$provInfo['name']} on case {$caseInfo['case_number']}",
        ]);
    }
}

// Auto-advance case (ini -> rec) when all providers complete
$doneStatuses = ['received_complete','verified','no_records'];
if (in_array($newStatus, $doneStatuses)) {
    $caseRow = dbFetchOne("SELECT id, status FROM cases WHERE id = ?", [$cp['case_id']]);
    if ($caseRow && $caseRow['status'] === 'ini') {
        $totalProviders = dbCount('case_providers', 'case_id = ?', [$cp['case_id']]);
        $doneProviders  = dbCount(
            'case_providers',
            "case_id = ? AND overall_status IN ('received_complete','verified','no_records')",
            [$cp['case_id']]
        );
        if ($totalProviders > 0 && $doneProviders >= $totalProviders) {
            $newOwner = STATUS_OWNER_MAP['rec'] ?? null;
            $caseUpdate = ['status' => 'rec'];
            if ($newOwner) $caseUpdate['assigned_to'] = $newOwner;
            dbUpdate('cases', $caseUpdate, 'id = ?', [$cp['case_id']]);
            logActivity($userId, 'auto_advance', 'case', $cp['case_id'], [
                'from' => 'ini',
                'to'   => 'rec',
            ]);
        }
    }
}

logActivity($userId, 'create', 'record_receipt', $id, [
    'case_provider_id' => $cpId,
    'is_complete'      => $data['is_complete'],
]);

successResponse(['id' => $id], 'Receipt recorded');
