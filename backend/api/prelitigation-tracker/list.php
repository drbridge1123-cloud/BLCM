<?php
// GET /api/prelitigation-tracker/list
$userId = requireAuth();
requirePermission('prelitigation_tracker');

$search = sanitizeString($_GET['search'] ?? '');
$assignedTo = $_GET['assigned_to'] ?? '';
$filter = $_GET['filter'] ?? '';
$treatmentStatus = $_GET['treatment_status'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'next_followup_date';
$sortDir = strtoupper($_GET['sort_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

$allowedSorts = ['case_number', 'client_name', 'doi', 'treatment_status', 'last_followup_date', 'next_followup_date', 'followup_count', 'assigned_name'];
if (!in_array($sortBy, $allowedSorts)) $sortBy = 'next_followup_date';

$where = "c.status = 'prelitigation'";
$params = [];

if ($search) {
    $where .= " AND (c.case_number LIKE ? OR c.client_name LIKE ? OR c.client_phone LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($assignedTo) {
    $where .= " AND c.assigned_to = ?";
    $params[] = (int)$assignedTo;
}
if ($treatmentStatus) {
    $where .= " AND c.treatment_status = ?";
    $params[] = $treatmentStatus;
}

$sql = "SELECT c.id, c.case_number, c.client_name, c.client_phone, c.client_email,
               c.client_dob, c.doi, c.assigned_to, c.treatment_status, c.treatment_end_date,
               c.prelitigation_start_date, c.created_at,
               u.full_name AS assigned_name,
               lf.last_followup_date, lf.last_followup_type, lf.last_contact_result,
               lf.next_followup_date, lf.followup_count
        FROM cases c
        LEFT JOIN users u ON u.id = c.assigned_to
        LEFT JOIN (
            SELECT pf1.case_id,
                   pf1.followup_date AS last_followup_date,
                   pf1.followup_type AS last_followup_type,
                   pf1.contact_result AS last_contact_result,
                   pf1.next_followup_date,
                   cnt.followup_count
            FROM prelitigation_followups pf1
            INNER JOIN (
                SELECT case_id, MAX(id) AS max_id, COUNT(*) AS followup_count
                FROM prelitigation_followups
                GROUP BY case_id
            ) cnt ON cnt.case_id = pf1.case_id AND cnt.max_id = pf1.id
        ) lf ON lf.case_id = c.id
        WHERE {$where}";

// Apply quick filters
if ($filter === 'overdue') {
    $sql .= " AND (lf.next_followup_date IS NOT NULL AND lf.next_followup_date < CURDATE())";
} elseif ($filter === 'followup_due') {
    $sql .= " AND (lf.next_followup_date IS NOT NULL AND lf.next_followup_date <= CURDATE())";
} elseif ($filter === 'no_contact') {
    $sql .= " AND lf.last_followup_date IS NULL";
} elseif ($filter === 'treatment_complete') {
    $sql .= " AND c.treatment_status = 'treatment_done'";
}

// Null-safe sort for dates
if ($sortBy === 'next_followup_date') {
    $sql .= " ORDER BY CASE WHEN lf.next_followup_date IS NULL THEN 1 ELSE 0 END, lf.next_followup_date {$sortDir}";
} elseif ($sortBy === 'last_followup_date') {
    $sql .= " ORDER BY CASE WHEN lf.last_followup_date IS NULL THEN 1 ELSE 0 END, lf.last_followup_date {$sortDir}";
} elseif ($sortBy === 'assigned_name') {
    $sql .= " ORDER BY u.full_name {$sortDir}";
} elseif ($sortBy === 'followup_count') {
    $sql .= " ORDER BY COALESCE(lf.followup_count, 0) {$sortDir}";
} else {
    $sql .= " ORDER BY c.{$sortBy} {$sortDir}";
}

$items = dbFetchAll($sql, $params);

// Compute flags
$today = date('Y-m-d');
foreach ($items as &$item) {
    $item['days_since_last_contact'] = $item['last_followup_date']
        ? (int)round((strtotime($today) - strtotime($item['last_followup_date'])) / 86400)
        : null;
    $item['is_followup_due'] = $item['next_followup_date'] && $item['next_followup_date'] <= $today;
    $item['is_overdue'] = $item['next_followup_date'] && $item['next_followup_date'] < $today;
    $item['followup_count'] = (int)($item['followup_count'] ?? 0);
}
unset($item);

// Summary stats (computed from filtered results)
$summaryTotal = count($items);
$followupDue = 0; $noContact = 0; $treatmentComplete = 0;
foreach ($items as $item) {
    if ($item['is_followup_due']) $followupDue++;
    if ($item['last_followup_date'] === null) $noContact++;
    if ($item['treatment_status'] === 'treatment_done') $treatmentComplete++;
}

jsonResponse([
    'success' => true,
    'data' => $items,
    'summary' => [
        'total' => $summaryTotal,
        'followup_due' => $followupDue,
        'no_contact' => $noContact,
        'treatment_complete' => $treatmentComplete,
    ]
]);
