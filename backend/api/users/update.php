<?php
/**
 * PUT /api/users/{id}
 * Update user (admin only)
 */
requireAdmin();
$id = (int)$_GET['id'];
$input = getInput();

$user = dbFetchOne("SELECT * FROM users WHERE id = ?", [$id]);
if (!$user) errorResponse('User not found', 404);

$data = [];

if (isset($input['full_name'])) $data['full_name'] = sanitizeString($input['full_name']);
if (isset($input['display_name'])) $data['display_name'] = sanitizeString($input['display_name']);
if (isset($input['email'])) $data['email'] = sanitizeString($input['email']);
if (isset($input['job_title'])) $data['job_title'] = sanitizeString($input['job_title']);
if (isset($input['card_last4'])) $data['card_last4'] = sanitizeString($input['card_last4']);
if (array_key_exists('team', $input)) $data['team'] = sanitizeString($input['team'] ?? '');

if (isset($input['role'])) {
    $validRoles = ['admin', 'manager', 'accounting', 'staff', 'attorney'];
    if (!validateEnum($input['role'], $validRoles)) errorResponse('Invalid role');
    $data['role'] = $input['role'];
}

if (isset($input['commission_rate'])) {
    $rate = (float)$input['commission_rate'];
    if ($rate < COMMISSION_RATE_MIN || $rate > COMMISSION_RATE_MAX) {
        errorResponse("Commission rate must be between " . COMMISSION_RATE_MIN . "% and " . COMMISSION_RATE_MAX . "%");
    }
    $data['commission_rate'] = $rate;
}

if (isset($input['uses_presuit_offer'])) {
    $data['uses_presuit_offer'] = (int)$input['uses_presuit_offer'];
}

if (isset($input['permissions'])) {
    $data['permissions'] = json_encode($input['permissions']);
}

if (empty($data)) errorResponse('No fields to update');

dbUpdate('users', $data, 'id = ?', [$id]);

logActivity($_SESSION['user_id'], 'update', 'user', $id, $data);

successResponse(null, 'User updated successfully');
