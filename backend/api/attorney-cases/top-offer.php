<?php
/**
 * POST /api/attorney-cases/top-offer
 * Submit top offer for a demand case
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$user = getCurrentUser();
$input = getInput();

$errors = validateRequired($input, ['case_id', 'top_offer_amount', 'assignee_id']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId = (int)$input['case_id'];
$amount = (float)$input['top_offer_amount'];
$assigneeId = (int)$input['assignee_id'];
$note = sanitizeString($input['note'] ?? '');

if ($amount <= 0) errorResponse('Top offer amount must be greater than 0');

$case = dbFetchOne("SELECT * FROM attorney_cases WHERE id = ? AND deleted_at IS NULL", [$caseId]);
if (!$case) errorResponse('Case not found', 404);
if ($case['phase'] !== 'demand') errorResponse('Case is not in demand phase');

$today = date('Y-m-d');

dbUpdate('attorney_cases', [
    'top_offer_amount' => $amount,
    'top_offer_date' => $today,
    'top_offer_assignee_id' => $assigneeId,
    'top_offer_note' => $note ?: null
], 'id = ?', [$caseId]);

// Send message to assignee
$subject = "Top Offer Received - {$case['case_number']}";
$msgBody = "Top offer of $" . number_format($amount, 2)
    . " received for case {$case['case_number']} ({$case['client_name']}).";
if ($note) $msgBody .= " {$note}";

dbInsert('messages', [
    'from_user_id' => $userId,
    'to_user_id' => $assigneeId,
    'subject' => $subject,
    'message' => $msgBody,
    'created_at' => date('Y-m-d H:i:s')
]);

logActivity($userId, 'submit_top_offer', 'attorney_case', $caseId, [
    'top_offer_amount' => $amount,
    'assignee_id' => $assigneeId
]);

successResponse(['top_offer_date' => $today], 'Top offer submitted');
