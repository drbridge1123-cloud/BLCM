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

        <!-- Header -->
        <div class="sp-header">
            <div>
                <div class="sp-eyebrow">Attorney Cases</div>
                <h2 class="sp-title">Case Management</h2>
            </div>
            <button @click="openCreateModal()" class="sp-new-btn">+ New Case</button>
        </div>

        <!-- Toolbar: Main phase tabs + search/sort/export -->
        <div class="sp-toolbar">
            <div class="sp-tabs">
                <template x-for="tab in tabs" :key="tab.key">
                    <button class="sp-tab" :class="activeTab === tab.key && 'on'"
                            @click="switchTab(tab.key)">
                        <span x-text="tab.label"></span>
                        <span class="sp-tab-count" :style="'background:' + (tab.bg || 'rgba(138,138,130,.12)') + '; color:' + (tab.color || '#8a8a82')" x-text="tab.count"></span>
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
