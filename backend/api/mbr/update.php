<?php
/**
 * PUT /api/mbr/{id}
 * Update MBR report header fields
 */
$userId = requireAuth();
requirePermission('mbr');

$id    = (int)$_GET['id'];
$input = getInput();

$report = dbFetchOne("SELECT * FROM mbr_reports WHERE id = ?", [$id]);
if (!$report) errorResponse('MBR report not found', 404);

// Allow reopening completed reports to draft
if ($report['status'] === 'completed' && !empty($input['reopen'])) {
    $input['status'] = 'draft';
}

$allowedFields = [
    'pip1_name', 'pip2_name', 'health1_name', 'health2_name', 'health3_name',
    'has_wage_loss', 'has_essential_service', 'has_health_subrogation',
    'has_health_subrogation2', 'notes'
];

$data    = [];
$changes = [];

foreach ($allowedFields as $field) {
    if (!array_key_exists($field, $input)) continue;
    $newValue = $input[$field];

    // Cast boolean flags to int
    if (strpos($field, 'has_') === 0) {
        $newValue = (int)(bool)$newValue;
    } else {
        $newValue = sanitizeString($newValue);
    }

    $oldValue = $report[$field] ?? null;
    if ((string)$newValue !== (string)$oldValue) {
        $changes[$field] = ['from' => $oldValue, 'to' => $newValue];
    }
    $data[$field] = $newValue;
}

// Handle status reopen
if (isset($input['status']) && $input['status'] === 'draft' && $report['status'] === 'completed') {
    $data['status']       = 'draft';
    $data['completed_by'] = null;
    $data['completed_at'] = null;
    $changes['status']    = ['from' => 'completed', 'to' => 'draft'];
}

if (empty($data)) errorResponse('No fields to update');

dbUpdate('mbr_reports', $data, 'id = ?', [$id]);

// Auto-create / delete mbr_lines when has_* flags are toggled
$flagLineMap = [
    'has_wage_loss'          => ['line_type' => 'wage_loss',          'provider_name' => 'Wage Loss'],
    'has_essential_service'  => ['line_type' => 'essential_service',  'provider_name' => 'Essential Service'],
    'has_health_subrogation' => ['line_type' => 'health_subrogation', 'provider_name' => 'Health Subrogation #1'],
    'has_health_subrogation2'=> ['line_type' => 'health_subrogation2','provider_name' => 'Health Subrogation #2'],
];

foreach ($flagLineMap as $flag => $lineDef) {
    if (!array_key_exists($flag, $data)) continue;
    $wasOn = (int)($report[$flag] ?? 0);
    $isOn  = (int)$data[$flag];

    if ($isOn && !$wasOn) {
        // Toggled ON → create line if not exists
        $exists = dbFetchOne(
            "SELECT id FROM mbr_lines WHERE report_id = ? AND line_type = ?",
            [$id, $lineDef['line_type']]
        );
        if (!$exists) {
            $maxSort = dbFetchOne(
                "SELECT COALESCE(MAX(sort_order), 0) AS mx FROM mbr_lines WHERE report_id = ?",
                [$id]
            );
            dbInsert('mbr_lines', [
                'report_id'     => $id,
                'line_type'     => $lineDef['line_type'],
                'provider_name' => $lineDef['provider_name'],
                'sort_order'    => (int)($maxSort['mx'] ?? 0) + 1,
            ]);
        }
    } elseif (!$isOn && $wasOn) {
        // Toggled OFF → delete line
        dbDelete('mbr_lines', 'report_id = ? AND line_type = ?', [$id, $lineDef['line_type']]);
    }
}

if (!empty($changes)) {
    logActivity($userId, 'update', 'mbr_report', $id, $changes);
}

successResponse(null, 'MBR report updated successfully');
