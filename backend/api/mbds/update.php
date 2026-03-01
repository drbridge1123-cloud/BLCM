<?php
/**
 * PUT /api/mbds/{id}
 * Update MBDS report header fields
 */
$userId = requireAuth();
requirePermission('mbds');

$id    = (int)$_GET['id'];
$input = getInput();

$report = dbFetchOne("SELECT * FROM mbds_reports WHERE id = ?", [$id]);
if (!$report) errorResponse('MBDS report not found', 404);

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

dbUpdate('mbds_reports', $data, 'id = ?', [$id]);

if (!empty($changes)) {
    logActivity($userId, 'update', 'mbds_report', $id, $changes);
}

successResponse(null, 'MBDS report updated successfully');
