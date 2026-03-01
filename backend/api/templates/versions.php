<?php
// GET /api/templates/{id}/versions - Get version history for template
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireAuth();

$templateId = (int)($_GET['id'] ?? 0);
if (!$templateId) {
    errorResponse('Template ID is required', 400);
}

// Verify template exists
$template = dbFetchOne("SELECT id, name FROM letter_templates WHERE id = ?", [$templateId]);
if (!$template) {
    errorResponse('Template not found', 404);
}

// Fetch versions
$versions = dbFetchAll(
    "SELECT
        id,
        template_id,
        version_number,
        change_notes,
        created_at,
        changed_by,
        (SELECT full_name FROM users WHERE id = letter_template_versions.changed_by) AS changed_by_name
     FROM letter_template_versions
     WHERE template_id = ?
     ORDER BY version_number DESC",
    [$templateId]
);

successResponse([
    'template' => $template,
    'versions' => $versions
], 'Retrieved ' . count($versions) . ' version(s)');
