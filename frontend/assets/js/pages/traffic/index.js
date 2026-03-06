/**
 * Traffic Cases Page Controller
 * Alpine.js component for traffic cases + requests.
 *
 * Tabs: Cases, Requests
 * Commission: Dismissed = $150, Amended = $100
 */
function trafficPage() {
    return {
        // -------------------------------------------------------
        //  State
        // -------------------------------------------------------
        activeTab: 'cases',
        loading: false,
        saving: false,

        cases: [],
        requests: [],
        pagination: null,
        pendingCount: 0,

        summary: {
            active_count: 0,
            resolved_count: 0,
            total_commission: 0,
            paid_commission: 0,
            unpaid_commission: 0
        },

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
        statusFilter: '',
        requestStatusFilter: '',

        // -------------------------------------------------------
        //  Modal state
        // -------------------------------------------------------
        showCaseModal: false,
        showRequestModal: false,

        caseForm: {
            id: null,
            client_name: '', client_phone: '', client_email: '',
            court: '', court_date: '', charge: '', case_number: '',
            prosecutor_offer: '', disposition: 'pending',
            discovery: false, note: '',
            noa_sent_date: '', citation_issued_date: ''
        },

        requestForm: {
            client_name: '', client_phone: '',
            court: '', court_date: '', charge: '', case_number: '',
            note: ''
        },

        // -------------------------------------------------------
        //  Lifecycle
        // -------------------------------------------------------
        async init() {
            await Promise.all([this.loadCases(), this.loadRequests()]);
        },

        async switchTab(tab) {
            this.activeTab = tab;
            if (tab === 'cases') await this.loadCases();
            else await this.loadRequests();
        },

        // -------------------------------------------------------
        //  Data Loading
        // -------------------------------------------------------
        async loadCases(page = 1) {
            this.loading = true;
            try {
                const params = { page, per_page: 50 };
                if (this.statusFilter) params.status = this.statusFilter;
                if (this.search) params.search = this.search;

                const res = await api.get('traffic' + buildQueryString(params));
                this.cases = res.data || [];
                const pg = res.pagination || null;
                this.pagination = pg ? { ...pg, current_page: pg.page } : null;
                this.summary = res.summary || this.summary;
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.loading = false;
        },

        async loadRequests() {
            try {
                const params = {};
                if (this.requestStatusFilter) params.status = this.requestStatusFilter;

                const res = await api.get('traffic-requests' + buildQueryString(params));
                const data = res.data || {};
                this.requests = data.requests || [];
                this.pendingCount = data.pending_count || 0;
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        // -------------------------------------------------------
        //  Case CRUD
        // -------------------------------------------------------
        openCreateModal() {
            this.caseForm = {
                id: null, client_name: '', client_phone: '', client_email: '',
                court: '', court_date: '', charge: '', case_number: '',
                prosecutor_offer: '', disposition: 'pending',
                discovery: false, note: '',
                noa_sent_date: '', citation_issued_date: ''
            };
            this.showCaseModal = true;
        },

        openEditModal(c) {
            this.caseForm = {
                id: c.id,
                client_name: c.client_name || '',
                client_phone: c.client_phone || '',
                client_email: c.client_email || '',
                court: c.court || '',
                court_date: c.court_date ? c.court_date.slice(0, 10) : '',
                charge: c.charge || '',
                case_number: c.case_number || '',
                prosecutor_offer: c.prosecutor_offer || '',
                disposition: c.disposition || 'pending',
                discovery: c.discovery == 1,
                note: c.note || '',
                noa_sent_date: c.noa_sent_date || '',
                citation_issued_date: c.citation_issued_date || ''
            };
            this.showCaseModal = true;
        },

        async saveCase() {
            if (!this.caseForm.client_name) {
                return showToast('Client name is required', 'error');
            }
            this.saving = true;
            try {
                if (this.caseForm.id) {
                    await api.put('traffic/' + this.caseForm.id, this.caseForm);
                    showToast('Case updated');
                } else {
                    await api.post('traffic', this.caseForm);
                    showToast('Case created');
                }
                this.showCaseModal = false;
                await this.loadCases();
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.saving = false;
        },

        async deleteCase(id) {
            if (!confirm('Delete this traffic case?')) return;
            try {
                await api.delete('traffic/' + id);
                showToast('Case deleted');
                await this.loadCases();
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async togglePaid(c) {
            try {
                await api.put('traffic/' + c.id, {
                    action: 'mark_paid',
                    paid: c.paid == 1 ? 0 : 1
                });
                c.paid = c.paid == 1 ? 0 : 1;
                await this.loadCases(); // refresh summary
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        // -------------------------------------------------------
        //  Request CRUD
        // -------------------------------------------------------
        openRequestModal() {
            this.requestForm = {
                client_name: '', client_phone: '',
                court: '', court_date: '', charge: '', case_number: '',
                note: ''
            };
            this.showRequestModal = true;
        },

        async submitRequest() {
            if (!this.requestForm.client_name) {
                return showToast('Client name is required', 'error');
            }
            this.saving = true;
            try {
                await api.post('traffic-requests', this.requestForm);
                showToast('Request sent');
                this.showRequestModal = false;
                await this.loadRequests();
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.saving = false;
        },

        async respondRequest(id, action) {
            if (action === 'deny') {
                const reason = prompt('Enter deny reason:');
                if (!reason) return;
                try {
                    await api.put('traffic-requests/' + id, { action: 'deny', deny_reason: reason });
                    showToast('Request denied');
                } catch (e) {
                    showToast(e.message, 'error');
                }
            } else {
                try {
                    await api.put('traffic-requests/' + id, { action: 'accept' });
                    showToast('Request accepted, case created');
                } catch (e) {
                    showToast(e.message, 'error');
                }
            }
            await Promise.all([this.loadRequests(), this.loadCases()]);
        },

        // -------------------------------------------------------
        //  Helpers
        // -------------------------------------------------------
        getCommissionAmount(disposition) {
            if (disposition === 'dismissed') return '150.00';
            if (disposition === 'amended') return '100.00';
            return '0.00';
        },

        getDispositionClass(disposition) {
            const map = {
                'pending': 'bg-gray-100 text-gray-600',
                'dismissed': 'bg-green-100 text-green-700',
                'amended': 'bg-blue-100 text-blue-700',
                'other': 'bg-purple-100 text-purple-700'
            };
            return map[disposition] || 'bg-gray-100 text-gray-600';
        },

        // -------------------------------------------------------
        //  Pagination
        // -------------------------------------------------------
        goToPage(page) {
            if (page < 1 || (this.pagination && page > this.pagination.total_pages)) return;
            this.loadCases(page);
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
                this.loadCases();
            }, 300);
        }
    };
}
