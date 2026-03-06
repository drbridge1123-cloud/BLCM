function healthLedgerPanel(caseId, caseNumber) {
    return {
        open: false,
        loading: true,
        items: [],

        async init() {
            await this.loadItems();
            this.loading = false;
        },

        async loadItems() {
            try {
                const res = await api.get('health-ledger/list?case_id=' + caseId + '&per_page=50&sort_by=created_at&sort_dir=asc');
                this.items = res.data || [];
            } catch (e) {
                this.items = [];
            }
        },

        get receivedCount() {
            return this.items.filter(i => i.overall_status === 'received' || i.overall_status === 'done').length;
        },

        getStatusLabel(s) {
            const m = { not_started: 'Not Started', requesting: 'Requesting', follow_up: 'Follow Up', received: 'Received', done: 'Done' };
            return m[s] || s;
        },

        goToHealthTracker() {
            const url = '/blcm/frontend/pages/billing/index.php?tab=health&case_id=' + caseId;
            window.open(url, '_blank');
        }
    };
}
