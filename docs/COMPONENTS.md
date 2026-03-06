# BLCM UI Components

> Complete reference for all reusable UI patterns in the BLCM application.

---

## 1. Layout Components

### Main Layout (layouts/main.php)
```
<html>
  <head>
    - Meta tags, title with $pageTitle
    - Google Fonts (Libre Franklin, IBM Plex Mono, IBM Plex Sans)
    - app.css + sp-design-system.css (with cache-bust ?v=filemtime)
    - Tailwind CDN + custom config (colors, fonts)
    - Shared JS: app.js, utils.js, shared.js
    - Optional $pageHeadScripts (e.g. pdf.js)
  </head>
  <body class="bg-v2-bg font-franklin min-h-screen" x-data x-init="$store.auth.load()">
    - sidebar.php include
    - <div class="main-content"> with :class="{'expanded': $store.sidebar.collapsed}">
      - header.php include
      - <main class="p-6"> with $pageContent include
    - Toast container: <div id="toast-container" class="fixed top-4 right-4 z-[100]">
    - Page scripts via $pageScripts array
    - alpine-stores.js
    - Alpine.js Collapse plugin (defer)
    - Alpine.js core (defer)
  </body>
</html>
```

### Sidebar (components/sidebar.php)
- Background: `#161616`, fixed left, full height, z-30
- Logo: Gold gradient "BL" badge (32px, rounded 8px) + "Bridge Law" text
- Navigation sections with permission gating via `x-if="$store.auth.hasPermission('...')"`
- Active state via PHP `$currentPage` variable matching sidebar link
- Collapsible: `$store.sidebar.toggle()`, collapsed width 56px
- User section at bottom: avatar initial, display name + role, logout button
- Toggle button: absolute circle at right:-12px, top:80px

### Header (components/header.php)
- White background, sticky top, z-20, border-bottom
- Left: "BRIDGE LAW & ASSOCIATES" badge (navy bg, gold text) + page title (`v2-page-title`)
- Right: Messages icon with unread count badge (red, polled every 30s)

---

## 2. Modal Pattern

Used across **36 modal files**. All modals follow this exact structure:

### Standard Modal (max-width: 600px)
```html
<div x-show="showCreateModal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(0,0,0,.45);"
     @click.self="showCreateModal = false">
    <div style="background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22);
                width:100%; max-width:600px; max-height:90vh; overflow:hidden;
                display:flex; flex-direction:column;"
         @click.stop>

        <!-- Header (Navy) -->
        <div style="background:#0F1B2D; padding:18px 24px; display:flex;
                    align-items:center; justify-content:space-between; flex-shrink:0;">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">
                Modal Title
            </h3>
            <button @click="showCreateModal = false"
                    style="background:none; border:none; color:rgba(255,255,255,.4);
                           cursor:pointer; font-size:20px;">
                &times;
            </button>
        </div>

        <!-- Body (Scrollable) -->
        <div style="padding:24px; overflow-y:auto; display:flex;
                    flex-direction:column; gap:16px;">
            <!-- Form fields go here -->
        </div>

        <!-- Footer -->
        <div style="padding:16px 24px; border-top:1px solid #eee;
                    display:flex; justify-content:flex-end; gap:8px; flex-shrink:0;">
            <button @click="showCreateModal = false" class="v2-btn">Cancel</button>
            <button @click="saveItem()" class="v2-btn v2-btn-primary" :disabled="saving">
                <span x-show="!saving">Save</span>
                <span x-show="saving">Saving...</span>
            </button>
        </div>
    </div>
</div>
```

### Modal Size Variants
| Size | max-width | Usage |
|------|-----------|-------|
| Small | `440px` | Simple confirmations, single-field forms |
| Standard | `600px` | Most CRUD modals |
| Large | `800px` | Complex forms, multi-section |
| Extra Large | `900px` or `95vw` | Preview/document modals |

### Alpine.js Modal State
```javascript
// In page controller:
showCreateModal: false,
showEditModal: false,
saving: false,
createForm: { field1: '', field2: '' },
editForm: { id: null, field1: '', field2: '' },
```

---

## 3. Form Field Patterns

### Text Input
```html
<div>
    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82;
                  text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">
        Field Name
    </label>
    <input type="text" x-model="form.fieldName" class="sp-search" style="width:100%;">
</div>
```

### Select Dropdown
```html
<div>
    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82;
                  text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">
        Category
    </label>
    <select x-model="form.category" class="sp-select" style="width:100%;">
        <option value="">Select...</option>
        <option value="val1">Label 1</option>
        <option value="val2">Label 2</option>
    </select>
</div>
```

