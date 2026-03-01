<?php
/**
 * GET /api/bank-reconciliation/batches
 * List import batches with summary stats
 */
requireAuth();
requirePermission('bank_reconciliation');

$sql = "SELECT
            b.batch_id,
            MIN(b.imported_at) AS imported_at,
            u.full_name AS imported_by_name,
            COUNT(*) AS total_entries,
            SUM(b.reconciliation_status = 'matched') AS matched_count,
            SUM(b.reconciliation_status = 'unmatched') AS unmatched_count,
            SUM(b.reconciliation_status = 'ignored') AS ignored_count,
            SUM(b.amount) AS total_amount
        FROM bank_statement_entries b
        JOIN users u ON b.imported_by = u.id
        GROUP BY b.batch_id, b.imported_by, u.full_name
        ORDER BY imported_at DESC";

$batches = dbFetchAll($sql);

// Cast numeric fields
foreach ($batches as &$row) {
    $row['total_entries']  = (int)$row['total_entries'];
    $row['matched_count']  = (int)$row['matched_count'];
    $row['unmatched_count']= (int)$row['unmatched_count'];
    $row['ignored_count']  = (int)$row['ignored_count'];
    $row['total_amount']   = (float)$row['total_amount'];
}
unset($row);

successResponse($batches);
