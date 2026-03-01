<?php
// POST /api/templates/{id}/restore — restore template to a previous version
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$user = getCurrentUser();
if ($user['role'] !== 'admin') {
    errorResponse('Only administrators can restore templates', 403);
}

$templateId = (int)($_GET['id'] ?? 0);
if (!$templateId) {
    errorResponse('Template ID is required', 400);
}

$input = getInput();
$versionId = (int)($input['version_id'] ?? 0);
if (!$versionId) {
    errorResponse('version_id is required', 400);
}

$template = dbFetchOne("SELECT * FROM letter_templates WHERE id = ?", [$templateId]);
if (!$template) {
    errorResponse('Template not found', 404);
}

$version = dbFetchOne(
    "SELECT * FROM letter_template_versions WHERE id = ? AND template_id = ?",
    [$versionId, $templateId]
);
if (!$version) {
    errorResponse('Version not found', 404);
}

$pdo = getDBConnection();
$pdo->beginTransaction();

try {
    // Update template with version's content
    dbUpdate('letter_templates', [
        'body_template' => $version['body_template'],
        'subject_template' => $version['subject_template'],
    ], 'id = ?', [$templateId]);

    // Create a new version entry for the restore
    $latestVersion = dbFetchOne(
        "SELECT MAX(version_number) as max_version FROM letter_template_versions WHERE template_id = ?",
        [$templateId]
    );
    $nextVersion = ($latestVersion['max_version'] ?? 0) + 1;

    dbInsert('letter_template_versions', [
        'template_id' => $templateId,
        'version_number' => $nextVersion,
        'body_template' => $version['body_template'],
        'subject_template' => $version['subject_template'],
        'changed_by' => $userId,
        'change_notes' => 'Restored from version ' . $version['version_number'],
    ]);

    logActivity($userId, 'template_restored', 'letter_template', $templateId, [
        'restored_from_version' => $version['version_number'],
        'new_version' => $nextVersion,
    ]);

    $pdo->commit();

    successResponse([
        'id' => $templateId,
        'restored_from' => $version['version_number'],
        'new_version' => $nextVersion,
    ], 'Template restored to version ' . $version['version_number']);

} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Failed to restore template: ' . $e->getMessage(), 500);
}