### Textarea
```html
<div>
    <label style="...label styles...">Notes</label>
    <textarea x-model="form.notes" class="sp-search"
              style="width:100%; min-height:80px;" rows="3"></textarea>
</div>
```

### Date Input
```html
<div>
    <label style="...label styles...">Date of Incident</label>
    <input type="date" x-model="form.incident_date" class="sp-search" style="width:100%;">
</div>
```

### Currency Input (with $ prefix)
```html
<div>
    <label style="...label styles...">Settlement Amount</label>
    <div style="position:relative;">
        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%);
                     color:#8a8a82; font-size:13px;">$</span>
        <input type="number" x-model="form.amount" class="sp-search"
               style="width:100%; padding-left:24px;" step="0.01">
    </div>
</div>
```

### Phone Input
```html
<div>
    <label style="...label styles...">Phone</label>
    <input type="tel" x-model="form.phone" class="sp-search" style="width:100%;"
           placeholder="(555) 123-4567">
</div>
```

### Two-Column Grid
```html
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
    <div><!-- Field 1 --></div>
    <div><!-- Field 2 --></div>
</div>
```

### Three-Column Grid
```html
<div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px;">
    <div><!-- Field 1 --></div>
    <div><!-- Field 2 --></div>
    <div><!-- Field 3 --></div>
</div>
```

### Section Divider (Gold underline)
```html
<div style="font-size:11px; font-weight:700; color:#0F1B2D; text-transform:uppercase;
            letter-spacing:.05em; padding-bottom:8px; border-bottom:2px solid #C9A84C;
            margin-bottom:12px;">
    Section Title
</div>
```

### Read-Only Display Field
```html
<div>
    <label style="...label styles...">Field Name</label>
    <div style="font-size:14px; font-weight:600; color:#0F1B2D;"
         x-text="item.fieldName || '-'"></div>
</div>
```

---

## 4. Table Pattern

### Standard List Table
```html
<div class="sp-card">
    <table class="sp-table sp-table-compact">
        <thead>
            <tr>
                <th @click="sort('column_name')" style="cursor:pointer;">
                    Column Name
                    <span x-show="sortBy==='column_name'"
                          x-text="sortDir==='asc' ? '&#9650;' : '&#9660;'"
                          style="font-size:9px;"></span>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Loading -->
            <template x-if="loading">
                <tr><td colspan="N" class="sp-loading">Loading...</td></tr>
            </template>
            <!-- Empty -->
            <template x-if="!loading && items.length === 0">
                <tr><td colspan="N" class="sp-empty">No items found</td></tr>
            </template>
            <!-- Data -->
            <template x-for="item in items" :key="item.id">
                <tr>
                    <td class="sp-col-case-number" x-text="item.case_number"></td>
                    <td class="sp-col-client" x-text="item.client_name"></td>
                    <td class="sp-col-date" x-text="formatDate(item.created_at)"></td>
                    <td class="sp-col-commission-amount" x-text="formatCurrency(item.amount)"></td>
                    <td>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                              :class="getStatusColor(item.status)"
                              x-text="getStatusLabel(item.status)"></span>
                    </td>
                    <td class="sp-actions">
                        <button class="sp-action-btn sp-action-btn-edit"
                                @click="openEditModal(item)">Edit</button>
                        <button class="sp-action-btn sp-action-btn-delete"
                                @click="deleteItem(item.id)">Delete</button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>
```

---

## 5. Toolbar / Filter Bar

```html
<div class="sp-toolbar">
    <!-- Left: Tabs -->
    <div class="sp-tabs">
        <button class="sp-tab" :class="{'active': activeTab==='tab1'}"
                @click="switchTab('tab1')">Tab 1</button>
        <button class="sp-tab" :class="{'active': activeTab==='tab2'}"
                @click="switchTab('tab2')">Tab 2</button>
    </div>
    <!-- Right: Search + Actions -->
    <div style="display:flex; gap:8px; align-items:center;">
        <input type="text" class="sp-search" placeholder="Search..."
               x-model="search" @input.debounce.300ms="loadData(1)">
        <select class="sp-select" x-model="filterStatus" @change="loadData(1)">
            <option value="">All Status</option>
        </select>
        <button class="sp-new-btn" @click="showCreateModal = true">+ Add New</button>
    </div>
</div>
```

---

## 6. Pagination

```html
<div class="sp-pagination" x-show="pagination && pagination.total_pages > 1">
    <!-- Prev -->
    <button class="sp-page-btn"
            @click="goToPage(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1">&laquo;</button>
    <!-- Page Numbers -->
    <template x-for="p in pageNumbers()" :key="'p'+p">
        <button class="sp-page-btn"
                :class="{'active': p === pagination.current_page}"
                @click="typeof p === 'number' && goToPage(p)"
                x-text="p"
                :disabled="p === '...'"></button>
    </template>
    <!-- Next -->
    <button class="sp-page-btn"
            @click="goToPage(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.total_pages">&raquo;</button>
</div>
```

