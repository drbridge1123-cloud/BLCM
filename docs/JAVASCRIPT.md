# BLCM JavaScript Reference

> Complete documentation of all JavaScript files, functions, and patterns.

---

## 1. Script Loading Order (main.php)

```
1. app.js          - API helper, toast, formatters (global)
2. utils.js        - Constants, lookup maps, utility functions (global)
3. shared.js       - listPageBase() mixin (global)
4. $pageHeadScripts - Optional head scripts (e.g., pdf.js for case detail)
5. [Page HTML renders with Alpine x-data components]
6. $pageScripts    - Page-specific JS controllers (per page)
7. alpine-stores.js - Global Alpine stores (auth, messages, sidebar)
8. Alpine Collapse  - Plugin (defer)
9. Alpine.js Core   - Framework (defer)
```

All scripts use cache busting: `?v=<?= filemtime(...) ?>`

---

## 2. app.js - API Helper & Global Utilities

### API Object
```javascript
const api = {
    async get(endpoint)              // GET request
    async post(endpoint, body)       // POST with JSON body
    async put(endpoint, body)        // PUT with JSON body
    async delete(endpoint)           // DELETE request
    async upload(endpoint, formData, onProgress)  // File upload with progress
    async _request(endpoint, options)             // Internal fetch wrapper
}
```

**Base URL:** All endpoints auto-prepend `/blcm/backend/api/`
**Auth:** 401 responses auto-redirect to login page
**Errors:** Throws `{ response, data }` object on non-ok responses

### Global Functions

| Function | Signature | Returns | Description |
|----------|-----------|---------|-------------|
| `showToast` | `(message, type='info', duration=3000)` | void | Show toast notification |
| `formatCurrency` | `(amount)` | `'$1,234.56'` | Format number as USD |
| `parseCurrency` | `(str)` | `1234.56` | Parse currency string to number |
| `formatDate` | `(dateStr)` | `'Mar 4, 2026'` | Format ISO date string |
| `debounce` | `(fn, ms=300)` | function | Debounce wrapper |
| `getQueryParam` | `(name)` | string\|null | Get URL query parameter |
| `confirmAction` | `(message)` | Promise\<boolean\> | Promise-based confirm dialog |
| `daysElapsed` | `(dateStr)` | number | Days since a date |
| `getStatusLabel` | `(status)` | string | Human-readable status label |
| `getRecordTypeShort` | `(type)` | string | Short label (MR, Bill, etc.) |

### STATUS_LABELS Map
```javascript
treating: 'Treating', treatment_complete: 'Tx Complete',
not_started: 'Not Started', requesting: 'Requesting',
follow_up: 'Follow Up', action_needed: 'Action Needed',
received_partial: 'Partial', on_hold: 'On Hold',
no_records: 'No Records', received_complete: 'Complete',
verified: 'Verified',
ini: 'Treatment', rec: 'Collection', verification: 'Verification',
rfd: 'Demand', neg: 'Negotiate', lit: 'Litigation',
final_verification: 'Settlement', accounting: 'Accounting', closed: 'Closed'
```

### Workflow Transitions
```javascript
FORWARD_TRANSITIONS = {
    ini -> [rec], rec -> [verification], verification -> [rfd],
    rfd -> [neg], neg -> [lit], lit -> [final_verification],
    final_verification -> [accounting], accounting -> [closed], closed -> []
}

BACKWARD_TRANSITIONS = {
    ini -> [], rec -> [ini], verification -> [ini, rec],
    rfd -> [ini, rec, verification],
    // ... each stage can go back to any previous stage
}
```

---

## 3. utils.js - Constants & Utilities

### Constant Maps

| Constant | Keys | Example |
|----------|------|---------|
| `PROVIDER_TYPES` | hospital, er, chiro, imaging, physician, surgery_center, pharmacy, acupuncture, massage, pain_management, pt, other | `'Hospital'` |
| `REQUEST_METHODS` | email, fax, portal, phone, mail, chartswap, online | `'Email'` |
| `REQUEST_TYPES` | initial, follow_up, re_request, rfd | `'Initial'` |
| `NOTE_TYPES` | general, follow_up, issue, handoff | `'General'` |
| `DIFFICULTY_LEVELS` | easy, medium, hard | `'Easy'` |
| `CASE_STATUSES` | prelitigation, collecting, verification, completed, rfd, final_verification, disbursement, accounting, closed | `'Collection'` |
| `STATUS_COLORS` | (same as CASE_STATUSES) | `'bg-blue-100 text-blue-700'` |
| `PROVIDER_STATUS_COLORS` | treating, not_started, requesting, follow_up, action_needed, received_partial, on_hold, no_records, received_complete, verified | `'bg-green-100 text-green-700'` |
| `INSURANCE_TYPES` | auto, health, workers_comp, liability, um_uim, other | `'Auto'` |
| `ADJUSTER_TYPES` | pip, um, uim, 3rd_party, liability, pd, bi | `'PIP'` |

