# BLCM Frontend Pages Reference

> Complete documentation of all frontend pages, navigation, tabs, modals, and permissions.

---

## 1. Page Entry Point Pattern

Every page follows this standard pattern:

```php
<?php
$pageTitle = 'Attorney Cases';           // Shown in header
$currentPage = 'attorney_cases';         // Sidebar active state key
$pageContent = __DIR__ . '/_content.php'; // Content partial to include
$pageScripts = [                         // JS files loaded after content
    'assets/js/pages/attorney/index.js'
];
// Optional:
// $pageHeadScripts = ['https://cdn.../pdf.min.js'];  // Scripts before content
// $pageSubtitle = 'Manage all cases';                 // Subtitle below title

require_once __DIR__ . '/../../layouts/main.php';
```

The layout (`main.php`) then:
1. Renders `<head>` with shared CSS/JS
2. Includes sidebar with `$currentPage` for active highlighting
3. Includes header with `$pageTitle`
4. Includes `$pageContent` in `<main>`
5. Loads `$pageScripts` JS files
6. Loads Alpine.js last

---

## 2. Navigation Structure (Sidebar)

### Top Navigation (no section label)
| Sidebar Label | URL | $currentPage | Permission | Icon |
|---------------|-----|-------------|------------|------|
| Dashboard | `/dashboard/index.php` | `dashboard` | none | House |
| Database | `/providers/index.php` | `providers` | none | Building |
| Messages | `/messages/index.php` | `messages` | none | Chat bubble |

### Cases Section
| Sidebar Label | URL | $currentPage | Permission | Icon |
|---------------|-----|-------------|------------|------|
| BL Cases | `/bl-cases/index.php` | `cases` | `cases` | Document |
| Traffic Cases | `/traffic/index.php` | `traffic` | `traffic` | Clock |
| Referrals | `/referrals/index.php` | `referrals` | `referrals` | People |

### Trackers Section
| Sidebar Label | URL | $currentPage | Permission | Icon |
|---------------|-----|-------------|------------|------|
| Prelitigation | `/prelitigation/index.php` | `prelitigation_tracker` | `prelitigation_tracker` | Phone |
| Billing | `/billing/index.php` | `mr_tracker` | `mr_tracker` | Clipboard |
| Attorney | `/attorney/index.php` | `attorney_cases` | `attorney_cases` | Scale |
| Accounting | `/accounting/index.php` | `accounting_tracker` | `accounting_tracker` | Calculator |

### Finance Section
| Sidebar Label | URL | $currentPage | Permission | Icon |
|---------------|-----|-------------|------------|------|
| Commissions | `/commissions/index.php` | `commissions` | `commissions` | Dollar |

### Admin Section
| Sidebar Label | URL | $currentPage | Permission | Icon |
|---------------|-----|-------------|------------|------|
| Performance | `/reports/index.php` | `reports` | `reports` | Bar chart |
| Goals | `/reports/goals.php` | `goals` | `goals` | Lightning |
| Admin Control | `/admin/users.php` | `users` | `users` | People group |
| Activity Log | `/admin/activity-log.php` | `activity_log` | `activity_log` | Clipboard |

---

## 3. Detailed Page Documentation

### Dashboard
| Property | Value |
|----------|-------|
| **URL** | `/dashboard/index.php` |
| **Files** | `index.php`, `_content.php` |
| **JS Controller** | `dashboard/index.js` -> `dashboardPage()` |
| **API Calls** | `dashboard/summary` |
| **Features** | Summary stat cards, pending assignments, recent activity |
| **Modals** | None |
| **Tabs** | None |

---

### BL Cases - List
| Property | Value |
|----------|-------|
| **URL** | `/bl-cases/index.php` |
| **Files** | `index.php`, `_content.php` |
| **JS Controller** | `bl-cases/list.js` -> `casesListPage()` |
| **API Calls** | `bl-cases` (list), `bl-cases/{id}/change-status`, `bl-cases/{id}/assign` |
| **Features** | Case list, staff pills filter, status pipeline filter, reassignment |
| **Modals** | None (inline actions) |
| **Tabs** | None (filter pills) |

---

