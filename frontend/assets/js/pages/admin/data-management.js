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
                    api.get('bl-cases?per_page=1'),
                    api.get('providers?per_page=1'),
                    api.get('insurance-companies'),
                    api.get('adjusters'),
                    api.get('templates'),
                    api.get('health-ledger?per_page=1'),
                    api.get('commissions?per_page=1'),
                    api.get('referrals?per_page=1'),
                    api.get('bank-reconciliation?per_page=1'),
                    api.get('attorney'),
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
            window.location.href = '/CMCdemo/backend/api/cases/export' + q;
        },
        exportProviders() {
            window.location.href = '/CMCdemo/backend/api/providers/export';
        },
        exportCommissions() {
            const q = this._qs({ employee_id: this.filters.commissions.staff });
            window.location.href = '/CMCdemo/backend/api/commissions/export' + q;
        },
        exportExpenseReport() {
            const q = this._qs({ staff_id: this.filters.expenseReport.staff });
            window.location.href = '/CMCdemo/backend/api/expense-report/export' + q;
        },
        exportAttorneyCases() {
            const q = this._qs({ attorney_user_id: this.filters.attorneyCases.staff });
            window.location.href = '/CMCdemo/backend/api/attorney/export' + q;
        },
        exportReferrals() {
            const q = this._qs({ lead_id: this.filters.referrals.staff });
            window.location.href = '/CMCdemo/backend/api/referrals/export' + q;
        },

        // ── Imports ──

        async importAttorneyCases(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (!file.name.toLowerCase().endsWith('.csv')) { showToast('Please select a CSV file', 'error'); event.target.value = ''; return; }
            if (!confirm(`Import attorney cases from "${file.name}"? Existing cases with matching case numbers will be updated.`)) { event.target.value = ''; return; }
            try {
                const formData = new FormData();
                formData.append('file', file);
                const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token') || '';
                const res = await fetch('/CMCdemo/backend/api/attorney/import', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token },
                    body: formData
                });
                const data = await res.json();
                if (!res.ok && !data.success) throw new Error(data.message || 'Import failed');
                showToast(data.message || 'Import complete', 'success');
                if (data.data?.errors?.length) {
                    console.warn('Import errors:', data.data.errors);
                }
                this.loadCounts();
            } catch (e) { showToast(e.message, 'error'); }
            event.target.value = '';
        },

        async importHealthLedger(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (!file.name.toLowerCase().endsWith('.csv')) { showToast('Please select a CSV file', 'error'); event.target.value = ''; return; }
            try {
                const formData = new FormData();
                formData.append('file', file);
                const res = await fetch('/CMCdemo/backend/api/health-ledger/import', { method: 'POST', body: formData });
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
                const res = await fetch('/CMCdemo/backend/api/bank-reconciliation/import', { method: 'POST', body: formData });
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
                const res = await fetch('/CMCdemo/backend/api/mr-fee-payments/import', {
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

        async importMbrReport(event) {
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
                const res = await fetch('/CMCdemo/backend/api/mbr/import', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token },
                    body: formData
                });
                const data = await res.json();
                if (!res.ok && !data.success) throw new Error(data.message || data.error || 'Import failed');
                showToast(data.message || `Imported ${data.imported || 0} MBR lines`, 'success');
                this.loadCounts();
            } catch (e) { showToast(e.message, 'error'); }
            event.target.value = '';
        },

        // ── Prelitigation Import ──

        prelitImportFiles: { cases: null, providers: null, followups: null },

        setPrelitFile(type, event) {
            const file = event.target.files[0];
            if (file && !file.name.toLowerCase().endsWith('.csv')) {
                showToast('Please select a CSV file', 'error');
                event.target.value = '';
                return;
            }
            this.prelitImportFiles[type] = file || null;
        },

        async importPrelitigation() {
            if (!this.prelitImportFiles.cases) {
                showToast('Cases CSV is required', 'error');
                return;
            }
            if (!confirm('Import prelitigation cases? This will create new cases in the system.')) return;

            try {
                const formData = new FormData();
                formData.append('cases', this.prelitImportFiles.cases);
                if (this.prelitImportFiles.providers) formData.append('providers', this.prelitImportFiles.providers);
                if (this.prelitImportFiles.followups) formData.append('followups', this.prelitImportFiles.followups);

                const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token') || '';
                const res = await fetch('/CMCdemo/backend/api/prelitigation/import', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token },
                    body: formData
                });
                const data = await res.json();
                if (!res.ok && !data.success) throw new Error(data.message || 'Import failed');
                showToast(data.message || 'Import complete', 'success');
                if (data.data?.errors?.length) {
                    console.warn('Prelitigation import errors:', data.data.errors);
                    showToast(`${data.data.errors.length} warnings — check console`, 'warning');
                }
                // Reset file inputs
                this.prelitImportFiles = { cases: null, providers: null, followups: null };
                document.querySelectorAll('.prelit-import-file').forEach(el => el.value = '');
                this.loadCounts();
            } catch (e) { showToast(e.message, 'error'); }
        },

        downloadPrelitTemplate(type) {
            window.location.href = '/CMCdemo/backend/api/prelitigation/template?type=' + type;
        },

        // ── Template downloads ──

        downloadCasesTemplate() {
            window.location.href = '/CMCdemo/backend/api/cases/export?template=1';
        },
        downloadProvidersTemplate() {
            window.location.href = '/CMCdemo/backend/api/providers/export?template=1';
        },
    };
}