### Lookup Functions
```javascript
getProviderTypeLabel(type)    // PROVIDER_TYPES[type] || type
getRequestMethodLabel(m)      // REQUEST_METHODS[m] || m
getCaseStatusLabel(s)         // CASE_STATUSES[s] || s
getStatusColor(s)             // STATUS_COLORS[s] || 'bg-gray-100 text-gray-600'
getProviderStatusColor(s)     // PROVIDER_STATUS_COLORS[s] || 'bg-gray-100 text-gray-600'
```

### Utility Functions

| Function | Signature | Returns | Description |
|----------|-----------|---------|-------------|
| `buildQueryString` | `(params)` | `'?key=val&...'` | Build URL query from object, skips null/empty |
| `timeAgo` | `(dateStr)` | `'5m ago'` | Relative time (just now, Xm, Xh, Xd ago) |
| `truncate` | `(str, len=40)` | string | Truncate with ellipsis |
| `formatPhoneNumber` | `(phone)` | `'(555) 123-4567'` | Format 10-digit phone |
| `createDebouncedSave` | `(saveFn, delay=500)` | function | Debounced save with per-key timers |
| `daysUntil` | `(dateStr)` | number | Days until date (negative = overdue) |
| `getDeadlineInfo` | `(targetDate)` | `{class, label, urgency}` | Deadline with color coding |
| `initScrollContainer` | `(el, bottomPadding=16)` | void | Dynamic max-height scroll container |

### Deadline Info Return Values
```javascript
// overdue:  { class: 'text-red-600 font-bold', label: '3d overdue', urgency: 'overdue' }
// critical: { class: 'text-red-500 font-semibold', label: '2d left', urgency: 'critical' }
// warning:  { class: 'text-amber-600 font-medium', label: '5d left', urgency: 'warning' }
// normal:   { class: 'text-v2-text-mid', label: 'Mar 15, 2026', urgency: 'normal' }
// none:     { class: 'text-v2-text-light', label: '-', urgency: 'none' }
```

---

## 4. shared.js - List Page Base Mixin

```javascript
function listPageBase(apiEndpoint, options = {}) {
    // options:
    //   defaultSort: 'created_at'
    //   defaultDir: 'desc'
    //   perPage: 25
    //   filtersToParams: function() { return {} }  // page-specific filter params

    return {
        items: [],
        loading: true,
        search: '',
        sortBy: config.defaultSort,
        sortDir: config.defaultDir,
        pagination: null,

        async loadData(page = 1),    // Fetch with search/sort/page/filters
        sort(column),                 // Toggle sort direction on column
        goToPage(page),               // Navigate to page number
        resetFilters(),               // Reset search + sort + call _resetPageFilters()
        hasActiveFilters(),           // Check search + call _hasPageFilters()
    }
}
```

### Extension Hooks
Pages can define these methods to extend the base:
- `_resetPageFilters()` - Called by `resetFilters()` to reset page-specific filters
- `_hasPageFilters()` - Called by `hasActiveFilters()` to check page-specific filters
- `filtersToParams()` - Returns object of extra query params for API call

### Usage (how pages SHOULD use it)
```javascript
function myPage() {
    return {
        ...listPageBase('my-endpoint/list', {
            defaultSort: 'name',
            perPage: 50,
            filtersToParams() { return { status: this.filterStatus }; }
        }),

        // Page-specific state
        filterStatus: '',
        showCreateModal: false,

        // Page-specific methods
        init() { this.loadData(); },
        _resetPageFilters() { this.filterStatus = ''; },
    }
}
```

> **Note:** Most page controllers currently DON'T use `listPageBase()` and duplicate the loadData/sort/pagination logic instead. This is a key refactoring target.

