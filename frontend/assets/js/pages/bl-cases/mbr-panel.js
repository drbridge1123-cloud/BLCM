function mbrPanel(caseId) {
    return {
        caseId: caseId,
        mbrOpen: false,
        caseData: null,
        report: null,
        lines: [],
        settings: {
            pip1_name: '', pip2_name: '', health1_name: '', health2_name: '', health3_name: '',
            has_wage_loss: false, has_essential_service: false, has_health_subrogation: false, has_health_subrogation2: false,
            notes: ''
        },
        totals: { charges: 0, pip1: 0, pip2: 0, health1: 0, health2: 0, health3: 0, discount: 0, officePaid: 0, clientPaid: 0, balance: 0 },
        loading: true,
        saving: false,
        _debounceSave: null,
        _editingField: null,
        expandedNote: null,
        notePopoverPos: { top: 0, right: 0 },
        showMbrImportModal: false,
        mbrImportPreview: [],
        mbrImportSummary: {},
        mbrImporting: false,
        _mbrImportFile: null,

        // Insurance autocomplete
        insAutoField: null,   // which field is open: 'pip1_name', 'pip2_name', etc.
        insAutoQuery: '',
        insAutoResults: [],
        _insAutoTimeout: null,
        showInsQuickAdd: false,
        insQuickAddSaving: false,
        insNewCompany: { name: '', type: 'auto', phone: '', fax: '', email: '', address: '', city: '', state: '', zip: '', website: '', notes: '' },

        async init() {
            this._debounceSave = createDebouncedSave((line) => this.saveLine(line), 500);
            await this.loadReport();
            this.loading = false;

            // Reload when providers are activated or changed externally
            window.addEventListener('providers-changed', () => this.loadReport());
        },

        async loadReport() {
            try {
                const res = await api.get('mbr/' + this.caseId);
                if (!res.data) {
                    await this.createReport();
                    return;
                }
                this.report = res.data;
                this.caseData = {
                    case_number: res.data.case_number,
                    client_name: res.data.client_name,
                    doi: res.data.doi,
                    case_status: res.data.case_status
                };
                this.lines = res.data.lines || [];
                this.settings = {
                    pip1_name: res.data.pip1_name || '',
                    pip2_name: res.data.pip2_name || '',
                    health1_name: res.data.health1_name || '',
                    health2_name: res.data.health2_name || '',
                    health3_name: res.data.health3_name || '',
                    has_wage_loss: !!res.data.has_wage_loss,
                    has_essential_service: !!res.data.has_essential_service,
                    has_health_subrogation: !!res.data.has_health_subrogation,
                    has_health_subrogation2: !!res.data.has_health_subrogation2,
                    notes: res.data.notes || ''
                };
                this.recalcTotals();
            } catch (e) {
                showToast('Failed to load Medical Balance report', 'error');
            }
        },

        async createReport() {
            try {
                const caseRes = await api.get('bl-cases/' + this.caseId);
                this.caseData = {
                    case_number: caseRes.data.case_number,
                    client_name: caseRes.data.client_name,
                    doi: caseRes.data.doi,
                    case_status: caseRes.data.status
                };
                await api.post('mbr/' + this.caseId);
                showToast('Medical Balance report created');
                await this.loadReport();
            } catch (e) {
                showToast(e.data?.message || 'Failed to create report', 'error');
            }
        },

        async saveSettings() {
            if (!this.report) return;
            this.saving = true;
            try {
                await api.put('mbr/' + this.report.id, this.settings);
                await this.loadReport();
                window.dispatchEvent(new CustomEvent('mbr-updated'));
            } catch (e) {
                showToast('Failed to save settings', 'error');
            }
            this.saving = false;
        },

        cellVal(lineId, field, value) {
            if (this._editingField === lineId + '_' + field) return '';
            return formatCurrency(value);
        },

        startCellEdit(el, line, field) {
            this._editingField = line.id + '_' + field;
            this.$nextTick(() => {
                el.value = line[field] || '';
                el.select();
            });
        },

        endCellEdit(el, line, field) {
            line[field] = parseCurrency(el.value);
            this._editingField = null;
            this.debounceSaveLine(line);
        },

        openNote(e, rowId) {
            if (this.expandedNote === rowId) { this.expandedNote = null; return; }
            const rect = e.currentTarget.getBoundingClientRect();
            const popW = 320, popH = 200;
            let top = rect.top - popH - 4;
            if (top < 8) top = rect.bottom + 4;
            let right = window.innerWidth - rect.right;
            if (right < 0) right = 8;
            this.notePopoverPos = { top: top + 'px', right: right + 'px' };
            this.expandedNote = rowId;
        },

        getCategoryForLine(line) {
            if (line.line_type === 'health_subrogation') return 'health_subrogation';
            if (line.line_type === 'health_subrogation2') return 'health_subrogation2';
            if (line.line_type === 'wage_loss') return 'wage_loss';
            if (line.line_type === 'essential_service') return 'essential_service';
            if (line.line_type === 'rx') return 'rx';
            if (['hospital', 'er', 'surgery_center', 'physician'].includes(line.provider_type)) return 'hospital';
            return 'treatment';
        },

        getMbrCategoryLabel(cat) {
            const labels = {
                'health_subrogation': 'HEALTH SUBROGATION #1',
                'health_subrogation2': 'HEALTH SUBROGATION #2',
                'wage_loss': 'WAGE LOSS',
                'essential_service': 'ESSENTIAL SERVICE',
                'treatment': 'TREATMENT PROVIDERS',
                'hospital': 'HOSPITAL / PHYSICIANS',
                'rx': 'RX'
            };
            return labels[cat] || cat.toUpperCase();
        },

        get displayRows() {
            const categoryOrder = ['health_subrogation', 'health_subrogation2', 'wage_loss', 'essential_service', 'treatment', 'hospital', 'rx'];
            const grouped = {};

            for (const line of this.lines) {
                const cat = this.getCategoryForLine(line);
                if (!grouped[cat]) grouped[cat] = [];
                grouped[cat].push(line);
            }

            const rows = [];
            for (const cat of categoryOrder) {
                if (!grouped[cat] || grouped[cat].length === 0) continue;
                rows.push({ _type: 'header', _key: 'hdr_' + cat, label: this.getMbrCategoryLabel(cat), _lineRef: {} });
                for (const line of grouped[cat]) {
                    rows.push({ _type: 'line', _key: 'ln_' + line.id, ...line, _lineRef: line });
                }
            }
            return rows;
        },

        get insuranceColspan() {
            let c = 0;
            if (this.settings.pip1_name) c++;
            if (this.settings.pip2_name) c++;
            if (this.settings.health1_name) c++;
            if (this.settings.health2_name) c++;
            if (this.settings.health3_name) c++;
            return c;
        },

        get totalCols() {
            return 2 + this.insuranceColspan + 7 + (this.report?.status === 'draft' ? 1 : 0);
        },

        formatDateInput(e, line) {
            const input = e.target;
            const digits = input.value.replace(/\D/g, '');
            let formatted = '';
            for (let i = 0; i < digits.length && i < 12; i++) {
                if (i === 2 || i === 4 || i === 8 || i === 10) formatted += '/';
                if (i === 6) formatted += '-';
                formatted += digits[i];
            }
            line.treatment_dates = formatted;
            input.value = formatted;
        },

        calcBalance(line) {
            return Math.round(((line.charges || 0) - (line.pip1_amount || 0) - (line.pip2_amount || 0)
                - (line.health1_amount || 0) - (line.health2_amount || 0) - (line.health3_amount || 0)
                - (line.discount || 0) - (line.office_paid || 0) - (line.client_paid || 0)) * 100) / 100;
        },

        recalcTotals() {
            const t = { charges: 0, pip1: 0, pip2: 0, health1: 0, health2: 0, health3: 0, discount: 0, officePaid: 0, clientPaid: 0, balance: 0 };
            for (const l of this.lines) {
                t.charges += l.charges || 0;
                t.pip1 += l.pip1_amount || 0;
                t.pip2 += l.pip2_amount || 0;
                t.health1 += l.health1_amount || 0;
                t.health2 += l.health2_amount || 0;
                t.health3 += l.health3_amount || 0;
                t.discount += l.discount || 0;
                t.officePaid += l.office_paid || 0;
                t.clientPaid += l.client_paid || 0;
            }
            t.balance = Math.round((t.charges - t.pip1 - t.pip2 - t.health1 - t.health2 - t.health3 - t.discount - t.officePaid - t.clientPaid) * 100) / 100;
            for (const k in t) t[k] = Math.round(t[k] * 100) / 100;
            this.totals = t;
        },

        debounceSaveLine(line) {
            this.recalcTotals();
            this._debounceSave(line, line.id);
        },

        async saveLine(line) {
            this.saving = true;
            try {
                await api.put('mbr-lines/' + line.id, {
                    charges: line.charges || 0,
                    pip1_amount: line.pip1_amount || 0,
                    pip2_amount: line.pip2_amount || 0,
                    health1_amount: line.health1_amount || 0,
                    health2_amount: line.health2_amount || 0,
                    health3_amount: line.health3_amount || 0,
                    discount: line.discount || 0,
                    office_paid: line.office_paid || 0,
                    client_paid: line.client_paid || 0,
                    treatment_dates: line.treatment_dates || '',
                    visits: line.visits || '',
                    note: line.note || ''
                });
                window.dispatchEvent(new CustomEvent('mbr-updated'));
            } catch (e) {
                showToast('Failed to save', 'error');
            }
            this.saving = false;
        },

        // Provider search for Add Line
        showProviderSearch: false,
        providerSearchQuery: '',
        providerSearchResults: [],
        _providerSearchTimeout: null,

        openProviderSearch() {
            this.showProviderSearch = true;
            this.providerSearchQuery = '';
            this.providerSearchResults = [];
            this.$nextTick(() => {
                const input = document.getElementById('mbr-provider-search-input');
                if (input) input.focus();
            });
        },

        async searchProviders() {
            clearTimeout(this._providerSearchTimeout);
            const q = this.providerSearchQuery.trim();
            if (q.length < 1) { this.providerSearchResults = []; return; }
            this._providerSearchTimeout = setTimeout(async () => {
                try {
                    const res = await api.get('providers/search?q=' + encodeURIComponent(q));
                    this.providerSearchResults = res.data || [];
                } catch (e) {
                    this.providerSearchResults = [];
                }
            }, 200);
        },

        async selectProvider(provider) {
            this.showProviderSearch = false;
            try {
                await api.post('mbr/' + this.report.id + '/lines', {
                    line_type: 'provider',
                    provider_id: provider.id,
                    provider_name: provider.name
                });
                await this.loadReport();
                window.dispatchEvent(new CustomEvent('mbr-updated'));
                window.dispatchEvent(new CustomEvent('providers-changed'));
                window.dispatchEvent(new CustomEvent('costs-changed'));
                showToast('Added: ' + provider.name);
            } catch (e) {
                showToast('Failed to add provider', 'error');
            }
        },

        async addLine(type) {
            if (type === 'provider') {
                this.openProviderSearch();
                return;
            }
            try {
                const name = type === 'rx' ? 'RX' : prompt('Line name:');
                if (!name) return;
                await api.post('mbr/' + this.report.id + '/lines', {
                    line_type: type,
                    provider_name: name
                });
                await this.loadReport();
                window.dispatchEvent(new CustomEvent('mbr-updated'));
            } catch (e) {
                showToast('Failed to add line', 'error');
            }
        },

        async deleteLine(line) {
            if (!confirm('Delete "' + line.provider_name + '"?')) return;
            try {
                await api.delete('mbr-lines/' + line.id);
                await this.loadReport();
                window.dispatchEvent(new CustomEvent('mbr-updated'));
                showToast('Line deleted');
            } catch (e) {
                showToast('Failed to delete', 'error');
            }
        },

        async markComplete() {
            try {
                await api.post('mbr/' + this.report.id + '/complete');
                showToast('Report marked as completed');
                await this.loadReport();
            } catch (e) {
                showToast(e.data?.message || 'Failed', 'error');
            }
        },

        async approveReport() {
            if (!confirm('Approve this report and close the case?')) return;
            try {
                await api.post('mbr/' + this.report.id + '/approve');
                showToast('Report approved — case closed');
                await this.loadReport();
            } catch (e) {
                showToast(e.data?.message || 'Failed', 'error');
            }
        },

        async reopenDraft() {
            if (!confirm('Reopen this report as draft?')) return;
            try {
                await api.put('mbr/' + this.report.id, { status: 'draft' });
                await this.loadReport();
                showToast('Report reopened as draft');
            } catch (e) {
                showToast(e.data?.message || 'Failed', 'error');
            }
        },

        mbrFormatDate(d) {
            if (!d) return '';
            return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },

        getMbrStatusLabel(status) {
            if (status === 'draft') return 'Draft';
            if (status === 'completed') return 'Completed';
            if (status === 'approved') return 'Approved';
            return status || '';
        },

        balanceColor(amount) {
            if (amount === 0) return 'mbr-balance-green';
            if (amount < 0) return 'mbr-balance-red';
            if (amount >= 5000) return 'mbr-balance-red';
            return 'mbr-balance-gold';
        },

        // ==========================================
        // Insurance Autocomplete
        // ==========================================

        openInsAuto(field) {
            this.insAutoField = field;
            this.insAutoQuery = this.settings[field] || '';
            this.insAutoResults = [];
            this.$nextTick(() => {
                const el = document.getElementById('ins-auto-input-' + field);
                if (el) { el.focus(); el.select(); }
            });
        },

        closeInsAuto() {
            // Small delay so click on result registers first
            setTimeout(() => { this.insAutoField = null; }, 150);
        },

        async searchInsurance() {
            clearTimeout(this._insAutoTimeout);
            const q = this.insAutoQuery.trim();
            if (q.length < 1) { this.insAutoResults = []; return; }
            this._insAutoTimeout = setTimeout(async () => {
                try {
                    const res = await api.get('insurance-companies/search?q=' + encodeURIComponent(q));
                    this.insAutoResults = res.data || [];
                } catch (e) {
                    this.insAutoResults = [];
                }
            }, 200);
        },

        selectInsurance(company) {
            if (!this.insAutoField) return;
            this.settings[this.insAutoField] = company.name;
            this.insAutoField = null;
            this.insAutoResults = [];
            this.saveSettings();
        },

        clearInsField(field) {
            this.settings[field] = '';
            this.insAutoField = null;
            this.saveSettings();
        },

        openInsQuickAdd() {
            const f = this.insAutoField || '';
            const defaultType = f.startsWith('pip') ? 'auto' : (f.startsWith('health') ? 'health' : 'auto');
            this.insNewCompany = {
                name: this.insAutoQuery || '',
                type: defaultType,
                phone: '', fax: '', email: '',
                address: '', city: '', state: '', zip: '',
                website: '', notes: ''
            };
            this.showInsQuickAdd = true;
        },

        async saveInsQuickAdd() {
            const name = this.insNewCompany.name.trim();
            if (!name) return;
            this.insQuickAddSaving = true;
            try {
                const payload = {};
                for (const [k, v] of Object.entries(this.insNewCompany)) {
                    if (typeof v === 'string' && v.trim()) payload[k] = v.trim();
                }
                await api.post('insurance-companies', payload);
                showToast('Insurance company added: ' + name);
                this.settings[this.insAutoField] = name;
                this.showInsQuickAdd = false;
                this.insAutoField = null;
                this.saveSettings();
            } catch (e) {
                showToast(e.data?.message || 'Failed to add', 'error');
            }
            this.insQuickAddSaving = false;
        },

        // ==========================================
        // CSV Import
        // ==========================================

        handleMbrImportFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            this._mbrImportFile = file;
            this.previewMbrImport(file);
            event.target.value = '';
        },

        async previewMbrImport(file) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('preview', '1');

            try {
                const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
                const res = await fetch('/CMCdemo/backend/api/mbr/' + this.caseId + '/import', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token },
                    body: formData
                });
                const data = await res.json();
                if (!data.success) {
                    showToast(data.message || 'Failed to parse CSV', 'error');
                    return;
                }
                this.mbrImportPreview = data.preview || [];
                this.mbrImportSummary = {
                    count: data.count,
                    total_charges: data.total_charges,
                    total_pip1: data.total_pip1,
                    total_balance: data.total_balance,
                };
                this.showMbrImportModal = true;
            } catch (e) {
                showToast('Failed to preview CSV', 'error');
            }
        },

        async confirmMbrImport() {
            if (!this._mbrImportFile) return;
            this.mbrImporting = true;

            const formData = new FormData();
            formData.append('file', this._mbrImportFile);

            try {
                const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
                const res = await fetch('/CMCdemo/backend/api/mbr/' + this.caseId + '/import', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token },
                    body: formData
                });
                const data = await res.json();
                if (!data.success) {
                    showToast(data.message || 'Import failed', 'error');
                    this.mbrImporting = false;
                    return;
                }
                showToast('Imported ' + data.imported + ' Medical Balance lines');
                this.showMbrImportModal = false;
                this._mbrImportFile = null;
                await this.loadReport();
                window.dispatchEvent(new CustomEvent('mbr-updated'));
            } catch (e) {
                showToast('Import failed', 'error');
            }
            this.mbrImporting = false;
        },

        printMbr() {
            const s = this.settings;
            const cd = this.caseData;

            const insCols = [];
            if (s.pip1_name) insCols.push({ key: 'pip1_amount', label: 'PIP #1 (' + s.pip1_name + ')' });
            if (s.pip2_name) insCols.push({ key: 'pip2_amount', label: 'PIP #2 (' + s.pip2_name + ')' });
            if (s.health1_name) insCols.push({ key: 'health1_amount', label: 'Health #1 (' + s.health1_name + ')' });
            if (s.health2_name) insCols.push({ key: 'health2_amount', label: 'Health #2 (' + s.health2_name + ')' });
            if (s.health3_name) insCols.push({ key: 'health3_amount', label: 'Health #3 (' + s.health3_name + ')' });

            const fmtZero = (v) => {
                const num = Number(v) || 0;
                return num === 0 ? '' : formatCurrency(num);
            };

            let thead = '<th style="text-align:left">Provider</th><th class="r">Charges</th>';
            insCols.forEach(c => { thead += '<th class="r">' + c.label + '</th>'; });
            thead += '<th class="r">Discount</th><th class="r">Office Paid</th><th class="r">Client Paid</th><th class="r bal-hdr">Balance</th><th>Dates</th><th>Visits</th>';

            let tbody = '';
            for (const row of this.displayRows) {
                if (row._type === 'header') {
                    const colCount = 7 + insCols.length + 2;
                    tbody += '<tr class="cat"><td colspan="' + colCount + '">' + row.label + '</td></tr>';
                } else {
                    const bal = this.calcBalance(row);
                    const balClass = bal === 0 ? 'bal-green' : (bal >= 5000 || bal < 0 ? 'bal-red' : 'bal-gold');
                    tbody += '<tr class="data">';
                    tbody += '<td class="prov">' + (row.provider_name || '') + '</td>';
                    tbody += '<td class="r mono">' + fmtZero(row.charges) + '</td>';
                    insCols.forEach(c => { tbody += '<td class="r mono">' + fmtZero(row[c.key]) + '</td>'; });
                    tbody += '<td class="r mono">' + fmtZero(row.discount) + '</td>';
                    tbody += '<td class="r mono">' + fmtZero(row.office_paid) + '</td>';
                    tbody += '<td class="r mono">' + fmtZero(row.client_paid) + '</td>';
                    tbody += '<td class="r mono ' + balClass + '">' + formatCurrency(bal) + '</td>';
                    tbody += '<td class="dates">' + (row.treatment_dates || '') + '</td>';
                    tbody += '<td class="ctr">' + (row.visits || '') + '</td>';
                    tbody += '</tr>';
                }
            }

            const t = this.totals;
            const totKeys = { pip1: 'pip1_amount', pip2: 'pip2_amount', health1: 'health1_amount', health2: 'health2_amount', health3: 'health3_amount' };
            tbody += '<tr class="total"><td class="total-label">TOTAL</td>';
            tbody += '<td class="r">' + formatCurrency(t.charges) + '</td>';
            insCols.forEach(c => {
                const tKey = Object.keys(totKeys).find(k => totKeys[k] === c.key);
                tbody += '<td class="r">' + formatCurrency(t[tKey] || 0) + '</td>';
            });
            tbody += '<td class="r">' + formatCurrency(t.discount) + '</td>';
            tbody += '<td class="r">' + formatCurrency(t.officePaid) + '</td>';
            tbody += '<td class="r">' + formatCurrency(t.clientPaid) + '</td>';
            tbody += '<td class="r total-bal">' + formatCurrency(t.balance) + '</td>';
            tbody += '<td colspan="2"></td></tr>';

            const notes = s.notes ? '<div class="notes"><strong>Notes:</strong> ' + s.notes + '</div>' : '';

            const html = `<!DOCTYPE html><html><head><title>Medical Balance - ${cd.case_number}</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
    @page { size: landscape; margin: 15mm; }
    body { font-family: 'IBM Plex Sans', sans-serif; font-size: 13px; color: #1a2535; }
    h2 { margin: 0 0 2px; font-size: 16px; font-weight: 600; color: #0F1B2D; }
    .sub { color: #8a8a82; margin-bottom: 12px; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    .mono { font-family: 'IBM Plex Mono', monospace; }
    th { background: #E5E5E0; padding: 8px 10px; text-align: right; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #0F1B2D; border-bottom: 2px solid #C8C8C2; white-space: nowrap; }
    th.r { text-align: right; }
    th.bal-hdr { color: #C9A84C; }
    td { padding: 8px 10px; border-bottom: 1px solid #e8e8e3; font-family: 'IBM Plex Mono', monospace; font-size: 13px; text-align: right; }
    td.prov { font-family: 'IBM Plex Sans', sans-serif; font-weight: 500; text-align: left; }
    td.dates { font-size: 11px; white-space: nowrap; text-align: center; }
    td.ctr { text-align: center; }
    .zero-val { color: #c5c5be; }
    tr.cat td { background: #E5E5E0; font-family: 'IBM Plex Sans', sans-serif; font-weight: 700; font-size: 9px; letter-spacing: 0.1em; text-transform: uppercase; color: #5a5a54; padding: 5px 10px; text-align: left; border-top: 1px solid #C8C8C2; border-bottom: 1px solid #C8C8C2; }
    tr.total td { font-weight: 600; background: #0F1B2D; color: rgba(255,255,255,0.7); border: none; padding: 12px 10px; }
    tr.total td.total-label { font-family: 'IBM Plex Sans', sans-serif; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.1em; font-size: 11px; font-weight: 700; text-align: left; }
    tr.total td.total-bal { color: #C9A84C; font-size: 14px; }
    .bal-green { color: #2a6b4a; }
    .bal-gold { color: #C9A84C; }
    .bal-red { color: #b83232; }
    .notes { margin-top: 14px; font-size: 13px; font-family: 'IBM Plex Sans', sans-serif; color: #5a5a54; padding: 10px 0; border-top: 1px solid #E5E5E0; }
    .notes strong { color: #1a2535; }
</style>
</head><body>
    <h2>Medical Billing Data Summary</h2>
    <div class="sub">${cd.client_name} &mdash; ${cd.case_number}${cd.doi ? ' | DOI: ' + this.mbrFormatDate(cd.doi) : ''}</div>
    <table><thead><tr>${thead}</tr></thead><tbody>${tbody}</tbody></table>
    ${notes}
</body></html>`;

            const w = window.open('', '_blank');
            w.document.write(html);
            w.document.close();
            w.onload = () => { w.print(); };
        }
    };
}
