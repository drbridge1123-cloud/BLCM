# BLCM Backend API Reference

> Complete backend documentation covering routing, helpers, and all API endpoints.

---

## 1. API Architecture

### Router (backend/api/index.php)
- **Base URL:** `/blcm/backend/api/`
- **Pattern:** `/{resource}/{id}/{action}`
- **Method:** Determined by `$_SERVER['REQUEST_METHOD']`
- **Auth:** Every request goes through `requireAuth()` (except login)
- **Response:** Always JSON

### URL Routing Flow
```
Request: PUT /blcm/backend/api/attorney/42
  -> Router parses: resource='attorney', id=42, action=null
  -> Sets $GLOBALS['resource_id'] = 42
  -> Includes: backend/api/attorney/update.php
```

---

## 2. PHP Helpers (backend/helpers/)

### db.php - Database Abstraction
```php
getDB()                                    // PDO singleton (MySQL)
dbQuery($sql, $params = [])                // Execute query, return PDOStatement
dbFetchAll($sql, $params = [])             // Fetch all rows as assoc array
dbFetchOne($sql, $params = [])             // Fetch single row
dbInsert($table, $data)                    // INSERT, return lastInsertId
dbUpdate($table, $data, $where, $params)   // UPDATE with WHERE clause
dbDelete($table, $where, $params)          // DELETE with WHERE clause
dbCount($sql, $params = [])                // COUNT query, return integer
```

### auth.php - Authentication
```php
requireAuth()                // Check session, return user array or send 401
requirePermission($perm)     // Check permission or send 403
requireRole($roles)          // Check role (string or array) or send 403
getCurrentUser()             // Get user from session (no error if missing)
logActivity($userId, $action, $entityType, $entityId, $details = [])
```

### response.php - JSON Responses
```php
successResponse($data = null, $message = null)
// -> {"success": true, "data": ..., "message": ...}

errorResponse($message, $code = 400)
// -> HTTP $code, {"success": false, "message": "..."}

paginatedResponse($data, $pagination, $extra = [])
// -> {"success": true, "data": [...], "pagination": {...}, ...extra}
```

### validator.php - Input Validation
```php
getInput()                           // Get JSON body or $_POST
validateRequired($input, $fields)    // Returns array of error messages
sanitizeString($str)                 // trim + htmlspecialchars
sanitizeEmail($email)                // filter_var FILTER_VALIDATE_EMAIL
sanitizeInt($val)                    // intval
sanitizeFloat($val)                  // floatval
getPaginationParams()                // Returns [$page, $perPage] from $_GET
```

### commission.php - Commission Engine
```php
calculateCommission($settlementData, $employee, $phase)
// Complex calculation based on:
//   - Employee role and commission rate
//   - Settlement phase (demand, litigation, UIM)
//   - Attorney fee percentage
//   - Reduction calculations
```

### csv.php - CSV Import/Export
```php
parseCSV($uploadedFile)              // Parse uploaded CSV, return array of rows
outputCSV($filename, $headers, $rows) // Stream CSV download
```

### pdf-generator.php - PDF Generation
```php
generatePDF($html, $options = [])    // Render HTML to PDF via Dompdf
// Options: paper_size, orientation, filename
```

### pdf-overlay.php - PDF Template Overlay
```php
// Uses FPDI library to overlay text on existing PDF templates
// Adds: provider name, date, custom text at specified positions
```

### email.php - Email Sending
```php
sendEmail($config)
// $config: to, subject, body, attachments[], from, replyTo
// Uses PHPMailer with SMTP
// Supports embedded images
```

### fax.php - Fax Integration
```php
sendFax($config)
// $config: to (fax number), file (PDF path), provider ('faxage'|'phaxio')
// Integrates with Faxage and Phaxio APIs
```

### date.php - Date Utilities
```php
nextFollowUpDate($lastDate, $intervalDays)
isOverdue($dateStr)
businessDaysBetween($start, $end)
```

### escalation.php - Escalation Logic
```php
getEscalationTier($case)
// Determines tier based on days elapsed, follow-up count, status
createEscalationNotification($case, $tier)
```

### file-upload.php - File Management
```php
validateUpload($file, $allowedTypes, $maxSize)
saveUpload($file, $directory)          // Save to uploads/ directory
deleteFile($path)                      // Remove file from disk
```

### letter-template.php - Letter Renderer (52KB)
```php
renderLetterTemplate($templateId, $variables)
// Renders medical records request letters with variable substitution
// Supports multiple formats and template types
```

---

## 3. Standard API Patterns

