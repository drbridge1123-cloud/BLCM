<!-- All sp- styles loaded from shared sp-design-system.css -->

<div x-data="trafficPage()">

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">

        <!-- Gold bar -->
        <div class="sp-gold-bar"></div>

        <!-- Header -->
        <div class="sp-header">
            <div>
                <div class="sp-eyebrow">Case Management</div>
                <h1 class="sp-title">Traffic Cases</h1>
            </div>
            <button @click="openCreateModal()" class="sp-new-btn">+ New Case</button>
        </div>

        <!-- Toolbar -->
        <div class="sp-toolbar">
            <div class="sp-tabs">
                <button class="sp-tab" :class="activeTab === 'cases' && 'on'" @click="switchTab('cases')">Cases</button>
                <button class="sp-tab" :class="activeTab === 'requests' && 'on'" @click="switchTab('requests')">
                    Requests
                    <span x-show="pendingCount > 0" class="sp-tab-count" style="background:rgba(231,76,60,.12); color:#e74c3c;" x-text="pendingCount"></span>
                </button>
            </div>
        </div>

        <!-- Cases Tab -->
        <div x-show="activeTab === 'cases'" x-cloak>
            <?php include __DIR__ . '/tabs/_tab-cases.php'; ?>
        </div>

        <!-- Requests Tab -->
        <div x-show="activeTab === 'requests'" x-cloak>
            <?php include __DIR__ . '/tabs/_tab-requests.php'; ?>
        </div>


    </div><!-- /sp-card -->

    <!-- Modals -->
    <?php include __DIR__ . '/modals/_modal-case.php'; ?>
    <?php include __DIR__ . '/modals/_modal-request.php'; ?>

</div>
