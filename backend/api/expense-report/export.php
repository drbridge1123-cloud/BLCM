<?php
// GET /api/expense-report/export
$userId = requireAuth();
requirePermission('expense_report');

$conditions = [];
$params = [];

if (!empty($_GET['date_from'])) {
    $conditions[] = 'p.payment_date >= ?';
    $params[] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $conditions[] = 'p.payment_date <= ?';
    $params[] = $_GET['date_to'];
}
if (!empty($_GET['staff_id'])) {
    $conditions[] = 'p.paid_by = ?';
    $params[] = (int)$_GET['staff_id'];
}
if (!empty($_GET['category'])) {
    $conditions[] = 'p.expense_category = ?';
    $params[] = sanitizeString($_GET['category']);
}
if (!empty($_GET['payment_type'])) {
    $conditions[] = 'p.payment_type = ?';
    $params[] = sanitizeString($_GET['payment_type']);
}
if (!empty($_GET['search'])) {
    $search = '%' . sanitizeString($_GET['search']) . '%';
    $conditions[] = '(c.case_number LIKE ? OR c.client_name LIKE ? OR p.provider_name LIKE ? OR p.description LIKE ? OR p.check_number LIKE ?)';
    $params = array_merge($params, [$search, $search, $search, $search, $search]);
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

$rows = dbFetchAll(
    "SELECT p.payment_date,
            c.case_number,
            c.client_name,
            COALESCE(p.provider_name, prov.name, '') AS provider_name,
            p.description,
            p.expense_category,
            p.billed_amount,
            p.paid_amount,
            p.payment_type,
            p.check_number,
            u_paid.full_name AS paid_by_name,
            p.notes
     FROM mr_fee_payments p
     LEFT JOIN cases c ON p.case_id = c.id
     LEFT JOIN users u_paid ON p.paid_by = u_paid.id
     LEFT JOIN case_providers cp ON p.case_provider_id = cp.id
     LEFT JOIN providers prov ON cp.provider_id = prov.id
     {$whereClause}
     ORDER BY p.payment_date DESC, p.id DESC",
    $params
);

if (ob_get_level() > 0) ob_end_clean();
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="expense_report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Case #', 'Client', 'Provider', 'Description', 'Category', 'Billed', 'Paid', 'Payment Type', 'Check #', 'Paid By', 'Notes']);

foreach ($rows as $row) {
    $categoryLabel = match($row['expense_category']) {
        'mr_cost' => 'MR Cost',
        'litigation' => 'Litigation',
        'other' => 'Other',
        default => $row['expense_category']
    };
    $typeLabel = match($row['payment_type']) {
        'check' => 'Check',
        'card' => 'Card',
        'cash' => 'Cash',
        'wire' => 'Wire',
        'other' => 'Other',
        default => ''
    };
    fputcsv($output, [
        $row['payment_date'] ?? '',
        $row['case_number'] ?? '',
        $row['client_name'] ?? '',
        $row['provider_name'] ?? '',
        $row['description'] ?? '',
        $categoryLabel,
        number_format((float)$row['billed_amount'], 2, '.', ''),
        number_format((float)$row['paid_amount'], 2, '.', ''),
        $typeLabel,
        $row['check_number'] ?? '',
        $row['paid_by_name'] ?? '',
        $row['notes'] ?? '',
    ]);
}

fclose($output);
exit;
