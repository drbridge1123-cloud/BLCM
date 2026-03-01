function insuranceListPage() {
    return {
        ...listPageBase('insurance-companies', {
            defaultSort: 'name',
            defaultDir: 'asc',
            filtersToParams() {
                return {
                    type: this.typeFilter,
                };
            }
        }),

        typeFilter: '',
        showCreateModal: false,
        showEditModal: false,
        saving: false,
        selectedCompany: null,

        newCompany: { name: '', type: 'auto', phone: '', fax: '', email: '', address: '', city: '', state: '', zip: '', website: '', notes: '' },
        editCompany: { id: null, name: '', type: 'auto', phone: '', fax: '', email: '', address: '', city: '', state: '', zip: '', website: '', notes: '' },

        _resetPageFilters() { this.typeFilter = ''; },
        _hasPageFilters() { return this.typeFilter !== ''; },

        getInsuranceTypeLabel(type) {
            const labels = { auto: 'Auto', health: 'Health', workers_comp: "Worker's Comp", liability: 'Liability', um_uim: 'UM/UIM', government: 'Government', other: 'Other' };
            return labels[type] || type || '-';
        },

        getTypeColor(type) {
            const colors = {
                auto: { bg: '#EFF6FF', color: '#1E40AF' },
                health: { bg: '#F0FDF4', color: '#166534' },
                workers_comp: { bg: '#FFFBEB', color: '#D97706' },
                liability: { bg: '#FEF2F2', color: '#DC2626' },
                um_uim: { bg: '#F5F3FF', color: '#6B21A8' },
                government: { bg: '#ECFEFF', color: '#0E7490' },
                other: { bg: '#F5F5F0', color: '#5A6B82' },
            };
            return colors[type] || colors.other;
        },

        async viewCompany(id) {
            try {
                const res = await api.get('insurance-companies/' + id);
                this.selectedCompany = res.data;
            } catch (e) {
                showToast('Failed to load insurance company', 'error');
            }
        },

        openEditModal() {
            if (!this.selectedCompany) return;
            const c = this.selectedCompany;
            this.editCompany = {
                id: c.id, name: c.name || '', type: c.type || 'auto',
                phone: c.phone || '', fax: c.fax || '', email: c.email || '',
                address: c.address || '', city: c.city || '', state: c.state || '',
                zip: c.zip || '', website: c.website || '', notes: c.notes || ''
            };
            this.showEditModal = true;
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.newCompany = { name: '', type: 'auto', phone: '', fax: '', email: '', address: '', city: '', state: '', zip: '', website: '', notes: '' };
        },

        closeEditModal() {
            this.showEditModal = false;
            this.editCompany = { id: null, name: '', type: 'auto', phone: '', fax: '', email: '', address: '', city: '', state: '', zip: '', website: '', notes: '' };
        },

        async createCompany() {
            this.saving = true;
            try {
                await api.post('insurance-companies', { ...this.newCompany });
                showToast('Insurance company created');
                this.closeCreateModal();
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to create', 'error');
            }
            this.saving = false;
        },

        async updateCompany() {
            if (!this.editCompany.id) return;
            this.saving = true;
            try {
                await api.put('insurance-companies/' + this.editCompany.id, { ...this.editCompany });
                showToast('Insurance company updated');
                this.closeEditModal();
                this.selectedCompany = null;
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to update', 'error');
            }
            this.saving = false;
        },

        async deleteCompany(id, name) {
            if (!confirm('Delete "' + name + '"? This cannot be undone.')) return;
            try {
                await api.delete('insurance-companies/' + id);
                showToast('Insurance company deleted');
                this.selectedCompany = null;
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete', 'error');
            }
        }
    };
}
