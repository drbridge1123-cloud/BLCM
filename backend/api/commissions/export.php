<?php
/**
 * GET /api/commissions/export
 * Export commissions to CSV
 */
$userId = requireAuth();
requirePermission('commissions');
$user = getCurrentUser();

$status   = $_GET['status'] ?? null;
$month    = $_GET['month'] ?? null;
$year     = $_GET['year'] ?? null;
$employee = $_GET['employee_id'] ?? null;

$where  = 'c.deleted_at IS NULL';
$params = [];

if (!in_array($user['role'], ['admin', 'manager'])) {
    $where .= ' AND c.employee_user_id = ?';
    $params[] = $userId;
} elseif ($employee) {
    $where .= ' AND c.employee_user_id = ?';
    $params[] = (int)$employee;
}

if ($status) {
    $where .= ' AND c.status = ?';
    $params[] = $status;
}
if ($month) {
    $where .= ' AND c.month = ?';
    $params[] = $month;
}
if ($year) {
    $where .= ' AND c.month LIKE ?';
    $params[] = "%{$year}";
}

$rows = dbFetchAll("
    SELECT c.case_number, c.client_name, c.case_type, u.full_name AS employee,
           c.settled, c.presuit_offer, c.difference, c.fee_rate, c.legal_fee,
           c.discounted_legal_fee, c.commission_rate, c.commission,
           c.is_marketing, c.month, c.status, c.check_received, c.note
    FROM employee_commissions c
    JOIN users u ON c.employee_user_id = u.id
    WHERE {$where}
    ORDER BY c.submitted_at DESC
", $params);

$headers = [
    'Case #', 'Client Name', 'Case Type', 'Employee',
    'Settled', 'Pre-Suit Offer', 'Difference', 'Fee Rate',
    'Legal Fee', 'Disc. Legal Fee', 'Comm. Rate', 'Commission',
    'Marketing', 'Month', 'Status', 'Check Received', 'Note'
];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="commissions_export_' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, $headers);

foreach ($rows as $r) {
    fputcsv($out, [
        $r['case_number'], $r['client_name'], $r['case_type'], $r['employee'],
        $r['settled'], $r['presuit_offer'], $r['difference'], $r['fee_rate'],
        $r['legal_fee'], $r['discounted_legal_fee'], $r['commission_rate'], $r['commission'],
        $r['is_marketing'] ? 'Yes' : 'No', $r['month'], $r['status'],
        $r['check_received'] ? 'Yes' : 'No', $r['note']
    ]);
}

fclose($out);
exit;
