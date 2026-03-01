/**
 * Data Management — centralised import / export hub
 */
function dataManagementPage() {
    return {
        counts: {},
        loading: true,
        staffList: [],

        // Filter state per export card
        filters: {
            cases:         { staff: '' },
            commissions:   { staff: '' },
            attorneyCases: { staff: '' },
            expenseReport: { staff: '' },
            referrals:     { staff: '' },
        },

        async init() {
            await Promise.all([this.loadCounts(), this.loadStaff()]);
        },

        async loadStaff() {
            try {
                const res = await api.get('users?active_only=1');
                this.staffList = res.data || [];
            } catch (e) { this.staffList = []; }
        },

        async loadCounts() {
            this.loading = true;
            try {
                const [cases, providers, insuranceCo, adjusters, templates, healthLedger, commissions, referrals, bankEntries, attorneyCases] = await Promise.allSettled([
                    api.get('cases?per_page=1'),
                    api.get('providers?per_page=1'),
                    api.get('insurance-companies'),
                    api.get('adjusters'),
                    api.get('templates'),
                    api.get('health-ledger?per_page=1'),
                    api.get('commissions?per_page=1'),
                    api.get('referrals?per_page=1'),
                    api.get('bank-reconciliation/entries?per_page=1'),
                    api.get('attorney-cases'),
                ]);

                this.counts = {
                    cases:        cases.status === 'fulfilled' ? (cases.value.pagination?.total ?? (cases.value.data || []).length) : '—',
                    providers:    providers.status === 'fulfilled' ? (providers.value.pagination?.total ?? (providers.value.data || []).length) : '—',
                    insurance:    insuranceCo.status === 'fulfilled' ? (insuranceCo.value.data || []).length : '—',
                    adjusters:    adjusters.status === 'fulfilled' ? (adjusters.value.data || []).length : '—',
                    templates:    templates.status === 'fulfilled' ? (templates.value.data || []).length : '—',
                    healthLedger: healthLedger.status === 'fulfilled' ? (healthLedger.value.pagination?.total ?? (healthLedger.value.data || []).length) : '—',
                    commissions:  commissions.status === 'fulfilled' ? (commissions.value.pagination?.total ?? (commissions.value.data || []).length) : '—',
                    referrals:    referrals.status === 'fulfilled' ? (referrals.value.pagination?.total ?? (referrals.value.data || []).length) : '—',
                    bankEntries:  bankEntries.status === 'fulfilled' ? (bankEntries.value.pagination?.total ?? (bankEntries.value.data || []).length) : '—',
                    attorneyCases: attorneyCases.status === 'fulfilled' ? (attorneyCases.value.pagination?.total ?? (attorneyCases.value.data || []).length) : '—',
                };
            } catch (e) {
                console.error('Data management counts error', e);
            }
            this.loading = false;
        },

        // ── Helper: build query string from params object ──
        _qs(params) {
            const p = new URLSearchParams();
            Object.entries(params).forEach(([k, v]) => { if (v) p.set(k, v); });
            const s = p.toString();
            return s ? '?' + s : '';
        },

        // ── Exports (server-side CSV download) ──

        exportCases() {
            const q = this._qs({ assigned_to: this.filters.cases.staff });
            window.location.href = '/CMC/backend/api/cases/export' + q;
        },
        exportProviders() {
            window.location.href = '/CMC/backend/api/providers/export';
        },
        exportCommissions() {
            const q = this._qs({ employee_id: this.filters.commissions.staff });
            window.location.href = '/CMC/backend/api/commissions/export' + q;
        },
        exportExpenseReport() {
            const q = this._qs({ staff_id: this.filters.expenseReport.staff });
            window.location.href = '/CMC/backend/api/expense-report/export' + q;
        },
        exportAttorneyCases() {
            const q = this._qs({ attorney_user_id: this.filters.attorneyCases.staff });
            window.location.href = '/CMC/backend/api/attorney-cases/export' + q;
        },
        exportReferrals() {
            const q = this._qs({ lead_id: this.filters.referrals.staff });
            window.location.href = '/CMC/backend/api/referrals/export' + q;
        },

        // ── Imports ──

        async importHealthLedger(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (!file.name.toLowerCase().endsWith('.csv')) { showToast('Please select a CSV file', 'error'); event.target.value = ''; return; }
            try {
                const formData = new FormData();
                formData.append('file', file);
                const res = await fetch('/CMC/backend/api/health-ledger/import', { method: 'POST', body: formData });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Import failed');
                showToast(data.message || 'Import complete', 'success');
                this.loadCounts();
            } catch (e) { showToast(e.message, 'error'); }
            event.target.value = '';
        },

        async importBankStatements(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (!file.name.toLowerCase().endsWith('.csv')) { showToast('Please select a CSV file', 'error'); event.target.value = ''; return; }
            try {
                const formData = new FormData();
                formData.append('file', file);
                const res = await fetch('/CMC/backend/api/bank-reconciliation/import', { method: 'POST', body: formData });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Import failed');
                showToast(data.message || 'Import complete', 'success');
                this.loadCounts();
            } catch (e) { showToast(e.message, 'error'); }
            event.target.value = '';
        },

        async importCostLedger(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (!file.name.toLowerCase().endsWith('.csv')) { showToast('Please select a CSV file', 'error'); event.target.value = ''; return; }
            const caseNumber = prompt('Enter the Case Number for this import:');
            if (!caseNumber) { event.target.value = ''; return; }
            try {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('case_number', caseNumber);
                const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token') || '';
                const res = await fetch('/CMC/backend/api/mr-fee-payments/import', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token },
                    body: formData
                });
                const data = await res.json();
                if (!res.ok && !data.success) throw new Error(data.message || data.error || 'Import failed');
                showToast(data.message || `Imported ${data.imported || 0} cost entries`, 'success');
                this.loadCounts();
            } catch (e) { showToast(e.message, 'error'); }
            event.target.value = '';
        },

        async importMbdsReport(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (!file.name.toLowerCase().endsWith('.csv')) { showToast('Please select a CSV file', 'error'); event.target.value = ''; return; }
            const caseNumber = prompt('Enter the Case Number for this import:');
            if (!caseNumber) { event.target.value = ''; return; }
            try {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('case_number', caseNumber);
                const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token') || '';
                const res = await fetch('/CMC/backend/api/mbds/import', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token },
                    body: formData
                });
                const data = await res.json();
                if (!res.ok && !data.success) throw new Error(data.message || data.error || 'Import failed');
                showToast(data.message || `Imported ${data.imported || 0} MBDS lines`, 'success');
                this.loadCounts();
            } catch (e) { showToast(e.message, 'error'); }
            event.target.value = '';
        },

        // ── Template downloads ──

        downloadCasesTemplate() {
            window.location.href = '/CMC/backend/api/cases/export?template=1';
        },
        downloadProvidersTemplate() {
            window.location.href = '/CMC/backend/api/providers/export?template=1';
        },
    };
}
