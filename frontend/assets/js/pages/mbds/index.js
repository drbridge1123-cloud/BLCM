/**
 * MBDS Page Controller
 * Manages list/detail views for Medical Bills Data Summary reports
 */
function mbdsPage() {
    const base = listPageBase('mbds', {
        defaultSort: 'r.created_at',
        defaultDir: 'desc',
        perPage: 25,
        filtersToParams() {
            return { status: this.statusFilter };
        }
    });

    return {
        ...base,

        // View state
        view: 'list',
        statusFilter: '',
        summary: { total: 0, draft: 0, completed: 0, approved: 0 },

        // Detail state
        currentReport: {},
        lines: [],
        totals: { charges: 0, pip1: 0, pip2: 0, health1: 0, health2: 0, health3: 0, discount: 0, office_paid: 0, client_paid: 0, balance: 0 },
        detailLoading: false,
        savingHeader: false,
        newLineType: 'provider',

        // Header form
        headerForm: {
            pip1_name: '', pip2_name: '', health1_name: '', health2_name: '', health3_name: '',
            has_wage_loss: false, has_essential_service: false,
            has_health_subrogation: false, has_health_subrogation2: false,
            notes: ''
        },

        // Create modal state
        showCreateModal: false,
        creatingReport: false,
        caseResults: [],
        createForm: {
            caseSearch: '',
            selectedCase: null,
            pip1_name: '', pip2_name: '',
            health1_name: '', health2_name: '', health3_name: ''
        },

        init() {
            this.loadData(1);
        },

        // ═══════════════════════════════
        //  LIST VIEW
        // ═══════════════════════════════

        getStatusBadge(status) {
            const map = {
                draft: 'bg-blue-100 text-blue-700',
                completed: 'bg-yellow-100 text-yellow-700',
                approved: 'bg-green-100 text-green-700'
            };
            return map[status] || 'bg-gray-100 text-gray-600';
        },

        paginationPages() {
            if (!this.pagination) return [];
            const total = this.pagination.total_pages;
            const current = this.pagination.page;
            const pages = [];
            const delta = 2;
            let start = Math.max(2, current - delta);
            let end = Math.min(total - 1, current + delta);
            pages.push(1);
            if (start > 2) pages.push('...');
            for (let i = start; i <= end; i++) pages.push(i);
            if (end < total - 1) pages.push('...');
            if (total > 1) pages.push(total);
            return pages;
        },

        resetFilters() {
            this.search = '';
            this.statusFilter = '';
            this.loadData(1);
        },

        // ═══════════════════════════════
        //  DETAIL VIEW
        // ═══════════════════════════════

        async viewReport(id) {
            this.detailLoading = true;
            this.view = 'detail';
            try {
                const res = await api.get('mbds/' + id);
                this.currentReport = res.data;
                this.lines = res.data.lines || [];
                this.totals = res.data.totals || this.totals;

                // Populate header form
                this.headerForm = {
                    pip1_name: this.currentReport.pip1_name || '',
                    pip2_name: this.currentReport.pip2_name || '',
                    health1_name: this.currentReport.health1_name || '',
                    health2_name: this.currentReport.health2_name || '',
                    health3_name: this.currentReport.health3_name || '',
                    has_wage_loss: !!parseInt(this.currentReport.has_wage_loss),
                    has_essential_service: !!parseInt(this.currentReport.has_essential_service),
                    has_health_subrogation: !!parseInt(this.currentReport.has_health_subrogation),
                    has_health_subrogation2: !!parseInt(this.currentReport.has_health_subrogation2),
                    notes: this.currentReport.notes || ''
                };

                // Cast numeric fields on lines
                this.lines.forEach(l => {
                    l.charges = parseFloat(l.charges) || 0;
                    l.pip1_amount = parseFloat(l.pip1_amount) || 0;
                    l.pip2_amount = parseFloat(l.pip2_amount) || 0;
                    l.health1_amount = parseFloat(l.health1_amount) || 0;
                    l.health2_amount = parseFloat(l.health2_amount) || 0;
                    l.health3_amount = parseFloat(l.health3_amount) || 0;
                    l.discount = parseFloat(l.discount) || 0;
                    l.office_paid = parseFloat(l.office_paid) || 0;
                    l.client_paid = parseFloat(l.client_paid) || 0;
                });
            } catch (e) {
                showToast(e.message, 'error');
                this.view = 'list';
            }
            this.detailLoading = false;
        },

        backToList() {
            this.view = 'list';
            this.currentReport = {};
            this.lines = [];
            this.loadData();
        },

        // ═══════════════════════════════
        //  HEADER SAVE
        // ═══════════════════════════════

        async saveHeader() {
            this.savingHeader = true;
            try {
                await api.put('mbds/' + this.currentReport.id, {
                    pip1_name: this.headerForm.pip1_name,
                    pip2_name: this.headerForm.pip2_name,
                    health1_name: this.headerForm.health1_name,
                    health2_name: this.headerForm.health2_name,
                    health3_name: this.headerForm.health3_name,
                    has_wage_loss: this.headerForm.has_wage_loss,
                    has_essential_service: this.headerForm.has_essential_service,
                    has_health_subrogation: this.headerForm.has_health_subrogation,
                    has_health_subrogation2: this.headerForm.has_health_subrogation2,
                    notes: this.headerForm.notes
                });
                showToast('Header saved', 'success');
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.savingHeader = false;
        },

        // ═══════════════════════════════
        //  LINE ITEMS
        // ═══════════════════════════════

        calcBalance(line) {
            return (parseFloat(line.charges) || 0)
                - (parseFloat(line.pip1_amount) || 0)
                - (parseFloat(line.pip2_amount) || 0)
                - (parseFloat(line.health1_amount) || 0)
                - (parseFloat(line.health2_amount) || 0)
                - (parseFloat(line.health3_amount) || 0)
                - (parseFloat(line.discount) || 0)
                - (parseFloat(line.office_paid) || 0)
                - (parseFloat(line.client_paid) || 0);
        },

        async saveLine(line) {
            try {
                const res = await api.put('mbds/' + this.currentReport.id + '/update-line', {
                    line_id: line.id,
                    provider_name: line.provider_name,
                    charges: parseFloat(line.charges) || 0,
                    pip1_amount: parseFloat(line.pip1_amount) || 0,
                    pip2_amount: parseFloat(line.pip2_amount) || 0,
                    health1_amount: parseFloat(line.health1_amount) || 0,
                    health2_amount: parseFloat(line.health2_amount) || 0,
                    health3_amount: parseFloat(line.health3_amount) || 0,
                    discount: parseFloat(line.discount) || 0,
                    office_paid: parseFloat(line.office_paid) || 0,
                    client_paid: parseFloat(line.client_paid) || 0
                });
                // Update line with server-calculated balance
                if (res.data) {
                    line.balance = parseFloat(res.data.balance) || 0;
                }
                this.recalcTotals();
                showToast('Line saved', 'success');
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async addLine() {
            try {
                const res = await api.post('mbds/' + this.currentReport.id + '/add-line', {
                    line_type: this.newLineType
                });
                if (res.data) {
                    const l = res.data;
                    l.charges = parseFloat(l.charges) || 0;
                    l.pip1_amount = parseFloat(l.pip1_amount) || 0;
                    l.pip2_amount = parseFloat(l.pip2_amount) || 0;
                    l.health1_amount = parseFloat(l.health1_amount) || 0;
                    l.health2_amount = parseFloat(l.health2_amount) || 0;
                    l.health3_amount = parseFloat(l.health3_amount) || 0;
                    l.discount = parseFloat(l.discount) || 0;
                    l.office_paid = parseFloat(l.office_paid) || 0;
                    l.client_paid = parseFloat(l.client_paid) || 0;
                    this.lines.push(l);
                }
                showToast('Line added', 'success');
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async deleteLine(lineId) {
            if (!confirm('Delete this line item?')) return;
            try {
                await api.delete('mbds/' + this.currentReport.id + '/delete-line?line_id=' + lineId);
                this.lines = this.lines.filter(l => l.id !== lineId);
                this.recalcTotals();
                showToast('Line deleted', 'success');
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        recalcTotals() {
            const t = { charges: 0, pip1: 0, pip2: 0, health1: 0, health2: 0, health3: 0, discount: 0, office_paid: 0, client_paid: 0, balance: 0 };
            this.lines.forEach(l => {
                t.charges     += parseFloat(l.charges) || 0;
                t.pip1        += parseFloat(l.pip1_amount) || 0;
                t.pip2        += parseFloat(l.pip2_amount) || 0;
                t.health1     += parseFloat(l.health1_amount) || 0;
                t.health2     += parseFloat(l.health2_amount) || 0;
                t.health3     += parseFloat(l.health3_amount) || 0;
                t.discount    += parseFloat(l.discount) || 0;
                t.office_paid += parseFloat(l.office_paid) || 0;
                t.client_paid += parseFloat(l.client_paid) || 0;
                t.balance     += this.calcBalance(l);
            });
            this.totals = t;
        },

        formatLineType(type) {
            const map = {
                provider: 'Provider', bridge_law: 'Bridge Law', wage_loss: 'Wage Loss',
                essential_service: 'Essential Svc', health_subrogation: 'Health Sub',
                health_subrogation2: 'Health Sub 2', rx: 'Rx'
            };
            return map[type] || type;
        },

        // ═══════════════════════════════
        //  WORKFLOW ACTIONS
        // ═══════════════════════════════

        async completeReport() {
            if (!confirm('Mark this report as completed?')) return;
            try {
                await api.put('mbds/' + this.currentReport.id + '/complete');
                showToast('Report marked as completed', 'success');
                await this.viewReport(this.currentReport.id);
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async approveReport() {
            if (!confirm('Approve this report?')) return;
            try {
                await api.put('mbds/' + this.currentReport.id + '/approve');
                showToast('Report approved', 'success');
                await this.viewReport(this.currentReport.id);
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async reopenReport() {
            if (!confirm('Reopen this report as draft?')) return;
            try {
                await api.put('mbds/' + this.currentReport.id, { reopen: true });
                showToast('Report reopened as draft', 'success');
                await this.viewReport(this.currentReport.id);
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async activateProviders() {
            try {
                const res = await api.post('mbds/' + this.currentReport.id + '/activate-providers');
                const count = res.data?.created || 0;
                showToast(count + ' provider line(s) added', 'success');
                if (count > 0) {
                    await this.viewReport(this.currentReport.id);
                }
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        // ═══════════════════════════════
        //  CREATE REPORT
        // ═══════════════════════════════

        openCreateModal() {
            this.createForm = {
                caseSearch: '', selectedCase: null,
                pip1_name: '', pip2_name: '',
                health1_name: '', health2_name: '', health3_name: ''
            };
            this.caseResults = [];
            this.showCreateModal = true;
        },

        async searchCases() {
            const q = this.createForm.caseSearch.trim();
            if (q.length < 2) { this.caseResults = []; return; }
            try {
                const res = await api.get('cases' + buildQueryString({ search: q, per_page: 10 }));
                this.caseResults = res.data || [];
            } catch (e) {
                this.caseResults = [];
            }
        },

        selectCase(c) {
            this.createForm.selectedCase = c;
            this.createForm.caseSearch = c.case_number + ' - ' + c.client_name;
            this.caseResults = [];
        },

        async createReport() {
            if (!this.createForm.selectedCase) {
                showToast('Please select a case', 'error');
                return;
            }
            this.creatingReport = true;
            try {
                const res = await api.post('mbds', {
                    case_id: this.createForm.selectedCase.id,
                    pip1_name: this.createForm.pip1_name,
                    pip2_name: this.createForm.pip2_name,
                    health1_name: this.createForm.health1_name,
                    health2_name: this.createForm.health2_name,
                    health3_name: this.createForm.health3_name
                });
                showToast('MBDS report created', 'success');
                this.showCreateModal = false;
                // Navigate to the new report
                if (res.data?.id) {
                    await this.viewReport(res.data.id);
                } else {
                    this.loadData(1);
                }
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.creatingReport = false;
        }
    };
}
