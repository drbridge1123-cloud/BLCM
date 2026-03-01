function healthLedgerPage() {
    return {
        ...listPageBase('health-ledger/list', {
            defaultSort: 'created_at',
            defaultDir: 'desc',
            perPage: 99999,
            filtersToParams() {
                const p = {
                    status: this.statusFilter,
                    tier: this.tierFilter,
                    assigned_to: this.assignedFilter,
                };
                if (this.caseIdFilter) p.case_id = this.caseIdFilter;
                return p;
            }
        }),

        // Page-specific filter state
        statusFilter: '',
        tierFilter: '',
        assignedFilter: '',
        caseIdFilter: '',
        staffList: [],
        summary: {},

        // Expand / request history
        expandedId: null,
        requestHistory: [],

        // Modal state
        showAddModal: false,
        showEditModal: false,
        showRequestModal: false,
        showSendModal: false,
        showImportModal: false,
        saving: false,
        sending: false,
        editId: null,
        form: {},
        reqForm: {},
        previewData: {},

        // Import state
        importFile: null,
        importResult: null,
        importing: false,
        dragover: false,

        // Case search (add modal)
        caseSearch: '',
        caseResults: [],
        showCaseDropdown: false,

        // Carrier autocomplete
        carrierResults: [],
        showCarrierDropdown: false,

        _resetPageFilters() {
            this.statusFilter = '';
            this.tierFilter = '';
            this.assignedFilter = '';
            this.caseIdFilter = '';
            // Clean URL params when resetting
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('case_id');
                window.history.replaceState({}, '', url);
            }
        },

        _hasPageFilters() {
            return !!(this.statusFilter || this.tierFilter || this.assignedFilter || this.caseIdFilter);
        },

        async init() {
            this.form = this.getEmptyForm();
            this.loadStaff();
            this.loadHlTemplates();
            const urlParams = new URLSearchParams(window.location.search);
            const urlCaseId = urlParams.get('case_id');
            if (urlCaseId) this.caseIdFilter = urlCaseId;
            await this.loadData(1);
            // Auto-expand first item when navigating from Case Detail
            if (urlCaseId && this.items.length > 0) {
                this.toggleExpand(this.items[0].id);
            }
        },

        // Health ledger templates
        hlTemplates: [],

        getEmptyForm() {
            return { client_name: '', case_number: '', insurance_carrier: '', carrier_contact_email: '', carrier_contact_fax: '', claim_number: '', member_id: '', assigned_to: '', note: '' };
        },

        async loadStaff() {
            try {
                const r = await api.get('users?active_only=1');
                this.staffList = r.data || [];
            } catch(e) {}
        },

        async loadHlTemplates() {
            try {
                const r = await api.get('templates?type=health_ledger&active_only=1');
                this.hlTemplates = r.data || [];
            } catch(e) {}
        },

        // --- Status helpers ---

        toggleStatusFilter(s) {
            this.statusFilter = this.statusFilter === s ? '' : s;
            this.loadData(1);
        },

        getStatusLabel(s) {
            const m = { not_started: 'Not Started', requesting: 'Requesting', follow_up: 'Follow Up', received: 'Received', done: 'Done' };
            return m[s] || s;
        },

        // --- Expand / Request History ---

        async toggleExpand(id) {
            if (this.expandedId === id) { this.expandedId = null; return; }
            this.expandedId = id;
            this.requestHistory = [];
            try {
                const r = await api.get(`health-ledger/${id}/requests`);
                this.requestHistory = r.data || [];
            } catch(e) { showToast('Failed to load requests', 'error'); }
        },

        // --- Add / Edit Item ---

        openAddModal() {
            this.form = this.getEmptyForm();
            this.editId = null;
            this.caseSearch = '';
            this.caseResults = [];
            this.showCaseDropdown = false;
            this.showEditModal = false;
            this.showAddModal = true;
        },

        openEditModal(item) {
            this.form = {
                client_name: item.client_name,
                case_number: item.case_number || '',
                insurance_carrier: item.insurance_carrier,
                carrier_contact_email: item.carrier_contact_email || '',
                carrier_contact_fax: item.carrier_contact_fax || '',
                claim_number: item.claim_number || '',
                member_id: item.member_id || '',
                assigned_to: item.assigned_to || '',
                note: item.note || ''
            };
            this.editId = item.id;
            this.showAddModal = false;
            this.showEditModal = true;
        },

        closeModals() {
            this.showAddModal = false;
            this.showEditModal = false;
            this.editId = null;
            this.showCaseDropdown = false;
            this.showCarrierDropdown = false;
        },

        // --- Case search (add modal) ---

        async searchCases() {
            if (this.caseSearch.length < 2) { this.caseResults = []; this.showCaseDropdown = false; return; }
            try {
                const r = await api.get('cases?search=' + encodeURIComponent(this.caseSearch) + '&per_page=10');
                this.caseResults = r.data || [];
                this.showCaseDropdown = this.caseResults.length > 0;
            } catch(e) { this.caseResults = []; }
        },

        selectCase(c) {
            this.form.client_name = c.client_name;
            this.form.case_number = c.case_number;
            this.caseSearch = '';
            this.caseResults = [];
            this.showCaseDropdown = false;
        },

        clearCaseSelection() {
            this.form.client_name = '';
            this.form.case_number = '';
            this.caseSearch = '';
        },

        // --- Carrier autocomplete ---

        async searchCarriers() {
            const q = this.form.insurance_carrier;
            if (!q || q.length < 2) { this.carrierResults = []; this.showCarrierDropdown = false; return; }
            try {
                const r = await api.get('insurance-companies?search=' + encodeURIComponent(q));
                this.carrierResults = r.data || [];
                this.showCarrierDropdown = this.carrierResults.length > 0;
            } catch(e) { this.carrierResults = []; }
        },

        selectCarrier(c) {
            this.form.insurance_carrier = c.name;
            if (c.email) this.form.carrier_contact_email = c.email;
            if (c.fax) this.form.carrier_contact_fax = c.fax;
            this.carrierResults = [];
            this.showCarrierDropdown = false;
        },

        async saveItem() {
            if (!this.form.client_name || !this.form.insurance_carrier) { showToast('Client name and carrier required', 'error'); return; }
            this.saving = true;
            try {
                if (this.showEditModal && this.editId) {
                    await api.put('health-ledger/' + this.editId, this.form);
                    showToast('Updated');
                } else {
                    await api.post('health-ledger', this.form);
                    showToast('Created');
                }
                this.closeModals();
                this.loadData(this.pagination?.page || 1);
            } catch(e) { showToast(e.data?.message || 'Error', 'error'); }
            this.saving = false;
        },

        async deleteItem(id) {
            if (!await confirmAction('Delete this item and all its requests?')) return;
            try {
                await api.delete('health-ledger/' + id);
                showToast('Deleted');
                this.loadData(this.pagination?.page || 1);
            } catch(e) { showToast('Delete failed', 'error'); }
        },

        async updateStatus(id, status) {
            try {
                await api.put('health-ledger/' + id, { overall_status: status });
                showToast('Status updated');
                this.loadData(this.pagination?.page || 1);
            } catch(e) { showToast('Update failed', 'error'); }
        },

        // --- Request modal ---

        openRequestModal(item) {
            // Auto-select default template
            const defaultTpl = this.hlTemplates.find(t => t.is_default == 1);
            this.reqForm = {
                item_id: item.id,
                request_date: new Date().toISOString().split('T')[0],
                request_method: '',
                request_type: item.request_count > 0 ? 'follow_up' : 'initial',
                sent_to: '',
                notes: '',
                template_id: defaultTpl ? defaultTpl.id : '',
                template_data: {},
                _showSettlement: false,
                _carrierLabel: item.client_name + ' - ' + item.insurance_carrier,
                _email: item.carrier_contact_email || '',
                _fax: item.carrier_contact_fax || ''
            };
            this.showRequestModal = true;
        },

        onTemplateChange() {
            const tpl = this.hlTemplates.find(t => t.id == this.reqForm.template_id);
            // Show settlement fields for "Final Health Lien" template
            this.reqForm._showSettlement = tpl && tpl.name.toLowerCase().includes('final health lien');
            if (!this.reqForm._showSettlement) {
                this.reqForm.template_data = {};
            }
        },

        updateRecipient() {
            if (this.reqForm.request_method === 'email') this.reqForm.sent_to = this.reqForm._email;
            else if (this.reqForm.request_method === 'fax') this.reqForm.sent_to = this.reqForm._fax;
            else this.reqForm.sent_to = '';
        },

        async submitRequest() {
            if (!this.reqForm.request_date || !this.reqForm.request_method) { showToast('Date and method required', 'error'); return; }
            this.saving = true;
            try {
                const payload = { ...this.reqForm };
                // Clean internal fields
                delete payload._carrierLabel;
                delete payload._email;
                delete payload._fax;
                delete payload._showSettlement;
                // Remove empty template_data
                if (!payload.template_data || Object.keys(payload.template_data).length === 0) {
                    delete payload.template_data;
                }
                if (!payload.template_id) delete payload.template_id;
                await api.post('health-ledger/' + payload.item_id + '/requests', payload);
                showToast('Request created');
                this.showRequestModal = false;
                if (this.expandedId === this.reqForm.item_id) {
                    this.toggleExpand(this.reqForm.item_id);
                    this.toggleExpand(this.reqForm.item_id);
                }
                this.loadData(this.pagination?.page || 1);
            } catch(e) { showToast(e.data?.message || 'Error', 'error'); }
            this.saving = false;
        },

        // --- Preview & Send ---

        async openSendModal(req) {
            try {
                const r = await api.get(`health-ledger/${req.id}/preview`);
                this.previewData = r.data;
                this.showSendModal = true;
            } catch(e) { showToast('Failed to load preview', 'error'); }
        },

        async confirmAndSend() {
            if (!this.previewData.recipient) { showToast('Recipient required', 'error'); return; }
            this.sending = true;
            try {
                await api.post(`health-ledger/${this.previewData.request_id}/send`, { recipient: this.previewData.recipient });
                showToast('Sent successfully!');
                this.showSendModal = false;
                if (this.expandedId) {
                    const id = this.expandedId;
                    this.expandedId = null;
                    this.toggleExpand(id);
                }
                this.loadData(this.pagination?.page || 1);
            } catch(e) { showToast(e.data?.message || 'Send failed', 'error'); }
            this.sending = false;
        },

        // --- Import ---

        async doImport() {
            if (!this.importFile) return;
            this.importing = true;
            this.importResult = null;
            try {
                const fd = new FormData();
                fd.append('file', this.importFile);
                const r = await api.upload('health-ledger/import', fd);
                this.importResult = r.data;
                this.importFile = null;
                showToast(r.message || 'Import complete');
                this.loadData(1);
            } catch(e) { showToast(e.data?.message || 'Import failed', 'error'); }
            this.importing = false;
        }
    };
}
