/**
 * Dashboard Page — Alpine.js Controller
 * Combines MRMS dashboard pattern with CMC-specific data
 */
function dashboardPage() {
    return {
        ...pendingAssignmentsMixin(),
        // MRMS-style state
        summary: {},
        followups: [],
        overdueItems: [],
        escalations: [],
        cases: [],
        staffMetrics: {},
        systemHealth: {},
        providerAnalytics: {},
        // CMC-specific state
        data: {},
        loading: true,

        async init() {
            await Promise.all([
                this.loadSummary(),
                this.loadCMCData(),
                this.loadFollowups(),
                this.loadOverdue(),
                this.loadEscalations(),
                this.loadCases(),
                this.loadStaffMetrics(),
                this.loadPendingCaseAssignments(),
                this.loadPendingProviderAssignments()
            ]);
            this.loading = false;
        },

        // MRMS dashboard summary (MR-specific metrics)
        async loadSummary() {
            try {
                const res = await api.get('dashboard/summary');
                this.summary = res.data || {};
                this.data = res.data || {};
            } catch (e) {}
        },

        // CMC-specific data (already included in summary endpoint)
        async loadCMCData() {
            // Data comes from loadSummary - this is a placeholder
            // for any additional CMC-specific API calls
        },

        async loadFollowups() {
            try {
                const res = await api.get('dashboard/followup-due');
                this.followups = res.data || [];
            } catch (e) {}
        },

        async loadOverdue() {
            try {
                const res = await api.get('dashboard/overdue');
                this.overdueItems = res.data || [];
            } catch (e) {}
        },

        async loadEscalations() {
            try {
                const res = await api.get('dashboard/escalations');
                this.escalations = res.data || [];
            } catch (e) {}
        },

        async loadCases() {
            try {
                const res = await api.get('bl-cases?per_page=10');
                this.cases = res.data || [];
            } catch (e) {}
        },

        async loadStaffMetrics() {
            try {
                const res = await api.get('dashboard/staff-metrics');
                this.staffMetrics = res.data || {};
            } catch (e) {}
        },

        formatCurrency(val) {
            return '$' + parseFloat(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },

        getStatusLabel(status) {
            const labels = {
                'ini': 'Treatment',
                'rec': 'Collection',
                'verification': 'Verification',
                'rfd': 'Demand',
                'neg': 'Negotiate',
                'lit': 'Litigation',
                'final_verification': 'Settlement',
                'accounting': 'Accounting',
                'closed': 'Closed'
            };
            return labels[status] || status;
        }
    };
}