---

## 5. alpine-stores.js - Global Stores

### $store.auth
```javascript
Alpine.store('auth', {
    user: null,           // Current user object {id, email, role, display_name, permissions, ...}
    loading: true,        // True until first load completes

    async load(),         // Fetch user via api.get('auth/me')
    hasPermission(perm),  // Check permission (admin always true)
    async logout(),       // POST logout, redirect to login

    // Computed getters:
    get isAdmin()      // user?.role === 'admin'
    get isManager()    // user?.role === 'manager'
    get isAttorney()   // user?.role === 'attorney'
    get isParalegal()  // user?.role === 'paralegal'
    get isBilling()    // user?.role === 'billing'
    get isStaff()      // ['paralegal', 'billing'].includes(role)
})
```

### $store.messages
```javascript
Alpine.store('messages', {
    unreadCount: 0,       // Updated every 30 seconds
    init(),               // Start polling
    async load(),         // Fetch unread count from auth/me
})
```

### $store.sidebar
```javascript
Alpine.store('sidebar', {
    collapsed: false,     // Persisted to localStorage('blcm_sidebar_collapsed')
    toggle(),             // Toggle collapsed state
})
```

---

## 6. Page Controllers (All 29)

### attorney/index.js - `attorneyPage()` (1,789 lines)
| Property | Type | Description |
|----------|------|-------------|
| `activeTab` | string | 'demand', 'litigation', 'uim', 'settled' |
| `items` | array | Current tab's cases |
| `loading` | boolean | Loading state |
| `search` | string | Search query |
| `filterAttorney` | string | Attorney filter |
| `attorneys` | array | Attorney list for filter |
| `summary` | object | Stats per tab |
| `showCreateModal` | boolean | Create modal visibility |
| `showEditModal` | boolean | Edit modal visibility |
| **Key Methods** | | |
| `loadTab(tab)` | | Load cases for selected tab |
| `createCase()` | | POST to attorney API |
| `updateCase()` | | PUT to attorney/{id} |
| `deleteCase(id)` | | DELETE attorney/{id} |
| `settleDemand()` | | POST attorney/settle-demand |
| `settleLitigation()` | | POST attorney/settle-litigation |
| `settleUim()` | | POST attorney/settle-uim |
| `toLitigation()` | | POST attorney/to-litigation |
| `toUim()` | | POST attorney/to-uim |
| `transferCase()` | | POST attorney/transfer |
| `sendToAccounting()` | | POST attorney/send-to-accounting |
| `sendToBilling()` | | POST attorney/send-to-billing-final |
| `submitTopOffer()` | | POST attorney/top-offer |

### bl-cases/list.js - `casesListPage()`
- Case list with staff pills, status filters
- Methods: `loadCases()`, `reassignCase()`, `changeStatus()`

### bl-cases/detail.js - `caseDetailPage()` (1,641 lines)
- Full case detail with multiple panels
- Providers, Notes, Activity, Documents
- Methods for every modal action (addProvider, logPayment, logReceipt, logRequest, etc.)
- Workflow transitions (forward/backward/reassign)
- Letter preview and send (email/fax)

### bl-cases/disbursement-panel.js - `disbursementPanel()`
- Settlement distribution calculations
- Mahler method and Hamm method formulas
- Auto-calculates attorney fees, costs, provider payments

### bl-cases/health-ledger-panel.js - `healthLedgerPanel()`
- Health insurance tracking per case
- CRUD for health ledger line items

### bl-cases/mbr-panel.js - `mbrPanel()`
- Medical Balance Report with provider line items
- CSV import for bulk line items
- Insurance tracking

### bl-cases/negotiate-panel.js - `negotiatePanel()`
- Case-level negotiation tracking
- Provider-level negotiation with auto-populate

### commissions/index.js - `commissionsPage()` (858 lines)
- Tabs: active, history, attorney, admin
- Commission create/edit with auto-calculation
- Admin approval workflow (approve/reject/bulk-approve)
- Check tracking, CSV export

### referrals/index.js - `referralsPage()`
- Tabs: list, report
- Referral CRUD
- Reporting with source/type breakdowns

### accounting/index.js - `accountingPage()`
- Accounting case queue
- Disbursement creation and management
- Case completion workflow

### billing/mr-tracker.js - `mrTrackerPage()`
- Medical records request tracking
- Status filtering, deadline monitoring