### BL Cases - Detail
| Property | Value |
|----------|-------|
| **URL** | `/bl-cases/detail.php?id={caseId}` |
| **Files** | `detail.php`, `_detail-content.php` |
| **JS Controller** | `bl-cases/detail.js` -> `caseDetailPage()` |
| **Additional JS** | `disbursement-panel.js`, `health-ledger-panel.js`, `mbr-panel.js`, `negotiate-panel.js` |
| **Head Scripts** | PDF.js (for document preview) |
| **API Calls** | `bl-cases/{id}`, `case-providers`, `requests`, `receipts`, `notes`, `documents`, `mbr`, `health-ledger`, `negotiations`, `provider-negotiations`, `settlement` |
| **Features** | Case header with pipeline stages, provider management, document management, MBR, health ledger, negotiation, disbursement |

**Modals (11):**
| Modal File | Purpose |
|------------|---------|
| `_modal-provider.php` | Add existing provider to case |
| `_modal-quick-add-provider.php` | Create new provider and add to case |
| `_modal-edit-case.php` | Edit case details (number, client, dates, attorney) |
| `_modal-payment.php` | Log payment with split cost functionality |
| `_modal-receipt.php` | Log record receipt with no-records option |
| `_modal-request.php` | Log record request with template selector |
| `_modal-preview.php` | Preview and send request letter (email/fax) |
| `_modal-deadline.php` | Change provider deadline with history |
| `_modal-ini-staff.php` | Activate provider for requesting (select record types) |
| `_modal-treatment-complete.php` | Mark treatment as complete |
| `_modal-workflow.php` | Unified case status transition (forward/backward/reassign) |

**Panels:** Providers, Notes, Activity, Documents, MBR, Health Ledger, Negotiate, Disbursement

---

### Attorney Cases
| Property | Value |
|----------|-------|
| **URL** | `/attorney/index.php` |
| **Files** | `index.php`, `_content.php` |
| **JS Controller** | `attorney/index.js` -> `attorneyPage()` |
| **API Calls** | `attorney` (all endpoints), `attorney/stats` |
| **Features** | 4-tab view, D-N-T-S progress dots, settlement workflow |

**Tabs (4):**
| Tab File | Tab Name | Description |
|----------|----------|-------------|
| `_tab-demand.php` | Demand | Demand phase cases with D-N-T-S tracker |
| `_tab-litigation.php` | Litigation | Cases in litigation |
| `_tab-uim.php` | UIM | UIM phase cases with D-N-S tracker |
| `_tab-settled.php` | Settled | Settled cases with billing/accounting routing |

**Modals (11):**
| Modal File | Purpose |
|------------|---------|
| `_modal-create.php` | Add new attorney case |
| `_modal-edit.php` | Edit case with transfer history |
| `_modal-transfer.php` | Transfer to another attorney |
| `_modal-settle-demand.php` | Settle in demand phase |
| `_modal-settle-litigation.php` | Settle in litigation (complex fee calc) |
| `_modal-settle-uim.php` | Settle UIM claim |
| `_modal-to-litigation.php` | Move case to litigation phase |
| `_modal-to-uim.php` | Move case to UIM phase |
| `_modal-top-offer.php` | Submit top offer amount |
| `_modal-send-billing.php` | Send to billing review |
| `_modal-send-accounting.php` | Send to accounting |

---

### Commissions
| Property | Value |
|----------|-------|
| **URL** | `/commissions/index.php` |
| **Files** | `index.php`, `_content.php` |
| **JS Controller** | `commissions/index.js` -> `commissionsPage()` |
| **API Calls** | `commissions` (all), `commissions/stats`, `commissions/export` |
| **Features** | 4-tab view, approval workflow, check tracking, CSV export |

**Tabs (4):**
| Tab File | Tab Name | Description |
|----------|----------|-------------|
| `_tab-active.php` | Active | Active commissions (sortable, paginated) |
| `_tab-history.php` | History | Historical commissions |
| `_tab-attorney.php` | Attorney | Attorney-specific commissions |
| `_tab-admin.php` | Admin | Approval/rejection queue |

**Modals (3):**
| Modal File | Purpose |
|------------|---------|
| `_modal-create.php` | Add commission with settlement details |
| `_modal-edit.php` | Edit commission (editable or read-only) |
| `_modal-attorney.php` | Attorney commission detail view |

---

### Referrals
| Property | Value |
|----------|-------|
| **URL** | `/referrals/index.php` |
| **Files** | `index.php`, `_content.php` |
| **JS Controller** | `referrals/index.js` -> `referralsPage()` |
| **API Calls** | `referrals` (CRUD), `referrals/report`, `referrals/export` |

