<!-- All sp- styles loaded from shared sp-design-system.css -->
<style>
/* ═══════════════════════════════════════════════════════
   Commissions — Page-specific styles
   ═══════════════════════════════════════════════════════ */
/* Sortable headers */
.sp-table th.sortable { cursor: pointer; user-select: none; white-space: nowrap; }
.sp-table th.sortable:hover { color: #C9A84C; }
.sp-table th.sortable .sort-icon { font-size: 9px; color: #c0bdb5; margin-left: 2px; }
.sp-table th.sortable.sorted .sort-icon { color: #C9A84C; }
.ec-kpi-grid {
    display: grid;
    grid-template-columns: 1fr 1.4fr 1.4fr 1.4fr;
    gap: 10px;
    margin-bottom: 0;
}
.ec-kpi {
    border-radius: 10px;
    padding: 14px 18px;
}
.ec-kpi-label {
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    font-family: 'IBM Plex Sans', sans-serif;
    margin-bottom: 4px;
}
.ec-kpi-num {
    font-family: 'IBM Plex Mono', monospace;
    font-weight: 700;
    line-height: 1;
}
.ec-kpi-plain {
    background: #fafaf8;
    border: 1px solid #e8e4dc;
}
.ec-kpi-plain .ec-kpi-label { color: #8a8a82; }
.ec-kpi-plain .ec-kpi-num { font-size: 28px; color: #1a2535; }

.ec-kpi-green {
    background: rgba(26,158,106,.04);
    border: 1px solid rgba(26,158,106,.2);
}
.ec-kpi-green .ec-kpi-label { color: rgba(26,158,106,.7); }
.ec-kpi-green .ec-kpi-num { font-size: 22px; color: #1a9e6a; }

.ec-kpi-blue {
    background: rgba(37,99,235,.04);
    border: 1px solid rgba(37,99,235,.2);
}
.ec-kpi-blue .ec-kpi-label { color: rgba(37,99,235,.6); }
.ec-kpi-blue .ec-kpi-num { font-size: 22px; color: #2563eb; }

.ec-kpi-amber {
    background: rgba(217,119,6,.04);
    border: 1px solid rgba(217,119,6,.2);
}
.ec-kpi-amber .ec-kpi-label { color: rgba(217,119,6,.7); }
.ec-kpi-amber .ec-kpi-num { font-size: 22px; color: #D97706; }

/* ── Main tabs (underline style) ── */
.ec-main-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid #f5f2ee;
    margin-bottom: 12px;
}
.ec-main-tab {
    padding: 8px 16px;
    font-size: 12.5px;
    font-weight: 600;
    color: #8a8a82;
    border: none;
    background: none;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all .15s;
    font-family: 'IBM Plex Sans', sans-serif;
}
.ec-main-tab:hover { color: #1a2535; }
.ec-main-tab.on {
    color: #1a2535;
    border-bottom-color: #C9A84C;
}
.ec-main-tab-count {
    font-size: 10px;
    font-weight: 700;
    font-family: 'IBM Plex Mono', monospace;
    background: rgba(201,168,76,.12);
    color: #B8973F;
    border-radius: 8px;
    padding: 1px 6px;
    margin-left: 4px;
}

/* ── Status filter pills ── */
.ec-pill {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 600;
    border: 1.5px solid #e8e4dc;
    background: #fff;
    color: #8a8a82;
    cursor: pointer;
    transition: all .15s;
    font-family: 'IBM Plex Sans', sans-serif;
}
.ec-pill:hover { border-color: #bbb; color: #1a2535; }
.ec-pill.on {
    background: #C9A84C;
    border-color: #C9A84C;
    color: #fff;
}

/* ── Pill-style select ── */
.ec-pill-select {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 600;
    border: 1.5px solid #e8e4dc;
    background: #fff;
    color: #8a8a82;
    cursor: pointer;
    outline: none;
    font-family: 'IBM Plex Sans', sans-serif;
}
.ec-pill-select:focus { border-color: #C9A84C; }

/* ── In Progress row opacity ── */
.ec-row-dim { opacity: 0.7; }

/* ── Check text ── */
.ec-check-received {
    font-size: 11px;
    font-weight: 600;
    color: #1a9e6a;
    font-family: 'IBM Plex Sans', sans-serif;
}
.ec-check-pending {
    font-size: 11px;
    font-weight: 600;
    color: #8a8a82;
    font-family: 'IBM Plex Sans', sans-serif;
}

</style>

<div x-data="commissionsPage()">

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">

        <!-- Gold bar -->
        <div class="sp-gold-bar"></div>

        <!-- Header -->
        <div class="sp-header" style="flex-wrap:wrap; gap:20px; padding:20px 24px 16px;">
            <!-- Top row: Title + Buttons -->
            <div style="display:flex; align-items:center; width:100%; margin-bottom:16px;">
                <h1 class="sp-title" style="flex:1">Commissions</h1>
                <div style="display:flex; gap:8px;">
                    <button @click="openCreateModal()" class="sp-new-btn-navy" style="box-shadow:0 2px 8px rgba(15,27,45,.2)">+ Add Commission</button>
                </div>
            </div>
            <!-- KPI cards -->
            <div class="ec-kpi-grid" style="width:100%;">
                <div class="ec-kpi ec-kpi-plain">
                    <div class="ec-kpi-label">Total Cases</div>
                    <div class="ec-kpi-num" x-text="stats.total_cases"></div>
                </div>
                <div class="ec-kpi ec-kpi-green">
                    <div class="ec-kpi-label">Total Commission</div>
                    <div class="ec-kpi-num" x-text="formatCurrency(stats.total_commission)"></div>
                </div>
                <div class="ec-kpi ec-kpi-blue">
                    <div class="ec-kpi-label">Paid</div>
                    <div class="ec-kpi-num" x-text="formatCurrency(stats.paid_commission)"></div>
                </div>
                <div class="ec-kpi ec-kpi-amber">
                    <div class="ec-kpi-label">Unpaid</div>
                    <div class="ec-kpi-num" x-text="formatCurrency(stats.unpaid_commission)"></div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="sp-toolbar" style="padding:12px 24px; flex-wrap:wrap; gap:10px;">
            <!-- Main tabs (underline style) -->
            <div class="ec-main-tabs" style="margin-bottom:0; border-bottom:none;">
                <template x-for="tab in tabs" :key="tab.key">
                    <button class="ec-main-tab" :class="activeTab === tab.key && 'on'"
                            @click="switchTab(tab.key)">
                        <span x-text="tab.label"></span>
                        <span class="ec-main-tab-count" x-text="tab.count"></span>
                    </button>
                </template>
            </div>

            <!-- Filters (active tab) -->
            <div class="sp-toolbar-right" x-show="activeTab === 'active'" x-cloak style="gap:6px;">
                <button class="ec-pill" :class="statusFilter === '' && 'on'"
                        @click="statusFilter = ''; commPage = 1; loadTab('active')">All</button>
                <button class="ec-pill" :class="statusFilter === 'in_progress' && 'on'"
                        @click="statusFilter = 'in_progress'; commPage = 1; loadTab('active')">In Progress</button>
                <button class="ec-pill" :class="statusFilter === 'unpaid' && 'on'"
                        @click="statusFilter = 'unpaid'; commPage = 1; loadTab('active')">Unpaid</button>

                <select class="ec-pill-select" x-model="yearFilter" @change="commPage = 1; loadTab('active')">
                    <option value="">All Years</option>
                    <template x-for="y in yearOptions" :key="y">
                        <option :value="y" x-text="y"></option>
                    </template>
                </select>

                <template x-if="isAdmin">
                    <select class="ec-pill-select" x-model="employeeFilter" @change="commPage = 1; loadTab('active')">
                        <option value="">All Employees</option>
                        <template x-for="u in employees" :key="u.id">
                            <option :value="u.id" x-text="u.display_name || u.full_name"></option>
                        </template>
                    </select>
                </template>

                <input type="text" class="sp-search" style="width:200px" placeholder="Search case # or client..."
                       x-model="search" @input="handleSearch()">
            </div>

            <!-- Filters (history tab) -->
            <div class="sp-toolbar-right" x-show="activeTab === 'history'" x-cloak style="gap:6px;">
                <button class="ec-pill" :class="historyStatusFilter === '' && 'on'"
                        @click="historyStatusFilter = ''; historyPage = 1; loadTab('history')">All</button>
                <button class="ec-pill" :class="historyStatusFilter === 'paid' && 'on'"
                        @click="historyStatusFilter = 'paid'; historyPage = 1; loadTab('history')">Paid</button>
                <button class="ec-pill" :class="historyStatusFilter === 'rejected' && 'on'"
                        @click="historyStatusFilter = 'rejected'; historyPage = 1; loadTab('history')">Rejected</button>

                <select class="ec-pill-select" x-model="historyYearFilter" @change="historyPage = 1; loadTab('history')">
                    <option value="">All Years</option>
                    <template x-for="y in yearOptions" :key="y">
                        <option :value="y" x-text="y"></option>
                    </template>
                </select>

                <template x-if="isAdmin">
                    <select class="ec-pill-select" x-model="historyEmployeeFilter" @change="historyPage = 1; loadTab('history')">
                        <option value="">All Employees</option>
                        <template x-for="u in employees" :key="u.id">
                            <option :value="u.id" x-text="u.display_name || u.full_name"></option>
                        </template>
                    </select>
                </template>

                <input type="text" class="sp-search" style="width:200px" placeholder="Search..."
                       x-model="historySearch" @input="handleHistorySearch()">
            </div>

            <!-- Filters (attorney/my cases tab) -->
            <div class="sp-toolbar-right" x-show="activeTab === 'attorney'" x-cloak style="gap:6px;">
                <button class="ec-pill" :class="attorneyStatusFilter === '' && 'on'"
                        @click="attorneyStatusFilter = ''">All</button>
                <button class="ec-pill" :class="attorneyStatusFilter === 'unpaid' && 'on'"
                        @click="attorneyStatusFilter = 'unpaid'">Unpaid</button>
                <button class="ec-pill" :class="attorneyStatusFilter === 'paid' && 'on'"
                        @click="attorneyStatusFilter = 'paid'">Paid</button>

                <select class="ec-pill-select" x-model="attorneyYearFilter">
                    <option value="">All Years</option>
                    <template x-for="y in yearOptions" :key="y">
                        <option :value="y" x-text="y"></option>
                    </template>
                </select>

                <select class="ec-pill-select" x-model="attorneyMonthFilter">
                    <option value="">All Months</option>
                    <template x-for="m in attorneyMonthOptions" :key="m">
                        <option :value="m" x-text="m"></option>
                    </template>
                </select>
            </div>
        </div>

        <!-- Tab Content: Active -->
        <div x-show="activeTab === 'active'" x-cloak>
            <?php include __DIR__ . '/tabs/_tab-active.php'; ?>
        </div>

        <!-- Tab Content: History -->
        <div x-show="activeTab === 'history'" x-cloak>
            <?php include __DIR__ . '/tabs/_tab-history.php'; ?>
        </div>

        <!-- Tab Content: Attorney -->
        <div x-show="activeTab === 'attorney'" x-cloak>
            <?php include __DIR__ . '/tabs/_tab-attorney.php'; ?>
        </div>

        <!-- Tab Content: Admin -->
        <div x-show="activeTab === 'admin'" x-cloak>
            <?php include __DIR__ . '/tabs/_tab-admin.php'; ?>
        </div>

    </div><!-- /sp-card -->

    <!-- Modals -->
    <?php include __DIR__ . '/modals/_modal-create.php'; ?>
    <?php include __DIR__ . '/modals/_modal-edit.php'; ?>
    <?php include __DIR__ . '/modals/_modal-attorney.php'; ?>

</div>
