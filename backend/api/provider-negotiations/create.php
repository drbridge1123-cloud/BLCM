<?php
/**
 * POST /api/provider-negotiations
 * Create a provider negotiation
 */
$userId = requireAuth();
requirePermission('cases');
$input = getInput();

$errors = validateRequired($input, ['case_id', 'provider_name']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId = (int)$input['case_id'];
$originalBalance = isset($input['original_balance']) ? (float)$input['original_balance'] : null;
$acceptedAmount  = isset($input['accepted_amount']) ? (float)$input['accepted_amount'] : null;

// Auto-calc reduction_percent
$reductionPercent = null;
if ($originalBalance && $originalBalance > 0 && $acceptedAmount !== null) {
    $reductionPercent = round(($originalBalance - $acceptedAmount) / $originalBalance * 100, 2);
}

$validStatuses = ['pending', 'negotiating', 'accepted', 'rejected', 'waived'];

$data = [
    'case_id'             => $caseId,
    'case_provider_id'    => !empty($input['case_provider_id']) ? (int)$input['case_provider_id'] : null,
    'mbds_line_id'        => !empty($input['mbds_line_id']) ? (int)$input['mbds_line_id'] : null,
    'provider_name'       => sanitizeString($input['provider_name']),
    'original_balance'    => $originalBalance,
    'requested_reduction' => isset($input['requested_reduction']) ? (float)$input['requested_reduction'] : null,
    'accepted_amount'     => $acceptedAmount,
    'reduction_percent'   => $reductionPercent,
    'status'              => validateEnum($input['status'] ?? 'pending', $validStatuses)
                             ? ($input['status'] ?? 'pending') : 'pending',
    'contact_name'        => sanitizeString($input['contact_name'] ?? ''),
    'contact_info'        => sanitizeString($input['contact_info'] ?? ''),
    'notes'               => sanitizeString($input['notes'] ?? ''),
    'created_by'          => $userId,
];

$id = dbInsert('provider_negotiations', $data);

logActivity($userId, 'create', 'provider_negotiation', $id, [
    'case_id' => $caseId, 'provider_name' => $data['provider_name']
]);

successResponse(['id' => $id], 'Provider negotiation created');
