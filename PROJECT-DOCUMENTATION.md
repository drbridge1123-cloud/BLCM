# BLCM (Bridge Law Case Management) - Project Documentation

> Bridge Law & Associates - Internal Case Management System
> **Stack:** PHP + Alpine.js + Tailwind CSS (CDN) | XAMPP / MySQL
> **Last Updated:** 2026-03-04

---

## Table of Contents

| Document | Description |
|----------|-------------|
| [DESIGN-SYSTEM.md](docs/DESIGN-SYSTEM.md) | Colors, typography, CSS classes, inline style patterns |
| [COMPONENTS.md](docs/COMPONENTS.md) | Modals, forms, tables, badges, toast, pagination UI patterns |
| [JAVASCRIPT.md](docs/JAVASCRIPT.md) | All JS files, shared utilities, page controllers, Alpine stores |
| [BACKEND-API.md](docs/BACKEND-API.md) | PHP helpers, API endpoints, routing, auth, DB patterns |
| [PAGES.md](docs/PAGES.md) | All frontend pages, tabs, modals, permissions, navigation |

---

## 1. Project Overview

BLCM is a case management system for a law firm (Bridge Law & Associates). It manages the full lifecycle of personal injury cases from referral intake through medical records collection, attorney review, negotiation, settlement, and accounting.

### Core Workflows
1. **Referral Intake** - New cases enter via referrals
2. **Prelitigation** - Initial case setup and treatment tracking
3. **Medical Records Collection** - Request, track, receive records from providers
4. **Attorney Review** - Demand letters, litigation, UIM claims
5. **Negotiation** - Settlement offers and counter-offers
6. **Settlement & Disbursement** - Financial distribution calculations
7. **Accounting** - Final reconciliation and closure
8. **Commissions** - Employee commission tracking and approval

---

## 2. Tech Stack

| Layer | Technology | Notes |
|-------|-----------|-------|
| **Frontend Framework** | Alpine.js 3.x (CDN) | Lightweight reactive framework |
| **CSS Framework** | Tailwind CSS (CDN) | Utility-first CSS |
| **Custom CSS** | `app.css` + `sp-design-system.css` | Design tokens + component classes |
| **Fonts** | Google Fonts | Libre Franklin (primary), IBM Plex Mono, IBM Plex Sans |
| **Backend** | PHP 8.x (vanilla, no framework) | RESTful API endpoints |
| **Database** | MySQL (via XAMPP) | PDO with prepared statements |
| **Server** | Apache (XAMPP) | .htaccess URL rewriting |
| **PDF Generation** | Dompdf | HTML-to-PDF rendering |
| **Email** | PHPMailer | SMTP email sending |
| **Fax** | Faxage / Phaxio | Fax API integration |

### CDN Dependencies
```html
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- PDF.js (case detail page only) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
```

---

## 3. Directory Structure

