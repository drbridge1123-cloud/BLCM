/**
 * Referrals Page Controller
 * Alpine.js component for managing referral entries.
 *
 * Tabs: List (referral entries), Report (analytics)
 */
function referralsPage() {
    return {
        // -------------------------------------------------------
        //  State
        // -------------------------------------------------------
        activeTab: 'list',
        loading: false,
        saving: false,

        referrals: [],
        pagination: null,
        summary: { total_entries: 0, month_count: 0 },
        topSource: '',

        report: {
            total_referrals: 0,
            by_personal: [],
            by_provider: [],
            by_destination: [],
            by_status: [],
            by_month: []
        },
        reportYear: new Date().getFullYear(),

        // -------------------------------------------------------
        //  Role check
        // -------------------------------------------------------
        get isAdmin() {
            const u = Alpine.store('auth')?.user;
            return u && (u.role === 'admin' || u.role === 'manager');
        },

        // -------------------------------------------------------
        //  Filters
        // -------------------------------------------------------
        search: '',
        yearFilter: String(new Date().getFullYear()),
        monthFilter: '',
        managerFilter: '',

        monthOptions: [
            { value: 'Jan. ' + new Date().getFullYear(), label: 'January' },
            { value: 'Feb. ' + new Date().getFullYear(), label: 'February' },
            { value: 'Mar. ' + new Date().getFullYear(), label: 'March' },
            { value: 'Apr. ' + new Date().getFullYear(), label: 'April' },
            { value: 'May. ' + new Date().getFullYear(), label: 'May' },
            { value: 'Jun. ' + new Date().getFullYear(), label: 'June' },
            { value: 'Jul. ' + new Date().getFullYear(), label: 'July' },
            { value: 'Aug. ' + new Date().getFullYear(), label: 'August' },
            { value: 'Sep. ' + new Date().getFullYear(), label: 'September' },
            { value: 'Oct. ' + new Date().getFullYear(), label: 'October' },
            { value: 'Nov. ' + new Date().getFullYear(), label: 'November' },
            { value: 'Dec. ' + new Date().getFullYear(), label: 'December' },
        ],

        // -------------------------------------------------------
        //  Users (for admin dropdowns)
        // -------------------------------------------------------
        users: [],

        // -------------------------------------------------------
        //  Modal state
        // -------------------------------------------------------
        showCreateModal: false,
        showEditModal: false,

        createForm: {
            client_name: '', client_dob: '', signed_date: '', file_number: '', status: '',
            date_of_loss: '', referred_by: '', referred_to_provider: '',
            referred_to_body_shop: '', referral_type: '', lead_id: '',
            case_manager_id: '', remark: ''
        },

        editForm: {
            id: null, client_name: '', client_dob: '', signed_date: '', file_number: '', status: '',
            date_of_loss: '', referred_by: '', referred_to_provider: '',
            referred_to_body_shop: '', referral_type: '', lead_id: '',
            case_manager_id: '', remark: ''
        },

        // Provider autocomplete
        providerSearchResults: [],
        showProviderDropdown: false,
        _providerSearchTimer: null,

        // -------------------------------------------------------
        //  Lifecycle
        // -------------------------------------------------------
        async init() {
            await this.loadUsers();
            await this.loadReferrals();
        },

        async loadUsers() {
            try {
                const res = await api.get('users?is_active=1&per_page=100');
                this.users = res.data || [];
            } catch (e) { /* ignore */ }
        },

        // -------------------------------------------------------
        //  Data Loading
        // -------------------------------------------------------
        async loadReferrals(page = 1) {
            this.loading = true;
            try {
                const params = { page, per_page: 50 };
                if (this.yearFilter) params.year = this.yearFilter;
                if (this.monthFilter) params.month = this.monthFilter;
                if (this.managerFilter) params.case_manager_id = this.managerFilter;
                if (this.search) params.search = this.search;

                const res = await api.get('referrals' + buildQueryString(params));
                this.referrals = res.data || [];
                const pg = res.pagination || null;
                this.pagination = pg ? { ...pg, current_page: pg.page } : null;
                this.summary = res.summary || this.summary;

                // Compute top source from data
                this._computeTopSource();
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.loading = false;
        },

        _computeTopSource() {
            const counts = {};
            this.referrals.forEach(r => {
                if (r.referred_by) {
                    counts[r.referred_by] = (counts[r.referred_by] || 0) + 1;
                }
            });
            let max = 0;
            this.topSource = '';
            for (const [k, v] of Object.entries(counts)) {
                if (v > max) { max = v; this.topSource = k; }
            }
        },

        async loadReport() {
            try {
                const res = await api.get('referrals/report' + buildQueryString({ year: this.reportYear }));
                this.report = res.data || this.report;
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        // -------------------------------------------------------
        //  Provider autocomplete
        // -------------------------------------------------------
        searchProviders(query) {
            clearTimeout(this._providerSearchTimer);
            if (query.length < 2) {
                this.providerSearchResults = [];
                this.showProviderDropdown = false;
                return;
            }
            this._providerSearchTimer = setTimeout(async () => {
                try {
                    const res = await api.get('providers/search?q=' + encodeURIComponent(query));
                    this.providerSearchResults = res.data || [];
                    this.showProviderDropdown = this.providerSearchResults.length > 0;
                } catch (e) {
                    this.providerSearchResults = [];
                    this.showProviderDropdown = false;
                }
            }, 250);
        },

        selectProvider(provider, formKey) {
            this[formKey].referred_to_provider = provider.name;
            this.providerSearchResults = [];
            this.showProviderDropdown = false;
        },

        // -------------------------------------------------------
        //  CRUD
        // -------------------------------------------------------
        openCreateModal() {
            this.createForm = {
                client_name: '', client_dob: '', signed_date: new Date().toISOString().slice(0, 10),
                file_number: '', status: '', date_of_loss: '',
                referred_by: '', referred_to_provider: '', referred_to_body_shop: '',
                referral_type: '', lead_id: '', case_manager_id: '', remark: ''
            };
            this.showCreateModal = true;
        },

        async createReferral() {
            if (!this.createForm.client_name) {
                return showToast('Client name is required', 'error');
            }
            this.saving = true;
            try {
                const res = await api.post('referrals', this.createForm);
                showToast('Referral added');
                if (res.data?.warnings) {
                    res.data.warnings.forEach(w => showToast(w, 'warning', 5000));
                }
                this.showCreateModal = false;
                await this.loadReferrals();
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.saving = false;
        },

        openEditModal(r) {
            this.editForm = {
                id: r.id,
                client_name: r.client_name || '',
                client_dob: r.client_dob || '',
                signed_date: r.signed_date || '',
                file_number: r.file_number || '',
                status: r.status || '',
                date_of_loss: r.date_of_loss || '',
                referred_by: r.referred_by || '',
                referred_to_provider: r.referred_to_provider || '',
                referred_to_body_shop: r.referred_to_body_shop || '',
                referral_type: r.referral_type || '',
                lead_id: r.lead_id || '',
                case_manager_id: r.case_manager_id || '',
                remark: r.remark || ''
            };
            this.showEditModal = true;
        },

        async updateReferral() {
            this.saving = true;
            try {
                const res = await api.put('referrals/' + this.editForm.id, this.editForm);
                showToast('Referral updated');
                if (res.data?.warnings) {
                    res.data.warnings.forEach(w => showToast(w, 'warning', 5000));
                }
                this.showEditModal = false;
                await this.loadReferrals();
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.saving = false;
        },

        async deleteReferral(id) {
            if (!confirm('Delete this referral?')) return;
            try {
                await api.delete('referrals/' + id);
                showToast('Referral deleted');
                await this.loadReferrals();
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        // -------------------------------------------------------
        //  Helpers
        // -------------------------------------------------------
        formatCurrency(val) {
            return '$' + parseFloat(val || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2, maximumFractionDigits: 2
            });
        },

        getStatusClass(status) {
            const map = {
                'INI': 'bg-blue-100 text-blue-700',
                'REC': 'bg-cyan-100 text-cyan-700',
                'NEG': 'bg-amber-100 text-amber-700',
                'FILE': 'bg-purple-100 text-purple-700',
                'LIT': 'bg-red-100 text-red-700',
                'SETTLE': 'bg-green-100 text-green-700',
                'RFD': 'bg-gray-100 text-gray-600',
                'HEALTH': 'bg-teal-100 text-teal-700',
            };
            return map[status] || 'bg-gray-100 text-gray-600';
        },

        // -------------------------------------------------------
        //  Export
        // -------------------------------------------------------
        exportCsv() {
            const rows = this.referrals;
            if (!rows.length) return showToast('No data to export', 'error');

            const headers = ['#', 'Signed Date', 'File #', 'Client', 'Status', 'DOL',
                'Referred By', 'Provider', 'Body Shop', 'Lead', 'Case Mgr', 'Remark'];
            const csv = [headers.join(',')];

            rows.forEach(r => {
                csv.push([
                    r.row_number, r.signed_date, r.file_number, `"${(r.client_name || '').replace(/"/g, '""')}"`,
                    r.status, r.date_of_loss, `"${(r.referred_by || '').replace(/"/g, '""')}"`,
                    `"${(r.referred_to_provider || '').replace(/"/g, '""')}"`,
                    `"${(r.referred_to_body_shop || '').replace(/"/g, '""')}"`,
                    `"${(r.lead_name || '').replace(/"/g, '""')}"`,
                    `"${(r.case_manager_name || '').replace(/"/g, '""')}"`,
                    `"${(r.remark || '').replace(/"/g, '""')}"`
                ].join(','));
            });

            const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'referrals_' + new Date().toISOString().slice(0, 10) + '.csv';
            a.click();
            URL.revokeObjectURL(url);
        },

        // -------------------------------------------------------
        //  Pagination
        // -------------------------------------------------------
        goToPage(page) {
            if (page < 1 || (this.pagination && page > this.pagination.total_pages)) return;
            this.loadReferrals(page);
        },

        paginationPages() {
            const p = this.pagination;
            if (!p) return [];
            const pages = [];
            for (let i = 1; i <= p.total_pages; i++) {
                if (i === 1 || i === p.total_pages || Math.abs(i - p.current_page) <= 2) {
                    pages.push(i);
                } else if (pages[pages.length - 1] !== '...') {
                    pages.push('...');
                }
            }
            return pages;
        },

        // -------------------------------------------------------
        //  Search (debounced)
        // -------------------------------------------------------
        _searchTimer: null,

        handleSearch() {
            clearTimeout(this._searchTimer);
            this._searchTimer = setTimeout(() => {
                this.loadReferrals();
            }, 300);
        }
    };
}
