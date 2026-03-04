<?php
/**
 * DELETE /api/case-providers/{id}
 * Remove a provider link from a case
 */
$userId = requireAuth();
$id     = (int)$_GET['id'];

$cp = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$id]);
if (!$cp) errorResponse('Case-provider not found', 404);

$provInfo = dbFetchOne("SELECT name FROM providers WHERE id = ?", [$cp['provider_id']]);

// Cascade delete: remove related Cost Ledger and MBR line entries
dbDelete('mr_fee_payments', 'case_provider_id = ?', [$id]);
dbDelete('mbr_lines', 'case_provider_id = ?', [$id]);

dbDelete('case_providers', 'id = ?', [$id]);

logActivity($userId, 'delete', 'case_provider', $id, [
    'case_id'       => $cp['case_id'],
    'provider_id'   => $cp['provider_id'],
    'provider_name' => $provInfo['name'] ?? null,
]);

successResponse(null, 'Provider removed from case');