```
blcm/
|-- index.php                          # Root redirect -> frontend/index.php
|-- .htaccess                          # Apache rewrite rules
|
|-- backend/
|   |-- api/
|   |   |-- index.php                  # Main API router
|   |   |-- auth/                      # Login, logout, me
|   |   |-- users/                     # User CRUD
|   |   |-- dashboard/                 # Dashboard stats
|   |   |-- bl-cases/                  # Case CRUD + status changes
|   |   |-- case-providers/            # Provider assignments per case
|   |   |-- requests/                  # Medical record requests
|   |   |-- receipts/                  # Record receipts
|   |   |-- documents/                 # Document upload/download
|   |   |-- notes/                     # Case notes
|   |   |-- attorney/                  # Attorney case tracking
|   |   |-- settlement/               # Settlement data
|   |   |-- commissions/              # Commission CRUD + approval
|   |   |-- referrals/                # Referral CRUD
|   |   |-- traffic/                  # Traffic cases
|   |   |-- traffic-requests/         # Traffic case requests
|   |   |-- demand-requests/          # Demand requests
|   |   |-- deadline-requests/        # Deadline extension requests
|   |   |-- prelitigation/            # Prelitigation tracker
|   |   |-- billing/                  # Billing tracker
|   |   |-- accounting/               # Accounting + disbursements
|   |   |-- mbr/                      # Medical Balance Reports
|   |   |-- health-ledger/            # Health insurance ledger
|   |   |-- negotiations/             # Case negotiations
|   |   |-- provider-negotiations/    # Provider-level negotiations
|   |   |-- mr-fee-payments/          # MR fee payments
|   |   |-- bank-reconciliation/      # Bank statement matching
|   |   |-- templates/                # Letter templates
|   |   |-- goals/                    # Staff goals
|   |   |-- performance/              # Performance reports
|   |   |-- providers/                # Provider database CRUD
|   |   |-- clients/                  # Client database CRUD
|   |   |-- adjusters/                # Adjuster database CRUD
|   |   |-- insurance-companies/      # Insurance company CRUD
|   |   |-- messages/                 # Internal messaging
|   |   |-- notifications/            # Notification system
|   |   |-- activity-log/             # Activity log
|   |   |-- expense-report/           # Expense reports
|   |
|   |-- helpers/
|       |-- db.php                     # Database abstraction (PDO)
|       |-- auth.php                   # Session auth + permissions
|       |-- response.php              # JSON response helpers
|       |-- validator.php             # Input validation + sanitization
|       |-- commission.php            # Commission calculation engine
|       |-- csv.php                   # CSV import/export
|       |-- pdf-generator.php         # Dompdf wrapper
|       |-- pdf-overlay.php           # PDF template overlay (FPDI)
|       |-- email.php                 # PHPMailer wrapper
|       |-- fax.php                   # Fax API integration
|       |-- date.php                  # Date calculation utilities
|       |-- escalation.php            # Escalation tier logic
|       |-- file-upload.php           # File upload validation
|       |-- letter-template.php       # Medical record request letter renderer
|
|-- frontend/
|   |-- index.php                      # Frontend entry point
|   |
|   |-- layouts/
|   |   |-- main.php                   # Authenticated layout (sidebar + header)
|   |   |-- auth.php                   # Login page layout
|   |
|   |-- components/
|   |   |-- sidebar.php                # Dark collapsible sidebar nav
|   |   |-- header.php                 # Top header bar
|   |
|   |-- assets/
|   |   |-- css/
|   |   |   |-- app.css                # Main styles (3,277 lines)
|   |   |   |-- sp-design-system.css   # Component classes (624 lines)
|   |   |
|   |   |-- js/
|   |       |-- app.js                 # API helper, toast, formatters
|   |       |-- utils.js               # Constants, lookup maps, utilities
|   |       |-- shared.js              # listPageBase() mixin
|   |       |-- alpine-stores.js       # Alpine global stores (auth, messages, sidebar)
|   |       |
|   |       |-- pages/                 # Page-specific JS controllers
|   |           |-- attorney/index.js
|   |           |-- bl-cases/list.js
|   |           |-- bl-cases/detail.js
|   |           |-- bl-cases/disbursement-panel.js
|   |           |-- bl-cases/health-ledger-panel.js
|   |           |-- bl-cases/mbr-panel.js
|   |           |-- bl-cases/negotiate-panel.js
|   |           |-- commissions/index.js
|   |           |-- referrals/index.js
|   |           |-- accounting/index.js
|   |           |-- billing/mr-tracker.js
|   |           |-- billing/health-tracker.js
|   |           |-- dashboard/index.js
|   |           |-- messages/index.js
|   |           |-- prelitigation/index.js
|   |           |-- traffic/index.js
|   |           |-- reports/index.js
|   |           |-- providers/providers.js
|   |           |-- providers/clients.js
|   |           |-- providers/adjusters.js
|   |           |-- providers/insurance-companies.js
|   |           |-- admin/users.js
|   |           |-- admin/templates.js
|   |           |-- admin/bank-reconciliation.js
|   |           |-- admin/data-management.js
|   |           |-- health-tracker/index.js
|   |           |-- mbr/index.js
|   |           |-- expense-report.js
|   |
|   |-- pages/
|       |-- auth/login.php
|       |-- dashboard/
|       |-- bl-cases/                  # index.php (list) + detail.php + modals/ + tabs/
|       |-- attorney/                  # index.php + modals/ + tabs/
|       |-- commissions/               # index.php + modals/ + tabs/
|       |-- referrals/                 # index.php + modals/ + tabs/
|       |-- traffic/                   # index.php + modals/ + tabs/
|       |-- billing/                   # index.php (MR + Health tracker)
|       |-- prelitigation/
|       |-- accounting/
|       |-- providers/                 # Database management (4 sub-sections)
|       |-- messages/
|       |-- reports/                   # index.php + goals.php
|       |-- admin/                     # users, templates, bank-recon, data-mgmt, activity-log, expense
|       |-- mbr/
|       |-- health-tracker/            # Redirects to billing/?tab=health
```

