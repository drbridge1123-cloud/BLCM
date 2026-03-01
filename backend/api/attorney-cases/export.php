<?php
/**
 * GET /api/attorney-cases/export
 * Export all attorney cases as CSV.
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$user = getCurrentUser();
require_once __DIR__ . '/../../helpers/csv.php';

$where = 'ac.deleted_at IS NULL';
$params = [];

if (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND ac.attorney_user_id = ?';
    $params[] = $userId;
}

$phase = $_GET['phase'] ?? null;
if ($phase) {
    $where .= ' AND ac.phase = ?';
    $params[] = $phase;
}

$attorneyId = $_GET['attorney_user_id'] ?? null;
if ($attorneyId && in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND ac.attorney_user_id = ?';
    $params[] = (int)$attorneyId;
}

$rows = dbFetchAll(
    "SELECT ac.case_number, ac.client_name, ac.case_type, ac.phase, ac.status,
            ac.settled, ac.presuit_offer, ac.difference, ac.fee_rate,
            ac.legal_fee, ac.discounted_legal_fee, ac.commission, ac.commission_type,
            ac.assigned_date, ac.demand_deadline, ac.demand_settled_date,
            ac.litigation_start_date, ac.litigation_settled_date,
            ac.month, ac.check_received,
            u.full_name AS attorney_name,
            DATEDIFF(ac.demand_deadline, CURDATE()) AS days_left
     FROM attorney_cases ac
     LEFT JOIN users u ON ac.attorney_user_id = u.id
     WHERE {$where}
     ORDER BY ac.phase ASC, ac.assigned_date DESC",
    $params
);

$headers = [
    'case_number', 'client_name', 'case_type', 'phase', 'status',
    'settled', 'presuit_offer', 'difference', 'fee_rate',
    'legal_fee', 'discounted_legal_fee', 'commission', 'commission_type',
    'assigned_date', 'demand_deadline', 'demand_settled_date',
    'litigation_start_date', 'litigation_settled_date',
    'month', 'check_received', 'attorney_name', 'days_left'
];

$suffix = $phase ? "_{$phase}" : '';
outputCSV("attorney_cases{$suffix}_" . date('Y-m-d') . '.csv', $headers, $rows);
