<?php
/**
 * DELETE /api/providers/{id}
 * Delete a provider. Prevents deletion if used in case_providers.
 */
requireAdmin();

$id = (int)$_GET['id'];

$provider = dbFetchOne("SELECT id, name FROM providers WHERE id = ?", [$id]);
if (!$provider) errorResponse('Provider not found', 404);

// Prevent deletion if provider is used in case_providers
$usageCount = dbCount('case_providers', 'provider_id = ?', [$id]);
if ($usageCount > 0) {
    errorResponse("Cannot delete provider: it is used in {$usageCount} case provider(s)");
}

dbDelete('providers', 'id = ?', [$id]);

logActivity($_SESSION['user_id'], 'delete', 'provider', $id, ['name' => $provider['name']]);

successResponse(null, 'Provider deleted successfully');