---

## 4. Architecture Pattern

### Frontend Pattern: Page Controller
Each page follows this pattern:

```
Page Entry (index.php)
  -> Sets $pageTitle, $currentPage, $pageScripts
  -> Sets $pageContent = '_content.php'
  -> Includes layout (main.php)
     -> Layout renders sidebar, header, content area
     -> Content file uses x-data="pageName()" for Alpine.js
     -> Modal files included inside content
     -> Tab files included inside content
     -> Page JS loaded via $pageScripts array
```

**Example page setup:**
```php
<?php
$pageTitle = 'Attorney Cases';
$currentPage = 'attorney_cases';
$pageContent = __DIR__ . '/_content.php';
$pageScripts = ['assets/js/pages/attorney/index.js'];
require_once __DIR__ . '/../../layouts/main.php';
```

### Backend Pattern: File-per-Endpoint
Each API action is a single PHP file:
```
GET    /api/attorney       -> backend/api/attorney/list.php
POST   /api/attorney       -> backend/api/attorney/create.php
PUT    /api/attorney/{id}  -> backend/api/attorney/update.php
DELETE /api/attorney/{id}  -> backend/api/attorney/delete.php
```

### Data Flow
```
Browser (Alpine.js)
  -> api.get/post/put/delete()   (app.js)
  -> fetch('/blcm/backend/api/...')
  -> API Router (backend/api/index.php)
  -> Endpoint file (e.g. attorney/list.php)
  -> Helper functions (db.php, auth.php, etc.)
  -> MySQL via PDO
  -> JSON response back to browser
  -> Alpine.js reactivity updates DOM
```

---

## 5. Authentication & Authorization

### Session-Based Auth
- Login creates PHP session with `$_SESSION['user_id']`
- Every API call checks `requireAuth()` from `auth.php`
- 401 response auto-redirects to login page (handled in `app.js`)

### Role System
| Role | Description |
|------|-------------|
| `admin` | Full access to everything |
| `manager` | Access to most features |
| `attorney` | Attorney-specific views |
| `paralegal` | Staff-level case management |
| `billing` | Billing and financial access |

### Permission System
Permissions are stored per-user and checked via `$store.auth.hasPermission('perm_name')` on frontend and `requirePermission('perm_name')` on backend.

| Permission | Controls Access To |
|------------|-------------------|
| `cases` | BL Cases |
| `traffic` | Traffic Cases |
| `referrals` | Referrals |
| `prelitigation_tracker` | Prelitigation |
| `mr_tracker` | Billing/MR Tracker |
| `attorney_cases` | Attorney Tracker |
| `accounting_tracker` | Accounting |
| `commissions` | Commissions |
| `reports` | Performance Reports |
| `goals` | Goals |
| `users` | Admin/User Management |
| `activity_log` | Activity Log |

### Frontend Permission Gating
```html
<template x-if="$store.auth.hasPermission('cases')">
    <a href="..." class="sb-item">BL Cases</a>
</template>
```

---

## 6. Case Lifecycle (Main Workflow)

```
ini (Treatment)
  -> rec (Collection)
    -> verification (Verification)
      -> rfd (Demand / Attorney)
        -> neg (Negotiate)
          -> lit (Litigation)  [optional]
            -> final_verification (Settlement)
              -> accounting (Accounting)
                -> closed (Closed)
```

Each transition is controlled by `FORWARD_TRANSITIONS` and `BACKWARD_TRANSITIONS` maps in `app.js`. Cases can move forward one step or backward to any previous step.

