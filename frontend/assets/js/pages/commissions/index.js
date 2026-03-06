/**
 * Employee Commissions Page Controller
 * Alpine.js component for managing employee commissions.
 *
 * Tabs: Active (in_progress + unpaid), History (paid + rejected), Attorney (Chong settled), Admin (approval)
 */
function commissionsPage() {
    return {
        // -------------------------------------------------------
        //  Tab state
        // -------------------------------------------------------
        activeTab: 'active',

        tabs: [
            { key: 'active',  label: 'Active',  count: 0 },
            { key: 'history', label: 'History',  count: 0 },
        ],

        // -------------------------------------------------------
        //  Data per tab
        // -------------------------------------------------------
        activeCases: [],
        historyCases: [],
        attorneyCases: [],
        adminCases: [],

        activePagination: null,
        historyPagination: null,

        // -------------------------------------------------------
        //  Loading / saving
        // -------------------------------------------------------
        loading: false,
        saving: false,

        // -------------------------------------------------------
        //  Role check
        // -------------------------------------------------------
        get isAdmin() {
            const u = Alpine.store('auth')?.user;
            return u && u.role === 'admin';
        },

        // -------------------------------------------------------
        //  Stats
        // -------------------------------------------------------
        stats: {
            total_cases: 0,
            in_progress_count: 0,
            unpaid_count: 0,
            paid_count: 0,
            rejected_count: 0,
            total_commission: 0,
            paid_commission: 0,
            unpaid_commission: 0,
            by_month: [],
            by_employee: []
        },

        // -------------------------------------------------------
        //  Filters
        // -------------------------------------------------------
        search: '',
        statusFilter: '',
        yearFilter: '',
        employeeFilter: '',
        historySearch: '',
        historyStatusFilter: '',
        historyYearFilter: '',
        historyEmployeeFilter: '',

        // Attorney tab filters
        attorneyStatusFilter: '',
        attorneyYearFilter: '',
        attorneyMonthFilter: '',

        // -------------------------------------------------------
        //  Sorting
        // -------------------------------------------------------
        sortColumn: '',
        sortAsc: true,

        // -------------------------------------------------------
        //  Client-side pagination
        // -------------------------------------------------------
        commPage: 1,
        commPerPage: 10000,
        historyPage: 1,
        historyPerPage: 10000,

        // -------------------------------------------------------
        //  Year options
        // -------------------------------------------------------
        get yearOptions() {
            const years = [];
            const cur = new Date().getFullYear();
            for (let y = cur; y >= cur - 5; y--) years.push(y);
            return years;
        },

        // -------------------------------------------------------
        //  Admin bulk selection
        // -------------------------------------------------------
        selectedIds: [],

        // -------------------------------------------------------
        //  Employees list (for admin)
        // -------------------------------------------------------
        employees: [],

        // -------------------------------------------------------
        //  Modal flags
        // -------------------------------------------------------
        showCreateModal: false,
        showEditModal: false,
        showAttorneyModal: false,

        // -------------------------------------------------------
        //  Forms
        // -------------------------------------------------------
        createForm: {
            case_number: '',
            client_name: '',
            case_type: 'Auto',
            employee_user_id: '',
            settled: 0,
            presuit_offer: 0,
            fee_rate: '33.33',
            month: '',
            is_marketing: false,
            check_received: false,
            note: ''
        },

        editForm: {
            id: null,
            case_number: '',
            client_name: '',
            settled: 0,
            presuit_offer: 0,
            fee_rate: '33.33',
            month: '',
            is_marketing: false,
            check_received: false,
            status: 'in_progress',
            note: '',
            _commissionRate: 10,
            _employeeName: '',
            _readOnly: false
        },

        attForm: {
            id: null,
            case_number: '',
            client_name: '',
            _phaseLabel: '',
            _phaseClass: '',
            _commRate: 5,
            _showPresuit: false,
            _hasUim: false,
            _formulaDesc: '',
            resolution_type: '',
            settled: 0,
            presuit_offer: 0,
            fee_rate: 33.33,
            legal_fee: 0,
            discounted_legal_fee: 0,
            commission: 0,
            uim_settled: 0,
            uim_legal_fee: 0,
            uim_discounted_legal_fee: 0,
            uim_commission: 0,
            month: '',
            check_received: false,
            status: 'unpaid',
            note: ''
        },

        // -------------------------------------------------------
        //  Computed: Active cases filtered + paginated
        // -------------------------------------------------------
        get filteredActiveCases() {
            return this.activeCases;
        },

        get commTotalPages() {
            return Math.ceil(this.filteredActiveCases.length / this.commPerPage) || 1;
        },

        get paginatedActiveCases() {
            const sorted = this._sortData(this.filteredActiveCases);
            const start = (this.commPage - 1) * this.commPerPage;
            return sorted.slice(start, start + this.commPerPage);
        },

        commPageNumbers() {
            return buildPageNumbers({ current_page: this.commPage, total_pages: this.commTotalPages });
        },

        // -------------------------------------------------------
        //  Computed: History cases filtered + paginated
        // -------------------------------------------------------
        get filteredHistoryCases() {
            return this.historyCases;
        },

        get historyTotalPages() {
            return Math.ceil(this.filteredHistoryCases.length / this.historyPerPage) || 1;
        },

        get paginatedHistoryCases() {
            const sorted = this._sortData(this.filteredHistoryCases);
            const start = (this.historyPage - 1) * this.historyPerPage;
            return sorted.slice(start, start + this.historyPerPage);
        },

        historyPageNumbers() {
            return buildPageNumbers({ current_page: this.historyPage, total_pages: this.historyTotalPages });
        },

        // -------------------------------------------------------
        //  Computed: Attorney cases filtered + paginated
        // -------------------------------------------------------
        get filteredAttorneyCases() {
            let cases = this.attorneyCases;
            if (this.attorneyStatusFilter) {
                cases = cases.filter(c => c.status === this.attorneyStatusFilter);
            }
            if (this.attorneyYearFilter) {
                cases = cases.filter(c => {
                    const m = c.month || '';
                    return m.includes(String(this.attorneyYearFilter));
                });
            }
            if (this.attorneyMonthFilter) {
                cases = cases.filter(c => c.month === this.attorneyMonthFilter);
            }
            return cases;
        },

        get paginatedAttorneyCases() {
            return this._sortData(this.filteredAttorneyCases);
        },

        get attorneyMonthOptions() {
            const months = new Set();
            this.attorneyCases.forEach(c => { if (c.month) months.add(c.month); });
            return [...months].sort().reverse();
        },

        // -------------------------------------------------------
        //  Computed-like helpers
        // -------------------------------------------------------
        get pagination() {
            return this[this.activeTab + 'Pagination'];
        },

        // -------------------------------------------------------
        //  Lifecycle
        // -------------------------------------------------------
        async init() {
            // Wait for auth store to be ready
            const auth = Alpine.store('auth');
            if (auth && auth.loading) {
                await new Promise(r => {
                    const iv = setInterval(() => { if (!auth.loading) { clearInterval(iv); r(); } }, 50);
                });
            }

            // Insert Attorney tab before Admin
            this.tabs.push({ key: 'attorney', label: 'My Cases', count: 0 });
            if (this.isAdmin) {
                this.tabs.push({ key: 'admin', label: 'Admin', count: 0 });
            }
            await this.loadEmployees();
            await Promise.all([this.loadStats(), this.loadAttorneyCases()]);
            await this.loadTab('active');
            if (this.isAdmin) {
                await this.loadAdminCases();
            }
            this._syncTabCounts();

            // Handle hash navigation (e.g. #attorney from Attorney Cases page)
            const hash = window.location.hash.replace('#', '');
            if (hash && this.tabs.find(t => t.key === hash)) {
                this.switchTab(hash);
            }
        },

        _syncTabCounts() {
            this.tabs = this.tabs.map(t => {
                if (t.key === 'active') return { ...t, count: (this.stats.in_progress_count || 0) + (this.stats.unpaid_count || 0) };
                if (t.key === 'history') return { ...t, count: (this.stats.paid_count || 0) + (this.stats.rejected_count || 0) };
                if (t.key === 'attorney') return { ...t, count: this.attorneyCases.length };
                if (t.key === 'admin') return { ...t, count: this.adminCases.length };
                return t;
            });
        },

        // -------------------------------------------------------
        //  Data Loading
        // -------------------------------------------------------
        async loadEmployees() {
            try {
                const all = await Alpine.store('staff').getList();
                // Filter to commission-eligible roles (staff, manager)
                this.employees = all.filter(u =>
                    u.role === 'paralegal' || u.role === 'billing' || u.role === 'manager'
                );
            } catch (e) { /* ignore */ }
        },

        async loadStats() {
            try {
                // Show personal stats (KPI cards show current user's own commissions)
                const myId = Alpine.store('auth')?.user?.id;
                let url = 'commissions/stats';
                if (myId) url += '?employee_id=' + myId;
                const res = await api.get(url);
                this.stats = res.data || this.stats;
            } catch (e) { /* ignore */ }
        },

        async switchTab(tab) {
            this.activeTab = tab;
            this.sortColumn = '';
            this.sortAsc = true;
            if (tab === 'admin') {
                await this.loadAdminCases();
            } else if (tab === 'attorney') {
                await this.loadAttorneyCases();
            } else {
                await this.loadTab(tab);
            }
        },

        async loadTab(tab, page = 1) {
            this.loading = true;
            if (tab === 'active') this.commPage = 1;
            if (tab === 'history') this.historyPage = 1;
            try {
                const params = { page, per_page: 500 };
                // Active & History tabs always show current user's own commissions
                const myId = Alpine.store('auth')?.user?.id;
                if (myId) params.employee_id = myId;

                if (tab === 'active') {
                    if (this.statusFilter) params.status = this.statusFilter;
                    if (this.search) params.search = this.search;
                    if (this.yearFilter) params.year = this.yearFilter;
                    if (this.employeeFilter) params.employee_id = this.employeeFilter;
                } else if (tab === 'history') {
                    if (this.historyStatusFilter) params.status = this.historyStatusFilter;
                    if (this.historySearch) params.search = this.historySearch;
                    if (this.historyYearFilter) params.year = this.historyYearFilter;
                    if (this.historyEmployeeFilter) params.employee_id = this.historyEmployeeFilter;
                }

                const res = await api.get('commissions' + buildQueryString(params));
                const cases = res.data || [];
                const pg = res.pagination || null;
                const pagination = pg ? { ...pg, current_page: pg.page } : null;

                if (tab === 'active') {
                    // Filter to active statuses only (in_progress, unpaid)
                    this.activeCases = this.statusFilter
                        ? cases
                        : cases.filter(c => c.status === 'in_progress' || c.status === 'unpaid');
                    this.activePagination = pagination;
                } else if (tab === 'history') {
                    // Filter to history statuses only (paid, rejected)
                    this.historyCases = this.historyStatusFilter
                        ? cases
                        : cases.filter(c => c.status === 'paid' || c.status === 'rejected');
                    this.historyPagination = pagination;
                }
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.loading = false;
        },

        async loadAdminCases() {
            try {
                const res = await api.get('commissions' + buildQueryString({
                    status: 'unpaid', per_page: 100
                }));
                this.adminCases = res.data || [];
                this.selectedIds = [];
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        // -------------------------------------------------------
        //  Attorney Cases (from attorney_cases table)
        // -------------------------------------------------------
        async loadAttorneyCases() {
            this.loading = true;
            try {
                // "My Cases" — only show cases where current user is the assigned attorney
                const myId = Alpine.store('auth')?.user?.id;
                let url = 'attorney?phase=settled&per_page=10000';
                if (myId) url += '&attorney_user_id=' + myId;
                const res = await api.get(url);
                this.attorneyCases = (res.data || []).filter(c =>
                    parseFloat(c.commission) > 0 || parseFloat(c.uim_commission) > 0
                );
                this._syncTabCounts();
            } catch (e) {
                console.error('Failed to load attorney cases', e);
            }
            this.loading = false;
        },

        async toggleAttorneyCheck(c) {
            try {
                await api.put('attorney/' + c.id, { check_received: c.check_received ? 0 : 1 });
                c.check_received = c.check_received ? 0 : 1;
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        attorneyPhaseClass(c) {
            if (c.uim_settled) return 'sp-phase-uim';
            if (c.litigation_settled_date) return 'sp-phase-litigation';
            return 'sp-phase-demand';
        },

        attorneyPhaseLabel(c) {
            if (c.uim_settled) return 'UIM';
            if (c.litigation_settled_date) return 'Litigation';
            return 'Demand';
        },

        openAttorneyModal(c) {
            const phase = this.attorneyPhaseLabel(c);
            const resType = c.resolution_type || '';
            const feeRate = parseFloat(c.fee_rate) || 33.33;

            // Determine commission rate & presuit visibility based on phase
            let commRate = 5;
            let showPresuit = false;
            let formulaDesc = 'Disc. Legal Fee × 5% = Commission';

            if (phase === 'Litigation') {
                commRate = 20;
                const group33 = ['No Offer Settle', 'File and Bump', 'Post Deposition Settle', 'Mediation', 'Settled Post Arbitration', 'Settlement Conference'];
                showPresuit = group33.includes(resType) || (!['Arbitration Award', 'Beasley'].includes(resType) && parseFloat(c.presuit_offer) > 0);
                if (showPresuit) {
                    formulaDesc = '(Settled - Pre-Suit) × ' + feeRate + '% = Disc. Legal Fee → × 20% = Commission';
                } else {
                    formulaDesc = 'Settled × ' + feeRate + '% = Disc. Legal Fee → × 20% = Commission';
                }
            } else if (phase === 'Demand') {
                formulaDesc = 'Settled ÷ 3 = Legal Fee = Disc. Legal Fee → × 5% = Commission';
            } else if (phase === 'UIM') {
                formulaDesc = 'Settled ÷ 3 = Legal Fee = Disc. Legal Fee → × 5% = Commission';
            }

            const hasUim = parseFloat(c.uim_settled) > 0 || parseFloat(c.uim_commission) > 0;

            this.attForm = {
                id: c.id,
                case_number: c.case_number,
                client_name: c.client_name,
                _phaseLabel: phase,
                _phaseClass: this.attorneyPhaseClass(c),
                _commRate: commRate,
                _showPresuit: showPresuit,
                _hasUim: hasUim,
                _formulaDesc: formulaDesc,
                resolution_type: resType,
                settled: parseFloat(c.settled) || 0,
                presuit_offer: parseFloat(c.presuit_offer) || 0,
                fee_rate: feeRate,
                legal_fee: parseFloat(c.legal_fee) || 0,
                discounted_legal_fee: parseFloat(c.discounted_legal_fee) || 0,
                commission: parseFloat(c.commission) || 0,
                uim_settled: parseFloat(c.uim_settled) || 0,
                uim_legal_fee: parseFloat(c.uim_legal_fee) || 0,
                uim_discounted_legal_fee: parseFloat(c.uim_discounted_legal_fee) || 0,
                uim_commission: parseFloat(c.uim_commission) || 0,
                month: c.month || '',
                check_received: c.check_received == 1,
                status: c.status || 'unpaid',
                note: c.note || ''
            };
            this.showAttorneyModal = true;
        },

        // -- Attorney commission calculation helpers --

        attCalcDifference() {
            return Math.max(0, (this.attForm.settled || 0) - (this.attForm.presuit_offer || 0));
        },

        attCalcLegalFee() {
            const rate = this.attForm.fee_rate || 33.33;
            if (this.attForm._showPresuit) {
                return this.attCalcDifference() * rate / 100;
            }
            return (this.attForm.settled || 0) * rate / 100;
        },

        recalcAttComm() {
            // When Settled changes, recalc Disc. Legal Fee and Commission
            this.attForm.discounted_legal_fee = Math.round(this.attCalcLegalFee() * 100) / 100;
            this.attForm.commission = Math.round(this.attForm.discounted_legal_fee * this.attForm._commRate) / 100;
        },

        recalcAttCommFromDLF() {
            // When Disc. Legal Fee is manually changed, only recalc Commission
            this.attForm.commission = Math.round(this.attForm.discounted_legal_fee * this.attForm._commRate) / 100;
        },

        attCalcUimLegalFee() {
            return (this.attForm.uim_settled || 0) / 3;
        },

        recalcUimComm() {
            // When UIM Settled changes, recalc UIM Disc. Legal Fee and UIM Commission
            this.attForm.uim_discounted_legal_fee = Math.round(this.attCalcUimLegalFee() * 100) / 100;
            this.attForm.uim_commission = Math.round(this.attForm.uim_discounted_legal_fee * 5) / 100;
        },

        recalcUimCommFromDLF() {
            // When UIM Disc. Legal Fee is manually changed, only recalc UIM Commission
            this.attForm.uim_commission = Math.round(this.attForm.uim_discounted_legal_fee * 5) / 100;
        },

        async saveAttorneyCase() {
            this.saving = true;
            try {
                await api.put('attorney/' + this.attForm.id, {
                    settled: this.attForm.settled,
                    discounted_legal_fee: this.attForm.discounted_legal_fee,
                    commission: this.attForm.commission,
                    uim_settled: this.attForm.uim_settled,
                    uim_discounted_legal_fee: this.attForm.uim_discounted_legal_fee,
                    uim_commission: this.attForm.uim_commission,
                    month: this.attForm.month,
                    check_received: this.attForm.check_received ? 1 : 0,
                    status: this.attForm.status,
                    note: this.attForm.note
                });
                showToast('Attorney commission updated');
                this.showAttorneyModal = false;
                await this.loadAttorneyCases();
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.saving = false;
        },

        // -------------------------------------------------------
        //  Sorting
        // -------------------------------------------------------
        sortBy(col) {
            if (this.sortColumn === col) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortColumn = col;
                this.sortAsc = true;
            }
        },

        _sortVal(item, col) {
            if (col === '_total_comm') return (parseFloat(item.commission) || 0) + (parseFloat(item.uim_commission) || 0);
            if (col === '_phase') {
                if (item.uim_settled) return 'UIM';
                if (item.litigation_settled_date) return 'Litigation';
                return 'Demand';
            }
            return item[col];
        },

        _sortData(arr) {
            if (!this.sortColumn) return arr;
            const col = this.sortColumn;
            const dir = this.sortAsc ? 1 : -1;
            return [...arr].sort((a, b) => {
                let va = this._sortVal(a, col);
                let vb = this._sortVal(b, col);
                const na = parseFloat(va), nb = parseFloat(vb);
                if (!isNaN(na) && !isNaN(nb)) return (na - nb) * dir;
                va = String(va || '').toLowerCase();
                vb = String(vb || '').toLowerCase();
                return va.localeCompare(vb) * dir;
            });
        },

        sortIcon(col) {
            if (this.sortColumn !== col) return ' ↕';
            return this.sortAsc ? ' ▲' : ' ▼';
        },

        // -------------------------------------------------------
        //  CRUD Operations
        // -------------------------------------------------------
        openCreateModal() {
            this.createForm = {
                case_number: '', client_name: '', case_type: 'Auto',
                employee_user_id: '', settled: 0, presuit_offer: 0,
                fee_rate: '33.33', month: '', is_marketing: false,
                check_received: false, note: ''
            };
            this.showCreateModal = true;
        },

        async createCommission() {
            if (!this.createForm.case_number || !this.createForm.client_name) {
                return showToast('Case number and client name are required', 'error');
            }
            this.saving = true;
            try {
                await api.post('commissions', this.createForm);
                showToast('Commission added');
                this.showCreateModal = false;
                await this.loadStats();
                await this.loadTab(this.activeTab);
                this._syncTabCounts();
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.saving = false;
        },

        openEditModal(c, readOnly = false) {
            this.editForm = {
                id: c.id,
                case_number: c.case_number,
                client_name: c.client_name,
                settled: parseFloat(c.settled) || 0,
                presuit_offer: parseFloat(c.presuit_offer) || 0,
                fee_rate: String(c.fee_rate || '33.33'),
                month: c.month || '',
                is_marketing: c.is_marketing == 1,
                check_received: c.check_received == 1,
                status: c.status,
                note: c.note || '',
                _commissionRate: parseFloat(c.commission_rate) || 10,
                _employeeName: c.employee_name || '',
                _readOnly: readOnly
            };
            this.showEditModal = true;
        },

        async updateCommission() {
            this.saving = true;
            try {
                await api.put('commissions/' + this.editForm.id, this.editForm);
                showToast('Commission updated');
                this.showEditModal = false;
                await this.loadStats();
                await this.loadTab(this.activeTab);
                if (this.isAdmin) await this.loadAdminCases();
                this._syncTabCounts();
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.saving = false;
        },

        async deleteCase(id) {
            if (!confirm('Delete this commission?')) return;
            try {
                await api.delete('commissions/' + id);
                showToast('Commission deleted');
                await this.loadStats();
                await this.loadTab(this.activeTab);
                this._syncTabCounts();
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        // -------------------------------------------------------
        //  Check Toggle
        // -------------------------------------------------------
        async toggleCheck(id) {
            try {
                await api.post('commissions/toggle-check', { case_id: id });
                // Update in-memory
                [this.activeCases, this.historyCases, this.adminCases].forEach(arr => {
                    const c = arr.find(x => x.id === id);
                    if (c) c.check_received = c.check_received == 1 ? 0 : 1;
                });
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        // -------------------------------------------------------
        //  Admin: Approve / Reject
        // -------------------------------------------------------
        async approveCase(id, action) {
            try {
                await api.post('commissions/approve', { case_id: id, action });
                showToast(`Commission ${action}d`);
                await this.loadStats();
                await this.loadAdminCases();
                await this.loadTab('active');
                this._syncTabCounts();
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async bulkApprove(action) {
            if (this.selectedIds.length === 0) return;
            if (!confirm(`${action === 'approve' ? 'Approve' : 'Reject'} ${this.selectedIds.length} commission(s)?`)) return;
            try {
                const res = await api.post('commissions/bulk-approve', {
                    case_ids: this.selectedIds.map(Number),
                    action
                });
                const data = res.data || {};
                showToast(`${data.updated || 0} updated, ${data.skipped || 0} skipped`);
                if (data.warning) showToast(data.warning, 'warning');
                await this.loadStats();
                await this.loadAdminCases();
                await this.loadTab('active');
                this._syncTabCounts();
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedIds = this.adminCases.map(c => String(c.id));
            } else {
                this.selectedIds = [];
            }
        },

        // -------------------------------------------------------
        //  Edit Form Calculations (preview)
        // -------------------------------------------------------
        calcEditDifference() {
            const diff = Math.max(0, (this.editForm.settled || 0) - (this.editForm.presuit_offer || 0));
            return diff.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        calcEditLegalFee() {
            const rate = parseFloat(this.editForm.fee_rate) || 33.33;
            const lf = ((this.editForm.settled || 0) * rate) / 100;
            return lf.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        calcEditDiscLF() {
            const diff = Math.max(0, (this.editForm.settled || 0) - (this.editForm.presuit_offer || 0));
            const rate = parseFloat(this.editForm.fee_rate) || 33.33;
            const dlf = (diff * rate) / 100;
            return dlf.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        calcEditCommission() {
            const diff = Math.max(0, (this.editForm.settled || 0) - (this.editForm.presuit_offer || 0));
            const feeRate = parseFloat(this.editForm.fee_rate) || 33.33;
            const dlf = (diff * feeRate) / 100;
            const commRate = this.editForm.is_marketing ? 5 : (this.editForm._commissionRate || 10);
            const comm = (dlf * commRate) / 100;
            return comm.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        // -------------------------------------------------------
        //  Export
        // -------------------------------------------------------
        exportCsv() {
            const params = {};
            if (this.statusFilter) params.status = this.statusFilter;
            if (this.yearFilter) params.year = this.yearFilter;
            if (this.employeeFilter) params.employee_id = this.employeeFilter;

            const url = '/blcm/backend/api/commissions/export' + buildQueryString(params);
            window.open(url, '_blank');
        },

        // -------------------------------------------------------
        //  Pagination
        // -------------------------------------------------------
        goToPage(page) {
            if (page < 1 || (this.pagination && page > this.pagination.total_pages)) return;
            this.loadTab(this.activeTab, page);
        },

        paginationPages() {
            return buildPageNumbers(this.pagination);
        },

        // -------------------------------------------------------
        //  Search (debounced)
        // -------------------------------------------------------
        _searchTimer: null,

        handleSearch() {
            clearTimeout(this._searchTimer);
            this._searchTimer = setTimeout(() => {
                this.commPage = 1;
                this.loadTab('active');
            }, 300);
        },

        handleHistorySearch() {
            clearTimeout(this._searchTimer);
            this._searchTimer = setTimeout(() => {
                this.historyPage = 1;
                this.loadTab('history');
            }, 300);
        }
    };
}
