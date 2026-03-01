<!-- All sp- styles loaded from shared sp-design-system.css -->

<div x-data="{
    goals: [],
    employees: [],
    loading: true,
    saving: false,
    selectedYear: new Date().getFullYear(),
    showModal: false,
    form: { user_id: '', year: new Date().getFullYear(), target_cases: 50, target_legal_fee: 500000, notes: '' },

    get yearOptions() {
        const y = new Date().getFullYear();
        return [y - 1, y, y + 1];
    },

    async init() {
        await this.loadEmployees();
        await this.loadGoals();
    },

    async loadEmployees() {
        try {
            const res = await api.get('users?is_active=1&per_page=100');
            this.employees = res.data || [];
        } catch (e) { /* ignore */ }
    },

    async loadGoals() {
        this.loading = true;
        try {
            const res = await api.get('goals?year=' + this.selectedYear);
            this.goals = res.data || [];
        } catch (e) {
            showToast(e.message, 'error');
        }
        this.loading = false;
    },

    openModal(goal) {
        if (goal) {
            this.form = {
                user_id: goal.user_id,
                year: goal.year,
                target_cases: goal.target_cases,
                target_legal_fee: goal.target_legal_fee,
                notes: goal.notes || ''
            };
        } else {
            this.form = { user_id: '', year: this.selectedYear, target_cases: 50, target_legal_fee: 500000, notes: '' };
        }
        this.showModal = true;
    },

    async saveGoal() {
        if (!this.form.user_id) return showToast('Please select an employee', 'error');
        this.saving = true;
        try {
            await api.post('goals', this.form);
            showToast('Goal saved');
            this.showModal = false;
            await this.loadGoals();
        } catch (e) {
            showToast(e.message, 'error');
        }
        this.saving = false;
    },

    fmt(val) {
        return '$' + parseFloat(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}">

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">

        <!-- Gold bar -->
        <div class="sp-gold-bar"></div>

        <!-- Header -->
        <div class="sp-header">
            <div style="flex:1;">
                <div class="sp-eyebrow">Performance</div>
                <h1 class="sp-title">Employee Goals</h1>
            </div>
            <select x-model="selectedYear" @change="loadGoals()" class="sp-select">
                <template x-for="y in yearOptions" :key="y">
                    <option :value="y" x-text="y"></option>
                </template>
            </select>
            <button @click="openModal(null)" class="sp-new-btn">+ Set Goal</button>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="sp-loading">Loading goals...</div>

        <!-- Goals Table -->
        <div x-show="!loading" x-cloak>
            <table class="sp-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th class="right">Target Cases</th>
                        <th class="right">Target Legal Fee</th>
                        <th>Notes</th>
                        <th class="right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="goals.length === 0">
                        <tr style="cursor:default;"><td colspan="5" class="sp-empty" style="padding:32px 0;">No goals set for <span x-text="selectedYear"></span>.</td></tr>
                    </template>
                    <template x-for="g in goals" :key="g.id">
                        <tr style="cursor:default;">
                            <td><span class="sp-client" x-text="g.employee_name"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" x-text="g.target_cases"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" x-text="fmt(g.target_legal_fee)"></span></td>
                            <td><span style="font-size:12px; color:#8a8a82; max-width:200px; display:inline-block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="g.notes || '—'"></span></td>
                            <td style="text-align:right;">
                                <div class="sp-actions">
                                    <button @click="openModal(g)" class="sp-act sp-act-gold">
                                        <span>✎</span>
                                        <span class="sp-tip">Edit Goal</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

    </div><!-- /sp-card -->

    <!-- ═══ Goal Setting Modal ═══ -->
    <template x-if="showModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showModal=false">
            <div class="sp-card" style="width:100%; max-width:420px; margin:16px;" @click.stop>
                <div class="sp-gold-bar"></div>
                <div class="sp-header" style="padding:16px 20px;">
                    <h3 class="sp-title" style="font-size:15px; flex:1;">Set Employee Goal</h3>
                    <button @click="showModal=false" style="color:#8a8a82; font-size:18px; cursor:pointer; background:none; border:none;">&times;</button>
                </div>
                <div style="padding:16px 20px; display:flex; flex-direction:column; gap:14px;">
                    <div>
                        <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Employee</label>
                        <select x-model="form.user_id" class="sp-select" style="width:100%; padding:8px 12px;">
                            <option value="">Select employee...</option>
                            <template x-for="u in employees" :key="u.id">
                                <option :value="u.id" x-text="u.full_name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Year</label>
                        <input type="number" x-model="form.year" class="sp-search" style="width:100%; padding:8px 12px;">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Target Cases</label>
                            <input type="number" x-model="form.target_cases" class="sp-search" style="width:100%; padding:8px 12px;">
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Target Legal Fee</label>
                            <input type="number" step="0.01" x-model="form.target_legal_fee" class="sp-search" style="width:100%; padding:8px 12px;">
                        </div>
                    </div>
                    <div>
                        <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Notes</label>
                        <textarea x-model="form.notes" rows="2" class="sp-search" style="width:100%; padding:8px 12px; resize:none;"></textarea>
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; padding:12px 20px; background:#fafaf8; border-top:1px solid #f5f2ee;">
                    <button @click="showModal=false" class="sp-btn">Cancel</button>
                    <button @click="saveGoal()" :disabled="saving" class="sp-new-btn">
                        <span x-show="!saving">Save Goal</span>
                        <span x-show="saving">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