---

## 7. Key Constants & Labels

Defined in `utils.js`:

| Constant | Purpose |
|----------|---------|
| `PROVIDER_TYPES` | Hospital, ER, Chiro, Imaging, etc. |
| `REQUEST_METHODS` | Email, Fax, Portal, Phone, Mail, ChartSwap |
| `REQUEST_TYPES` | Initial, Follow Up, Re-Request, RFD |
| `NOTE_TYPES` | General, Follow Up, Issue, Handoff |
| `DIFFICULTY_LEVELS` | Easy, Medium, Hard |
| `CASE_STATUSES` | Prelitigation through Closed |
| `STATUS_COLORS` | Tailwind color classes per status |
| `PROVIDER_STATUS_COLORS` | Tailwind color classes per provider status |
| `INSURANCE_TYPES` | Auto, Health, Workers' Comp, Liability, UM/UIM |
| `ADJUSTER_TYPES` | PIP, UM, UIM, 3rd Party, Liability, PD, BI |

---

## 8. Shared Global Functions

Defined in `app.js` (loaded on every page):

| Function | Purpose |
|----------|---------|
| `api.get(endpoint)` | HTTP GET request |
| `api.post(endpoint, body)` | HTTP POST request |
| `api.put(endpoint, body)` | HTTP PUT request |
| `api.delete(endpoint)` | HTTP DELETE request |
| `api.upload(endpoint, formData, onProgress)` | File upload with progress |
| `showToast(message, type, duration)` | Toast notification (success/error/warning/info) |
| `formatCurrency(amount)` | Format as `$1,234.56` |
| `parseCurrency(str)` | Parse `$1,234.56` to number |
| `formatDate(dateStr)` | Format as `Mar 4, 2026` |
| `debounce(fn, ms)` | Debounce function calls |
| `getQueryParam(name)` | Get URL query parameter |
| `confirmAction(message)` | Promise-based confirm dialog |
| `daysElapsed(dateStr)` | Days since a date |
| `getStatusLabel(status)` | Human-readable status label |

---

## 9. Alpine.js Global Stores

Defined in `alpine-stores.js`:

### `$store.auth`
- `user` - Current user object
- `loading` - Auth loading state
- `isAdmin`, `isManager`, `isAttorney`, `isParalegal`, `isBilling` - Role checks
- `hasPermission(perm)` - Permission check
- `logout()` - Logout and redirect

### `$store.messages`
- `unreadCount` - Unread message count (polled every 30 seconds)

### `$store.sidebar`
- `collapsed` - Sidebar collapsed state (persisted to localStorage)
- `toggle()` - Toggle sidebar

---

## 10. File Naming Conventions

| Pattern | Purpose | Example |
|---------|---------|---------|
| `index.php` | Page entry point | `attorney/index.php` |
| `_content.php` | Page content (included by index) | `attorney/_content.php` |
| `_modal-{name}.php` | Modal dialog | `_modal-create.php` |
| `_tab-{name}.php` | Tab content | `_tab-demand.php` |
| `list.php` | API list endpoint | `api/attorney/list.php` |
| `create.php` | API create endpoint | `api/attorney/create.php` |
| `update.php` | API update endpoint | `api/attorney/update.php` |
| `delete.php` | API delete endpoint | `api/attorney/delete.php` |
| `{page}/index.js` | Page JS controller | `pages/attorney/index.js` |

> Files prefixed with `_` are partials (included, not accessed directly).

---

## 11. Development Environment

### Requirements
- XAMPP (Apache + MySQL + PHP 8.x)
- Browser with JavaScript enabled
- No build step required (all CDN)

### Running the App
1. Start XAMPP (Apache + MySQL)
2. Place project in `C:\xampp\htdocs\blcm\`
3. Access `http://localhost/blcm/`
4. Login at `/blcm/frontend/pages/auth/login.php`

### Cache Busting
All CSS/JS files use `?v=<?= filemtime(...) ?>` for automatic cache invalidation on file change.

### API Base Path
All API calls go through: `/blcm/backend/api/{resource}/{action}`
Handled by the `api` object in `app.js` which prepends the base path automatically.