### List Endpoint Pattern
```php
<?php
require_once __DIR__ . '/../../helpers/db.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/validator.php';

requireAuth();

$search = $_GET['search'] ?? null;
$sortBy = $_GET['sort_by'] ?? 'created_at';
$sortDir = strtoupper($_GET['sort_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

$where = '1=1';
$params = [];

if ($search) {
    $where .= ' AND (name LIKE ? OR email LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$allowedSorts = ['name', 'email', 'created_at'];
if (!in_array($sortBy, $allowedSorts)) $sortBy = 'created_at';

list($page, $perPage) = getPaginationParams();
$offset = ($page - 1) * $perPage;

$total = dbFetchOne("SELECT COUNT(*) as cnt FROM table WHERE {$where}", $params)['cnt'];
$items = dbFetchAll(
    "SELECT * FROM table WHERE {$where} ORDER BY {$sortBy} {$sortDir} LIMIT {$perPage} OFFSET {$offset}",
    $params
);

paginatedResponse($items, [
    'current_page' => $page,
    'per_page' => $perPage,
    'total' => (int)$total,
    'total_pages' => ceil($total / $perPage)
]);
```

### Create Endpoint Pattern
```php
requireAuth();
$input = getInput();
$errors = validateRequired($input, ['field1', 'field2']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$data = [
    'field1' => sanitizeString($input['field1']),
    'field2' => sanitizeString($input['field2'] ?? ''),
    'created_by' => $_SESSION['user_id'],
    'created_at' => date('Y-m-d H:i:s'),
];
$id = dbInsert('table_name', $data);
logActivity($_SESSION['user_id'], 'created', 'entity_type', $id);
successResponse(['id' => $id], 'Created successfully');
```

### Update Endpoint Pattern
```php
requireAuth();
$id = $GLOBALS['resource_id'] ?? null;
if (!$id) errorResponse('ID required', 400);

$input = getInput();
$data = [
    'field1' => sanitizeString($input['field1']),
    'updated_at' => date('Y-m-d H:i:s'),
];
dbUpdate('table_name', $data, 'id = ?', [$id]);
logActivity($_SESSION['user_id'], 'updated', 'entity_type', $id);
successResponse(null, 'Updated successfully');
```

### Delete Endpoint Pattern
```php
requireAuth();
$id = $GLOBALS['resource_id'] ?? null;
if (!$id) errorResponse('ID required', 400);

// Optional: check for dependencies
$count = dbCount("SELECT COUNT(*) as cnt FROM related_table WHERE parent_id = ?", [$id]);
if ($count > 0) errorResponse('Cannot delete: has related records');

dbDelete('table_name', 'id = ?', [$id]);
logActivity($_SESSION['user_id'], 'deleted', 'entity_type', $id);
successResponse(null, 'Deleted successfully');
```

---

## 4. JSON Response Format

### Success
```json
{ "success": true, "data": { "id": 1, "name": "..." }, "message": "Created successfully" }
```

### Error
```json
{ "success": false, "message": "Validation error: name is required" }
```

### Paginated List
```json
{
    "success": true,
    "data": [ { "id": 1, ... }, { "id": 2, ... } ],
    "pagination": {
        "current_page": 1,
        "per_page": 25,
        "total": 150,
        "total_pages": 6
    },
    "summary": { "total_amount": 50000 },
    "staff": [ { "id": 1, "display_name": "John" } ]
}
```

---

## 5. Complete API Endpoint Reference

### Auth (3 endpoints)
| Method | URL | Description |
|--------|-----|-------------|
| POST | `/auth/login` | Login with email/password |
| POST | `/auth/logout` | Destroy session |
| GET | `/auth/me` | Get current user info + unread_messages count |

### Users (7 endpoints)
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/users` | List users (search, sort) |
| GET | `/users/{id}` | Get single user |
| POST | `/users` | Create user (email, password, role, permissions) |
| PUT | `/users/{id}` | Update user |
| PUT | `/users/{id}/toggle-active` | Toggle active status |
| PUT | `/users/{id}/reset-password` | Reset password |
| DELETE | `/users/{id}` | Delete user |

### Dashboard (5 endpoints)
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/dashboard/summary` | Unified stats (role-based) |
| GET | `/dashboard/escalations` | Escalated cases |
| GET | `/dashboard/followup-due` | Cases due for follow-up |
| GET | `/dashboard/overdue` | Overdue cases |
| GET | `/dashboard/staff-metrics` | Staff performance |

### BL Cases (12 endpoints)
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/bl-cases` | List cases (search, sort, status, staff filters) |
| GET | `/bl-cases/{id}` | Get case detail with providers |
| GET | `/bl-cases/export` | Export CSV |
| GET | `/bl-cases/pending-assignments` | Pending assignments |
| POST | `/bl-cases` | Create case (case_number, client, dates) |
| POST | `/bl-cases/{id}/assign` | Assign to staff |
| POST | `/bl-cases/{id}/activate-providers` | Activate providers |
| POST | `/bl-cases/{id}/change-status` | Change case status |
| POST | `/bl-cases/{id}/send-back` | Send back to previous stage |
| PUT | `/bl-cases/{id}` | Update case |
| PUT | `/bl-cases/{id}/respond-assignment` | Accept/decline |
| DELETE | `/bl-cases/{id}` | Delete case |

### Case Providers (9 endpoints)
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/case-providers` | List providers for case |
| POST | `/case-providers` | Add provider to case |
| PUT | `/case-providers/{id}` | Update case-provider |
| PUT | `/case-providers/{id}/assign` | Assign to staff |
| PUT | `/case-providers/{id}/respond` | Accept/decline |
| PUT | `/case-providers/{id}/update-status` | Change status |
| PUT | `/case-providers/{id}/update-deadline` | Change deadline |
| PUT | `/case-providers/{id}/complete-treatment` | Mark treatment complete |
| DELETE | `/case-providers/{id}` | Remove from case |

