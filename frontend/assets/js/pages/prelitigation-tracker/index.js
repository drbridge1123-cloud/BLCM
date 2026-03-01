function prelitTrackerPage() {
    return {
        ...listPageBase('prelitigation-tracker/list', {
            defaultSort: 'next_followup_date',
            defaultDir: 'asc',
            perPage: 99999,
            filtersToParams() {
                return {
                    filter: this.activeFilter,
                    treatment_status: this.treatmentStatusFilter,
                    assigned_to: this.staffFilter,
                };
            }
        }),

        // Page-specific state
        summary: { total: 0, followup_due: 0, no_contact: 0, treatment_complete: 0 },
        activeFilter: '',
        treatmentStatusFilter: '',
        staffFilter: '',
        staffList: [],

        // Expand / follow-up history
        expandedId: null,
        followupHistory: [],

        // Follow-up modal
        showFollowupModal: false,
        saving: false,
        followupForm: {},

        // Complete modal
        showCompleteModal: false,
        completeForm: {},

        _resetPageFilters() {
            this.activeFilter = '';
            this.treatmentStatusFilter = '';
            // Preserve staffFilter for non-admin users (security)
            const user = Alpine.store('auth')?.user;
            if (user && user.role !== 'admin' && user.role !== 'manager') {
                this.staffFilter = user.id.toString();
            } else {
                this.staffFilter = '';
            }
        },

        _hasPageFilters() {
            return !!(this.activeFilter || this.treatmentStatusFilter || this.staffFilter);
        },

        async init() {
            // Auto-filter non-admin to own user
            const user = Alpine.store('auth')?.user;
            if (user && user.role !== 'admin' && user.role !== 'manager') {
                this.staffFilter = user.id.toString();
            }

            this.loadStaff();
            await this.loadData(1);
        },

        async loadStaff() {
            const prelitNames = ['dave', 'soyong', 'jimi', 'chloe', 'daniel'];
            try {
                const res = await api.get('users?active_only=1');
                const all = res.data || [];
                this.staffList = all.filter(u => {
                    const name = (u.display_name || u.full_name || '').toLowerCase();
                    return prelitNames.some(n => name.includes(n));
                });
            } catch(e) { this.staffList = []; }
        },

        toggleFilter(filter) {
            this.activeFilter = this.activeFilter === filter ? '' : filter;
            this.loadData(1);
        },

        goToCase(caseId) {
            window.location.href = '/CMC/frontend/pages/cases/detail.php?id=' + caseId;
        },

        getContactResultLabel(result) {
            const labels = {
                reached: 'Reached',
                voicemail: 'Voicemail',
                no_answer: 'No Answer',
                callback_scheduled: 'Callback',
                treatment_update: 'Treatment Update'
            };
            return labels[result] || result || '';
        },

        // --- Expand / Follow-up History ---

        async toggleExpand(id) {
            if (this.expandedId === id) { this.expandedId = null; return; }
            this.expandedId = id;
            this.followupHistory = [];
            try {
                const r = await api.get('prelitigation-tracker/followup-history?case_id=' + id);
                this.followupHistory = r.data || [];
            } catch(e) { this.followupHistory = []; }
        },

        // --- Follow-up Modal ---

        openFollowupModal(item) {
            this.followupForm = {
                _caseId: item.id,
                _label: '#' + item.case_number + ' - ' + item.client_name,
                followup_date: new Date().toISOString().split('T')[0],
                followup_type: '',
                contact_result: '',
                treatment_status_update: '',
                next_followup_date: '',
                notes: ''
            };
            this.showFollowupModal = true;
        },

        async submitFollowup() {
            if (!this.followupForm.followup_date || !this.followupForm.followup_type || !this.followupForm.contact_result) {
                showToast('Date, method, and result are required', 'error');
                return;
            }
            this.saving = true;
            try {
                const payload = {
                    case_id: this.followupForm._caseId,
                    followup_date: this.followupForm.followup_date,
                    followup_type: this.followupForm.followup_type,
                    contact_result: this.followupForm.contact_result,
                    treatment_status_update: this.followupForm.treatment_status_update || undefined,
                    next_followup_date: this.followupForm.next_followup_date || undefined,
                    notes: this.followupForm.notes || undefined
                };
                await api.post('prelitigation-tracker/log-followup', payload);
                showToast('Follow-up logged');
                this.showFollowupModal = false;

                // Refresh expanded history
                if (this.expandedId === this.followupForm._caseId) {
                    const r = await api.get('prelitigation-tracker/followup-history?case_id=' + this.followupForm._caseId);
                    this.followupHistory = r.data || [];
                }
                this.loadData(1);
            } catch(e) {
                showToast(e.data?.message || 'Failed to log follow-up', 'error');
            }
            this.saving = false;
        },

        // --- Complete / Send to Billing ---

        openCompleteModal(item) {
            this.completeForm = {
                _caseId: item.id,
                _label: '#' + item.case_number + ' - ' + item.client_name,
                note: ''
            };
            this.showCompleteModal = true;
        },

        async submitComplete() {
            this.saving = true;
            try {
                await api.post('prelitigation-tracker/complete', {
                    case_id: this.completeForm._caseId,
                    note: this.completeForm.note || undefined
                });
                showToast('Case sent to billing', 'success');
                this.showCompleteModal = false;
                this.expandedId = null;
                this.loadData(1);
            } catch(e) {
                showToast(e.data?.message || 'Failed to send to billing', 'error');
            }
            this.saving = false;
        }
    };
}
