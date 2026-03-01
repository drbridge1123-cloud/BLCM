<!-- All sp- styles loaded from shared sp-design-system.css -->

<div x-data="referralsPage()">

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">

        <!-- Gold bar -->
        <div class="sp-gold-bar"></div>

        <!-- Header -->
        <div class="sp-header" style="flex-wrap:wrap; gap:16px;">
            <div style="flex:1;">
                <div class="sp-eyebrow">Case Management</div>
                <h1 class="sp-title">Referrals</h1>
            </div>
            <div class="sp-stats">
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#1a2535;" x-text="summary.month_count || 0"></div>
                    <div class="sp-stat-label">This Month</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#2563eb;" x-text="summary.total_entries || 0"></div>
                    <div class="sp-stat-label">YTD Total</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#1a9e6a; font-size:13px;" x-text="topSource || '—'"></div>
                    <div class="sp-stat-label">Top Source</div>
                </div>
            </div>
            <div style="display:flex; gap:8px;">
                <button @click="openCreateModal()" class="sp-new-btn-navy">+ New Referral</button>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="sp-toolbar" style="flex-wrap:wrap; gap:8px;">
            <div class="sp-tabs">
                <button class="sp-tab" :class="activeTab === 'list' && 'on'" @click="activeTab = 'list'">Referrals</button>
                <button class="sp-tab" :class="activeTab === 'report' && 'on'" @click="activeTab = 'report'; loadReport()">Report</button>
            </div>

            <div class="sp-toolbar-right" x-show="activeTab === 'list'" style="gap:6px;">
                <select x-model="yearFilter" @change="loadReferrals()" class="sp-select">
                    <option value="">All Years</option>
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                </select>

                <select x-model="monthFilter" @change="loadReferrals()" class="sp-select">
                    <option value="">All Months</option>
                    <template x-for="m in monthOptions" :key="m.value">
                        <option :value="m.value" x-text="m.label"></option>
                    </template>
                </select>

                <template x-if="isAdmin">
                    <select x-model="managerFilter" @change="loadReferrals()" class="sp-select">
                        <option value="">All Managers</option>
                        <template x-for="u in users" :key="u.id">
                            <option :value="u.id" x-text="u.full_name"></option>
                        </template>
                    </select>
                </template>

                <input type="text" x-model="search" @input="handleSearch()" placeholder="Search client, file #..."
                       class="sp-search">
            </div>
        </div>

        <!-- List Tab -->
        <div x-show="activeTab === 'list'" x-cloak>
            <?php include __DIR__ . '/tabs/_tab-list.php'; ?>
        </div>

        <!-- Report Tab -->
        <div x-show="activeTab === 'report'" x-cloak>
            <?php include __DIR__ . '/tabs/_tab-report.php'; ?>
        </div>


    </div><!-- /sp-card -->

    <!-- Modals -->
    <?php include __DIR__ . '/modals/_modal-create.php'; ?>
    <?php include __DIR__ . '/modals/_modal-edit.php'; ?>

</div>
