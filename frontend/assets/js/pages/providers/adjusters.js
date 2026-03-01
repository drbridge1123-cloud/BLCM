function adjustersListPage() {
    return {
        ...listPageBase('adjusters', {
            defaultSort: 'last_name',
            defaultDir: 'asc',
            filtersToParams() {
                return {
                    insurance_company_id: this.companyFilter,
                    adjuster_type: this.typeFilter,
                    is_active: this.activeFilter,
                };
            }
        }),

        companyFilter: '',
        typeFilter: '',
        activeFilter: '',
        insuranceCompanies: [],
        showCreateModal: false,
        showEditModal: false,
        saving: false,
        selectedAdjuster: null,

        newAdjuster: { first_name: '', last_name: '', title: '', adjuster_type: '', insurance_company_id: '', phone: '', fax: '', email: '', notes: '' },
        editAdjuster: { id: null, first_name: '', last_name: '', title: '', adjuster_type: '', insurance_company_id: '', phone: '', fax: '', email: '', notes: '', is_active: 1 },

        async init() {
            try {
                const res = await api.get('insurance-companies?sort_by=name&sort_dir=asc');
                this.insuranceCompanies = res.data || [];
            } catch (e) { /* ignore */ }
            this.loadData();
        },

        _resetPageFilters() { this.companyFilter = ''; this.typeFilter = ''; this.activeFilter = ''; },
        _hasPageFilters() { return this.companyFilter !== '' || this.typeFilter !== '' || this.activeFilter !== ''; },

        getTypeLabel(type) {
            const labels = { pip: 'PIP', um: 'UM', uim: 'UIM', '3rd_party': '3rd Party', liability: 'Liability', pd: 'PD', bi: 'BI' };
            return labels[type] || type || '-';
        },

        getCompanyName(id) {
            const c = this.insuranceCompanies.find(c => c.id == id);
            return c ? c.name : '-';
        },

        async viewAdjuster(id) {
            try {
                const res = await api.get('adjusters/' + id);
                this.selectedAdjuster = res.data;
            } catch (e) {
                showToast('Failed to load adjuster', 'error');
            }
        },

        openEditModal() {
            if (!this.selectedAdjuster) return;
            const a = this.selectedAdjuster;
            this.editAdjuster = {
                id: a.id, first_name: a.first_name || '', last_name: a.last_name || '',
                title: a.title || '', adjuster_type: a.adjuster_type || '',
                insurance_company_id: a.insurance_company_id || '',
                phone: a.phone || '', fax: a.fax || '', email: a.email || '',
                notes: a.notes || '', is_active: a.is_active
            };
            this.showEditModal = true;
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.newAdjuster = { first_name: '', last_name: '', title: '', adjuster_type: '', insurance_company_id: '', phone: '', fax: '', email: '', notes: '' };
        },

        closeEditModal() {
            this.showEditModal = false;
            this.editAdjuster = { id: null, first_name: '', last_name: '', title: '', adjuster_type: '', insurance_company_id: '', phone: '', fax: '', email: '', notes: '', is_active: 1 };
        },

        async createAdjuster() {
            this.saving = true;
            try {
                const data = { ...this.newAdjuster };
                if (!data.insurance_company_id) delete data.insurance_company_id;
                await api.post('adjusters', data);
                showToast('Adjuster created');
                this.closeCreateModal();
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to create', 'error');
            }
            this.saving = false;
        },

        async updateAdjuster() {
            if (!this.editAdjuster.id) return;
            this.saving = true;
            try {
                const data = { ...this.editAdjuster };
                if (!data.insurance_company_id) data.insurance_company_id = null;
                await api.put('adjusters/' + data.id, data);
                showToast('Adjuster updated');
                this.closeEditModal();
                this.selectedAdjuster = null;
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to update', 'error');
            }
            this.saving = false;
        },

        async deleteAdjuster(id, name) {
            if (!confirm('Delete "' + name + '"? This cannot be undone.')) return;
            try {
                await api.delete('adjusters/' + id);
                showToast('Adjuster deleted');
                this.selectedAdjuster = null;
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete', 'error');
            }
        },

        async toggleActive(id, currentState) {
            try {
                await api.put('adjusters/' + id, { is_active: currentState ? 0 : 1 });
                showToast(currentState ? 'Adjuster deactivated' : 'Adjuster activated');
                this.selectedAdjuster = null;
                this.loadData();
            } catch (e) {
                showToast('Failed to update status', 'error');
            }
        }
    };
}
