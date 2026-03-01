<?php
/**
 * GET /api/users
 * List users - admins/managers get full data, regular users get basic name list
 */
$userId = requireAuth();
$userRole = $_SESSION['user_role'] ?? 'staff';
$isAdmin = in_array($userRole, ['admin', 'manager']);

$role = $_GET['role'] ?? null;
$active = $_GET['active'] ?? null;
$activeOnly = $_GET['active_only'] ?? null;
$search = $_GET['search'] ?? null;
$team = $_GET['team'] ?? null;

$where = '1=1';
$params = [];

if ($role) {
    $where .= ' AND role = ?';
    $params[] = $role;
}
if ($active !== null && $active !== '') {
    $where .= ' AND is_active = ?';
    $params[] = (int)$active;
}
if ($activeOnly) {
    $where .= ' AND is_active = 1';
    $where .= " AND full_name != 'New Attorney'";
}
if ($search) {
    $where .= ' AND (username LIKE ? OR full_name LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($team) {
    $where .= ' AND team = ?';
    $params[] = $team;
}

if ($isAdmin) {
    $users = dbFetchAll(
        "SELECT id, username, full_name, display_name, role, team, email,
                job_title, card_last4, commission_rate, uses_presuit_offer,
                permissions, is_active, created_at
         FROM users WHERE {$where} ORDER BY id",
        $params
    );

    foreach ($users as &$u) {
        $u['permissions'] = $u['permissions']
            ? json_decode($u['permissions'], true)
            : getDefaultPermissions($u['role']);
        $u['commission_rate'] = (float)$u['commission_rate'];
    }
    unset($u);
} else {
    // Non-admin users get a basic list (for staff tabs, dropdowns, etc.)
    $users = dbFetchAll(
        "SELECT id, full_name, display_name, role, team
         FROM users WHERE {$where} ORDER BY full_name",
        $params
    );
}

successResponse($users);
