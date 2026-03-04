/**
 * Client Info Modal Mixin
 * Spread into any Alpine component: ...clientInfoMixin()
 */
function clientInfoMixin() {
    return {
        showClientInfoModal: false,
        clientInfoLoading: false,
        clientInfoSaving: false,
        clientInfoForm: {},
        _clientInfoCaseId: null,

        async openClientInfo(caseItem) {
            this._clientInfoCaseId = caseItem.id;
            this.clientInfoLoading = true;
            this.showClientInfoModal = true;
            this.clientInfoForm = {};

            if (caseItem.client_id) {
                // Already linked — fetch client data
                try {
                    const res = await api.get('clients/' + caseItem.client_id);
                    this.clientInfoForm = { ...res.data };
                } catch (e) {
                    showToast('Failed to load client info', 'error');
                    this.showClientInfoModal = false;
                }
                this.clientInfoLoading = false;
                return;
            }

            // Not linked — try auto-match by name + dob
            try {
                const params = new URLSearchParams();
                if (caseItem.client_name) params.set('name', caseItem.client_name);
                if (caseItem.client_dob) params.set('dob', caseItem.client_dob);

                if (caseItem.client_name) {
                    const res = await api.get('clients/lookup?' + params.toString());
                    if (res.data) {
                        // Match found — auto-link + show
                        this.clientInfoForm = { ...res.data };
                        await api.post('clients/save', {
                            id: res.data.id,
                            case_id: caseItem.id,
                            name: res.data.name,
                        });
                        caseItem.client_id = res.data.id;
                        if (this.caseData && this.caseData.id === caseItem.id) {
                            this.caseData.client_id = res.data.id;
                        }
                        this.clientInfoLoading = false;
                        return;
                    }
                }
            } catch (e) {
                // Lookup failed — fall through to add form
            }

            // No match — pre-fill "Add Client" form from case data
            this.clientInfoForm = {
                id: null,
                name: caseItem.client_name || '',
                dob: caseItem.client_dob || '',
                phone: caseItem.client_phone || '',
                email: caseItem.client_email || '',
                address_street: '',
                address_city: '',
                address_state: '',
                address_zip: '',
            };
            this.clientInfoLoading = false;
        },

        async saveClientInfo() {
            if (!this.clientInfoForm.name || !this.clientInfoForm.name.trim()) {
                return showToast('Client name is required', 'error');
            }
            this.clientInfoSaving = true;
            try {
                const payload = {
                    ...this.clientInfoForm,
                    case_id: this._clientInfoCaseId,
                };
                const res = await api.post('clients/save', payload);
                showToast(res.message || 'Client saved', 'success');
                this.showClientInfoModal = false;

                // Update local data
                if (res.data && res.data.id) {
                    this.clientInfoForm.id = res.data.id;
                    if (this.caseData && this.caseData.id === this._clientInfoCaseId) {
                        this.caseData.client_id = res.data.id;
                    }
                    if (this.items) {
                        const item = this.items.find(i => i.id === this._clientInfoCaseId);
                        if (item) item.client_id = res.data.id;
                    }
                }
            } catch (e) {
                showToast(e.data?.message || 'Failed to save client', 'error');
            }
            this.clientInfoSaving = false;
        },
    };
}