**Tabs (2):** `_tab-list.php` (List), `_tab-report.php` (Report)
**Modals (2):** `_modal-create.php`, `_modal-edit.php`

---

### Traffic Cases
| Property | Value |
|----------|-------|
| **URL** | `/traffic/index.php` |
| **Files** | `index.php`, `_content.php` |
| **JS Controller** | `traffic/index.js` -> `trafficPage()` |
| **API Calls** | `traffic` (CRUD), `traffic/files`, `traffic-requests` |

**Tabs (2):** `_tab-cases.php` (Cases), `_tab-requests.php` (Requests)
**Modals (2):** `_modal-case.php`, `_modal-request.php`

---

### Billing / MR Tracker
| Property | Value |
|----------|-------|
| **URL** | `/billing/index.php` |
| **Files** | `index.php`, `_content.php`, `_mr-modals.php`, `_health-modals.php` |
| **JS Controllers** | `billing/mr-tracker.js` + `billing/health-tracker.js` |
| **API Calls** | `billing/list`, `case-providers`, `requests`, `health-ledger` |
| **Features** | Combined MR + Health tracker, tab switching |

---

### Prelitigation
| Property | Value |
|----------|-------|
| **URL** | `/prelitigation/index.php` |
| **JS Controller** | `prelitigation/index.js` -> `prelitigationPage()` |
| **API Calls** | `prelitigation/list`, `prelitigation/log-followup`, `prelitigation/complete`, `prelitigation/import` |
| **Features** | Follow-up logging, case completion, CSV import |

---

### Accounting
| Property | Value |
|----------|-------|
| **URL** | `/accounting/index.php` |
| **JS Controller** | `accounting/index.js` -> `accountingPage()` |
| **API Calls** | `accounting/list`, `accounting/create-disbursement`, `accounting/complete` |
| **Features** | Accounting queue, disbursement management, case closure |

---

### Database (Providers Page)
| Property | Value |
|----------|-------|
| **URL** | `/providers/index.php` |
| **JS Controllers** | `providers/providers.js`, `providers/clients.js`, `providers/adjusters.js`, `providers/insurance-companies.js` |
| **Features** | 4-section tabbed database: Providers, Clients, Adjusters, Insurance Companies |
| **API Calls** | `providers`, `clients`, `adjusters`, `insurance-companies` (all CRUD) |

---

### Messages
| Property | Value |
|----------|-------|
| **URL** | `/messages/index.php` |
| **JS Controller** | `messages/index.js` -> `messagesPage()` |
| **API Calls** | `messages` (list, create, delete, mark-read) |
| **Features** | Internal messaging, compose, mark read |

---

### Reports / Performance
| Property | Value |
|----------|-------|
| **URL** | `/reports/index.php` |
| **JS Controller** | `reports/index.js` -> `reportsPage()` |
| **API Calls** | `performance`, `commissions/stats`, `attorney/stats` |
| **Features** | Multi-tab reporting with data visualization |

### Goals
| Property | Value |
|----------|-------|
| **URL** | `/reports/goals.php` |
| **API Calls** | `goals` (list, create, update) |
| **Features** | Staff goal setting and tracking |

---

### Admin - Users
| Property | Value |
|----------|-------|
| **URL** | `/admin/users.php` |
| **JS Controller** | `admin/users.js` -> `usersPage()` |
| **API Calls** | `users` (CRUD), `users/toggle-active`, `users/reset-password` |
| **Features** | User CRUD, roles, permissions checkboxes, commission rate config |

### Admin - Templates
| Property | Value |
|----------|-------|
| **URL** | `/admin/templates.php` |
| **JS Controller** | `admin/templates.js` -> `templatesPage()` |
| **API Calls** | `templates` (CRUD, preview, versions, restore) |
| **Features** | Letter template editing with version history and live preview |

### Admin - Bank Reconciliation
| Property | Value |
|----------|-------|
| **URL** | `/admin/bank-reconciliation.php` |
| **JS Controller** | `admin/bank-reconciliation.js` -> `bankReconciliationPage()` |
| **API Calls** | `bank-reconciliation` (list, import, match, unmatch, ignore, batches) |
| **Features** | CSV import, statement-to-payment matching workflow |

