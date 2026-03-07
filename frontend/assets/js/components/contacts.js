/**
 * Contacts Modal Mixin (Client + Dynamic Adjuster Tabs)
 * Supports multiple instances of same coverage type via coverage_index.
 * e.g., two 3rd Party adjusters for multi-party accidents.
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

        // Client
        clientForm: {},
        clientMatchStatus: 'idle',
        clientSearch: '',
        clientResults: [],
        showClientDropdown: false,

        // Dynamic adjuster tabs (each has tabKey = "type_index")
        adjusterTabs: [],
        showAddTabMenu: false,

        // Adjuster search (shared, reset on tab switch)
        adjusterSearchQuery: '',
        adjusterSearchResults: [],
        showAdjusterDropdown: false,

        // Insurance company search
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

        coverageLabels: {
            '3rd_party': '3rd Party',
            'um': 'UM',
            'uim': 'UIM',
            'pip': 'PIP',
            'pd': 'PD',
            'dv': 'DV',
        },

        coverageTags: {
            '3rd_party': 'BI',
            'um': 'Uninsured',
            'uim': 'Underinsured',
            'pip': 'Personal Injury Protection',
            'pd': 'Property',
            'dv': 'Diminished Value',
        },

        allCoverageTypes: ['3rd_party','um','uim','pip','pd','dv'],

        getActiveTab() {
            return this.adjusterTabs.find(t => t.tabKey === this.contactsTab) || null;
        },

        _getTabLabel(tab) {
            const baseLabel = this.coverageLabels[tab.coverage_type] || tab.coverage_type;
            const sameType = this.adjusterTabs.filter(t => t.coverage_type === tab.coverage_type);
            if (sameType.length > 1) {
                return `${baseLabel} (${tab.coverage_index})`;
            }
            return baseLabel;
        },

        async openContacts(caseItem) {
            this._contactsCaseId = caseItem.id;
            this._contactsCaseItem = caseItem;
            this.contactsTab = 'client';
            this.contactsLoading = true;
            this.showContactsModal = true;
            this.clientForm = {};
            this.adjusterTabs = [];
            this.clientMatchStatus = 'idle';
            this.clientSearch = '';
            this.clientResults = [];
            this.showClientDropdown = false;
            this._resetAdjusterSearch();
            this.showAddTabMenu = false;

            await Promise.all([
                this._loadClientTab(caseItem),
                this._loadAdjusterTabs(caseItem.id),
            ]);

            this.contactsLoading = false;
        },

        // ── Load adjuster tabs from case_adjusters ──
        async _loadAdjusterTabs(caseId) {
            try {
                // Load adjusters and negotiations in parallel
                const [adjRes, negRes] = await Promise.all([
                    api.get('case-adjusters?case_id=' + caseId),
                    api.get('negotiations/' + caseId),
                ]);
                const rows = adjRes.data || [];
                // Build set of negotiate keys from existing negotiation tabs
                const negKeys = new Set();
                if (negRes.success && negRes.tabs) {
                    negRes.tabs.forEach(t => negKeys.add(t.key));
                }
                this.adjusterTabs = rows.map(r => {
                    const tabKey = r.coverage_type + '_' + (r.coverage_index || 1);
                    return {
                        coverage_type: r.coverage_type,
                        coverage_index: r.coverage_index || 1,
                        tabKey,
                        caseAdjusterId: r.id,
                        matchStatus: 'matched',
                        negotiateChecked: negKeys.has(tabKey),
                        form: {
                            id: r.adjuster_id,
                            first_name: r.first_name || '',
                            last_name: r.last_name || '',
                            title: r.title || '',
                            insurance_company_id: r.insurance_company_id || '',
                            company_name: r.company_name || '',
                            phone: r.phone || '',
                            fax: r.fax || '',
                            email: r.email || '',
                            claim_number: r.claim_number || '',
                        },
                    };
                });
            } catch (e) {
                this.adjusterTabs = [];
            }
        },

        addAdjusterTab(coverageType) {
            const existing = this.adjusterTabs.filter(t => t.coverage_type === coverageType);
            const nextIndex = existing.length > 0 ? Math.max(...existing.map(t => t.coverage_index)) + 1 : 1;
            const tabKey = coverageType + '_' + nextIndex;

            // If adding a second of same type, relabel the first one
            if (existing.length === 1) {
                // Force reactivity by touching label-dependent rendering
            }

            this.adjusterTabs.push({
                coverage_type: coverageType,
                coverage_index: nextIndex,
                tabKey,
                caseAdjusterId: null,
                matchStatus: 'idle',
                negotiateChecked: false,
                form: this._emptyAdjusterForm(),
            });
            this.contactsTab = tabKey;
            this.showAddTabMenu = false;
            this._resetAdjusterSearch();
        },

        async toggleNegotiate(tab) {
            if (!tab || !tab.caseAdjusterId) return;
            const caseId = this._contactsCaseId;
            if (tab.negotiateChecked) {
                // Create local tab in negotiate panel (no DB round yet)
                document.dispatchEvent(new CustomEvent('negotiate-add-tab', { detail: {
                    coverage_type: tab.coverage_type,
                    coverage_index: tab.coverage_index,
                    insurance_company: tab.form.company_name || '',
                    party: ((tab.form.first_name || '') + ' ' + (tab.form.last_name || '')).trim(),
                    adjuster_phone: tab.form.phone || '',
                    adjuster_fax: tab.form.fax || '',
                    adjuster_email: tab.form.email || '',
                    claim_number: tab.form.claim_number || '',
                }}));
            } else {
                // Remove negotiate tab — delete all rounds for this group
                try {
                    await api.delete(`negotiations/group/${caseId}/${tab.coverage_type}/${tab.coverage_index}`);
                } catch (e) { /* may not have DB rounds yet */ }
                document.dispatchEvent(new CustomEvent('negotiate-remove-tab', { detail: {
                    coverage_type: tab.coverage_type,
                    coverage_index: tab.coverage_index,
                }}));
            }
        },

        async removeAdjusterTab(tabKey) {
            const tab = this.adjusterTabs.find(t => t.tabKey === tabKey);
            if (!tab) return;
            if (tab.caseAdjusterId) {
                try {
                    await api.delete('case-adjusters/' + tab.caseAdjusterId);
                } catch (e) { /* silent */ }
            }
            this.adjusterTabs = this.adjusterTabs.filter(t => t.tabKey !== tabKey);
            if (this.contactsTab === tabKey) {
                this.contactsTab = 'client';
            }
            // Notify negotiate panel
            document.dispatchEvent(new CustomEvent('adjuster-removed', {
                detail: { coverage_type: tab.coverage_type, coverage_index: tab.coverage_index }
            }));
        },

        switchAdjusterTab(tabKey) {
            this.contactsTab = tabKey;
            this._resetAdjusterSearch();
            const tab = this.getActiveTab();
            if (tab && (tab.matchStatus === 'adding' || tab.matchStatus === 'matched')) {
                this.insuranceSearch = tab.form.company_name || '';
            }
        },

        _resetAdjusterSearch() {
            this.adjusterSearchQuery = '';
            this.adjusterSearchResults = [];
            this.showAdjusterDropdown = false;
            this.insuranceSearch = '';
            this.insuranceResults = [];
            this.showInsuranceDropdown = false;
            this.customTitle = false;
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

        _emptyAdjusterForm() {
            return {
                id: null,
                first_name: '',
                last_name: '',
                title: '',
                insurance_company_id: '',
                company_name: '',
                phone: '',
                fax: '',
                email: '',
                claim_number: '',
            };
        },

        // Client search
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
                this.showClientDropdown = true;
            }
        },

        async selectClient(client) {
            this.clientForm = { ...client };
            this.clientMatchStatus = 'matched';
            this.clientSearch = client.name;
            this.clientResults = [];
            this.showClientDropdown = false;

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

        // ── Adjuster tab helpers ──
        getAdjusterMatchStatus() {
            const tab = this.getActiveTab();
            return tab ? tab.matchStatus : 'idle';
        },

        getActiveAdjusterForm() {
            const tab = this.getActiveTab();
            return tab ? tab.form : this._emptyAdjusterForm();
        },

        startAddAdjuster() {
            const tab = this.getActiveTab();
            if (!tab) return;
            tab.form = this._emptyAdjusterForm();
            if (this.adjusterSearchQuery) {
                const parts = this.adjusterSearchQuery.trim().split(/\s+/);
                tab.form.first_name = parts[0] || '';
                tab.form.last_name = parts.slice(1).join(' ') || '';
            }
            tab.matchStatus = 'adding';
            this.adjusterSearchResults = [];
            this.showAdjusterDropdown = false;
            this.insuranceSearch = '';
            this.insuranceResults = [];
            this.showInsuranceDropdown = false;
            this.customTitle = false;
        },

        clearAdjuster() {
            const tab = this.getActiveTab();
            if (!tab) return;
            tab.form = this._emptyAdjusterForm();
            tab.matchStatus = 'idle';
            this._resetAdjusterSearch();
        },

        // ── Save ──
        async saveContacts() {
            this.contactsSaving = true;
            try {
                if (this.contactsTab === 'client') {
                    await this._saveClientTab();
                } else {
                    await this._saveAdjusterTab();
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

        async _saveAdjusterTab() {
            const tab = this.getActiveTab();
            if (!tab) return;
            const form = tab.form;
            if (!form.first_name?.trim() || !form.last_name?.trim()) {
                this.contactsSaving = false;
                return showToast('First name and last name are required', 'error');
            }

            // Resolve insurance company
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

            const payload = {
                case_id: this._contactsCaseId,
                coverage_type: tab.coverage_type,
                coverage_index: tab.coverage_index,
                id: form.id || null,
                first_name: form.first_name,
                last_name: form.last_name,
                title: form.title || '',
                insurance_company_id: insuranceCompanyId,
                phone: form.phone || '',
                fax: form.fax || '',
                email: form.email || '',
                claim_number: form.claim_number || '',
            };

            const res = await api.post('case-adjusters', payload);
            if (res.data && res.data.id) {
                tab.form = {
                    id: res.data.id,
                    first_name: res.data.first_name || '',
                    last_name: res.data.last_name || '',
                    title: res.data.title || '',
                    insurance_company_id: res.data.insurance_company_id || '',
                    company_name: res.data.company_name || '',
                    phone: res.data.phone || '',
                    fax: res.data.fax || '',
                    email: res.data.email || '',
                    claim_number: res.data.claim_number || '',
                };
                tab.matchStatus = 'matched';
                if (res.data.case_adjuster_id) {
                    tab.caseAdjusterId = res.data.case_adjuster_id;
                }
                this.adjusterSearchQuery = '';
            }
            showToast(res.message || 'Adjuster saved', 'success');

            // Notify negotiate panel
            this._dispatchAdjusterSaved(tab);
        },

        _dispatchAdjusterSaved(tab) {
            document.dispatchEvent(new CustomEvent('adjuster-saved', { detail: {
                coverage_type: tab.coverage_type,
                coverage_index: tab.coverage_index,
                insurance_company: tab.form.company_name || '',
                party: ((tab.form.first_name || '') + ' ' + (tab.form.last_name || '')).trim(),
                adjuster_phone: tab.form.phone || '',
                adjuster_fax: tab.form.fax || '',
                adjuster_email: tab.form.email || '',
                claim_number: tab.form.claim_number || '',
            }}));
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
                this.showAdjusterDropdown = true;
            }
        },

        selectAdjuster(adj) {
            const tab = this.getActiveTab();
            if (!tab) return;
            tab.form = {
                id: adj.id,
                first_name: adj.first_name || '',
                last_name: adj.last_name || '',
                title: adj.title || '',
                insurance_company_id: adj.insurance_company_id || '',
                company_name: adj.company_name || '',
                phone: adj.phone || '',
                fax: adj.fax || '',
                email: adj.email || '',
                claim_number: adj.claim_number || '',
            };
            tab.matchStatus = 'matched';
            this.adjusterSearchQuery = '';
            this.adjusterSearchResults = [];
            this.showAdjusterDropdown = false;

            // Auto-save link to case
            this._saveAdjusterLink(tab);
        },

        async _saveAdjusterLink(tab) {
            try {
                const res = await api.post('case-adjusters', {
                    case_id: this._contactsCaseId,
                    coverage_type: tab.coverage_type,
                    coverage_index: tab.coverage_index,
                    adjuster_id: tab.form.id,
                });
                if (res.data && res.data.case_adjuster_id) {
                    tab.caseAdjusterId = res.data.case_adjuster_id;
                }
                this._dispatchAdjusterSaved(tab);
            } catch (e) { /* silent */ }
        },

        // ── Insurance Company search ──
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
            const tab = this.getActiveTab();
            if (tab) {
                tab.form.insurance_company_id = co.id;
                tab.form.company_name = co.name;
            }
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

        canSave() {
            if (this.contactsTab === 'client') {
                return this.clientMatchStatus === 'adding';
            }
            const tab = this.getActiveTab();
            return tab && tab.matchStatus === 'adding';
        },
    };
}