### billing/health-tracker.js - `healthTrackerPage()`
- Health ledger tracking
- Bulk request management

### dashboard/index.js - `dashboardPage()`
- Summary stats cards
- Pending assignments
- Recent activity feed

### messages/index.js - `messagesPage()`
- Message list with read/unread
- Compose and reply
- Mark read

### prelitigation/index.js - `prelitigationPage()`
- Follow-up logging
- Case completion
- CSV bulk import

### traffic/index.js - `trafficPage()`
- Tabs: cases, requests
- Traffic case CRUD with file uploads
- Traffic request management

### reports/index.js - `reportsPage()`
- Multi-tab reporting
- Commission reports, attorney performance, staff metrics

### providers/providers.js - `providersPage()`
- Provider database CRUD
- Contact info, fax, email
- Template association

### providers/clients.js - `clientsPage()`
- Client database CRUD
- Name, phone, DOB, address

### providers/adjusters.js - `adjustersPage()`
- Adjuster CRUD
- Insurance company linking
- Type classification (PIP, UM, etc.)

### providers/insurance-companies.js - `insuranceCompaniesPage()`
- Insurance company CRUD
- Type classification (Auto, Health, etc.)

### admin/users.js - `usersPage()`
- User CRUD
- Role assignment
- Permission checkboxes
- Commission rate configuration

### admin/templates.js - `templatesPage()`
- Letter template CRUD
- Version history
- Live preview with variable substitution

### admin/bank-reconciliation.js - `bankReconciliationPage()`
- CSV bank statement import
- Statement-to-payment matching workflow
- Batch management

### admin/data-management.js - `dataManagementPage()`
- Centralized import/export hub
- Supports all data types (attorney, prelitigation, providers)

### health-tracker/index.js - `healthTrackerListPage()`
- Health ledger list view

### mbr/index.js - `mbrPage()`
- MBR list and detail views

### expense-report.js - `expenseReportPage()`
- Expense tracking and filtering
- CSV export

---

## 7. Common JS Patterns

### CRUD Create
```javascript
async createItem() {
    this.saving = true;
    try {
        await api.post('endpoint', this.createForm);
        showToast('Created successfully', 'success');
        this.showCreateModal = false;
        this.createForm = { field1: '', field2: '' };  // Reset form
        await this.loadData(1);
    } catch (e) {
        showToast(e.data?.message || e.message, 'error');
    }
    this.saving = false;
}
```

### CRUD Update
```javascript
async updateItem() {
    this.saving = true;
    try {
        await api.put('endpoint/' + this.editForm.id, this.editForm);
        showToast('Updated successfully', 'success');
        this.showEditModal = false;
        await this.loadData();
    } catch (e) {
        showToast(e.data?.message || e.message, 'error');
    }
    this.saving = false;
}
```

### CRUD Delete
```javascript
async deleteItem(id) {
    if (!await confirmAction('Are you sure you want to delete this item?')) return;
    try {
        await api.delete('endpoint/' + id);
        showToast('Deleted successfully', 'success');
        await this.loadData();
    } catch (e) {
        showToast(e.data?.message || e.message, 'error');
    }
}
```

### Open Edit Modal
```javascript
openEditModal(item) {
    this.editForm = {
        id: item.id,
        field1: item.field1 || '',
        field2: item.field2 || '',
    };
    this.showEditModal = true;
}
```

### Sort Column
```javascript
sort(column) {
    if (this.sortBy === column) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
    } else {
        this.sortBy = column;
        this.sortDir = 'asc';
    }
    this.loadData(1);
}
```

### Currency Formatting (inline)
```javascript
fmt(val) {
    return parseFloat(val || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2, maximumFractionDigits: 2
    });
}
```

### Page Numbers Generation
```javascript
pageNumbers() {
    const total = this.pagination?.total_pages || 1;
    const cur = this.pagination?.current_page || 1;
    const pages = [];
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || Math.abs(i - cur) <= 2) {
            pages.push(i);
        } else if (pages[pages.length - 1] !== '...') {
            pages.push('...');
        }
    }
    return pages;
}
```

### Debounced Search
```html
<input x-model="search" @input.debounce.300ms="loadData(1)">
```

### Tab Switching
```javascript
switchTab(tab) {
    this.activeTab = tab;
    this.loadTab(tab);
}
```
