<!-- All sp- styles loaded from shared sp-design-system.css -->

<div x-data="{
    logs: [],
    loading: true,
    pagination: null,
    search: '',
    entityFilter: '',

    async init() {
        await this.loadLogs();
    },

    async loadLogs(page = 1) {
        this.loading = true;
        try {
            const params = { page, per_page: 50 };
            if (this.search) params.search = this.search;
            if (this.entityFilter) params.entity_type = this.entityFilter;
            const res = await api.get('activity-log' + buildQueryString(params));
            this.logs = res.data || [];
            this.pagination = res.pagination || null;
        } catch (e) {
            showToast(e.message, 'error');
        }
        this.loading = false;
    },

    goToPage(page) {
        if (page < 1 || (this.pagination && page > this.pagination.total_pages)) return;
        this.loadLogs(page);
    }
}">

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">

        <!-- Gold bar -->
        <div class="sp-gold-bar"></div>

        <!-- Header -->
        <div class="sp-header">
            <div style="flex:1;">
                <div class="sp-eyebrow">Admin</div>
                <h1 class="sp-title">Activity Log</h1>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="sp-toolbar">
            <div class="sp-toolbar-right" style="display:flex; gap:8px; width:100%;">
                <input type="text" x-model="search" @input.debounce.300ms="loadLogs(1)" placeholder="Search activity..."
                       class="sp-search" style="flex:1; min-width:200px;">
                <select x-model="entityFilter" @change="loadLogs(1)" class="sp-select">
                    <option value="">All Entities</option>
                    <option value="case">Cases</option>
                    <option value="user">Users</option>
                    <option value="provider">Providers</option>
                    <option value="commission">Commissions</option>
                </select>
            </div>
        </div>

        <!-- Loading -->
        <template x-if="loading">
            <div class="sp-loading"><div class="spinner"></div></div>
        </template>

        <!-- Table -->
        <template x-if="!loading">
            <div>
                <table class="sp-table">
                    <thead><tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Details</th>
                    </tr></thead>
                    <tbody>
                        <template x-if="logs.length === 0">
                            <tr><td colspan="5" class="sp-empty">No activity found.</td></tr>
                        </template>
                        <template x-for="l in logs" :key="l.id">
                            <tr>
                                <td style="white-space:nowrap; font-size:12px; color:#9ca3af;" x-text="timeAgo(l.created_at)"></td>
                                <td x-text="l.full_name || l.user_id"></td>
                                <td><span class="sp-stage" style="background:#dbeafe; color:#2563eb;" x-text="l.action"></span></td>
                                <td style="text-transform:capitalize;" x-text="(l.entity_type || '') + (l.entity_id ? ' #' + l.entity_id : '')"></td>
                                <td style="max-width:200px; font-size:12px; color:#9ca3af;" class="truncate" x-text="l.details || '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>

            </div>
        </template>

    </div><!-- /sp-card -->

</div>
