<?php
// GET /api/accounting-tracker/list
$userId = requireAuth();
requirePermission('accounting_tracker');

$search = sanitizeString($_GET['search'] ?? '');
$assignedTo = $_GET['assigned_to'] ?? '';
$filter = $_GET['filter'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'sent_to_accounting_date';
$sortDir = strtoupper($_GET['sort_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

$allowedSorts = ['case_number', 'client_name', 'settlement_amount', 'sent_to_accounting_date', 'assigned_name', 'days_in_accounting'];
if (!in_array($sortBy, $allowedSorts)) $sortBy = 'sent_to_accounting_date';

// ── Regular cases (from cases table) ──
$caseWhere = "c.status = 'accounting'";
$caseParams = [];

if ($search) {
    $caseWhere .= " AND (c.case_number LIKE ? OR c.client_name LIKE ?)";
    $caseParams[] = "%{$search}%";
    $caseParams[] = "%{$search}%";
}
if ($assignedTo) {
    $caseWhere .= " AND c.assigned_to = ?";
    $caseParams[] = (int)$assignedTo;
}

$caseSql = "SELECT c.id, NULL AS attorney_case_id, 'case' AS source_type,
                   c.case_number, c.client_name, c.client_dob, c.doi,
                   c.assigned_to, c.settlement_amount, c.attorney_fee_percent,
                   c.sent_to_accounting_date, c.closed_date, c.file_location,
                   c.created_at,
                   u.full_name AS assigned_name,
                   d.disbursement_count, d.total_disbursed, d.pending_count,
                   DATEDIFF(CURDATE(), c.sent_to_accounting_date) AS days_in_accounting
            FROM cases c
            LEFT JOIN users u ON u.id = c.assigned_to
            LEFT JOIN (
                SELECT case_id,
                       COUNT(*) AS disbursement_count,
                       COALESCE(SUM(CASE WHEN status != 'void' THEN amount ELSE 0 END), 0) AS total_disbursed,
                       SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count
                FROM accounting_disbursements
                WHERE case_id IS NOT NULL
                GROUP BY case_id
            ) d ON d.case_id = c.id
            WHERE {$caseWhere}";

// ── Attorney cases (from attorney_cases table) ──
$attWhere = "ac.status = 'accounting' AND ac.deleted_at IS NULL";
$attParams = [];

if ($search) {
    $attWhere .= " AND (ac.case_number LIKE ? OR ac.client_name LIKE ?)";
    $attParams[] = "%{$search}%";
    $attParams[] = "%{$search}%";
}
if ($assignedTo) {
    $attWhere .= " AND ac.accounting_assigned_to = ?";
    $attParams[] = (int)$assignedTo;
}

$attSql = "SELECT ac.case_id AS id, ac.id AS attorney_case_id, 'attorney' AS source_type,
                  ac.case_number, ac.client_name, NULL AS client_dob, NULL AS doi,
                  ac.accounting_assigned_to AS assigned_to, ac.settled AS settlement_amount,
                  NULL AS attorney_fee_percent,
                  ac.sent_to_accounting_date, NULL AS closed_date, NULL AS file_location,
                  ac.submitted_at AS created_at,
                  u2.full_name AS assigned_name,
                  ad.disbursement_count, ad.total_disbursed, ad.pending_count,
                  DATEDIFF(CURDATE(), ac.sent_to_accounting_date) AS days_in_accounting
           FROM attorney_cases ac
           LEFT JOIN users u2 ON u2.id = ac.accounting_assigned_to
           LEFT JOIN (
               SELECT attorney_case_id,
                      COUNT(*) AS disbursement_count,
                      COALESCE(SUM(CASE WHEN status != 'void' THEN amount ELSE 0 END), 0) AS total_disbursed,
                      SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count
               FROM accounting_disbursements
               WHERE attorney_case_id IS NOT NULL
               GROUP BY attorney_case_id
           ) ad ON ad.attorney_case_id = ac.id
           WHERE {$attWhere}";

// Combine with UNION ALL
$sql = "({$caseSql}) UNION ALL ({$attSql})";
$params = array_merge($caseParams, $attParams);

// Sorting (applied to combined results)
$orderCol = $sortBy;
if ($sortBy === 'assigned_name') $orderCol = 'assigned_name';
elseif ($sortBy === 'settlement_amount') $orderCol = 'COALESCE(settlement_amount, 0)';
$sql = "SELECT * FROM ({$sql}) AS combined ORDER BY {$orderCol} {$sortDir}";

$items = dbFetchAll($sql, $params);

// Quick filters (applied post-query)
if ($filter === 'overdue') {
    $items = array_values(array_filter($items, fn($i) => (int)$i['days_in_accounting'] > ACCOUNTING_DISBURSE_DAYS));
} elseif ($filter === 'pending') {
    $items = array_values(array_filter($items, fn($i) => (int)($i['pending_count'] ?? 0) > 0 || (int)($i['disbursement_count'] ?? 0) === 0));
}

// Compute flags
foreach ($items as &$item) {
    $item['days_in_accounting'] = (int)($item['days_in_accounting'] ?? 0);
    $item['is_overdue'] = $item['days_in_accounting'] > ACCOUNTING_DISBURSE_DAYS;
    $item['disbursement_count'] = (int)($item['disbursement_count'] ?? 0);
    $item['total_disbursed'] = (float)($item['total_disbursed'] ?? 0);
    $item['pending_count'] = (int)($item['pending_count'] ?? 0);
    $item['settlement_amount'] = (float)($item['settlement_amount'] ?? 0);

    if ($item['source_type'] === 'attorney') {
        // For attorney cases, attorney_fee is discounted_legal_fee (already in settlement data)
        $item['attorney_fee'] = 0; // Disbursements already include attorney fee
    } else {
        $item['attorney_fee'] = round($item['settlement_amount'] * (float)($item['attorney_fee_percent'] ?? 0.3333), 2);
    }
    $item['remaining'] = round($item['settlement_amount'] - $item['total_disbursed'], 2);
}
unset($item);

// Summary stats
$summaryTotal = count($items);
$overdue = 0; $pending = 0; $totalSettlement = 0;
foreach ($items as $item) {
    if ($item['is_overdue']) $overdue++;
    if ($item['pending_count'] > 0 || $item['disbursement_count'] === 0) $pending++;
    $totalSettlement += $item['settlement_amount'];
}

jsonResponse([
    'success' => true,
    'data' => $items,
    'summary' => [
        'total' => $summaryTotal,
        'overdue' => $overdue,
        'pending' => $pending,
        'total_settlement' => round($totalSettlement, 2),
    ]
]);
