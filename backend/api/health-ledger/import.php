<?php
/**
 * POST /api/health-ledger/import
 * Bulk import health ledger items from CSV
 */
$userId = requireAuth();
requirePermission('health_tracker');

if (empty($_FILES['file']['tmp_name'])) {
    errorResponse('CSV file is required');
}

$rows = parseCSV($_FILES['file']['tmp_name']);
if (empty($rows)) errorResponse('No valid rows found in CSV');

$inserted = 0;
$skipped  = 0;

$expectedCols = ['case_number', 'client_name', 'insurance_carrier'];

foreach ($rows as $row) {
    // Require at minimum client_name and insurance_carrier
    if (empty($row['client_name']) || empty($row['insurance_carrier'])) {
        $skipped++;
        continue;
    }

    // Skip duplicates: same case_number + insurance_carrier
    if (!empty($row['case_number'])) {
        $existing = dbFetchOne(
            "SELECT id FROM health_ledger_items WHERE case_number = ? AND insurance_carrier = ?",
            [trim($row['case_number']), trim($row['insurance_carrier'])]
        );
        if ($existing) {
            $skipped++;
            continue;
        }
    }

    $data = [
        'client_name'       => sanitizeString($row['client_name']),
        'insurance_carrier' => sanitizeString($row['insurance_carrier']),
    ];

    $optional = ['case_number', 'claim_number', 'member_id', 'carrier_contact_email', 'carrier_contact_fax'];
    foreach ($optional as $col) {
        if (!empty($row[$col])) $data[$col] = sanitizeString($row[$col]);
    }

    dbInsert('health_ledger_items', $data);
    $inserted++;
}

logActivity($userId, 'import', 'health_ledger', null, [
    'inserted' => $inserted, 'skipped' => $skipped
]);

successResponse([
    'inserted' => $inserted,
    'skipped'  => $skipped,
], "Import complete: {$inserted} inserted, {$skipped} skipped");
