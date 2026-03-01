<?php
/**
 * GET /api/referrals/export
 * Export all referral entries as CSV.
 */
$userId = requireAuth();
requirePermission('referrals');
$user = getCurrentUser();
require_once __DIR__ . '/../../helpers/csv.php';

$where = 'r.deleted_at IS NULL';
$params = [];

if (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND r.lead_id = ?';
    $params[] = $userId;
}

$leadId = $_GET['lead_id'] ?? null;
if ($leadId && in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND r.lead_id = ?';
    $params[] = (int)$leadId;
}

$managerId = $_GET['case_manager_id'] ?? null;
if ($managerId) {
    $where .= ' AND r.case_manager_id = ?';
    $params[] = (int)$managerId;
}

$rows = dbFetchAll(
    "SELECT r.row_number, r.signed_date, r.file_number, r.client_name,
            r.status, r.date_of_loss, r.referred_by,
            r.referred_to_provider, r.referred_to_body_shop,
            ld.full_name AS lead_name,
            cm.full_name AS case_manager_name,
            r.remark
     FROM referral_entries r
     LEFT JOIN users ld ON r.lead_id = ld.id
     LEFT JOIN users cm ON r.case_manager_id = cm.id
     WHERE {$where}
     ORDER BY r.signed_date DESC, r.row_number DESC",
    $params
);

$headers = ['row_number', 'signed_date', 'file_number', 'client_name',
            'status', 'date_of_loss', 'referred_by',
            'referred_to_provider', 'referred_to_body_shop',
            'lead_name', 'case_manager_name', 'remark'];

outputCSV('referrals_' . date('Y-m-d') . '.csv', $headers, $rows);
