<?php
/**
 * GET /api/cases/export
 * Export cases as CSV. ?template=1 for an empty import template.
 */
$userId = requireAuth();

$headers = [
    'case_number', 'client_name', 'client_dob', 'doi',
    'status', 'assigned_to', 'attorney_name',
    'ini_completed', 'notes', 'created_at'
];

// Empty template mode
if (!empty($_GET['template'])) {
    outputCSV('cases_import_template.csv', $headers, []);
}

// Build filtered query (re-use same filter logic as list)
$status    = $_GET['status'] ?? null;
$assignedTo = $_GET['assigned_to'] ?? null;
$search    = $_GET['search'] ?? null;

$where  = '1=1';
$params = [];

if ($status) {
    $statuses = array_map('trim', explode(',', $status));
    $placeholders = implode(',', array_fill(0, count($statuses), '?'));
    $where .= " AND c.status IN ({$placeholders})";
    $params = array_merge($params, $statuses);
}

if ($assignedTo) {
    $where .= ' AND c.assigned_to = ?';
    $params[] = (int)$assignedTo;
}

if ($search) {
    $where .= ' AND (c.client_name LIKE ? OR c.case_number LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$rows = dbFetchAll(
    "SELECT c.case_number, c.client_name, c.client_dob, c.doi,
            c.status, u.full_name AS assigned_to, c.attorney_name,
            c.ini_completed, c.notes, c.created_at
     FROM cases c
     LEFT JOIN users u ON c.assigned_to = u.id
     WHERE {$where}
     ORDER BY c.created_at DESC",
    $params
);

outputCSV('cases_export_' . date('Y-m-d') . '.csv', $headers, $rows);
