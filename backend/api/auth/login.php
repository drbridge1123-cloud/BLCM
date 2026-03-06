<?php
/**
 * POST /api/auth/login
 * Authenticate user and create session
 */
if ($method !== 'POST') errorResponse('Method not allowed', 405);

$input = getInput();
$errors = validateRequired($input, ['username', 'password']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

// ── Rate limiting (file-based, per username) ──
$rateLimitDir = sys_get_temp_dir() . '/blcm_rate_limit';
if (!is_dir($rateLimitDir)) @mkdir($rateLimitDir, 0700, true);

$username = strtolower(trim($input['username']));
$rateLimitFile = $rateLimitDir . '/' . md5($username) . '.json';
$maxAttempts = 5;
$windowSeconds = 900; // 15 minutes

if (file_exists($rateLimitFile)) {
    $rlData = json_decode(file_get_contents($rateLimitFile), true) ?: [];
    // Purge attempts older than window
    $rlData['attempts'] = array_values(array_filter(
        $rlData['attempts'] ?? [],
        fn($ts) => $ts > time() - $windowSeconds
    ));
    if (count($rlData['attempts']) >= $maxAttempts) {
        errorResponse('Too many login attempts. Please try again in 15 minutes.', 429);
    }
}

$user = dbFetchOne(
    "SELECT * FROM users WHERE username = ? AND is_active = 1",
    [$input['username']]
);

if (!$user || !password_verify($input['password'], $user['password_hash'])) {
    // Record failed attempt
    $rlData = file_exists($rateLimitFile)
        ? (json_decode(file_get_contents($rateLimitFile), true) ?: [])
        : [];
    $rlData['attempts'] = array_values(array_filter(
        $rlData['attempts'] ?? [],
        fn($ts) => $ts > time() - $windowSeconds
    ));
    $rlData['attempts'][] = time();
    file_put_contents($rateLimitFile, json_encode($rlData));

    errorResponse('Invalid username or password', 401);
}

// Login successful — clear rate limit
if (file_exists($rateLimitFile)) @unlink($rateLimitFile);

startSecureSession();
session_regenerate_id(true); // Prevent session fixation

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_permissions'] = $user['permissions']
    ? json_decode($user['permissions'], true)
    : getDefaultPermissions($user['role']);

logActivity($user['id'], 'login', 'user', $user['id']);

successResponse([
    'id' => $user['id'],
    'username' => $user['username'],
    'full_name' => $user['full_name'],
    'display_name' => $user['display_name'] ?: $user['full_name'],
    'role' => $user['role'],
    'permissions' => $_SESSION['user_permissions'],
    'commission_rate' => (float)$user['commission_rate'],
], 'Login successful');
