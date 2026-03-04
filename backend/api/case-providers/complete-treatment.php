<?php
/**
 * PUT /api/case-providers/{id}/complete-treatment
 * Mark a treating provider's treatment as complete (treating → treatment_complete)
 */
require_once __DIR__ . '/../../helpers/date.php';

$userId = requireAuth();
$id     = (int)$_GET['id'];
$input  = getInput();

$cp = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$id]);
if (!$cp) errorResponse('Case-provider not found', 404);

if ($cp['overall_status'] !== 'treating') {
    errorResponse('Provider is not in treating status', 400);
}

$updateData = ['overall_status' => 'treatment_complete'];

// Treatment end date (required)
if (empty($input['treatment_end_date'])) {
    errorResponse('Treatment end date is required', 400);
}
if (!validateDate($input['treatment_end_date'])) {
    errorResponse('Invalid treatment end date format', 400);
}
$updateData['treatment_end_date'] = $input['treatment_end_date'];

dbUpdate('case_providers', $updateData, 'id = ?', [$id]);

$provInfo = dbFetchOne("SELECT name FROM providers WHERE id = ?", [$cp['provider_id']]);

logActivity($userId, 'complete_treatment', 'case_provider', $id, [
    'case_id'            => $cp['case_id'],
    'provider_name'      => $provInfo['name'] ?? null,
    'treatment_end_date' => $input['treatment_end_date'],
]);

successResponse(null, 'Treatment marked as complete');
