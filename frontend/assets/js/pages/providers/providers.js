function providersListPage() {
    return {
        ...listPageBase('providers', {
            defaultSort: '',
            defaultDir: 'asc',
            filtersToParams() {
                return {
                    type: this.typeFilter,
                    difficulty_level: this.difficultyFilter,
                };
            }
        }),

        // Page-specific state
        typeFilter: '',
        difficultyFilter: '',
        showCreateModal: false,
        showDetailModal: false,
        showProviderModal: false,
        saving: false,
        detailProvider: null,
        selectedProvider: null,
        editProvider: {
            id: null, name: '', type: 'hospital', preferred_method: 'fax', address: '', city: '', state: '', zip: '',
            phone: '', fax: '', email: '', portal_url: '', difficulty_level: 'medium', uses_third_party: false, no_record_fee: false,
            third_party_name: '', third_party_contact: '', notes: '', contacts: []
        },
        newProvider: {
            name: '', type: 'hospital', preferred_method: 'fax', address: '', city: '', state: '', zip: '',
            phone: '', fax: '', email: '', portal_url: '', difficulty_level: 'medium', uses_third_party: false, no_record_fee: false,
            third_party_name: '', third_party_contact: '', notes: '', contacts: []
        },
        nameSearchResults: [],
        showNameDropdown: false,
        _nameSearchTimer: null,

        searchProviderName() {
            clearTimeout(this._nameSearchTimer);
            const q = (this.newProvider.name || '').trim();
            if (q.length < 1) { this.nameSearchResults = []; this.showNameDropdown = false; return; }
            this._nameSearchTimer = setTimeout(async () => {
                try {
                    const res = await api.get('providers/search?q=' + encodeURIComponent(q));
                    this.nameSearchResults = Array.isArray(res.data) ? res.data : [];
                    this.showNameDropdown = this.nameSearchResults.length > 0;
                } catch(e) { this.nameSearchResults = []; this.showNameDropdown = false; }
            }, 250);
        },

        async selectExistingProvider(pr) {
            this.showNameDropdown = false;
            this.nameSearchResults = [];
            try {
                const res = await api.get('providers/' + pr.id);
                const p = res.data;
                this.newProvider.name = p.name || '';
                this.newProvider.type = p.type || 'hospital';
                this.newProvider.preferred_method = p.preferred_method || 'fax';
                this.newProvider.address = p.address || '';
                this.newProvider.city = p.city || '';
                this.newProvider.state = p.state || '';
                this.newProvider.zip = p.zip || '';
                this.newProvider.phone = p.phone || '';
                this.newProvider.fax = p.fax || '';
                this.newProvider.email = p.email || '';
                this.newProvider.portal_url = p.portal_url || '';
                this.newProvider.difficulty_level = p.difficulty_level || 'medium';
                this.newProvider.uses_third_party = !!p.uses_third_party;
                this.newProvider.no_record_fee = p.charges_record_fee == 0;
                this.newProvider.third_party_name = p.third_party_name || '';
                this.newProvider.third_party_contact = p.third_party_contact || '';
                this.newProvider.notes = p.notes || '';
                this.newProvider.contacts = p.contacts || [];
                showToast('Provider info loaded', 'success');
            } catch(e) {
                this.newProvider.name = pr.name;
                this.newProvider.type = pr.type || 'hospital';
            }
        },

        _resetPageFilters() {
            this.typeFilter = '';
            this.difficultyFilter = '';
        },

        _hasPageFilters() {
            return this.typeFilter !== '' || this.difficultyFilter !== '';
        },

        getDifficultyStyle(level) {
            const styles = {
                easy:   { background: '#F0FDF4', color: '#166534' },
                medium: { background: '#FFFBEB', color: '#D97706' },
                hard:   { background: '#FEF2F2', color: '#DC2626' }
            };
            return styles[level] || {};
        },

        getAvgColor(days) {
            if (!days) return '#5A6B82';
            if (days > 21) return '#DC2626';
            if (days > 10) return '#D97706';
            return '#166534';
        },

        getContactTypeStyle(type) {
            const styles = {
                email:  { background: '#EFF6FF', color: '#1E40AF' },
                fax:    { background: '#F5F3FF', color: '#6B21A8' },
                phone:  { background: '#ECFEFF', color: '#0E7490' },
                portal: { background: '#FFF7ED', color: '#C2410C' }
            };
            return styles[type] || { background: '#F5F5F0', color: '#5A6B82' };
        },

        async viewProvider(id) {
            try {
                const res = await api.get('providers/' + id);
                this.detailProvider = res.data;
                this.selectedProvider = res.data;
            } catch (e) {
                showToast('Failed to load provider', 'error');
            }
        },

        async deleteProvider(id, name) {
            if (!confirm('Delete "' + name + '"? This cannot be undone.')) return;
            try {
                await api.delete('providers/' + id);
                showToast('Provider deleted');
                this.selectedProvider = null;
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete provider', 'error');
            }
        },

        addContact(target) {
            target.contacts.push({ department: '', contact_type: 'fax', contact_value: '', is_primary: target.contacts.length === 0 ? 1 : 0 });
        },

        removeContact(target, index) {
            const wasPrimary = target.contacts[index].is_primary;
            target.contacts.splice(index, 1);
            if (wasPrimary && target.contacts.length > 0) {
                target.contacts[0].is_primary = 1;
            }
        },

        setPrimary(target, index) {
            target.contacts.forEach((c, i) => c.is_primary = i === index ? 1 : 0);
        },

        closeCreateModal() {
            this.showCreateModal = false;
            this.newProvider = {
                name: '', type: 'hospital', preferred_method: 'fax', address: '', city: '', state: '', zip: '',
                phone: '', fax: '', email: '', portal_url: '', difficulty_level: 'medium', uses_third_party: false, no_record_fee: false,
                third_party_name: '', third_party_contact: '', notes: '', contacts: []
            };
        },

        closeEditModal() {
            this.showProviderModal = false;
            this.editProvider = {
                id: null, name: '', type: 'hospital', preferred_method: 'fax', address: '', city: '', state: '', zip: '',
                phone: '', fax: '', email: '', portal_url: '', difficulty_level: 'medium', uses_third_party: false, no_record_fee: false,
                third_party_name: '', third_party_contact: '', notes: '', contacts: []
            };
        },

        async createProvider() {
            this.saving = true;
            try {
                const data = { ...this.newProvider };
                data.uses_third_party = data.uses_third_party ? 1 : 0;
                data.charges_record_fee = data.no_record_fee ? 0 : 1;
                delete data.no_record_fee;
                data.contacts = [...(this.newProvider.contacts || [])];
                await api.post('providers', data);
                showToast('Provider created successfully');
                this.closeCreateModal();
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to create provider', 'error');
            }
            this.saving = false;
        },

        exportCSV() {
            const params = new URLSearchParams();
            if (this.typeFilter) params.set('type', this.typeFilter);
            if (this.difficultyFilter) params.set('difficulty_level', this.difficultyFilter);
            if (this.search) params.set('search', this.search);
            const qs = params.toString();
            window.location.href = '/blcm/backend/api/providers/export' + (qs ? '?' + qs : '');
        },

        async updateProvider() {
            if (!this.editProvider.id) return;
            this.saving = true;
            try {
                const data = { ...this.editProvider };
                data.uses_third_party = data.uses_third_party ? 1 : 0;
                data.charges_record_fee = data.no_record_fee ? 0 : 1;
                delete data.no_record_fee;
                data.contacts = [...(this.editProvider.contacts || [])].map(c => ({...c}));
                await api.put('providers/' + data.id, data);
                showToast('Provider updated successfully');
                this.closeEditModal();
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to update provider', 'error');
            }
            this.saving = false;
        }
    };
}
