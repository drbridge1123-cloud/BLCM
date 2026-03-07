<?php
/**
 * BLCM Central API Router
 * Routes all API requests to the appropriate handler file
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../storage/logs/php_errors.log');

ob_start();

$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoloadPath)) require_once $autoloadPath;

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/email.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../helpers/commission.php';
require_once __DIR__ . '/../helpers/date.php';
require_once __DIR__ . '/../helpers/csv.php';
require_once __DIR__ . '/../helpers/escalation.php';
require_once __DIR__ . '/../helpers/letter-template.php';
require_once __DIR__ . '/../helpers/email.php';
require_once __DIR__ . '/../helpers/fax.php';

ob_end_clean();
ob_start();

header('Content-Type: application/json; charset=utf-8');

$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/blcm/backend/api';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = str_replace($basePath, '', $path);
$path = trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];

$segments = $path ? explode('/', $path) : [];
$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;
$action = $segments[2] ?? null;

switch ($resource) {

    // ── Auth ──
    case 'auth':
        $authAction = $id ?? '';
        switch ($authAction) {
            case 'login':  require __DIR__ . '/auth/login.php'; break;
            case 'logout': require __DIR__ . '/auth/logout.php'; break;
            case 'me':     require __DIR__ . '/auth/me.php'; break;
            default: errorResponse('Auth endpoint not found', 404);
        }
        break;

    // ── Users ──
    case 'users':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/users/list.php';
        } elseif ($method === 'GET' && $id && !$action) {
            $_GET['id'] = $id;
            require __DIR__ . '/users/get.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/users/create.php';
        } elseif ($method === 'PUT' && $id && $action === 'toggle-active') {
            $_GET['id'] = $id;
            require __DIR__ . '/users/toggle-active.php';
        } elseif ($method === 'PUT' && $id && $action === 'reset-password') {
            $_GET['id'] = $id;
            require __DIR__ . '/users/reset-password.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/users/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/users/delete.php';
        } else {
            errorResponse('Users endpoint not found', 404);
        }
        break;

    // ── Dashboard ──
    case 'dashboard':
        $dashAction = $id ?? 'summary';
        $file = __DIR__ . "/dashboard/{$dashAction}.php";
        if (file_exists($file)) { require $file; }
        else { errorResponse('Dashboard endpoint not found', 404); }
        break;

    // ── Notifications ──
    case 'notifications':
        $file = __DIR__ . '/notifications/' . ($id ? ($action ?? 'get') : 'list') . '.php';
        if ($method === 'PUT' && $id === 'read-all') {
            require __DIR__ . '/notifications/read-all.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/notifications/read.php';
        } elseif ($method === 'GET') {
            require __DIR__ . '/notifications/list.php';
        } else {
            errorResponse('Notifications endpoint not found', 404);
        }
        break;

    // ── Messages ──
    case 'messages':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/messages/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/messages/create.php';
        } elseif ($method === 'PUT' && $id === 'read-all') {
            $_GET['id'] = 'read-all';
            require __DIR__ . '/messages/mark-read.php';
        } elseif ($method === 'PUT' && $id && $action === 'read') {
            $_GET['id'] = $id;
            require __DIR__ . '/messages/mark-read.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/messages/delete.php';
        } else {
            errorResponse('Messages endpoint not found', 404);
        }
        break;

    // ── Activity Log ──
    case 'activity-log':
        if ($method === 'GET') {
            require __DIR__ . '/activity-log/list.php';
        } else {
            errorResponse('Activity log endpoint not found', 404);
        }
        break;

    // ── BL Cases ──
    case 'bl-cases':
        if ($method === 'GET' && $id === 'export') {
            require __DIR__ . '/bl-cases/export.php';
        } elseif ($method === 'GET' && $id === 'pending-assignments') {
            require __DIR__ . '/bl-cases/pending-assignments.php';
        } elseif (in_array($method, ['PUT', 'POST']) && $id && $action === 'assign') {
            $_GET['id'] = $id;
            require __DIR__ . '/bl-cases/assign.php';
        } elseif ($method === 'PUT' && $id && $action === 'respond-assignment') {
            $_GET['id'] = $id;
            require __DIR__ . '/bl-cases/respond-assignment.php';
        } elseif ($method === 'GET' && !$id) {
            require __DIR__ . '/bl-cases/list.php';
        } elseif ($method === 'GET' && $id && !$action) {
            $_GET['id'] = $id;
            require __DIR__ . '/bl-cases/get.php';
        } elseif ($method === 'POST' && $id === 'change-status') {
            // POST /cases/change-status with case id in body — handled differently
            require __DIR__ . '/bl-cases/change-status.php';
        } elseif ($method === 'POST' && $id === 'send-back') {
            require __DIR__ . '/bl-cases/send-back.php';
        } elseif ($method === 'POST' && $id && $action === 'change-status') {
            $_GET['id'] = $id;
            require __DIR__ . '/bl-cases/change-status.php';
        } elseif ($method === 'POST' && $id && $action === 'send-back') {
            $_GET['id'] = $id;
            require __DIR__ . '/bl-cases/send-back.php';
        } elseif ($method === 'POST' && $id && $action === 'activate-providers') {
            $_GET['id'] = $id;
            require __DIR__ . '/bl-cases/activate-providers.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/bl-cases/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/bl-cases/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/bl-cases/delete.php';
        } else {
            errorResponse('Cases endpoint not found', 404);
        }
        break;

    // ── Clients ──
    case 'clients':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/clients/list.php';
        } elseif ($method === 'GET' && $id === 'lookup') {
            require __DIR__ . '/clients/lookup.php';
        } elseif ($method === 'GET' && $id && !$action) {
            $_GET['id'] = $id;
            require __DIR__ . '/clients/get.php';
        } elseif ($method === 'POST' && $id === 'save') {
            require __DIR__ . '/clients/save.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/clients/delete.php';
        } else {
            errorResponse('Clients endpoint not found', 404);
        }
        break;

    // ── Attorney ──
    case 'attorney':
        if ($method === 'GET' && $id === 'export') {
            require __DIR__ . '/attorney/export.php';
        } elseif ($method === 'GET' && $id === 'stats') {
            require __DIR__ . '/attorney/stats.php';
        } elseif ($method === 'GET' && $id === 'transfer-history') {
            require __DIR__ . '/attorney/transfer-history.php';
        } elseif ($method === 'GET' && !$id) {
            require __DIR__ . '/attorney/list.php';
        } elseif ($method === 'GET' && $id && !$action) {
            $_GET['id'] = $id;
            require __DIR__ . '/attorney/get.php';
        } elseif ($method === 'POST' && $id === 'import') {
            require __DIR__ . '/attorney/import.php';
        } elseif ($method === 'POST' && $id === 'transfer') {
            require __DIR__ . '/attorney/transfer.php';
        } elseif ($method === 'POST' && $id === 'send-to-billing-final') {
            require __DIR__ . '/attorney/send-to-billing-final.php';
        } elseif ($method === 'POST' && $id === 'toggle-date') {
            require __DIR__ . '/attorney/toggle-date.php';
        } elseif ($method === 'POST' && $id === 'top-offer') {
            require __DIR__ . '/attorney/top-offer.php';
        } elseif ($method === 'POST' && $id === 'settle-demand') {
            require __DIR__ . '/attorney/settle-demand.php';
        } elseif ($method === 'POST' && $id === 'to-litigation') {
            require __DIR__ . '/attorney/to-litigation.php';
        } elseif ($method === 'POST' && $id === 'to-uim') {
            require __DIR__ . '/attorney/to-uim.php';
        } elseif ($method === 'POST' && $id === 'settle-litigation') {
            require __DIR__ . '/attorney/settle-litigation.php';
        } elseif ($method === 'POST' && $id === 'settle-uim') {
            require __DIR__ . '/attorney/settle-uim.php';
        } elseif ($method === 'POST' && $id === 'send-to-accounting') {
            require __DIR__ . '/attorney/send-to-accounting.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/attorney/create.php';
        } elseif ($method === 'PUT' && $id === 'edit-litigation') {
            require __DIR__ . '/attorney/edit-litigation.php';
        } elseif ($method === 'PUT' && $id === 'edit-uim') {
            require __DIR__ . '/attorney/edit-uim.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/attorney/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/attorney/delete.php';
        } else {
            errorResponse('Attorney cases endpoint not found', 404);
        }
        break;

    // ── Employee Commissions ──
    case 'commissions':
        if ($method === 'GET' && $id === 'stats') {
            require __DIR__ . '/commissions/stats.php';
        } elseif ($method === 'GET' && $id === 'export') {
            require __DIR__ . '/commissions/export.php';
        } elseif ($method === 'GET' && !$id) {
            require __DIR__ . '/commissions/list.php';
        } elseif ($method === 'POST' && $id === 'approve') {
            require __DIR__ . '/commissions/approve.php';
        } elseif ($method === 'POST' && $id === 'bulk-approve') {
            require __DIR__ . '/commissions/bulk-approve.php';
        } elseif ($method === 'POST' && $id === 'toggle-check') {
            require __DIR__ . '/commissions/toggle-check.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/commissions/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/commissions/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/commissions/delete.php';
        } else {
            errorResponse('Commissions endpoint not found', 404);
        }
        break;

    // ── Referrals ──
    case 'referrals':
        if ($method === 'GET' && $id === 'export') {
            require __DIR__ . '/referrals/export.php';
        } elseif ($method === 'GET' && $id === 'report') {
            require __DIR__ . '/referrals/report.php';
        } elseif ($method === 'GET' && !$id) {
            require __DIR__ . '/referrals/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/referrals/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/referrals/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/referrals/delete.php';
        } else {
            errorResponse('Referrals endpoint not found', 404);
        }
        break;

    // ── Traffic ──
    case 'traffic':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/traffic/list.php';
        } elseif ($method === 'POST' && $id === 'files') {
            require __DIR__ . '/traffic/files.php';
        } elseif ($method === 'GET' && $id === 'files') {
            require __DIR__ . '/traffic/files.php';
        } elseif ($method === 'DELETE' && $id === 'files') {
            require __DIR__ . '/traffic/files.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/traffic/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/traffic/update.php';
        } elseif ($method === 'DELETE' && $id && $id !== 'files') {
            $_GET['id'] = $id;
            require __DIR__ . '/traffic/delete.php';
        } else {
            errorResponse('Traffic endpoint not found', 404);
        }
        break;

    // ── Traffic Requests ──
    case 'traffic-requests':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/traffic-requests/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/traffic-requests/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/traffic-requests/respond.php';
        } else {
            errorResponse('Traffic requests endpoint not found', 404);
        }
        break;

    // ── Demand Requests ──
    case 'demand-requests':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/demand-requests/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/demand-requests/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/demand-requests/respond.php';
        } else {
            errorResponse('Demand requests endpoint not found', 404);
        }
        break;

    // ── Deadline Requests ──
    case 'deadline-requests':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/deadline-requests/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/deadline-requests/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/deadline-requests/respond.php';
        } else {
            errorResponse('Deadline requests endpoint not found', 404);
        }
        break;

    // ── Goals ──
    case 'goals':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/goals/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/goals/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/goals/update.php';
        } else {
            errorResponse('Goals endpoint not found', 404);
        }
        break;

    // ── Performance ──
    case 'performance':
        if ($method === 'GET') {
            require __DIR__ . '/performance/list.php';
        } elseif ($method === 'POST') {
            require __DIR__ . '/performance/generate.php';
        } else {
            errorResponse('Performance endpoint not found', 404);
        }
        break;

    // ── Providers ──
    case 'providers':
        if ($method === 'GET' && $id === 'export') {
            require __DIR__ . '/providers/export.php';
        } elseif ($method === 'GET' && $id === 'search') {
            require __DIR__ . '/providers/search.php';
        } elseif ($method === 'GET' && !$id) {
            require __DIR__ . '/providers/list.php';
        } elseif ($method === 'GET' && $id && !$action) {
            $_GET['id'] = $id;
            require __DIR__ . '/providers/get.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/providers/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/providers/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/providers/delete.php';
        } else {
            errorResponse('Providers endpoint not found', 404);
        }
        break;

    // ── Insurance Companies ──
    case 'insurance-companies':
        if ($method === 'GET' && $id === 'search') {
            require __DIR__ . '/insurance-companies/search.php';
        } elseif ($method === 'GET' && !$id) {
            require __DIR__ . '/insurance-companies/list.php';
        } elseif ($method === 'GET' && $id && !$action) {
            $_GET['id'] = $id;
            require __DIR__ . '/insurance-companies/get.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/insurance-companies/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/insurance-companies/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/insurance-companies/delete.php';
        } else {
            errorResponse('Insurance companies endpoint not found', 404);
        }
        break;

    // ── Adjusters ──
    case 'adjusters':
        if ($method === 'GET' && $id === 'lookup') {
            require __DIR__ . '/adjusters/lookup.php';
        } elseif ($method === 'GET' && !$id) {
            require __DIR__ . '/adjusters/list.php';
        } elseif ($method === 'GET' && $id && !$action) {
            $_GET['id'] = $id;
            require __DIR__ . '/adjusters/get.php';
        } elseif ($method === 'POST' && $id === 'save') {
            require __DIR__ . '/adjusters/save.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/adjusters/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/adjusters/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/adjusters/delete.php';
        } else {
            errorResponse('Adjusters endpoint not found', 404);
        }
        break;

    // ── Prelitigation ──
    case 'prelitigation':
        if ($method === 'GET' && $id === 'list') {
            require __DIR__ . '/prelitigation/list.php';
        } elseif ($method === 'GET' && $id === 'followup-history') {
            require __DIR__ . '/prelitigation/followup-history.php';
        } elseif ($method === 'GET' && $id === 'template') {
            require __DIR__ . '/prelitigation/export-template.php';
        } elseif ($method === 'POST' && $id === 'import') {
            require __DIR__ . '/prelitigation/import.php';
        } elseif ($method === 'POST' && $id === 'log-followup') {
            require __DIR__ . '/prelitigation/log-followup.php';
        } elseif ($method === 'POST' && $id === 'complete') {
            require __DIR__ . '/prelitigation/complete.php';
        } else {
            errorResponse('Prelitigation tracker endpoint not found', 404);
        }
        break;

    // ── Accounting ──
    case 'accounting':
        if ($method === 'GET' && $id === 'list') {
            require __DIR__ . '/accounting/list.php';
        } elseif ($method === 'GET' && $id === 'list-disbursements') {
            require __DIR__ . '/accounting/list-disbursements.php';
        } elseif ($method === 'POST' && $id === 'create-disbursement') {
            require __DIR__ . '/accounting/create-disbursement.php';
        } elseif ($method === 'PUT' && $id === 'update-disbursement') {
            require __DIR__ . '/accounting/update-disbursement.php';
        } elseif ($method === 'POST' && $id === 'complete') {
            require __DIR__ . '/accounting/complete.php';
        } else {
            errorResponse('Accounting tracker endpoint not found', 404);
        }
        break;

    // ── Billing ──
    case 'billing':
        if ($method === 'GET' && $id === 'list') {
            require __DIR__ . '/billing/list.php';
        } elseif ($method === 'GET' && $id === 'pending-assignments') {
            require __DIR__ . '/billing/pending-assignments.php';
        } else {
            errorResponse('Billing endpoint not found', 404);
        }
        break;

    // ── Case Tasks (Checklist) ──
    case 'case-tasks':
        if ($method === 'GET' && $id === 'summary') {
            require __DIR__ . '/case-tasks/summary.php';
        } elseif ($method === 'GET' && !$id) {
            require __DIR__ . '/case-tasks/list.php';
        } elseif ($method === 'POST' && $id === 'initialize') {
            require __DIR__ . '/case-tasks/initialize.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/case-tasks/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/case-tasks/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/case-tasks/delete.php';
        } else {
            errorResponse('Case tasks endpoint not found', 404);
        }
        break;

    // ── Case Adjusters ──
    case 'case-adjusters':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/case-adjusters/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/case-adjusters/save.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/case-adjusters/delete.php';
        } else {
            errorResponse('Case Adjusters endpoint not found', 404);
        }
        break;

    // ── Case Providers ──
    case 'case-providers':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/case-providers/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/case-providers/create.php';
        } elseif ($method === 'PUT' && $id && $action === 'update-status') {
            $_GET['id'] = $id;
            require __DIR__ . '/case-providers/update-status.php';
        } elseif ($method === 'PUT' && $id && $action === 'assign') {
            $_GET['id'] = $id;
            require __DIR__ . '/case-providers/assign.php';
        } elseif ($method === 'PUT' && $id && $action === 'update-deadline') {
            $_GET['id'] = $id;
            require __DIR__ . '/case-providers/update-deadline.php';
        } elseif ($method === 'PUT' && $id === 'bulk-respond') {
            require __DIR__ . '/case-providers/bulk-respond.php';
        } elseif ($method === 'PUT' && $id && $action === 'respond') {
            $_GET['id'] = $id;
            require __DIR__ . '/case-providers/respond.php';
        } elseif ($method === 'PUT' && $id && $action === 'complete-treatment') {
            $_GET['id'] = $id;
            require __DIR__ . '/case-providers/complete-treatment.php';
        } elseif ($method === 'PUT' && $id && !$action) {
            $_GET['id'] = $id;
            require __DIR__ . '/case-providers/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/case-providers/delete.php';
        } else {
            errorResponse('Case-providers endpoint not found', 404);
        }
        break;

    // ── Record Requests ──
    case 'requests':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/requests/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/requests/create.php';
        } elseif ($method === 'GET' && $id && $action === 'preview') {
            $_GET['id'] = $id;
            require __DIR__ . '/requests/preview.php';
        } elseif ($method === 'POST' && $id && $action === 'send') {
            $_GET['id'] = $id;
            require __DIR__ . '/requests/send.php';
        } elseif ($method === 'POST' && $id && $action === 'attach') {
            $_GET['id'] = $id;
            require __DIR__ . '/requests/attach.php';
        } elseif ($method === 'POST' && $id === 'preview-bulk') {
            require __DIR__ . '/requests/preview-bulk.php';
        } elseif ($method === 'POST' && $id === 'bulk-create') {
            require __DIR__ . '/requests/bulk-create.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/requests/delete.php';
        } else {
            errorResponse('Requests endpoint not found', 404);
        }
        break;

    // ── Record Receipts ──
    case 'receipts':
        if ($method === 'POST' && !$id) {
            require __DIR__ . '/receipts/create.php';
        } else {
            errorResponse('Receipts endpoint not found', 404);
        }
        break;

    // ── Case Notes ──
    case 'notes':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/notes/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/notes/create.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/notes/delete.php';
        } else {
            errorResponse('Notes endpoint not found', 404);
        }
        break;

    // ── Case Documents ──
    case 'documents':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/documents/list.php';
        } elseif ($method === 'POST' && $id === 'upload') {
            require __DIR__ . '/documents/upload.php';
        } elseif ($method === 'POST' && $id === 'generate-provider-version') {
            require __DIR__ . '/documents/generate-provider-version.php';
        } elseif ($method === 'GET' && $id && $action === 'download') {
            $_GET['id'] = $id;
            require __DIR__ . '/documents/download.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/documents/delete.php';
        } else {
            errorResponse('Documents endpoint not found', 404);
        }
        break;

    // ── Letter Templates ──
    case 'templates':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/templates/list.php';
        } elseif ($method === 'GET' && $id && $action === 'versions') {
            $_GET['id'] = $id;
            require __DIR__ . '/templates/versions.php';
        } elseif ($method === 'GET' && $id && !$action) {
            $_GET['id'] = $id;
            require __DIR__ . '/templates/get.php';
        } elseif ($method === 'POST' && $id && $action === 'restore') {
            $_GET['id'] = $id;
            require __DIR__ . '/templates/restore.php';
        } elseif ($method === 'POST' && $id === 'preview') {
            require __DIR__ . '/templates/preview.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/templates/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/templates/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/templates/delete.php';
        } else {
            errorResponse('Templates endpoint not found', 404);
        }
        break;

    // ── MBR Lines (direct line operations) ──
    case 'mbr-lines':
        if ($method === 'PUT' && $id) {
            $_GET['line_id'] = $id;
            require __DIR__ . '/mbr/update-line.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['line_id'] = $id;
            require __DIR__ . '/mbr/delete-line.php';
        } else {
            errorResponse('MBR Lines endpoint not found', 404);
        }
        break;

    // ── MBR ──
    case 'mbr':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/mbr/list.php';
        } elseif ($method === 'GET' && $id && !$action) {
            $_GET['id'] = $id;
            require __DIR__ . '/mbr/get.php';
        } elseif ($method === 'POST' && $id && ($action === 'add-line' || $action === 'lines')) {
            $_GET['id'] = $id;
            require __DIR__ . '/mbr/add-line.php';
        } elseif ($method === 'POST' && $id && $action === 'activate-providers') {
            $_GET['id'] = $id;
            require __DIR__ . '/mbr/activate-providers.php';
        } elseif ($method === 'POST' && $id && !$action) {
            $_GET['id'] = $id;
            require __DIR__ . '/mbr/create.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/mbr/create.php';
        } elseif (($method === 'PUT' || $method === 'POST') && $id && $action === 'complete') {
            $_GET['id'] = $id;
            require __DIR__ . '/mbr/complete.php';
        } elseif (($method === 'PUT' || $method === 'POST') && $id && $action === 'approve') {
            $_GET['id'] = $id;
            require __DIR__ . '/mbr/approve.php';
        } elseif ($method === 'PUT' && $id && $action === 'update-line') {
            $_GET['id'] = $id;
            require __DIR__ . '/mbr/update-line.php';
        } elseif ($method === 'DELETE' && $id && $action === 'delete-line') {
            $_GET['id'] = $id;
            require __DIR__ . '/mbr/delete-line.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/mbr/update.php';
        } else {
            errorResponse('MBR endpoint not found', 404);
        }
        break;

    // ── Bank Reconciliation ──
    case 'bank-reconciliation':
        if ($method === 'GET' && $id === 'batches') {
            require __DIR__ . '/bank-reconciliation/batches.php';
        } elseif ($method === 'GET' && $id === 'search-payments') {
            require __DIR__ . '/bank-reconciliation/search-payments.php';
        } elseif ($method === 'GET' && !$id) {
            require __DIR__ . '/bank-reconciliation/list.php';
        } elseif ($method === 'POST' && $id === 'import') {
            require __DIR__ . '/bank-reconciliation/import.php';
        } elseif ($method === 'PUT' && $id && $action === 'match') {
            $_GET['id'] = $id;
            require __DIR__ . '/bank-reconciliation/match.php';
        } elseif ($method === 'PUT' && $id && $action === 'unmatch') {
            $_GET['id'] = $id;
            require __DIR__ . '/bank-reconciliation/unmatch.php';
        } elseif ($method === 'PUT' && $id && $action === 'ignore') {
            $_GET['id'] = $id;
            require __DIR__ . '/bank-reconciliation/ignore.php';
        } elseif ($method === 'DELETE' && $id === 'delete-batch') {
            require __DIR__ . '/bank-reconciliation/delete-batch.php';
        } else {
            errorResponse('Bank reconciliation endpoint not found', 404);
        }
        break;

    // ── Settlement ──
    case 'settlement':
        if ($method === 'GET' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/settlement/get.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/settlement/update.php';
        } else {
            errorResponse('Settlement endpoint not found', 404);
        }
        break;

    // ── Expense Report ──
    case 'expense-report':
        if ($method === 'GET' && $id === 'list') {
            require __DIR__ . '/expense-report/list.php';
        } elseif ($method === 'GET' && $id === 'export') {
            require __DIR__ . '/expense-report/export.php';
        } else {
            errorResponse('Expense report endpoint not found', 404);
        }
        break;

    // ── MR Fee Payments ──
    case 'mr-fee-payments':
        if ($method === 'GET' && $id === 'expense-report') {
            require __DIR__ . '/mr-fee-payments/expense-report.php';
        } elseif ($method === 'GET' && !$id) {
            require __DIR__ . '/mr-fee-payments/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/mr-fee-payments/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/mr-fee-payments/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/mr-fee-payments/delete.php';
        } else {
            errorResponse('MR fee payments endpoint not found', 404);
        }
        break;

    // ── Health Ledger ──
    case 'health-ledger':
        if ($method === 'GET' && $id === 'list') {
            require __DIR__ . '/health-ledger/list.php';
        } elseif ($method === 'POST' && $id === 'import') {
            require __DIR__ . '/health-ledger/import.php';
        } elseif ($method === 'GET' && $id && $action === 'requests') {
            $_GET['id'] = $id;
            require __DIR__ . '/health-ledger/list-requests.php';
        } elseif ($method === 'POST' && $id && $action === 'requests') {
            $_GET['id'] = $id;
            require __DIR__ . '/health-ledger/create-request.php';
        } elseif ($method === 'GET' && !$id) {
            require __DIR__ . '/health-ledger/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/health-ledger/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/health-ledger/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/health-ledger/delete.php';
        } else {
            errorResponse('Health ledger endpoint not found', 404);
        }
        break;

    // ── Case Negotiations ──
    case 'negotiations':
        if ($method === 'DELETE' && $id === 'group' && isset($segments[2], $segments[3], $segments[4])) {
            // DELETE /api/negotiations/group/{case_id}/{coverage_type}/{coverage_index}
            $_GET['case_id'] = $segments[2];
            $_GET['coverage_type'] = $segments[3];
            $_GET['coverage_index'] = $segments[4];
            require __DIR__ . '/negotiations/delete-group.php';
        } elseif ($method === 'GET' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/negotiations/get.php';
        } elseif ($method === 'POST' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/negotiations/save.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/negotiations/delete.php';
        } else {
            errorResponse('Negotiations endpoint not found', 404);
        }
        break;

    // ── Provider Negotiations ──
    case 'provider-negotiations':
        if ($method === 'GET' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/provider-negotiations/get.php';
        } elseif ($method === 'POST' && $id && $action === 'populate') {
            $_GET['id'] = $id;
            require __DIR__ . '/provider-negotiations/auto-populate.php';
        } elseif ($method === 'POST' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/provider-negotiations/save.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/provider-negotiations/delete.php';
        } else {
            errorResponse('Provider negotiations endpoint not found', 404);
        }
        break;

    default:
        errorResponse("Resource '{$resource}' not found", 404);
}
