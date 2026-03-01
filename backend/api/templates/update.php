<?php
/**
 * PUT /api/templates/{id}
 * Update a letter template. Saves a version snapshot before updating.
 */
$userId = requireAuth();
$id     = (int)$_GET['id'];
$input  = getInput();

$template = dbFetchOne("SELECT * FROM letter_templates WHERE id = ?", [$id]);
if (!$template) errorResponse('Template not found', 404);

// Save current version in letter_template_versions before update
$maxVersion = dbFetchOne(
    "SELECT COALESCE(MAX(version_number), 0) AS max_ver FROM letter_template_versions WHERE template_id = ?",
    [$id]
);
$nextVersion = ($maxVersion['max_ver'] ?? 0) + 1;

dbInsert('letter_template_versions', [
    'template_id'      => $id,
    'version_number'   => $nextVersion,
    'body_template'    => $template['body_template'],
    'subject_template' => $template['subject_template'],
    'changed_by'       => $userId,
    'change_notes'     => sanitizeString($input['change_notes'] ?? ''),
]);

// Build update data
$data = [];

if (isset($input['name']))             $data['name'] = sanitizeString($input['name']);
if (isset($input['description']))      $data['description'] = sanitizeString($input['description']);
if (isset($input['body_template']))    $data['body_template'] = $input['body_template'];
if (isset($input['subject_template'])) $data['subject_template'] = sanitizeString($input['subject_template']);

if (!empty($input['template_type'])) {
    $validTypes = ['medical_records','health_ledger','bulk_request','custom','balance_verification'];
    if (!validateEnum($input['template_type'], $validTypes)) errorResponse('Invalid template_type');
    $data['template_type'] = $input['template_type'];
}

if (isset($input['is_default'])) {
    $data['is_default'] = (int)$input['is_default'];
    if ($data['is_default']) {
        $type = $input['template_type'] ?? $template['template_type'];
        dbUpdate('letter_templates', ['is_default' => 0],
            'template_type = ? AND is_default = 1 AND id != ?',
            [$type, $id]
        );
    }
}

if (isset($input['is_active'])) {
    $data['is_active'] = (int)$input['is_active'];
}

if (empty($data)) errorResponse('No fields to update');

dbUpdate('letter_templates', $data, 'id = ?', [$id]);

logActivity($userId, 'update', 'letter_template', $id, [
    'version' => $nextVersion,
    'fields'  => array_keys($data),
]);

successResponse(null, 'Template updated');
