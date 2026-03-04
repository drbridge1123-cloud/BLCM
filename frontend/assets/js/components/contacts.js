/**
 * Contacts Modal Mixin (Client + 3rd Adjuster + UM Adjuster)
 * Search → Auto-fill pattern (Add Provider style):
 *   - Type to search → dropdown with matches + "Create" option
 *   - Select existing → auto-fill read-only
 *   - Create → inline form → save → auto-fill read-only
 * Spread into any Alpine component: ...contactsMixin()
 */
function contactsMixin() {
    return {
        showContactsModal: false,
        contactsTab: 'client',
        contactsLoading: false,
        contactsSaving: false,
        _contactsCaseId: null,
        _contactsCaseItem: null,

        // Separate form objects per tab
        clientForm: {},
        adjuster3rdForm: {},
        adjusterUmForm: {},

        // Match status per tab: idle | matched | adding
        clientMatchStatus: 'idle',
        adjuster3rdMatchStatus: 'idle',
        adjusterUmMatchStatus: 'idle',

        // Client search
        clientSearch: '',
        clientResults: [],
        showClientDropdown: false,

        // Adjuster search
        adjusterSearchQuery: '',
        adjusterSearchResults: [],
        showAdjusterDropdown: false,

        // Insurance company search (for adjuster "adding" mode)
        insuranceSearch: '',
        insuranceResults: [],
        showInsuranceDropdown: false,

        // Title dropdown
        titleOptions: [
            'BI Adjuster', 'PIP Adjuster', 'Claims Adjuster', 'Senior Claims Adjuster',
            'Claims Examiner', 'Claims Manager', 'Property Damage Adjuster',
            'UM/UIM Adjuster', 'Subrogation Specialist'
        ],
        customTitle: false,

        async openContacts(caseItem) {
            this._contactsCaseId = caseItem.id;
            this._contactsCaseItem = caseItem;
            this.contactsTab = 'client';
            this.contactsLoading = true;
            this.showContactsModal = true;
            this.clientForm = {};
            this.adjuster3rdForm = {};
            this.adjusterUmForm = {};
            this.clientMatchStatus = 'idle';
            this.adjuster3rdMatchStatus = 'idle';
            this.adjusterUmMatchStatus = 'idle';
            this.clientSearch = '';
            this.clientResults = [];
            this.showClientDropdown = false;
            this.adjusterSearchQuery = '';
            this.adjusterSearchResults = [];
            this.showAdjusterDropdown = false;
            this.insuranceSearch = '';
            this.insuranceResults = [];
            this.showInsuranceDropdown = false;

            // Load all 3 tabs in parallel
            await Promise.all([
                this._loadClientTab(caseItem),
                this._loadAdjusterTab(caseItem, '3rd', 'adjuster_3rd_id'),
                this._loadAdjusterTab(caseItem, 'um', 'adjuster_um_id'),
            ]);

            this.contactsLoading = false;
        },

        // ── Client tab ──
        async _loadClientTab(caseItem) {
            if (caseItem.client_id) {
                try {
                    const res = await api.get('clients/' + caseItem.client_id);
                    this.clientForm = { ...res.data };
                    this.clientMatchStatus = 'matched';
                } catch (e) {
                    this.clientForm = this._emptyClientForm();
                    this.clientMatchStatus = 'idle';
                }
                return;
            }
            this.clientForm = this._emptyClientForm();
            this.clientMatchStatus = 'idle';
        },

        _emptyClientForm() {
            return {
                id: null,
                name: '',
                dob: '',
                phone: '',
                email: '',
                address_street: '',
                address_city: '',
                address_state: '',
                address_zip: '',
            };
        },

        // Client search (partial name match)
        async searchClients() {
            if (this.clientSearch.length < 2) {
                this.clientResults = [];
                this.showClientDropdown = false;
                return;
            }
            try {
                const res = await api.get('clients?search=' + encodeURIComponent(this.clientSearch));
                this.clientResults = res.data || [];
                this.showClientDropdown = true;
            } catch (e) {
                this.clientResults = [];
                this.showClientDropdown = true; // still show dropdown for Create option
            }
        },

        async selectClient(client) {
            this.clientForm = { ...client };
            this.clientMatchStatus = 'matched';
            this.clientSearch = client.name;
            this.clientResults = [];
            this.showClientDropdown = false;

            // Auto-link to case
            try {
                await api.post('clients/save', {
                    id: client.id,
                    case_id: this._contactsCaseId,
                    name: client.name,
                });
                if (this._contactsCaseItem) this._contactsCaseItem.client_id = client.id;
                if (this.caseData && this.caseData.id === this._contactsCaseId) {
                    this.caseData.client_id = client.id;
                }
            } catch (e) { /* silent */ }
        },

        startAddClient() {
            this.clientForm = this._emptyClientForm();
            this.clientForm.name = this.clientSearch || '';
            this.clientMatchStatus = 'adding';
            this.clientResults = [];
            this.showClientDropdown = false;
        },

        clearClientMatch() {
            this.clientForm = this._emptyClientForm();
            this.clientMatchStatus = 'idle';
            this.clientSearch = '';
        },

        // ── Adjuster tab ──
        async _loadAdjusterTab(caseItem, tabKey, fkField) {
            const formKey = tabKey === '3rd' ? 'adjuster3rdForm' : 'adjusterUmForm';
            const statusKey = tabKey === '3rd' ? 'adjuster3rdMatchStatus' : 'adjusterUmMatchStatus';
            const adjusterId = caseItem[fkField];

            if (adjusterId) {
                try {
                    const res = await api.get('adjusters/' + adjusterId);
                    this[formKey] = { ...res.data };
                    this[statusKey] = 'matched';
                } catch (e) {
                    this[formKey] = this._emptyAdjusterForm(tabKey);
                    this[statusKey] = 'idle';
                }
                return;
            }
            this[formKey] = this._emptyAdjusterForm(tabKey);
            this[statusKey] = 'idle';
        },

        _emptyAdjusterForm(tabKey) {
            return {
                id: null,
                first_name: '',
                last_name: '',
                title: '',
                insurance_company_id: '',
                company_name: '',
                adjuster_type: tabKey === '3rd' ? '3rd_party' : 'um',
                phone: '',
                fax: '',
                email: '',
            };
        },

        getAdjusterMatchStatus() {
            return this.contactsTab === '3rd' ? this.adjuster3rdMatchStatus : this.adjusterUmMatchStatus;
        },

        getActiveAdjusterForm() {
            return this.contactsTab === '3rd' ? this.adjuster3rdForm : this.adjusterUmForm;
        },

        startAddAdjuster() {
            const statusKey = this.contactsTab === '3rd' ? 'adjuster3rdMatchStatus' : 'adjusterUmMatchStatus';
            const formKey = this.contactsTab === '3rd' ? 'adjuster3rdForm' : 'adjusterUmForm';
            this[formKey] = this._emptyAdjusterForm(this.contactsTab);
            // Pre-fill name from search query
            if (this.adjusterSearchQuery) {
                const parts = this.adjusterSearchQuery.trim().split(/\s+/);
                this[formKey].first_name = parts[0] || '';
                this[formKey].last_name = parts.slice(1).join(' ') || '';
            }
            this[statusKey] = 'adding';
            this.adjusterSearchResults = [];
            this.showAdjusterDropdown = false;
            this.insuranceSearch = '';
            this.insuranceResults = [];
            this.showInsuranceDropdown = false;
            this.customTitle = false;
        },

        clearAdjuster() {
            const statusKey = this.contactsTab === '3rd' ? 'adjuster3rdMatchStatus' : 'adjusterUmMatchStatus';
            const formKey = this.contactsTab === '3rd' ? 'adjuster3rdForm' : 'adjusterUmForm';
            this[formKey] = this._emptyAdjusterForm(this.contactsTab);
            this[statusKey] = 'idle';
            this.adjusterSearchQuery = '';
            this.adjusterSearchResults = [];
            this.showAdjusterDropdown = false;
            this.insuranceSearch = '';
            this.insuranceResults = [];
            this.showInsuranceDropdown = false;
        },

        // ── Save (tab-aware) ──
        async saveContacts() {
            this.contactsSaving = true;
            try {
                if (this.contactsTab === 'client') {
                    await this._saveClientTab();
                } else {
                    await this._saveAdjusterTab(this.contactsTab);
                }
            } catch (e) {
                showToast(e.data?.message || 'Failed to save', 'error');
            }
            this.contactsSaving = false;
        },

        async _saveClientTab() {
            if (!this.clientForm.name || !this.clientForm.name.trim()) {
                this.contactsSaving = false;
                return showToast('Client name is required', 'error');
            }
            const payload = {
                ...this.clientForm,
                case_id: this._contactsCaseId,
            };
            const res = await api.post('clients/save', payload);
            if (res.data && res.data.id) {
                this.clientForm = { ...res.data };
                this.clientMatchStatus = 'matched';
                this.clientSearch = res.data.name;
                if (this.caseData && this.caseData.id === this._contactsCaseId) {
                    this.caseData.client_id = res.data.id;
                }
                if (this._contactsCaseItem) {
                    this._contactsCaseItem.client_id = res.data.id;
                }
                if (this.items) {
                    const item = this.items.find(i => i.id === this._contactsCaseId);
                    if (item) item.client_id = res.data.id;
                }
            }
            showToast(res.message || 'Client saved', 'success');
        },

        async _saveAdjusterTab(tabKey) {
            const formKey = tabKey === '3rd' ? 'adjuster3rdForm' : 'adjusterUmForm';
            const form = this[formKey];
            const statusKey = tabKey === '3rd' ? 'adjuster3rdMatchStatus' : 'adjusterUmMatchStatus';
            if (!form.first_name?.trim() || !form.last_name?.trim()) {
                this.contactsSaving = false;
                return showToast('First name and last name are required', 'error');
            }

            // Resolve insurance company: if user typed a name but didn't select from dropdown
            let insuranceCompanyId = form.insurance_company_id || null;
            const insuranceName = (this.insuranceSearch || '').trim();

            if (!insuranceCompanyId && insuranceName) {
                try {
                    const searchRes = await api.get('insurance-companies?search=' + encodeURIComponent(insuranceName));
                    const companies = searchRes.data || [];
                    const exact = companies.find(c => c.name.toLowerCase() === insuranceName.toLowerCase());
                    if (exact) {
                        insuranceCompanyId = exact.id;
                        form.insurance_company_id = exact.id;
                        form.company_name = exact.name;
                    } else {
                        const createRes = await api.post('insurance-companies', { name: insuranceName });
                        if (createRes.data && createRes.data.id) {
                            insuranceCompanyId = createRes.data.id;
                            form.insurance_company_id = createRes.data.id;
                            form.company_name = insuranceName;
                        }
                    }
                } catch (e) {
                    console.warn('Could not resolve insurance company:', e);
                }
            }

            const linkField = tabKey === '3rd' ? 'adjuster_3rd_id' : 'adjuster_um_id';
            const payload = {
                id: form.id || null,
                first_name: form.first_name,
                last_name: form.last_name,
                title: form.title || '',
                insurance_company_id: insuranceCompanyId,
                phone: form.phone || '',
                fax: form.fax || '',
                email: form.email || '',
                case_id: this._contactsCaseId,
                link_field: linkField,
                adjuster_type: tabKey === '3rd' ? '3rd_party' : 'um',
            };
            const res = await api.post('adjusters/save', payload);
            if (res.data && res.data.id) {
                this[formKey] = { ...res.data };
                this[statusKey] = 'matched';
                this.adjusterSearchQuery = '';
                if (this.caseData && this.caseData.id === this._contactsCaseId) {
                    this.caseData[linkField] = res.data.id;
                }
                if (this._contactsCaseItem) {
                    this._contactsCaseItem[linkField] = res.data.id;
                }
                if (this.items) {
                    const item = this.items.find(i => i.id === this._contactsCaseId);
                    if (item) item[linkField] = res.data.id;
                }
            }
            showToast(res.message || 'Adjuster saved', 'success');
        },

        // ── Adjuster autocomplete ──
        async searchAdjusters(query) {
            this.adjusterSearchQuery = query;
            if (query.length < 2) {
                this.adjusterSearchResults = [];
                this.showAdjusterDropdown = false;
                return;
            }
            try {
                const res = await api.get('adjusters?search=' + encodeURIComponent(query) + '&is_active=1');
                this.adjusterSearchResults = res.data || [];
                this.showAdjusterDropdown = true;
            } catch (e) {
                this.adjusterSearchResults = [];
                this.showAdjusterDropdown = true; // still show for Create option
            }
        },

        selectAdjuster(adj) {
            const formKey = this.contactsTab === '3rd' ? 'adjuster3rdForm' : 'adjusterUmForm';
            const statusKey = this.contactsTab === '3rd' ? 'adjuster3rdMatchStatus' : 'adjusterUmMatchStatus';
            this[formKey] = {
                id: adj.id,
                first_name: adj.first_name,
                last_name: adj.last_name,
                title: adj.title || '',
                insurance_company_id: adj.insurance_company_id || '',
                company_name: adj.company_name || '',
                adjuster_type: adj.adjuster_type || (this.contactsTab === '3rd' ? '3rd_party' : 'um'),
                phone: adj.phone || '',
                fax: adj.fax || '',
                email: adj.email || '',
            };
            this[statusKey] = 'matched';
            this.adjusterSearchQuery = '';
            this.adjusterSearchResults = [];
            this.showAdjusterDropdown = false;

            // Auto-save link to case
            this._saveAdjusterLink(adj.id);
        },

        async _saveAdjusterLink(adjusterId) {
            const tabKey = this.contactsTab;
            const linkField = tabKey === '3rd' ? 'adjuster_3rd_id' : 'adjuster_um_id';
            try {
                const form = tabKey === '3rd' ? this.adjuster3rdForm : this.adjusterUmForm;
                await api.post('adjusters/save', {
                    id: adjusterId,
                    case_id: this._contactsCaseId,
                    link_field: linkField,
                    first_name: form.first_name,
                    last_name: form.last_name,
                    adjuster_type: form.adjuster_type,
                });
                if (this.caseData && this.caseData.id === this._contactsCaseId) {
                    this.caseData[linkField] = adjusterId;
                }
                if (this._contactsCaseItem) {
                    this._contactsCaseItem[linkField] = adjusterId;
                }
            } catch (e) { /* silent */ }
        },

        // ── Insurance Company search (for adding mode) ──
        async searchInsuranceCompanies(query) {
            this.insuranceSearch = query;
            if (query.length < 2) {
                this.insuranceResults = [];
                this.showInsuranceDropdown = false;
                return;
            }
            try {
                const res = await api.get('insurance-companies?search=' + encodeURIComponent(query));
                this.insuranceResults = res.data || [];
                this.showInsuranceDropdown = true;
            } catch (e) {
                this.insuranceResults = [];
                this.showInsuranceDropdown = false;
            }
        },

        selectInsuranceCompany(co) {
            const formKey = this.contactsTab === '3rd' ? 'adjuster3rdForm' : 'adjusterUmForm';
            this[formKey].insurance_company_id = co.id;
            this[formKey].company_name = co.name;
            this.insuranceSearch = co.name;
            this.insuranceResults = [];
            this.showInsuranceDropdown = false;
        },

        async createInsuranceCompany() {
            const name = (this.insuranceSearch || '').trim();
            if (!name) return;
            try {
                const res = await api.post('insurance-companies', { name });
                if (res.data && res.data.id) {
                    this.selectInsuranceCompany({ id: res.data.id, name });
                    showToast('Insurance company "' + name + '" created', 'success');
                }
            } catch (e) {
                showToast(e.data?.message || 'Failed to create insurance company', 'error');
            }
        },

        // Check if current tab can save (only in adding mode)
        canSave() {
            if (this.contactsTab === 'client') {
                return this.clientMatchStatus === 'adding';
            }
            const status = this.getAdjusterMatchStatus();
            return status === 'adding';
        },
    };
}
