/**
 * Reports & Analytics Page — Alpine.js Controller
 */
function reportsPage() {
    const currentYear = new Date().getFullYear();
    return {
        loading: false,
        saving: false,
        generating: false,
        activeTab: 'commission',
        selectedYear: currentYear,
        showGoalModal: false,
        report: {},
        employees: [],

        tabs: [
            { key: 'commission', label: 'Commission Summary' },
            { key: 'attorney',   label: 'Attorney Cases' },
            { key: 'performance', label: 'Performance' },
        ],

        yearOptions: Array.from({length: 5}, (_, i) => currentYear - i),

        goalForm: {
            user_id: '',
            year: currentYear,
            target_cases: 50,
            target_legal_fee: 500000,
            notes: ''
        },

        async init() {
            await this.loadEmployees();
            await this.loadReport();
        },

        switchTab(tab) {
            this.activeTab = tab;
            this.loadReport();
        },

        async loadEmployees() {
            try {
                const res = await api.get('users?active=1');
                this.employees = (res.data || []).filter(u => u.is_active);
            } catch (e) {
                console.error('Failed to load employees:', e);
            }
        },

        async loadReport() {
            this.loading = true;
            try {
                if (this.activeTab === 'commission') {
                    await this.loadCommissionReport();
                } else if (this.activeTab === 'attorney') {
                    await this.loadAttorneyReport();
                } else if (this.activeTab === 'performance') {
                    await this.loadPerformanceReport();
                }
            } catch (e) {
                console.error('Report load failed:', e);
                showToast('Failed to load report', 'error');
            } finally {
                this.loading = false;
            }
        },

        async loadCommissionReport() {
            // Employee commissions stats
            const res = await api.get(`commissions/stats?year=${this.selectedYear}`);
            const d = res.data || {};

            this.report.commission = {
                total_settled: d.total_settled || 0,
                total_commission: d.total_commission || 0,
                total_cases: d.total_cases || 0,
                avg_commission: d.total_cases ? (d.total_commission / d.total_cases) : 0,
                monthly: d.monthly || [],
                by_employee: d.by_employee || []
            };
        },

        async loadAttorneyReport() {
            // Attorney cases stats
            const res = await api.get(`attorney-cases/stats?year=${this.selectedYear}`);
            const d = res.data || {};

            this.report.attorney = {
                total_settled: d.total_settled || 0,
                total_legal_fee: d.total_legal_fee || 0,
                total_commission: d.total_commission || 0,
                avg_demand_days: d.avg_demand_days || 0,
                by_phase: d.by_phase || [],
                by_attorney: d.by_attorney || []
            };
        },

        async loadPerformanceReport() {
            // Goals + snapshots
            const [goalsRes, snapRes] = await Promise.all([
                api.get(`goals?year=${this.selectedYear}`),
                api.get(`performance?year=${this.selectedYear}`)
            ]);

            // Map goals with actual progress
            const goals = (goalsRes.data || []).map(g => {
                const progress = g.target_cases > 0
                    ? Math.round(((g.actual_cases || 0) / g.target_cases) * 100)
                    : 0;
                return { ...g, progress };
            });

            this.report.goals = goals;
            this.report.snapshots = snapRes.data || [];
        },

        async saveGoal() {
            if (!this.goalForm.user_id) {
                showToast('Select an employee', 'warning');
                return;
            }
            this.saving = true;
            try {
                await api.post('goals', this.goalForm);
                showToast('Goal saved successfully', 'success');
                this.showGoalModal = false;
                this.loadReport();
            } catch (e) {
                showToast(e.message || 'Failed to save goal', 'error');
            } finally {
                this.saving = false;
            }
        },

        async generateSnapshots() {
            this.generating = true;
            try {
                await api.post('performance');
                showToast('Snapshots generated', 'success');
                this.loadReport();
            } catch (e) {
                showToast(e.message || 'Failed to generate', 'error');
            } finally {
                this.generating = false;
            }
        }
    };
}
