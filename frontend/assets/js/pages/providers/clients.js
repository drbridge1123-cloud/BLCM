function clientsListPage() {
    return {
        ...listPageBase('clients', {
            defaultSort: 'name',
            defaultDir: 'asc',
        }),

        showCreateModal: false,
        showEditModal: false,
        saving: false,
        selectedClient: null,

        newClient: { name: '', dob: '', phone: '', email: '', address_street: '', address_city: '', address_state: '', address_zip: '' },
        editClient: { id: null, name: '', dob: '', phone: '', email: '', address_street: '', address_city: '', address_state: '', address_zip: '' },

        _resetPageFilters() {},
        _hasPageFilters() { return false; },

        async viewClient(id) {
            try {
                const res = await api.get('clients/' + id);
                this.selectedClient = res.data;
            } catch (e) {
                showToast('Failed to load client', 'error');
            }
        },

        openEditModal() {
            if (!this.selectedClient) return;
            const c = this.selectedClient;
            this.editClient = {
                id: c.id,
                name: c.name || '',
                dob: c.dob || '',
                phone: c.phone || '',
                email: c.email || '',
                address_street: c.address_street || '',
                address_city: c.address_city || '',
                address_state: c.address_state || '',
                address_zip: c.address_zip || '',
            };
            this.showEditModal = true;
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.newClient = { name: '', dob: '', phone: '', email: '', address_street: '', address_city: '', address_state: '', address_zip: '' };
        },

        closeEditModal() {
            this.showEditModal = false;
            this.editClient = { id: null, name: '', dob: '', phone: '', email: '', address_street: '', address_city: '', address_state: '', address_zip: '' };
        },

        async createClient() {
            this.saving = true;
            try {
                await api.post('clients/save', { ...this.newClient });
                showToast('Client created');
                this.closeCreateModal();
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to create client', 'error');
            }
            this.saving = false;
        },

        async updateClient() {
            if (!this.editClient.id) return;
            this.saving = true;
            try {
                await api.post('clients/save', { ...this.editClient });
                showToast('Client updated');
                this.closeEditModal();
                this.selectedClient = null;
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to update client', 'error');
            }
            this.saving = false;
        },

        async deleteClient(id, name) {
            if (!confirm('Delete "' + name + '"? This cannot be undone.')) return;
            try {
                await api.delete('clients/' + id);
                showToast('Client deleted');
                this.selectedClient = null;
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete client', 'error');
            }
        },

        formatDob(dob) {
            if (!dob) return '—';
            return formatDate(dob) || '—';
        },

        formatAddress(c) {
            const parts = [c.address_street, c.address_city, c.address_state].filter(Boolean);
            if (c.address_zip && parts.length > 0) {
                return parts.join(', ') + ' ' + c.address_zip;
            }
            return parts.join(', ') || '—';
        }
    };
}