---

## 7. Toast Notifications

```javascript
// Usage
showToast('Record saved successfully', 'success');
showToast('Failed to save', 'error');
showToast('Please check the form', 'warning');
showToast('Processing...', 'info');
```

Container: `<div id="toast-container" class="fixed top-4 right-4 z-[100] flex flex-col gap-2">`

Behavior:
- Slides in from right (translate-x-full -> 0)
- Auto-dismisses after 3000ms (default)
- Fades out with opacity transition
- Stacks vertically

---

## 8. Status Badges

### Case Status
```html
<span class="text-xs px-2 py-0.5 rounded-full font-medium"
      :class="getStatusColor(item.status)"
      x-text="getStatusLabel(item.status)"></span>
```

### Provider Status
```html
<span class="text-xs px-2 py-0.5 rounded-full font-medium"
      :class="getProviderStatusColor(item.status)"
      x-text="getStatusLabel(item.status)"></span>
```

### Active/Inactive Badge
```html
<span class="text-xs px-2 py-0.5 rounded-full font-medium"
      :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
      x-text="item.is_active ? 'Active' : 'Inactive'"></span>
```

---

## 9. D-N-T-S Progress Dots (Attorney Page)

Tracks case progress through phases: Demand, Negotiate, Trial/Litigation, Settlement

```html
<div class="sp-dots-group">
    <div class="sp-dot" :class="{'done': item.demand_sent}" title="Demand Sent">
        <span class="sp-dot-label">D</span>
    </div>
    <div class="sp-dot" :class="{'done': item.negotiation_started}" title="Negotiation">
        <span class="sp-dot-label">N</span>
    </div>
    <div class="sp-dot" :class="{'done': item.trial_date}" title="Trial">
        <span class="sp-dot-label">T</span>
    </div>
    <div class="sp-dot" :class="{'done': item.settled}" title="Settled">
        <span class="sp-dot-label">S</span>
    </div>
</div>
```

States:
- Default: Gray border, gray text
- `.done`: Gold background (#C9A84C), white text
- `.active`: Gold border, gold text

---

## 10. Staff Filter Pills

```html
<div class="sp-staff-pills">
    <button class="sp-staff-pill" :class="{'active': filterStaff === ''}"
            @click="filterStaff = ''; loadData(1)">All</button>
    <template x-for="s in staffList" :key="s.id">
        <button class="sp-staff-pill" :class="{'active': filterStaff == s.id}"
                @click="filterStaff = s.id; loadData(1)"
                x-text="s.display_name || s.full_name"></button>
    </template>
</div>
```

---

## 11. Card Component

### Standard Card
```html
<div class="sp-card">
    <div class="sp-card-header">
        <div class="sp-eyebrow">SECTION</div>
        <h2 style="font-size:16px; font-weight:700; color:#0F1B2D;">Card Title</h2>
    </div>
    <!-- Content -->
</div>
```

### Stat Cards Group
```html
<div class="sp-stats-group">
    <div class="sp-stat-card">
        <div class="sp-stat-value" x-text="summary.total || 0">0</div>
        <div class="sp-stat-label">Total Cases</div>
    </div>
    <div class="sp-stat-card">
        <div class="sp-stat-value" x-text="formatCurrency(summary.total_amount)">$0.00</div>
        <div class="sp-stat-label">Total Value</div>
    </div>
</div>
```

---

## 12. Search with Autocomplete

Used in provider/client lookups:

```html
<div style="position:relative;">
    <input type="text" class="sp-search" style="width:100%;"
           x-model="providerSearch"
           @input.debounce.300ms="searchProviders()"
           placeholder="Search providers...">
    <!-- Dropdown -->
    <div x-show="providerResults.length > 0"
         style="position:absolute; top:100%; left:0; right:0; z-index:10;
                background:#fff; border:1px solid #E5E5E0; border-radius:6px;
                box-shadow:0 4px 12px rgba(0,0,0,.1); max-height:200px; overflow-y:auto;">
        <template x-for="p in providerResults" :key="p.id">
            <div @click="selectProvider(p)"
                 style="padding:8px 12px; cursor:pointer; font-size:13px;
                        border-bottom:1px solid #f0f0f0;"
                 class="hover:bg-gray-50">
                <div style="font-weight:600;" x-text="p.name"></div>
                <div style="font-size:11px; color:#8a8a82;" x-text="p.type || ''"></div>
            </div>
        </template>
    </div>
</div>
```
