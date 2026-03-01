<?php
/**
 * POST /api/traffic
 * Create a new traffic case
 */
$userId = requireAuth();
requirePermission('traffic');
$user = getCurrentUser();
$input = getInput();

$clientName = trim($input['client_name'] ?? '');
if (!$clientName) {
    errorResponse('Client name is required');
}

$disposition = $input['disposition'] ?? 'pending';
$commission = 0;
if ($disposition === 'dismissed') $commission = 150.00;
elseif ($disposition === 'amended') $commission = 100.00;

$status = 'active';
if (in_array($disposition, ['dismissed', 'amended'])) {
    $status = 'resolved';
}

$id = dbInsert('traffic_cases', [
    'user_id'              => $userId,
    'client_name'          => $clientName,
    'client_phone'         => trim($input['client_phone'] ?? ''),
    'client_email'         => trim($input['client_email'] ?? ''),
    'court'                => trim($input['court'] ?? ''),
    'court_date'           => ($input['court_date'] ?? '') ?: null,
    'charge'               => trim($input['charge'] ?? ''),
    'case_number'          => trim($input['case_number'] ?? ''),
    'prosecutor_offer'     => trim($input['prosecutor_offer'] ?? ''),
    'disposition'          => $disposition,
    'commission'           => $commission,
    'discovery'            => !empty($input['discovery']) ? 1 : 0,
    'status'               => $status,
    'note'                 => trim($input['note'] ?? ''),
    'referral_source'      => trim($input['referral_source'] ?? ''),
    'paid'                 => 0,
    'noa_sent_date'        => ($input['noa_sent_date'] ?? '') ?: null,
    'citation_issued_date' => ($input['citation_issued_date'] ?? '') ?: null,
    'request_id'           => !empty($input['request_id']) ? (int)$input['request_id'] : null,
    'requested_by'         => !empty($input['requested_by']) ? (int)$input['requested_by'] : null,
    'resolved_at'          => $status === 'resolved' ? date('Y-m-d H:i:s') : null,
]);

logActivity($userId, 'traffic_case_created', 'traffic_cases', $id, [
    'client_name' => $clientName
]);

successResponse(['id' => $id], 'Traffic case created');
