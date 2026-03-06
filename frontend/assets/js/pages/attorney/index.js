/**
 * Attorney Cases Page Controller
 * Alpine.js component powering the attorney cases page.
 *
 * Tabs: Demand, Litigation, UIM, Settled
 * Modals: Create, Edit, Settle Demand, To Litigation, Settle Litigation,
 *         Settle UIM, Top Offer
 */
function attorneyCasesPage() {
    return {
        ...pendingAssignmentsMixin(),
        ...attorneyModalsMixin(),
        // -------------------------------------------------------
        //  Tab state
        // -------------------------------------------------------
        activeTab: 'demand',

        tabs: [
            { key: 'demand',     label: 'Demand',     count: 0 },
            { key: 'uim',       label: 'UIM',          count: 0 },
            { key: 'litigation', label: 'Litigation',  count: 0 },
            { key: 'settled',   label: 'Settled',      count: 0 }
        ],

        // -------------------------------------------------------
        //  Data per tab
        // -------------------------------------------------------
        demandCases: [],
        litigationCases: [],
        uimCases: [],
        settledCases: [],

        // -------------------------------------------------------
        //  Loading / saving
        // -------------------------------------------------------
        loading: false,
        saving: false,

        // -------------------------------------------------------
        //  Stats
        // -------------------------------------------------------
        stats: {
            total_active: 0,
            demand_count: 0,
            litigation_count: 0,
            uim_count: 0,
            settled_count: 0,
            overdue_count: 0,
            month_commission: 0,
            ytd_commission: 0
        },

        // -------------------------------------------------------
        //  Pagination per tab
        // -------------------------------------------------------
        demandPagination: null,
        litigationPagination: null,
        uimPagination: null,
        settledPagination: null,

        // -------------------------------------------------------
        //  Filters
        // -------------------------------------------------------
        search: '',
        monthFilter: '',
        yearFilter: '',
        stageFilter: '',
        staffFilter: '',
        staffList: [],
        fromCaseDetail: false,
        fromCaseDetailUrl: '',

        // -------------------------------------------------------
        //  Demand sub-filters & client-side pagination
        // -------------------------------------------------------
        demandSubFilter: 'all',
        demandSubFilters: [
            { key: 'all',           label: 'All' },
            { key: 'demand_out',    label: 'Demand Out' },
            { key: 'negotiating',   label: 'Negotiating' },
            { key: 'top_offer_set', label: 'Top Offer Set' },
            { key: 'settled',       label: 'Settled' }
        ],
        demandPage: 1,
        demandPerPage: 10000,
        demandSortAsc: true,

        // -------------------------------------------------------
        //  UIM sub-filters & client-side pagination
        // -------------------------------------------------------
        uimSubFilter: 'all',
        uimSubFilters: [
            { key: 'all',           label: 'All' },
            { key: 'uim_demand',    label: 'UIM Demand' },
            { key: 'uim_negotiate', label: 'UIM Negotiate' },
            { key: 'uim_settled',   label: 'UIM Settled' }
        ],
        uimPage: 1,
        uimPerPage: 10000,
        uimSortAsc: true,

        // -------------------------------------------------------
        //  Litigation client-side pagination & filter
        // -------------------------------------------------------
        litPage: 1,
        litPerPage: 10000,
        litSortAsc: true,
        litMonthFilter: '',

        // -------------------------------------------------------
        //  Settled client-side pagination & filters
        // -------------------------------------------------------
        settledPage: 1,
        settledPerPage: 10000,
        settledSortAsc: false,
        settledMonthFilter: '',
        settledYearFilter: '',

        // -------------------------------------------------------
        //  Filter options
        // -------------------------------------------------------
        get monthOptions() {
            const months = [];
            const now = new Date();
            for (let i = 0; i < 12; i++) {
                const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
                const label = d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                // Format like "Feb. 2026"
                months.push(label.replace(' ', '. '));
            }
            return months;
        },

        get yearOptions() {
            const years = [];
            const cur = new Date().getFullYear();
            for (let y = cur; y >= cur - 5; y--) years.push(y);
            return years;
        },

        // -------------------------------------------------------
        //  Users (for dropdowns)
        // -------------------------------------------------------
        users: [],

        get isAdmin() {
            return Alpine.store('auth')?.user?.role === 'admin';
        },

        // -------------------------------------------------------
        //  Modal visibility flags
        // -------------------------------------------------------
        showCreateModal: false,
        showSettleDemandModal: false,
        showToLitModal: false,
        showSettleLitModal: false,
        showSettleUimModal: false,
        showTopOfferModal: false,
        showToUimModal: false,
        showEditModal: false,
        showSendAcctModal: false,
        showSendBillingModal: false,
        showTransferModal: false,

        // -------------------------------------------------------
        //  Transfer data
        // -------------------------------------------------------
        transferForm: {
            case_id: null,
            to_attorney_id: '',
            note: '',
            _caseNumber: '',
            _clientName: '',
            _currentAttorney: '',
            _currentAttorneyId: null
        },
        transferAttorneyList: [],
        transferHistory: [],

        // -------------------------------------------------------
        //  Form data objects
        // -------------------------------------------------------
        createForm: {
            case_number: '',
            client_name: '',
            case_type: 'Auto',
            assigned_date: new Date().toISOString().split('T')[0],
            phase: 'demand',
            attorney_user_id: '',
            month: '',
            note: ''
        },

        settleForm: {
            case_id: null,
            settled: '',
            discounted_legal_fee: '',
            month: '',
            check_received: false,
            is_policy_limit: false
        },

        toLitForm: {
            case_id: null,
            litigation_start_date: new Date().toISOString().split('T')[0],
            presuit_offer: 0,
            note: ''
        },

        toUimForm: {
            case_id: null,
            note: ''
        },

        settleLitForm: {
            case_id: null,
            resolution_type: '',
            settled: '',
            discounted_legal_fee: '',
            presuit_offer: 0,
            month: '',
            check_received: false,
            is_policy_limit: false,
            manual_fee_rate: '',
            manual_commission_rate: '',
            fee_rate_override: false,
            note: ''
        },

        settleUimForm: {
            case_id: null,
            settled: '',
            discounted_legal_fee: '',
            month: '',
            check_received: false
        },

        topOfferForm: {
            case_id: null,
            top_offer_amount: '',
            assignee_id: '',
            note: ''
        },

        editForm: {
            id: null,
            case_number: '',
            client_name: '',
            case_type: '',
            assigned_date: '',
            month: '',
            note: '',
            stage: '',
            check_received: false,
            is_marketing: false,
            demand_out_date: '',
            negotiate_date: '',
            demand_deadline: '',
            top_offer_date: ''
        },

        sendAcctForm: {
            case_id: null,
            linked_case_id: null,
            assigned_to: '6',
            note: '',
            _caseNumber: '',
            _clientName: '',
            _matchedCase: null,
            _searching: false,
            _searchQuery: '',
            _searchResults: [],
            _noResults: false
        },

        accountingStaff: [],

        sendBillingForm: {
            case_id: null,
            assigned_to: '',
            note: '',
            _caseNumber: '',
            _clientName: ''
        },

        billingStaff: [],

        get currentUserHasCommission() {
            const user = Alpine.store('auth')?.user;
            return user && parseFloat(user.commission_rate || 0) > 0;
        },

        // -------------------------------------------------------
        //  Currently selected case (modal context)
        // -------------------------------------------------------
        settlingCase: null,
        editingCase: null,

        get hasCommission() {
            const uid = this.settlingCase?.attorney_user_id;
            if (!uid) return false;
            const user = this.users.find(u => u.id == uid);
            return user && parseFloat(user.commission_rate) > 0;
        },

        // -------------------------------------------------------
        //  Demand computed: sub-filter, stats, pagination
        // -------------------------------------------------------

        /** Demand cases filtered by sub-filter tab */
        get filteredDemandCases() {
            let cases = this.demandCases;
            switch (this.demandSubFilter) {
                case 'demand_out':
                    cases = cases.filter(c => c.demand_out_date && !c.negotiate_date);
                    break;
                case 'negotiating':
                    cases = cases.filter(c => c.negotiate_date && !c.demand_settled_date);
                    break;
                case 'top_offer_set':
                    cases = cases.filter(c => c.top_offer_date && !c.demand_settled_date);
                    break;
                case 'settled':
                    cases = cases.filter(c => c.demand_settled_date);
                    break;
            }
            // Sort by deadline (ascending = most urgent first)
            cases = [...cases].sort((a, b) => {
                const da = a.deadline_days_remaining ?? 9999;
                const db = b.deadline_days_remaining ?? 9999;
                return this.demandSortAsc ? da - db : db - da;
            });
            return cases;
        },

        /** Demand stats computed from loaded data */
        get demandStats() {
            const all = this.demandCases;
            return {
                overdue:      all.filter(c => c.deadline_days_remaining != null && c.deadline_days_remaining < 0).length,
                negotiating:  all.filter(c => c.negotiate_date && !c.demand_settled_date).length,
                settled:      all.filter(c => c.demand_settled_date).length
            };
        },

        /** Total pages for demand client-side pagination */
        get demandTotalPages() {
            return Math.ceil(this.filteredDemandCases.length / this.demandPerPage) || 1;
        },

        /** Current page slice of demand cases */
        get paginatedDemandCases() {
            const start = (this.demandPage - 1) * this.demandPerPage;
            return this.filteredDemandCases.slice(start, start + this.demandPerPage);
        },

        /** Page number array for demand pagination */
        demandPageNumbers() {
            return buildPageNumbers({ current_page: this.demandPage, total_pages: this.demandTotalPages });
        },

        /** Toggle demand sort direction */
        toggleDemandSort() {
            this.demandSortAsc = !this.demandSortAsc;
        },

        /** Export demand cases as CSV */
        exportDemand() {
            const cases = this.filteredDemandCases;
            if (cases.length === 0) { showToast('No cases to export', 'error'); return; }
            const headers = ['Case #', 'Client', 'Type', 'Assigned', 'Deadline', 'Days Left', 'Stage'];
            const rows = cases.map(c => [
                c.case_number,
                c.client_name,
                c.case_type || 'Auto',
                c.assigned_date || '',
                c.demand_deadline || '',
                c.deadline_days_remaining ?? '',
                (c.stage || '').replace(/_/g, ' ')
            ]);
            const csv = [headers, ...rows].map(r => r.map(v => '"' + String(v).replace(/"/g, '""') + '"').join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = 'demand_cases.csv'; a.click();
            URL.revokeObjectURL(url);
        },

        // -------------------------------------------------------
        //  UIM computed: filtering, pagination, helpers
        // -------------------------------------------------------

        /** UIM cases filtered by sub-filter + sorted */
        get filteredUimCases() {
            let cases = [...this.uimCases];
            switch (this.uimSubFilter) {
                case 'uim_demand':
                    cases = cases.filter(c => c.uim_demand_out_date && !c.uim_negotiate_date);
                    break;
                case 'uim_negotiate':
                    cases = cases.filter(c => c.uim_negotiate_date && !c.uim_settled_date);
                    break;
                case 'uim_settled':
                    cases = cases.filter(c => c.uim_settled_date);
                    break;
            }
            cases.sort((a, b) => {
                const da = a.deadline_days_remaining ?? 9999;
                const db = b.deadline_days_remaining ?? 9999;
                return this.uimSortAsc ? da - db : db - da;
            });
            return cases;
        },

        get uimTotalPages() {
            return Math.ceil(this.filteredUimCases.length / this.uimPerPage) || 1;
        },

        get paginatedUimCases() {
            const start = (this.uimPage - 1) * this.uimPerPage;
            return this.filteredUimCases.slice(start, start + this.uimPerPage);
        },

        uimPageNumbers() {
            return buildPageNumbers({ current_page: this.uimPage, total_pages: this.uimTotalPages });
        },

        toggleUimSort() {
            this.uimSortAsc = !this.uimSortAsc;
        },

        exportUim() {
            const cases = this.filteredUimCases;
            if (cases.length === 0) { showToast('No cases to export', 'error'); return; }
            const headers = ['Case #', 'Client', 'BI Settled', 'UIM Start Date', 'Deadline', 'Duration'];
            const rows = cases.map(c => [
                c.case_number,
                c.client_name,
                c.settled || '',
                c.uim_start_date || '',
                c.demand_deadline || '',
                this.uimDuration(c)
            ]);
            const csv = [headers, ...rows].map(r => r.map(v => '"' + String(v).replace(/"/g, '""') + '"').join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = 'uim_cases.csv'; a.click();
            URL.revokeObjectURL(url);
        },

        // -------------------------------------------------------
        //  Litigation computed: filtering, pagination, helpers
        // -------------------------------------------------------

        /** Litigation cases filtered by month + sorted */
        get filteredLitCases() {
            let cases = [...this.litigationCases];
            // Month filter (client-side)
            if (this.litMonthFilter) {
                cases = cases.filter(c => {
                    const m = c.month || '';
                    return m === this.litMonthFilter;
                });
            }
            // Sort by litigation_start_date
            cases.sort((a, b) => {
                const da = a.litigation_start_date || '9999';
                const db = b.litigation_start_date || '9999';
                return this.litSortAsc ? da.localeCompare(db) : db.localeCompare(da);
            });
            return cases;
        },

        /** Litigation stats computed from loaded data */
        get litStats() {
            const all = this.litigationCases;
            return {
                litigation: all.filter(c => !c.litigation_status || c.litigation_status === 'litigation').length,
                uim:        all.filter(c => c.litigation_status === 'uim').length,
                settled:    all.filter(c => c.litigation_settled_date).length
            };
        },

        /** Total pages for litigation client-side pagination */
        get litTotalPages() {
            return Math.ceil(this.filteredLitCases.length / this.litPerPage) || 1;
        },

        /** Current page slice of litigation cases */
        get paginatedLitCases() {
            const start = (this.litPage - 1) * this.litPerPage;
            return this.filteredLitCases.slice(start, start + this.litPerPage);
        },

        /** Page number array for litigation pagination */
        litPageNumbers() {
            return buildPageNumbers({ current_page: this.litPage, total_pages: this.litTotalPages });
        },

        /** Toggle litigation sort direction */
        toggleLitSort() {
            this.litSortAsc = !this.litSortAsc;
        },

        /** Export litigation cases as CSV */
        exportLit() {
            const cases = this.filteredLitCases;
            if (cases.length === 0) { showToast('No cases to export', 'error'); return; }
            const headers = ['Case #', 'Client', 'Type', 'Lit. Start', 'Duration', 'Pre-Suit Offer', 'Stage'];
            const rows = cases.map(c => [
                c.case_number,
                c.client_name,
                c.case_type || 'Auto',
                c.litigation_start_date || '',
                this.litDuration(c),
                c.presuit_offer || '',
                this.litStageLabel(c.litigation_status)
            ]);
            const csv = [headers, ...rows].map(r => r.map(v => '"' + String(v).replace(/"/g, '""') + '"').join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = 'litigation_cases.csv'; a.click();
            URL.revokeObjectURL(url);
        },

        /** Litigation stage badge class */
        litStageClass(status) {
            const map = {
                litigation:  'sp-stage-litigation',
                trial_set:   'sp-stage-trial-set',
                mediation:   'sp-stage-mediation',
                settled:     'sp-stage-settled'
            };
            return map[status] || 'sp-stage-litigation';
        },

        /** Litigation stage display label */
        litStageLabel(status) {
            const map = {
                litigation:  'Litigation',
                trial_set:   'Trial Set',
                mediation:   'Mediation',
                settled:     'Settled'
            };
            return map[status] || 'Litigation';
        },

        // -------------------------------------------------------
        //  Settled computed: filtering, pagination, helpers
        // -------------------------------------------------------

        /** Settled cases filtered by month + year + sorted */
        get filteredSettledCases() {
            let cases = [...this.settledCases];
            if (this.settledMonthFilter) {
                cases = cases.filter(c => (c.month || '') === this.settledMonthFilter);
            }
            if (this.settledYearFilter) {
                cases = cases.filter(c => {
                    const m = c.month || '';
                    return m.includes(this.settledYearFilter);
                });
            }
            // Sort by settled amount (desc = highest first by default)
            cases.sort((a, b) => {
                const sa = parseFloat(a.settled) || 0;
                const sb = parseFloat(b.settled) || 0;
                return this.settledSortAsc ? sa - sb : sb - sa;
            });
            return cases;
        },

        /** Total pages for settled client-side pagination */
        get settledTotalPages() {
            return Math.ceil(this.filteredSettledCases.length / this.settledPerPage) || 1;
        },

        /** Current page slice of settled cases */
        get paginatedSettledCases() {
            const start = (this.settledPage - 1) * this.settledPerPage;
            return this.filteredSettledCases.slice(start, start + this.settledPerPage);
        },

        /** Page number array for settled pagination */
        settledPageNumbers() {
            return buildPageNumbers({ current_page: this.settledPage, total_pages: this.settledTotalPages });
        },

        /** Toggle settled sort direction */
        toggleSettledSort() {
            this.settledSortAsc = !this.settledSortAsc;
        },

        /** Export settled cases as CSV */
        exportSettled() {
            const cases = this.filteredSettledCases;
            if (cases.length === 0) { showToast('No cases to export', 'error'); return; }
            const headers = ['Case #', 'Client', 'Phase', 'Settled', 'Legal Fee', 'Commission', 'UIM Settled', 'UIM Comm.', 'Total Comm.', 'Month', 'Check', 'Status', 'Duration'];
            const rows = cases.map(c => [
                c.case_number,
                c.client_name,
                this.settledPhaseLabel(c),
                c.settled || '',
                c.discounted_legal_fee || '',
                c.commission || '',
                c.uim_settled || '',
                c.uim_commission || '',
                (parseFloat(c.commission) || 0) + (parseFloat(c.uim_commission) || 0),
                c.month || '',
                c.check_received == 1 ? 'Yes' : 'No',
                c.status || 'unpaid',
                this.settledDuration(c)
            ]);
            const csv = [headers, ...rows].map(r => r.map(v => '"' + String(v).replace(/"/g, '""') + '"').join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = 'settled_cases.csv'; a.click();
            URL.revokeObjectURL(url);
        },

        /** Settled phase badge class */
        settledPhaseClass(c) {
            if (c.uim_settled) return 'sp-phase-uim';
            if (c.litigation_settled_date) return 'sp-phase-litigation';
            return 'sp-phase-demand';
        },

        /** UIM step date text class */
        uimStepDateClass(c, field) {
            if (!c[field]) return '';
            const steps = ['uim_demand_out_date', 'uim_negotiate_date', 'uim_settled_date'];
            const completed = steps.filter(s => c[s]);
            const lastDone = completed[completed.length - 1];
            if (field === 'uim_settled_date') return 'settled';
            if (field === lastDone && !c.uim_settled_date) return 'act';
            return 'done';
        },

        /** UIM dot class */
        uimDotClass(c, field) {
            if (!c[field]) return 'dot-emp';
            if (field === 'uim_settled_date') return 'dot-settled';
            const steps = ['uim_demand_out_date', 'uim_negotiate_date'];
            const completed = steps.filter(s => c[s]);
            const lastDone = completed[completed.length - 1];
            if (field === lastDone && !c.uim_settled_date) return 'dot-act';
            return 'dot-done';
        },

        // -------------------------------------------------------
        //  SP format helpers (Settlement Pipeline design)
        // -------------------------------------------------------

        /** Format date → MM/DD/YYYY */
        spFormatDate(dateStr) {
            if (!dateStr) return '—';
            return formatDate(dateStr) || '—';
        },

        /** Format date short → MM/DD */
        spShort(dateStr) {
            if (!dateStr) return '—';
            const d = new Date(dateStr + 'T00:00:00');
            return String(d.getMonth() + 1).padStart(2, '0') + '/' + String(d.getDate()).padStart(2, '0');
        },

        /** Days-left badge class */
        spDaysBadgeClass(days) {
            if (days === null || days === undefined) return '';
            if (days < 0)   return 'sp-days-over';
            if (days <= 30) return 'sp-days-warn';
            return 'sp-days-ok';
        },

        /** Step date text class for progress column */
        spStepDateClass(c, field) {
            if (!c[field]) return '';
            // Is it the latest completed step? → gold (active)
            const steps = ['demand_out_date', 'negotiate_date', 'top_offer_date', 'demand_settled_date'];
            const completed = steps.filter(s => c[s]);
            const lastDone = completed[completed.length - 1];
            if (field === 'demand_settled_date' && c[field]) return 'settled';
            if (field === lastDone && !c.demand_settled_date) return 'act';
            return 'done';
        },

        /** Dot class for progress column */
        spDotClass(c, field) {
            if (!c[field]) return 'dot-emp';
            if (field === 'demand_settled_date') return 'dot-settled';
            const steps = ['demand_out_date', 'negotiate_date', 'top_offer_date'];
            const completed = steps.filter(s => c[s]);
            const lastDone = completed[completed.length - 1];
            if (field === lastDone && !c.demand_settled_date) return 'dot-act';
            return 'dot-done';
        },

        /** Stage badge class */
        spStageClass(stage) {
            const map = {
                negotiate:     'sp-stage-negotiate',
                demand_review: 'sp-stage-demand-review',
                demand_write:  'sp-stage-demand-write',
                demand_sent:   'sp-stage-demand-sent',
                settled:       'sp-stage-settled'
            };
            return map[stage] || 'sp-stage-demand-review';
        },

        /** Stage display label */
        spStageLabel(stage) {
            const map = {
                demand_review: 'demand review',
                demand_write:  'demand write',
                demand_sent:   'demand sent',
                negotiate:     'negotiate',
                settled:       'settled'
            };
            return map[stage] || (stage || '').replace(/_/g, ' ');
        },

        // -------------------------------------------------------
        //  Computed-like helpers
        // -------------------------------------------------------

        /** Returns the pagination object for the active tab. */
        get pagination() {
            return this[this.activeTab + 'Pagination'];
        },

        // -------------------------------------------------------
        //  Lifecycle
        // -------------------------------------------------------

        async init() {
            // Auto-filter to own user if they are in the staff list (attorney role)
            const user = Alpine.store('auth')?.user;
            if (user && user.role === 'attorney') {
                this.staffFilter = user.id.toString();
            }

            // Check for incoming search from Case Tracker
            const urlParams = new URLSearchParams(window.location.search);
            const urlSearch = urlParams.get('search');
            if (urlSearch) {
                this.search = urlSearch;
                this.staffFilter = ''; // clear staff filter to search across all
            }
            if (urlParams.get('from') === 'case-detail' && urlParams.get('case_id')) {
                this.fromCaseDetail = true;
                this.fromCaseDetailUrl = '/blcm/frontend/pages/bl-cases/detail.php?id=' + urlParams.get('case_id');
            }

            this.loadPendingCaseAssignments();
            await this.loadUsers();
            await this.loadStaff();
            await this.loadStats();
            if (this.search.trim()) {
                // Search across all tabs when coming from URL
                await Promise.all([
                    this.loadTab('demand'),
                    this.loadTab('uim'),
                    this.loadTab('litigation'),
                    this.loadTab('settled')
                ]);
                // Auto-switch to first tab with results
                const initData = { demand: this.demandCases, uim: this.uimCases, litigation: this.litigationCases, settled: this.settledCases };
                if (initData[this.activeTab].length === 0) {
                    const tabWithData = ['demand', 'uim', 'litigation', 'settled'].find(t => initData[t].length > 0);
                    if (tabWithData) this.activeTab = tabWithData;
                }
            } else {
                await this.loadTab('demand');
            }
            this._syncTabCounts();
        },

        // Bridge for _staff-tabs.php which calls loadData(1)
        async loadData() {
            await this.loadStats();
            await this.loadTab(this.activeTab);
            this._syncTabCounts();
        },

        async loadStaff() {
            try {
                const all = await Alpine.store('staff').getList();
                const allowed = ['zaskia', 'karl', 'chong'];
                this.staffList = all.filter(u => {
                    const name = ((u.display_name || u.full_name) || '').toLowerCase();
                    return allowed.some(a => name.includes(a));
                });
            } catch(e) { this.staffList = []; }
        },

        /** Push stats numbers into the tabs array so badges update. */
        _syncTabCounts() {
            if (this.search.trim()) {
                // When searching, show actual loaded data counts
                const map = {
                    demand: this.demandCases.length,
                    litigation: this.litigationCases.length,
                    uim: this.uimCases.length,
                    settled: this.settledCases.length
                };
                this.tabs = this.tabs.map(t => ({ ...t, count: map[t.key] ?? 0 }));
            } else {
                const map = {
                    demand: this.stats.demand_count,
                    litigation: this.stats.litigation_count,
                    uim: this.stats.uim_count,
                    settled: this.stats.settled_count
                };
                this.tabs = this.tabs.map(t => ({ ...t, count: map[t.key] ?? 0 }));
            }
        },

        // -------------------------------------------------------
        //  Data loading
        // -------------------------------------------------------

        async loadUsers() {
            try {
                const res = await api.get('users');
                this.users = (res.data || []).map(u => ({
                    ...u,
                    name: u.display_name || u.full_name || (u.first_name + ' ' + u.last_name)
                }));
            } catch (e) {
                // non-critical - dropdowns will just be empty
            }
        },

        async loadStats() {
            try {
                const params = this.staffFilter ? `?attorney_user_id=${this.staffFilter}` : '';
                const res = await api.get('attorney/stats' + params);
                this.stats = res.data || this.stats;
                this._syncTabCounts();
            } catch (e) {
                // stats are non-critical
            }
        },

        async switchTab(tab) {
            this.activeTab = tab;
            if (!this.search.trim()) {
                // Only clear filters when not searching
                this.monthFilter = '';
                this.yearFilter = '';
                this.stageFilter = '';
            }
            if (tab === 'demand') { this.demandSubFilter = 'all'; this.demandPage = 1; }
            if (tab === 'uim') { this.uimSubFilter = 'all'; this.uimPage = 1; }
            if (tab === 'litigation') { this.litPage = 1; this.litMonthFilter = ''; }
            if (tab === 'settled') { this.settledPage = 1; this.settledMonthFilter = ''; this.settledYearFilter = ''; }
            // If searching, data is already loaded for all tabs — no need to reload
            if (!this.search.trim()) {
                await this.loadTab(tab);
            }
        },

        async loadTab(tab, page = 1) {
            this.loading = true;
            if (tab === 'demand') this.demandPage = 1;
            if (tab === 'uim') this.uimPage = 1;
            if (tab === 'litigation') this.litPage = 1;
            if (tab === 'settled') this.settledPage = 1;
            try {
                // All tabs: load all for client-side filtering/pagination
                const clientSide = true;
                const params = {
                    phase: tab === 'settled' ? 'settled' : tab,
                    search: this.search,
                    page: page,
                    per_page: clientSide ? 500 : 25
                };
                if (tab === 'settled') {
                    // Settled includes all phases that have settled
                    params.phase = 'settled';
                }
                if (this.monthFilter) params.month = this.monthFilter;
                if (this.yearFilter)  params.year = this.yearFilter;
                if (this.stageFilter) params.status = this.stageFilter;
                if (this.staffFilter) params.attorney_user_id = this.staffFilter;

                const res = await api.get('attorney' + buildQueryString(params));
                const cases = res.data || [];
                const pg = res.pagination || null;
                const pagination = pg ? { ...pg, current_page: pg.page } : null;

                switch (tab) {
                    case 'demand':
                        this.demandCases = cases;
                        this.demandPagination = pagination;
                        break;
                    case 'litigation':
                        this.litigationCases = cases;
                        this.litigationPagination = pagination;
                        break;
                    case 'uim':
                        this.uimCases = cases;
                        this.uimPagination = pagination;
                        break;
                    case 'settled':
                        this.settledCases = cases;
                        this.settledPagination = pagination;
                        break;
                }
            } catch (e) {
                showToast(e.data?.message || e.message || 'An error occurred', 'error');
            }
            this.loading = false;
        },

        // -------------------------------------------------------
        //  Formatting helpers
        // -------------------------------------------------------

        getDeadlineClass(daysLeft) {
            if (daysLeft === null || daysLeft === undefined) return '';
            if (daysLeft < 0)   return 'text-red-600 font-bold';
            if (daysLeft <= 14) return 'text-yellow-600 font-semibold';
            if (daysLeft <= 30) return 'text-orange-500';
            return 'text-green-600';
        },

        getDeadlineBg(daysLeft) {
            if (daysLeft === null || daysLeft === undefined) return '';
            if (daysLeft < 0)   return 'bg-red-50';
            if (daysLeft <= 14) return 'bg-yellow-50';
            return '';
        },

        getStageBadge(stage) {
            const colors = {
                demand_review: 'bg-gray-100 text-gray-600',
                demand_write:  'bg-blue-100 text-blue-700',
                demand_sent:   'bg-indigo-100 text-indigo-700',
                negotiate:     'bg-purple-100 text-purple-700'
            };
            return colors[stage] || 'bg-gray-100 text-gray-600';
        },

        getStatusBadge(status) {
            const colors = {
                in_progress: 'bg-blue-100 text-blue-700',
                unpaid:      'bg-yellow-100 text-yellow-700',
                paid:        'bg-green-100 text-green-700',
                rejected:    'bg-red-100 text-red-700'
            };
            return colors[status] || 'bg-gray-100 text-gray-600';
        },

        // -------------------------------------------------------
        //  Duration helpers
        // -------------------------------------------------------

        /** Days since litigation_start_date. */
        litDuration(c) {
            if (!c.litigation_start_date) return 0;
            const start = new Date(c.litigation_start_date + 'T00:00:00');
            const now = new Date();
            return Math.floor((now - start) / 86400000);
        },

        /** Days since uim_start_date. */
        uimDuration(c) {
            if (!c.uim_start_date) return 0;
            const start = new Date(c.uim_start_date + 'T00:00:00');
            const now = new Date();
            return Math.floor((now - start) / 86400000);
        },

        /** Total duration for a settled case. */
        settledDuration(c) {
            return (parseInt(c.demand_duration_days) || 0)
                 + (parseInt(c.litigation_duration_days) || 0)
                 + (parseInt(c.uim_duration_days) || 0);
        },

        /** Determine which phase a settled case came from. */
        settledPhaseLabel(c) {
            if (c.uim_settled) return 'UIM';
            if (c.litigation_settled_date) return 'Litigation';
            return 'Demand';
        },

        settledPhaseBadge(c) {
            if (c.uim_settled) return 'bg-indigo-100 text-indigo-700';
            if (c.litigation_settled_date) return 'bg-purple-100 text-purple-700';
            return 'bg-blue-100 text-blue-700';
        },

        // -------------------------------------------------------
        //  Pagination
        // -------------------------------------------------------

        goToPage(page) {
            const pag = this[this.activeTab + 'Pagination'];
            if (pag && page >= 1 && page <= pag.total_pages) {
                this.loadTab(this.activeTab, page);
            }
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
            this._searchTimer = setTimeout(async () => {
                this.demandPage = 1;
                this.uimPage = 1;
                this.litPage = 1;
                this.settledPage = 1;
                if (this.search.trim()) {
                    // Search across all tabs
                    await Promise.all([
                        this.loadTab('demand'),
                        this.loadTab('uim'),
                        this.loadTab('litigation'),
                        this.loadTab('settled')
                    ]);
                    this._syncTabCounts();
                    // Auto-switch to first tab with results if current tab is empty
                    const currentData = { demand: this.demandCases, uim: this.uimCases, litigation: this.litigationCases, settled: this.settledCases };
                    if (currentData[this.activeTab].length === 0) {
                        const tabWithData = ['demand', 'uim', 'litigation', 'settled'].find(t => currentData[t].length > 0);
                        if (tabWithData) this.activeTab = tabWithData;
                    }
                } else {
                    await this.loadStats();
                    await this.loadTab(this.activeTab);
                }
            }, 300);
        },

    };
}