### Record Requests (8 endpoints)
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/requests` | List requests for case/provider |
| GET | `/requests/{id}/preview` | Preview request letter |
| POST | `/requests` | Create request |
| POST | `/requests/bulk-create` | Bulk create requests |
| POST | `/requests/preview-bulk` | Preview bulk letters |
| POST | `/requests/{id}/send` | Send via email/fax |
| POST | `/requests/{id}/attach` | Attach file |
| DELETE | `/requests/{id}` | Delete request |

### Attorney (21 endpoints)
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/attorney` | List attorney cases |
| GET | `/attorney/{id}` | Get case detail |
| GET | `/attorney/export` | Export CSV |
| GET | `/attorney/stats` | Statistics |
| GET | `/attorney/transfer-history` | Transfer history |
| POST | `/attorney` | Create attorney case |
| POST | `/attorney/import` | Import from CSV |
| POST | `/attorney/transfer` | Transfer to attorney |
| POST | `/attorney/settle-demand` | Settle in demand |
| POST | `/attorney/settle-litigation` | Settle in litigation |
| POST | `/attorney/settle-uim` | Settle UIM |
| POST | `/attorney/to-litigation` | Move to litigation |
| POST | `/attorney/to-uim` | Move to UIM |
| POST | `/attorney/toggle-date` | Toggle date flag |
| POST | `/attorney/top-offer` | Set top offer |
| POST | `/attorney/send-to-accounting` | Send to accounting |
| POST | `/attorney/send-to-billing-final` | Send to billing |
| PUT | `/attorney/{id}` | Update case |
| PUT | `/attorney/edit-litigation` | Edit litigation |
| PUT | `/attorney/edit-uim` | Edit UIM |
| DELETE | `/attorney/{id}` | Delete case |

### Commissions (9 endpoints)
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/commissions` | List (pagination, filters, role-based) |
| GET | `/commissions/stats` | Statistics |
| GET | `/commissions/export` | Export CSV |
| POST | `/commissions` | Create commission |
| POST | `/commissions/approve` | Approve single |
| POST | `/commissions/bulk-approve` | Approve multiple |
| POST | `/commissions/toggle-check` | Toggle check received |
| PUT | `/commissions/{id}` | Update with recalculation |
| DELETE | `/commissions/{id}` | Delete commission |

### Referrals (6 endpoints)
| Method | URL | Description |
|--------|-----|-------------|
| GET | `/referrals` | List referrals |
| GET | `/referrals/export` | Export CSV |
| GET | `/referrals/report` | Referral report |
| POST | `/referrals` | Create referral |
| PUT | `/referrals/{id}` | Update referral |
| DELETE | `/referrals/{id}` | Delete referral |

### Providers (7), Clients (5), Adjusters (7), Insurance Companies (6)
Standard CRUD + search/export for each database entity.

### Traffic (6), Traffic Requests (3), Demand Requests (3), Deadline Requests (3)
CRUD + respond workflows.

### MBR (10+), Health Ledger (7), Negotiations (3), Provider Negotiations (4)
Specialized sub-resource endpoints with line-item management.

### Prelitigation (6), Accounting (5), Billing (2)
Tracker-specific endpoints with import/export.

### Bank Reconciliation (8)
CSV import, matching, unmatching, batch management.

### Documents (5), Notes (3), Receipts (1), Templates (8)
Supporting resource endpoints.

### Goals (3), Performance (2), Messages (4), Notifications (3), Activity Log (1)
Admin and communication endpoints.

### MR Fee Payments (5), Expense Report (2), Settlement (2)
Financial tracking endpoints.

---

## 6. Authentication Flow

```
1. User submits email/password to POST /auth/login
2. Server validates credentials against users table
3. On success: sets $_SESSION['user_id'], returns user object
4. Every subsequent API call: requireAuth() checks session
5. Frontend: api._request() catches 401, redirects to login page
6. Logout: POST /auth/logout destroys session
```

### Permission Check (Backend)
```php
requireAuth();                        // Must be logged in
requirePermission('cases');           // Must have 'cases' permission
requireRole('admin');                 // Must be admin
requireRole(['admin', 'manager']);    // Must be admin OR manager
```

### Permission Check (Frontend)
```javascript
$store.auth.hasPermission('cases')    // Returns boolean
$store.auth.isAdmin                   // Role check getter
```
