<?php
/**
 * POST /api/templates
 * Create a new letter template
 */
$userId = requireAuth();
$input  = getInput();

$errors = validateRequired($input, ['name', 'body_template']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$data = [
    'name'          => sanitizeString($input['name']),
    'body_template' => $input['body_template'],
    'created_by'    => $userId,
];

// Optional fields
if (isset($input['description'])) {
    $data['description'] = sanitizeString($input['description']);
}

if (!empty($input['template_type'])) {
    $validTypes = ['medical_records','health_ledger','bulk_request','custom','balance_verification'];
    if (!validateEnum($input['template_type'], $validTypes)) errorResponse('Invalid template_type');
    $data['template_type'] = $input['template_type'];
}

if (isset($input['subject_template'])) {
    $data['subject_template'] = sanitizeString($input['subject_template']);
}

if (isset($input['is_default'])) {
    $data['is_default'] = (int)$input['is_default'];
    // If setting as default, unset other defaults of same type
    if ($data['is_default'] && !empty($data['template_type'])) {
        dbUpdate('letter_templates', ['is_default' => 0],
            'template_type = ? AND is_default = 1',
            [$data['template_type']]
        );
    }
}

$id = dbInsert('letter_templates', $data);

logActivity($userId, 'create', 'letter_template', $id, [
    'name' => $data['name'],
]);

successResponse(['id' => $id], 'Template created');
