/**
 * Health Tracker Page Controller
 * Manages health ledger items and their request histories
 */
function healthTrackerPage() {
    const base = listPageBase('health-ledger', {
        defaultSort: 'created_at',
        defaultDir: 'desc',
        perPage: 50,
        filtersToParams() {
            return {
                overall_status: this.statusFilter,
                assigned_to: this.assignedFilter
            };
        }
    });

    return {
        ...base,

        // Filters
        statusFilter: '',
        assignedFilter: '',

        // Users list for dropdowns
        users: [],

        // Stats
        stats: { requesting: 0, follow_up: 0 },

        // Add modal
        showAddModal: false,
        addForm: {},
        saving: false,

        // Edit modal
        showEditModal: false,
        editingItem: null,
        editForm: {},

        // Requests panel
        showRequests: false,
        requestsFor: null,
        selectedItemRequests: [],
        loadingRequests: false,
        savingRequest: false,
        requestForm: {},

        // Import modal
        showImportModal: false,
        importing: false,
        importResult: null,

        async init() {
            await Promise.all([this.loadUsers(), this.loadData(1)]);
        },

        // Override loadData to also compute stats
        async loadData(page = 1) {
            await base.loadData.call(this, page);
            this.computeStats();
        },

        computeStats() {
            this.stats.requesting = this.items.filter(i => i.overall_status === 'requesting').length;
            this.stats.follow_up = this.items.filter(i => i.overall_status === 'follow_up').length;
        },

        async loadUsers() {
            try {
                const res = await api.get('users');
                this.users = (res.data || []).filter(u => u.is_active);
            } catch (e) { /* non-critical */ }
        },

        // ---- Filters ----

        resetFilters() {
            this.search = '';
            this.statusFilter = '';
            this.assignedFilter = '';
            this.loadData(1);
        },

        // ---- Status helpers ----

        getHealthStatusColor(status) {
            const colors = {
                not_started: 'bg-gray-100 text-gray-600',
                requesting: 'bg-blue-100 text-blue-700',
                follow_up: 'bg-yellow-100 text-yellow-700',
                received: 'bg-green-100 text-green-700',
                done: 'bg-emerald-100 text-emerald-700'
            };
            return colors[status] || 'bg-gray-100 text-gray-600';
        },

        formatHealthStatus(status) {
            const labels = {
                not_started: 'Not Started',
                requesting: 'Requesting',
                follow_up: 'Follow Up',
                received: 'Received',
                done: 'Done'
            };
            return labels[status] || status || 'N/A';
        },

        // ---- Add Item ----

        openAddModal() {
            this.addForm = {
                client_name: '', insurance_carrier: '', case_number: '',
                claim_number: '', member_id: '', carrier_contact_email: '',
                carrier_contact_fax: '', assigned_to: '', note: ''
            };
            this.showAddModal = true;
        },

        async createItem() {
            if (!this.addForm.client_name || !this.addForm.insurance_carrier) {
                showToast('Client Name and Insurance Carrier are required', 'error');
                return;
            }
            this.saving = true;
            try {
                const payload = { ...this.addForm };
                if (payload.assigned_to) payload.assigned_to = parseInt(payload.assigned_to);
                else delete payload.assigned_to;
                // Remove empty optional fields
                Object.keys(payload).forEach(k => { if (payload[k] === '') delete payload[k]; });

                await api.post('health-ledger', payload);
                showToast('Item created successfully', 'success');
                this.showAddModal = false;
                await this.loadData(1);
            } catch (e) { showToast(e.message, 'error'); }
            this.saving = false;
        },

        // ---- Edit Item ----

        openEditModal(item) {
            this.editingItem = item;
            this.editForm = {
                client_name: item.client_name || '',
                insurance_carrier: item.insurance_carrier || '',
                case_number: item.case_number || '',
                claim_number: item.claim_number || '',
                member_id: item.member_id || '',
                overall_status: item.overall_status || 'not_started',
                assigned_to: item.assigned_to || '',
                carrier_contact_email: item.carrier_contact_email || '',
                carrier_contact_fax: item.carrier_contact_fax || '',
                note: item.note || ''
            };
            this.showEditModal = true;
        },

        async updateItem() {
            if (!this.editForm.client_name || !this.editForm.insurance_carrier) {
                showToast('Client Name and Insurance Carrier are required', 'error');
                return;
            }
            this.saving = true;
            try {
                const payload = { ...this.editForm };
                if (payload.assigned_to) payload.assigned_to = parseInt(payload.assigned_to);
                else payload.assigned_to = null;

                await api.put('health-ledger/' + this.editingItem.id, payload);
                showToast('Item updated successfully', 'success');
                this.showEditModal = false;
                await this.loadData(this.pagination?.page || 1);
            } catch (e) { showToast(e.message, 'error'); }
            this.saving = false;
        },

        // ---- Delete Item ----

        async deleteItem(item) {
            if (!confirm('Delete "' + item.client_name + ' - ' + item.insurance_carrier + '"? This will also delete all associated requests.')) return;
            try {
                await api.delete('health-ledger/' + item.id);
                showToast('Item deleted successfully', 'success');
                await this.loadData(this.pagination?.page || 1);
            } catch (e) { showToast(e.message, 'error'); }
        },

        // ---- Requests ----

        async viewRequests(item) {
            this.requestsFor = item;
            this.selectedItemRequests = [];
            this.showRequests = true;
            this.resetRequestForm();
            await this.loadRequests(item.id);
        },

        async loadRequests(itemId) {
            this.loadingRequests = true;
            try {
                const res = await api.get('health-ledger/' + itemId + '/requests');
                this.selectedItemRequests = res.data || [];
            } catch (e) { showToast(e.message, 'error'); }
            this.loadingRequests = false;
        },

        resetRequestForm() {
            this.requestForm = {
                request_type: '', request_method: '', request_date: '',
                sent_to: '', next_followup_date: '', notes: ''
            };
        },

        async createRequest() {
            if (!this.requestForm.request_type || !this.requestForm.request_method || !this.requestForm.request_date) {
                showToast('Type, Method, and Date are required', 'error');
                return;
            }
            this.savingRequest = true;
            try {
                const payload = { ...this.requestForm };
                Object.keys(payload).forEach(k => { if (payload[k] === '') delete payload[k]; });

                await api.post('health-ledger/' + this.requestsFor.id + '/requests', payload);
                showToast('Request created successfully', 'success');
                this.resetRequestForm();
                await this.loadRequests(this.requestsFor.id);
                // Refresh main list to reflect updated status
                await this.loadData(this.pagination?.page || 1);
            } catch (e) { showToast(e.message, 'error'); }
            this.savingRequest = false;
        },

        // ---- Import CSV ----

        async importCSV() {
            const fileInput = this.$refs.csvFile;
            if (!fileInput || !fileInput.files[0]) {
                showToast('Please select a CSV file', 'error');
                return;
            }
            this.importing = true;
            this.importResult = null;
            try {
                const formData = new FormData();
                formData.append('file', fileInput.files[0]);

                const res = await fetch('/blcm/backend/api/health-ledger/import', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Import failed');

                this.importResult = { inserted: data.data?.inserted || 0, message: data.message };
                showToast(data.message, 'success');
                await this.loadData(1);
            } catch (e) { showToast(e.message, 'error'); }
            this.importing = false;
        }
    };
}
