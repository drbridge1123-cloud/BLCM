<?php
/**
 * POST /api/bank-reconciliation/import
 * Import bank statement CSV file
 */
requireAuth();
requirePermission('bank_reconciliation');

if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) errorResponse('CSV file is required');

$rows = parseCSV($_FILES['file']['tmp_name']);
if (empty($rows)) errorResponse('No valid rows found in CSV');

$user = getCurrentUser();
$batchId = date('Ymd-His') . '-' . substr(uniqid(), -6);
$count = 0;

// Column name mapping (flexible)
$dateKeys = ['date', 'transaction_date', 'trans_date', 'trans date'];
$descKeys = ['description', 'desc', 'memo'];
$amtKeys  = ['amount', 'amt'];
$chkKeys  = ['check_number', 'check_no', 'check #', 'check'];
$refKeys  = ['reference_number', 'reference', 'ref', 'ref_number'];
$catKeys  = ['category', 'bank_category', 'type'];

foreach ($rows as $row) {
    $date   = findColumn($row, $dateKeys);
    $desc   = findColumn($row, $descKeys);
    $amount = findColumn($row, $amtKeys);

    if (!$date || $amount === null) continue;

    // Parse date flexibly
    $parsedDate = parseFlexDate($date);
    if (!$parsedDate) continue;

    // Clean amount: strip $, commas
    $amount = (float)str_replace(['$', ',', ' '], '', $amount);

    dbInsert('bank_statement_entries', [
        'batch_id'              => $batchId,
        'transaction_date'      => $parsedDate,
        'description'           => trim($desc ?? ''),
        'amount'                => $amount,
        'check_number'          => trim(findColumn($row, $chkKeys) ?? ''),
        'reference_number'      => trim(findColumn($row, $refKeys) ?? ''),
        'bank_category'         => trim(findColumn($row, $catKeys) ?? ''),
        'reconciliation_status' => 'unmatched',
        'imported_by'           => $user['id'],
        'imported_at'           => date('Y-m-d H:i:s'),
    ]);
    $count++;
}

if ($count === 0) errorResponse('No valid entries could be parsed from CSV');

logActivity($user['id'], 'import', 'bank_reconciliation', null, [
    'batch_id' => $batchId, 'entries' => $count
]);

successResponse(['batch_id' => $batchId, 'count' => $count], "Imported {$count} entries");

// ── Helpers ──

function findColumn($row, $keys) {
    foreach ($keys as $k) {
        if (isset($row[$k]) && $row[$k] !== '') return $row[$k];
    }
    return null;
}

function parseFlexDate($val) {
    $formats = ['m/d/Y', 'n/j/Y', 'm/d/y', 'Y-m-d', 'm-d-Y', 'n/j/y'];
    foreach ($formats as $fmt) {
        $d = DateTime::createFromFormat($fmt, trim($val));
        if ($d) return $d->format('Y-m-d');
    }
    return null;
}
