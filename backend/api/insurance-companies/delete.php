<?php
/**
 * DELETE /api/insurance-companies/{id}
 * Delete an insurance company. Prevents deletion if it has adjusters.
 */
requireAdmin();

$id = (int)$_GET['id'];

$company = dbFetchOne("SELECT id, name FROM insurance_companies WHERE id = ?", [$id]);
if (!$company) errorResponse('Insurance company not found', 404);

// Prevent deletion if company has adjusters
$adjusterCount = dbCount('adjusters', 'insurance_company_id = ?', [$id]);
if ($adjusterCount > 0) {
    errorResponse("Cannot delete insurance company: it has {$adjusterCount} adjuster(s) associated");
}

dbDelete('insurance_companies', 'id = ?', [$id]);

logActivity($_SESSION['user_id'], 'delete', 'insurance_company', $id, ['name' => $company['name']]);

successResponse(null, 'Insurance company deleted successfully');