### Admin - Data Management
| Property | Value |
|----------|-------|
| **URL** | `/admin/data-management.php` |
| **JS Controller** | `admin/data-management.js` -> `dataManagementPage()` |
| **Features** | Centralized import/export for attorney, prelitigation, providers |

### Admin - Activity Log
| Property | Value |
|----------|-------|
| **URL** | `/admin/activity-log.php` |
| **API Calls** | `activity-log/list` |
| **Features** | Activity log viewer with filters |

### Admin - Expense Report
| Property | Value |
|----------|-------|
| **URL** | `/admin/expense-report.php` |
| **JS Controller** | `expense-report.js` -> `expenseReportPage()` |
| **API Calls** | `expense-report/list`, `expense-report/export` |

---

### MBR
| Property | Value |
|----------|-------|
| **URL** | `/mbr/index.php` |
| **JS Controller** | `mbr/index.js` -> `mbrPage()` |
| **API Calls** | `mbr` (CRUD, lines, approve, complete) |

### Health Tracker
| **URL** | `/health-tracker/index.php` |
| **Behavior** | Redirects to `/billing/?tab=health` |

---

## 4. Complete File Map

### /pages/accounting/ (2 files)
- `index.php` - Entry point
- `_content.php` - Content

### /pages/admin/ (6 files)
- `activity-log.php` - Activity log
- `bank-reconciliation.php` - Bank reconciliation
- `data-management.php` - Data management
- `expense-report.php` - Expense report
- `templates.php` - Letter templates
- `users.php` - User management

### /pages/attorney/ (16 files)
- `index.php`, `_content.php`
- `modals/`: `_modal-create.php`, `_modal-edit.php`, `_modal-transfer.php`, `_modal-settle-demand.php`, `_modal-settle-litigation.php`, `_modal-settle-uim.php`, `_modal-to-litigation.php`, `_modal-to-uim.php`, `_modal-top-offer.php`, `_modal-send-billing.php`, `_modal-send-accounting.php`
- `tabs/`: `_tab-demand.php`, `_tab-litigation.php`, `_tab-uim.php`, `_tab-settled.php`

### /pages/auth/ (1 file)
- `login.php`

### /pages/billing/ (4 files)
- `index.php`, `_content.php`, `_mr-modals.php`, `_health-modals.php`

### /pages/bl-cases/ (15 files)
- `index.php`, `_content.php`, `detail.php`, `_detail-content.php`
- `modals/`: `_modal-provider.php`, `_modal-quick-add-provider.php`, `_modal-edit-case.php`, `_modal-payment.php`, `_modal-receipt.php`, `_modal-request.php`, `_modal-preview.php`, `_modal-deadline.php`, `_modal-ini-staff.php`, `_modal-treatment-complete.php`, `_modal-workflow.php`

### /pages/commissions/ (9 files)
- `index.php`, `_content.php`
- `modals/`: `_modal-create.php`, `_modal-edit.php`, `_modal-attorney.php`
- `tabs/`: `_tab-active.php`, `_tab-history.php`, `_tab-attorney.php`, `_tab-admin.php`

### /pages/dashboard/ (2 files)
- `index.php`, `_content.php`

### /pages/health-tracker/ (1 file)
- `index.php` (redirect)

### /pages/mbr/ (2 files)
- `index.php`, `_content.php`

### /pages/messages/ (2 files)
- `index.php`, `_content.php`

### /pages/prelitigation/ (2 files)
- `index.php`, `_content.php`

### /pages/providers/ (2 files)
- `index.php`, `_content.php`

### /pages/referrals/ (8 files)
- `index.php`, `_content.php`
- `modals/`: `_modal-create.php`, `_modal-edit.php`
- `tabs/`: `_tab-list.php`, `_tab-report.php`

### /pages/reports/ (4 files)
- `index.php`, `_content.php`, `goals.php`, `_goals-content.php`

### /pages/traffic/ (8 files)
- `index.php`, `_content.php`
- `modals/`: `_modal-case.php`, `_modal-request.php`
- `tabs/`: `_tab-cases.php`, `_tab-requests.php`

---

## 5. Summary Statistics

| Category | Count |
|----------|-------|
| Page directories | 16 |
| Total PHP files | ~96 |
| Modal files | 36 |
| Tab files | 15 |
| JS controllers | 29 |
| API resource groups | 37 |
| API endpoint files | 250+ |
| Backend helper files | 14 |
| Sidebar nav items | 15 |
| User permissions | 12 |
