function casesListPage() {
    return {
        ...listPageBase('cases', {
            defaultSort: 'case_number',
            defaultDir: 'desc',
            perPage: 9999,
            filtersToParams() {
                return {
                    status: this.search ? '' : this.statusFilter,
                    assigned_to: this.assignedFilter,
                };
            }
        }),

        // Page-specific state
        summary: {},
        statusFilter: '',
        assignedFilter: '',
        showCreateModal: false,
        saving: false,
        staffList: [],
        newCase: { case_number: '', client_name: '', client_dob: '', doi: '', attorney_name: '', assigned_to: '', notes: '' },

        _resetPageFilters() {
            this.statusFilter = '';
            this.assignedFilter = '';
        },

        _hasPageFilters() {
            return this.statusFilter || this.assignedFilter;
        },

        async createCase() {
            this.saving = true;
            try {
                await api.post('cases', { ...this.newCase });
                showToast('Case created successfully');
                this.showCreateModal = false;
                this.newCase = { case_number: '', client_name: '', client_dob: '', doi: '', attorney_name: '', assigned_to: '', notes: '' };
                this.loadData(1);
            } catch (e) {
                showToast(e.data?.message || 'Failed to create case', 'error');
            }
            this.saving = false;
        },

        async deleteCase(id, caseNumber, clientName) {
            if (!confirm(`Delete case ${caseNumber} (${clientName})? This will also delete all providers, requests, and notes for this case.`)) return;
            try {
                await api.delete('cases/' + id);
                showToast('Case deleted');
                this.loadData(this.pagination?.page || 1);
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete case', 'error');
            }
        },

        caseStatusClass(status) {
            const map = {
                collecting: 'sp-stage-demand-write',
                verification: 'sp-stage-demand-review',
                completed: 'sp-stage-settled',
                rfd: 'sp-stage-demand-sent',
                final_verification: 'sp-stage-demand-review',
                prelitigation: 'sp-stage-litigation',
                accounting: 'sp-stage-mediation',
                disbursement: 'sp-stage-trial-set',
                closed: '',
            };
            return map[status] || '';
        },

        exportCSV() {
            const params = new URLSearchParams();
            if (this.statusFilter) params.set('status', this.statusFilter);
            if (this.assignedFilter) params.set('assigned_to', this.assignedFilter);
            if (this.search) params.set('search', this.search);
            const qs = params.toString();
            window.location.href = '/CMC/backend/api/cases/export' + (qs ? '?' + qs : '');
        },

        async init() {
            try {
                const res = await api.get('users?active_only=1');
                this.staffList = res.data || [];
            } catch (e) {}

            const auth = Alpine.store('auth');
            if (auth.loading) {
                await new Promise(r => {
                    const iv = setInterval(() => { if (!auth.loading) { clearInterval(iv); r(); } }, 50);
                });
            }

            const uid = auth.user?.id;
            if (uid === 2) this.statusFilter = 'collecting';

            await this.loadData();
        }
    };
}
