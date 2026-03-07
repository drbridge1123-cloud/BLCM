function accountingTrackerPage() {
    return {
        ...pendingAssignmentsMixin(),
        ...listPageBase('accounting/list', {
            defaultSort: 'sent_to_accounting_date',
            defaultDir: 'asc',
            perPage: 50,
            filtersToParams() {
                return {
                    filter: this.activeFilter,
                    assigned_to: this.staffFilter,
                };
            }
        }),

        // Page-specific state
        summary: { total: 0, overdue: 0, pending: 0, total_settlement: 0 },
        activeFilter: '',
        staffFilter: '',
        staffList: [],

        // Expand / disbursement history
        expandedId: null,
        expandedItem: null,
        disbursementHistory: [],

        // Disbursement modal
        showDisbursementModal: false,
        saving: false,
        disbForm: {},

        // Close modal
        showCloseModal: false,
        closeForm: {},

        _resetPageFilters() {
            this.activeFilter = '';
            const user = Alpine.store('auth')?.user;
            if (user && user.team === 'accounting') {
                this.staffFilter = user.id.toString();
            } else {
                this.staffFilter = '';
            }
        },

        _hasPageFilters() {
            return !!(this.activeFilter || this.staffFilter);
        },

        fromCaseDetail: false,
        fromCaseDetailUrl: '',

        async init() {
            // Default to own cases if user belongs to this team
            const user = Alpine.store('auth')?.user;
            if (user && user.team === 'accounting') {
                this.staffFilter = user.id.toString();
            }

            // Pick up search param from URL (e.g. from Case Detail / Cases list)
            const urlParams = new URLSearchParams(window.location.search);
            const urlSearch = urlParams.get('search');
            if (urlSearch) {
                this.search = urlSearch;
            }
            if (urlParams.get('case_id')) {
                this.fromCaseDetail = true;
                this.fromCaseDetailUrl = '/blcm/frontend/pages/bl-cases/detail.php?id=' + urlParams.get('case_id');
            }

            await Promise.all([this.loadPendingCaseAssignments(), this.loadStaff(), this.loadData(1)]);
        },

        async loadStaff() {
            const acctNames = ['chloe', 'daniel'];
            try {
                const all = await Alpine.store('staff').getList();
                this.staffList = all.filter(u => {
                    const name = (u.display_name || u.full_name || '').toLowerCase();
                    return acctNames.some(n => name.includes(n));
                });
            } catch(e) { this.staffList = []; }
        },

        toggleFilter(filter) {
            this.activeFilter = this.activeFilter === filter ? '' : filter;
            this.loadData(1);
        },

        goToCase(item) {
            if (typeof item === 'object' && item.source_type === 'attorney') {
                window.location.href = '/blcm/frontend/pages/attorney/index.php';
            } else {
                const id = typeof item === 'object' ? item.id : item;
                window.location.href = '/blcm/frontend/pages/bl-cases/detail.php?id=' + id;
            }
        },

        formatNumber(n) {
            return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        /** Build the query param for disbursement API based on item source_type */
        _disbQueryParam(item) {
            if (item && item.source_type === 'attorney' && item.attorney_case_id) {
                return 'attorney_case_id=' + item.attorney_case_id;
            }
            return 'case_id=' + (item?.id || item);
        },

        /** Unique key for expand toggle (to avoid id collision between sources) */
        _itemKey(item) {
            return (item.source_type === 'attorney' ? 'att_' : '') + (item.attorney_case_id || item.id);
        },

        // --- Expand / Disbursement History ---

        async toggleExpand(item) {
            const key = this._itemKey(item);
            if (this.expandedId === key) { this.expandedId = null; this.expandedItem = null; return; }
            this.expandedId = key;
            this.expandedItem = item;
            this.disbursementHistory = [];
            try {
                const r = await api.get('accounting/list-disbursements?' + this._disbQueryParam(item));
                this.disbursementHistory = r.data || [];
            } catch(e) { this.disbursementHistory = []; }
        },

        // --- Disbursement Modal ---

        openDisbursementModal(item) {
            this.disbForm = {
                _item: item,
                _label: '#' + item.case_number + ' - ' + item.client_name + ' ($' + this.formatNumber(item.settlement_amount) + ')',
                disbursement_type: '',
                payee_name: '',
                amount: '',
                check_number: '',
                payment_method: '',
                payment_date: new Date().toISOString().split('T')[0],
                notes: ''
            };
            this.showDisbursementModal = true;
        },

        async submitDisbursement() {
            if (!this.disbForm.disbursement_type || !this.disbForm.payee_name || !this.disbForm.amount) {
                showToast('Type, payee, and amount are required', 'error');
                return;
            }
            this.saving = true;
            const item = this.disbForm._item;
            const payload = {
                disbursement_type: this.disbForm.disbursement_type,
                payee_name: this.disbForm.payee_name,
                amount: this.disbForm.amount,
                check_number: this.disbForm.check_number || undefined,
                payment_method: this.disbForm.payment_method || undefined,
                payment_date: this.disbForm.payment_date || undefined,
                notes: this.disbForm.notes || undefined
            };

            if (item.source_type === 'attorney' && item.attorney_case_id) {
                payload.attorney_case_id = item.attorney_case_id;
            } else {
                payload.case_id = item.id;
            }

            try {
                await api.post('accounting/create-disbursement', payload);
                showToast('Disbursement added');
                this.showDisbursementModal = false;

                // Refresh expanded history
                if (this.expandedItem && this._itemKey(this.expandedItem) === this._itemKey(item)) {
                    const r = await api.get('accounting/list-disbursements?' + this._disbQueryParam(item));
                    this.disbursementHistory = r.data || [];
                }
                this.loadData(1);
            } catch(e) {
                showToast(e.data?.message || 'Failed to add disbursement', 'error');
            }
            this.saving = false;
        },

        async updateDisbStatus(disbId, newStatus) {
            if (newStatus === 'void' && !confirm('Void this disbursement?')) return;
            try {
                await api.put('accounting/update-disbursement', { id: disbId, status: newStatus });
                showToast('Status updated to ' + newStatus);
                // Refresh history
                if (this.expandedItem) {
                    const r = await api.get('accounting/list-disbursements?' + this._disbQueryParam(this.expandedItem));
                    this.disbursementHistory = r.data || [];
                }
                this.loadData(1);
            } catch(e) {
                showToast(e.data?.message || 'Update failed', 'error');
            }
        },

        // --- Close Case ---

        openCloseModal(item) {
            this.closeForm = {
                _item: item,
                _label: '#' + item.case_number + ' - ' + item.client_name,
                file_location: item.file_location || '',
                note: ''
            };
            this.showCloseModal = true;
        },

        async submitClose() {
            if (!this.closeForm.file_location) {
                showToast('File location is required', 'error');
                return;
            }
            this.saving = true;
            const item = this.closeForm._item;
            const payload = {
                file_location: this.closeForm.file_location,
                note: this.closeForm.note || undefined
            };

            if (item.source_type === 'attorney' && item.attorney_case_id) {
                payload.attorney_case_id = item.attorney_case_id;
            } else {
                payload.case_id = item.id;
            }

            try {
                await api.post('accounting/complete', payload);
                showToast('Case closed', 'success');
                this.showCloseModal = false;
                this.expandedId = null;
                this.expandedItem = null;
                this.loadData(1);
            } catch(e) {
                showToast(e.data?.message || 'Failed to close case', 'error');
            }
            this.saving = false;
        }
    };
}
