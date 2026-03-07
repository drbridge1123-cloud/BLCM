<?php
/**
 * Authentication, Session, CSRF, and Permission Helpers
 */

function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                 || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/blcm',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_name(SESSION_NAME);
        session_start();
    }
}

function requireAuth() {
    startSecureSession();
    if (empty($_SESSION['user_id'])) {
        if (isApiRequest()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            exit;
        }
        header('Location: /blcm/frontend/pages/auth/login.php');
        exit;
    }
    return $_SESSION['user_id'];
}

function requireAdmin() {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        if (isApiRequest()) {
            errorResponse('Admin access required', 403);
        }
        header('Location: /blcm/frontend/pages/dashboard/index.php');
        exit;
    }
}

function requireAdminOrManager() {
    requireAuth();
    if (!in_array($_SESSION['user_role'], ['admin', 'manager'])) {
        if (isApiRequest()) {
            errorResponse('Admin or Manager access required', 403);
        }
        header('Location: /blcm/frontend/pages/dashboard/index.php');
        exit;
    }
}

/**
 * All available permissions in BLCM
 */
function getAllPermissions() {
    return [
        'dashboard', 'cases', 'providers', 'mr_tracker',
        'prelitigation_tracker', 'accounting_tracker',
        'attorney_cases', 'traffic', 'commissions', 'commission_admin',
        'referrals', 'mbr', 'health_tracker', 'expense_report',
        'bank_reconciliation', 'reports', 'goals',
        'users', 'templates', 'activity_log', 'data_management', 'messages'
    ];
}

/**
 * Default permissions by role
 */
function getDefaultPermissions($role) {
    $defaults = [
        'admin' => getAllPermissions(),
        'manager' => [
            'dashboard', 'cases', 'providers', 'messages',
            'attorney_cases', 'prelitigation_tracker',
            'mr_tracker', 'health_tracker', 'accounting_tracker',
            'mbr', 'templates', 'goals'
        ],
        'attorney' => [
            'dashboard', 'attorney_cases', 'traffic',
            'commissions', 'messages'
        ],
        'paralegal' => [
            'dashboard', 'cases', 'providers', 'mr_tracker',
            'prelitigation_tracker',
            'commissions', 'referrals', 'goals', 'messages'
        ],
        'billing' => [
            'dashboard', 'cases', 'providers', 'mr_tracker',
            'commissions', 'messages'
        ],
        'accounting' => [
            'dashboard', 'cases', 'providers', 'mr_tracker',
            'accounting_tracker',
            'mbr', 'health_tracker', 'expense_report',
            'bank_reconciliation', 'messages'
        ],
    ];
    return $defaults[$role] ?? $defaults['paralegal'];
}

/**
 * Permission labels for UI display
 */
function getPermissionLabels() {
    return [
        'dashboard' => 'Dashboard',
        'cases' => 'Cases (MR)',
        'providers' => 'Providers',
        'mr_tracker' => 'MR Tracker',
        'attorney_cases' => 'Attorney Cases',
        'traffic' => 'Traffic',
        'commissions' => 'Commissions',
        'commission_admin' => 'Commission Admin',
        'referrals' => 'Referrals',
        'mbr' => 'MBR',
        'health_tracker' => 'Health Tracker',
        'expense_report' => 'Expense Report',
        'bank_reconciliation' => 'Bank Reconciliation',
        'reports' => 'Reports',
        'goals' => 'Goals',
        'users' => 'Users',
        'templates' => 'Templates',
        'activity_log' => 'Activity Log',
        'data_management' => 'Data Management',
        'prelitigation_tracker' => 'Prelitigation Tracker',
        'accounting_tracker' => 'Accounting Tracker',
        'messages' => 'Messages',
    ];
}

/**
 * Check if current user has a specific permission
 */
function requirePermission($page) {
    requireAuth();
    if ($_SESSION['user_role'] === 'admin') return;
    $perms = $_SESSION['user_permissions'] ?? [];
    if (!in_array($page, $perms)) {
        if (isApiRequest()) {
            errorResponse('Access denied', 403);
        }
        header('Location: /blcm/frontend/pages/dashboard/index.php');
        exit;
    }
}

function hasPermission($page) {
    if (empty($_SESSION['user_id'])) return false;
    if ($_SESSION['user_role'] === 'admin') return true;
    $perms = $_SESSION['user_permissions'] ?? [];
    return in_array($page, $perms);
}

function getCurrentUser() {
    startSecureSession();
    if (empty($_SESSION['user_id'])) return null;
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['user_role'],
        'permissions' => $_SESSION['user_permissions'] ?? []
    ];
}

function isApiRequest() {
    return strpos($_SERVER['REQUEST_URI'], '/api/') !== false
        || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

function generateCSRFToken() {
    startSecureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    startSecureSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
