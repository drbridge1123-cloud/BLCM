/**
 * Dashboard Page — Alpine.js Controller
 * Combines MRMS dashboard pattern with BLCM-specific data
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
        // BLCM-specific state
        data: {},
        loading: true,

        async init() {
            await Promise.all([
                this.loadSummary(),
                this.loadBLCMData(),
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

        // BLCM-specific data (already included in summary endpoint)
        async loadBLCMData() {
            // Data comes from loadSummary - this is a placeholder
            // for any additional BLCM-specific API calls
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

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr + 'T00:00:00');
            return String(d.getMonth() + 1).padStart(2, '0') + '/' + String(d.getDate()).padStart(2, '0');
        },

        getStatusLabel(status) {
            const labels = {
                'ini': 'Treatment',
                'rec': 'Records',
                'verification': 'Verification',
                'rfd': 'Demand',
                'neg': 'Negotiate',
                'lit': 'Litigation',
                'fbc': 'Final Review',
                'accounting': 'Settlement',
                'closed': 'Closed'
            };
            return labels[status] || status;
        }
    };
}
