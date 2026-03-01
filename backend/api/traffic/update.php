<?php
/**
 * PUT /api/traffic/{id}
 * Update a traffic case
 */
$userId = requireAuth();
requirePermission('traffic');
$user = getCurrentUser();
$input = getInput();

$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) errorResponse('Case ID required');

$row = dbFetchOne("SELECT * FROM traffic_cases WHERE id = ?", [$caseId]);
if (!$row) errorResponse('Traffic case not found', 404);

// Ownership check
if (!in_array($user['role'], ['admin', 'manager']) && (int)$row['user_id'] !== $userId) {
    errorResponse('Not authorized', 403);
}

// Handle bulk mark paid (admin only)
if (isset($input['action']) && $input['action'] === 'mark_paid' && in_array($user['role'], ['admin', 'manager'])) {
    $ids = $input['ids'] ?? [$caseId];
    $paidVal = isset($input['paid']) ? (int)$input['paid'] : 1;
    $paidAt = $paidVal ? date('Y-m-d H:i:s') : null;

    foreach ($ids as $id) {
        dbUpdate('traffic_cases', [
            'paid' => $paidVal,
            'paid_at' => $paidAt,
        ], 'id = ?', [(int)$id]);
    }
    successResponse(null, count($ids) . ' case(s) updated');
}

// Regular update
$disposition = $input['disposition'] ?? $row['disposition'];
$commission = (float)$row['commission'];
if ($disposition === 'dismissed') $commission = 150.00;
elseif ($disposition === 'amended') $commission = 100.00;
elseif ($disposition === 'pending' || $disposition === 'other') $commission = 0;

$status = $input['status'] ?? $row['status'];
if (in_array($disposition, ['dismissed', 'amended']) && $row['disposition'] !== $disposition) {
    $status = 'resolved';
}

$resolvedAt = $row['resolved_at'];
if ($status === 'resolved' && !$resolvedAt) {
    $resolvedAt = date('Y-m-d H:i:s');
} elseif ($status === 'active') {
    $resolvedAt = null;
}

$data = [
    'client_name'          => trim($input['client_name'] ?? $row['client_name']),
    'client_phone'         => trim($input['client_phone'] ?? $row['client_phone'] ?? ''),
    'client_email'         => trim($input['client_email'] ?? $row['client_email'] ?? ''),
    'court'                => trim($input['court'] ?? $row['court'] ?? ''),
    'court_date'           => $input['court_date'] ?? $row['court_date'],
    'charge'               => trim($input['charge'] ?? $row['charge'] ?? ''),
    'case_number'          => trim($input['case_number'] ?? $row['case_number'] ?? ''),
    'prosecutor_offer'     => trim($input['prosecutor_offer'] ?? $row['prosecutor_offer'] ?? ''),
    'disposition'          => $disposition,
    'commission'           => $commission,
    'discovery'            => isset($input['discovery']) ? ($input['discovery'] ? 1 : 0) : (int)$row['discovery'],
    'status'               => $status,
    'note'                 => trim($input['note'] ?? $row['note'] ?? ''),
    'referral_source'      => trim($input['referral_source'] ?? $row['referral_source'] ?? ''),
    'noa_sent_date'        => $input['noa_sent_date'] ?? $row['noa_sent_date'],
    'citation_issued_date' => $input['citation_issued_date'] ?? $row['citation_issued_date'],
    'resolved_at'          => $resolvedAt,
];

// Admin can change paid status
if (in_array($user['role'], ['admin', 'manager']) && isset($input['paid'])) {
    $data['paid'] = (int)$input['paid'];
    $data['paid_at'] = $input['paid'] ? date('Y-m-d H:i:s') : null;
}

dbUpdate('traffic_cases', $data, 'id = ?', [$caseId]);
logActivity($userId, 'traffic_case_updated', 'traffic_cases', $caseId);

successResponse(null, 'Traffic case updated');
