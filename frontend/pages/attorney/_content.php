<!-- All sp- styles loaded from shared sp-design-system.css -->

<div x-data="attorneyCasesPage()">

    <!-- Page header row -->
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <div>
            <a x-show="fromCaseDetail" x-cloak :href="fromCaseDetailUrl"
               style="display:inline-flex; align-items:center; gap:4px; font-size:11px; color:#8a8a82; text-decoration:none; margin-bottom:4px;">&larr; Case Detail</a>
            <div class="sp-eyebrow">Attorney Cases</div>
            <h1 class="sp-title" style="font-size:16px;">Case Management</h1>
        </div>
        <button @click="openCreateModal()" class="sp-new-btn">+ New Case</button>
    </div>

    <!-- Staff Tabs -->
    <div class="sp-staff-bar" x-show="staffList.length > 1" x-cloak>
        <button @click="staffFilter = ''; loadData(1)"
                class="sp-staff-pill" :class="staffFilter === '' && 'on'">All</button>
        <template x-for="staff in staffList" :key="staff.id">
            <button @click="staffFilter = staff.id.toString(); loadData(1)"
                    class="sp-staff-pill" :class="staffFilter === staff.id.toString() && 'on'"
                    x-text="staff.display_name || staff.full_name"></button>
        </template>
    </div>

    <!-- Pending Case Assignments -->
    <?php include __DIR__ . '/../../components/_pending-assignments.php'; ?>

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">

        <!-- Gold bar -->
        <div class="sp-gold-bar"></div>

        <!-- Header: Demand -->
        <div class="sp-header" x-show="activeTab === 'demand'" x-cloak>
            <div>
                <div class="sp-eyebrow">Settlement Pipeline</div>
                <h2 class="sp-title">Negotiate &amp; Settle</h2>
            </div>
            <div class="sp-stats">
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#1a2535" x-text="search.trim() ? demandCases.length : (stats.demand_count || 0)"></div>
                    <div class="sp-stat-label">Total</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#e74c3c" x-text="demandStats.overdue"></div>
                    <div class="sp-stat-label">Overdue</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#C9A84C" x-text="demandStats.negotiating"></div>
                    <div class="sp-stat-label">Negotiating</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#1a9e6a" x-text="demandStats.settled"></div>
                    <div class="sp-stat-label">Settled</div>
                </div>
            </div>
        </div>

        <!-- Header: UIM -->
        <div class="sp-header" x-show="activeTab === 'uim'" x-cloak>
            <div>
                <div class="sp-eyebrow">Uninsured Motorist</div>
                <h2 class="sp-title">UIM Cases</h2>
            </div>
            <div class="sp-stats">
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#1a2535" x-text="search.trim() ? uimCases.length : (stats.total_active || 0)"></div>
                    <div class="sp-stat-label">Total</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#C9A84C" x-text="search.trim() ? uimCases.length : (stats.uim_count || 0)"></div>
                    <div class="sp-stat-label">UIM Active</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#2563eb" x-text="search.trim() ? litigationCases.length : (stats.litigation_count || 0)"></div>
                    <div class="sp-stat-label">Litigation</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#1a9e6a" x-text="search.trim() ? settledCases.length : (stats.settled_count || 0)"></div>
                    <div class="sp-stat-label">Settled</div>
                </div>
            </div>
        </div>

        <!-- Header: Litigation -->
        <div class="sp-header" x-show="activeTab === 'litigation'" x-cloak>
            <div>
                <div class="sp-eyebrow">Attorney Cases</div>
                <h2 class="sp-title">Litigation</h2>
            </div>
            <div class="sp-stats">
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#1a2535" x-text="search.trim() ? litigationCases.length : (stats.litigation_count || 0)"></div>
                    <div class="sp-stat-label">Total</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#7C5CBF" x-text="litStats.litigation"></div>
                    <div class="sp-stat-label">Litigation</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#C9A84C" x-text="litStats.uim"></div>
                    <div class="sp-stat-label">UIM</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#1a9e6a" x-text="litStats.settled"></div>
                    <div class="sp-stat-label">Settled</div>
                </div>
            </div>
            <button @click="openCreateModal()" class="sp-new-btn-navy">+ New Case</button>
        </div>

        <!-- Header: Settled -->
        <div class="sp-header" x-show="activeTab === 'settled'" x-cloak>
            <div>
                <div class="sp-eyebrow">Completed Cases</div>
                <h2 class="sp-title">Settled Cases</h2>
            </div>
            <div class="sp-stats">
                <div class="sp-stat" style="min-width:110px">
                    <div class="sp-stat-num" style="color:#1a9e6a; font-size:20px" x-text="search.trim() ? settledCases.length : (stats.settled_count || 0)"></div>
                    <div class="sp-stat-label">Settled</div>
                </div>
                <div class="sp-stat" style="min-width:110px">
                    <div class="sp-stat-num" style="font-size:20px" :style="(stats.month_commission || 0) > 0 ? 'color:#1a9e6a' : 'color:#8a8a82'" x-text="formatCurrency(stats.month_commission)"></div>
                    <div class="sp-stat-label">Month Comm.</div>
                </div>
                <div class="sp-stat" style="min-width:110px">
                    <div class="sp-stat-num" style="color:#1a9e6a; font-size:20px" x-text="formatCurrency(stats.ytd_commission)"></div>
                    <div class="sp-stat-label">YTD Comm.</div>
                </div>
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                <template x-if="currentUserHasCommission">
                    <a href="/blcm/commissions#attorney" class="sp-btn" style="text-decoration:none; font-size:12px; color:#C9A84C; border-color:#C9A84C;">Commissions →</a>
                </template>
                <button @click="openCreateModal()" class="sp-new-btn-navy" style="box-shadow:0 2px 8px rgba(15,27,45,.2)">+ New Case</button>
            </div>
        </div>

        <!-- Toolbar: Main phase tabs + search/sort/export -->
        <div class="sp-toolbar">
            <div class="sp-tabs">
                <template x-for="tab in tabs" :key="tab.key">
                    <button class="sp-tab" :class="activeTab === tab.key && 'on'"
                            @click="switchTab(tab.key)">
                        <span x-text="tab.label"></span>
                        <span class="sp-tab-count" x-text="tab.count"></span>
                    </button>
                </template>
            </div>
            <div class="sp-toolbar-right">
                <input type="text" class="sp-search" placeholder="Search case or client..."
                       x-model="search" @input="handleSearch()">
                <button class="sp-btn" @click="toggleDemandSort()" x-show="activeTab === 'demand'">
                    <span style="margin-right:2px">↕</span> Sort
                </button>
                <button class="sp-btn" @click="toggleUimSort()" x-show="activeTab === 'uim'">
                    <span style="margin-right:2px">↕</span> Sort
                </button>
                <select class="sp-select" x-model="litMonthFilter" @change="litPage = 1" x-show="activeTab === 'litigation'">
                    <option value="">All Months</option>
                    <template x-for="m in monthOptions" :key="m">
                        <option :value="m" x-text="m"></option>
                    </template>
                </select>
                <button class="sp-btn" @click="toggleLitSort()" x-show="activeTab === 'litigation'">
                    <span style="margin-right:2px">↕</span> Sort
                </button>
                <select class="sp-select" x-model="settledMonthFilter" @change="settledPage = 1" x-show="activeTab === 'settled'">
                    <option value="">All Months</option>
                    <template x-for="m in monthOptions" :key="m">
                        <option :value="m" x-text="m"></option>
                    </template>
                </select>
                <select class="sp-select" x-model="settledYearFilter" @change="settledPage = 1" x-show="activeTab === 'settled'">
                    <option value="">All Years</option>
                    <template x-for="y in yearOptions" :key="y">
                        <option :value="y" x-text="y"></option>
                    </template>
                </select>
                <button class="sp-btn" @click="toggleSettledSort()" x-show="activeTab === 'settled'">
                    <span style="margin-right:2px">↕</span> Sort
                </button>
            </div>
        </div>

        <!-- Sub-filter bar (demand only) -->
        <div class="sp-toolbar" x-show="activeTab === 'demand'" x-cloak style="padding:8px 24px; gap:2px;">
            <div class="sp-tabs">
                <template x-for="f in demandSubFilters" :key="f.key">
                    <button class="sp-tab" :class="demandSubFilter === f.key && 'on'"
                            @click="demandSubFilter = f.key; demandPage = 1"
                            x-text="f.label"></button>
                </template>
            </div>
        </div>

        <!-- Tab Content: Demand -->
        <div x-show="activeTab === 'demand'" x-cloak>
            <?php include __DIR__ . '/tabs/_tab-demand.php'; ?>
        </div>

        <!-- Sub-filter bar (UIM) -->
        <div class="sp-toolbar" x-show="activeTab === 'uim'" x-cloak style="padding:8px 24px; gap:2px;">
            <div class="sp-tabs">
                <template x-for="f in uimSubFilters" :key="f.key">
                    <button class="sp-tab" :class="uimSubFilter === f.key && 'on'"
                            @click="uimSubFilter = f.key; uimPage = 1"
                            x-text="f.label"></button>
                </template>
            </div>
        </div>

        <!-- Tab Content: UIM -->
        <div x-show="activeTab === 'uim'" x-cloak>
            <?php include __DIR__ . '/tabs/_tab-uim.php'; ?>
        </div>

        <!-- Tab Content: Litigation -->
        <div x-show="activeTab === 'litigation'" x-cloak>
            <?php include __DIR__ . '/tabs/_tab-litigation.php'; ?>
        </div>

        <!-- Tab Content: Settled -->
        <div x-show="activeTab === 'settled'" x-cloak>
            <?php include __DIR__ . '/tabs/_tab-settled.php'; ?>
        </div>

    </div><!-- /sp-card -->

    <!-- Modals -->
    <?php include __DIR__ . '/modals/_modal-create.php'; ?>
    <?php include __DIR__ . '/modals/_modal-settle-demand.php'; ?>
    <?php include __DIR__ . '/modals/_modal-to-litigation.php'; ?>
    <?php include __DIR__ . '/modals/_modal-to-uim.php'; ?>
    <?php include __DIR__ . '/modals/_modal-settle-litigation.php'; ?>
    <?php include __DIR__ . '/modals/_modal-settle-uim.php'; ?>
    <?php include __DIR__ . '/modals/_modal-top-offer.php'; ?>
    <?php include __DIR__ . '/modals/_modal-edit.php'; ?>
    <?php include __DIR__ . '/modals/_modal-transfer.php'; ?>
    <?php include __DIR__ . '/modals/_modal-send-billing.php'; ?>
    <?php include __DIR__ . '/modals/_modal-send-accounting.php'; ?>

</div>
