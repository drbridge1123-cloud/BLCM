/**
 * Bank Reconciliation Page Controller
 * Admin page for importing bank statements and matching entries to payments.
 */
function bankReconciliationPage() {
    return {
        // Tab state
        tab: 'entries',

        // Entries state
        entries: [],
        loading: true,
        search: '',
        statusFilter: '',
        batchFilter: '',
        dateFrom: '',
        dateTo: '',
        pagination: { page: 1, perPage: 50, total: 0, totalPages: 0 },

        // Summary
        summary: {
            total_entries: 0,
            unmatched_count: 0,
            matched_count: 0,
            ignored_count: 0,
            unmatched_sum: 0,
            matched_sum: 0,
            ignored_sum: 0
        },

        // Batches state
        batches: [],
        batchList: [],     // For the filter dropdown (loaded once)
        batchesLoading: false,
        importing: false,

        // Match panel state
        showMatchPanel: false,
        matchingEntry: null,
        searchResults: [],
        selectedPaymentId: null,
        searchingPayments: false,
        matchingInProgress: false,
        paymentSearch: {
            amount: '',
            check_number: '',
            date_from: '',
            date_to: ''
        },

        // ── Lifecycle ──

        init() {
            this.loadEntries();
            this.loadBatchList();
        },

        // ── Entries ──

        async loadEntries(page) {
            if (page) this.pagination.page = page;
            this.loading = true;

            try {
                const params = buildQueryString({
                    search: this.search,
                    reconciliation_status: this.statusFilter,
                    batch_id: this.batchFilter,
                    date_from: this.dateFrom,
                    date_to: this.dateTo,
                    page: this.pagination.page,
                    per_page: this.pagination.perPage
                });

                const res = await api.get('bank-reconciliation' + params);
                this.entries = res.data || [];

                // Pagination is nested
                if (res.pagination) {
                    this.pagination.total = res.pagination.total || 0;
                    this.pagination.totalPages = res.pagination.total_pages || 0;
                    this.pagination.page = res.pagination.page || 1;
                }

                // Update summary from response
                if (res.summary) {
                    this.summary = res.summary;
                }
            } catch (e) {
                showToast(e.message, 'error');
            }

            this.loading = false;
        },

        clearFilters() {
            this.search = '';
            this.statusFilter = '';
            this.batchFilter = '';
            this.dateFrom = '';
            this.dateTo = '';
            this.loadEntries(1);
        },

        // ── Batches ──

        async loadBatches() {
            this.batchesLoading = true;
            try {
                const res = await api.get('bank-reconciliation/batches');
                this.batches = res.data || [];
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.batchesLoading = false;
        },

        async loadBatchList() {
            try {
                const res = await api.get('bank-reconciliation/batches');
                this.batchList = res.data || [];
            } catch (e) {
                // Silently fail - batch filter just won't populate
            }
        },

        filterByBatch(batchId) {
            this.tab = 'entries';
            this.batchFilter = batchId;
            this.loadEntries(1);
        },

        // ── CSV Import ──

        async importCSV(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.name.toLowerCase().endsWith('.csv')) {
                showToast('Please select a CSV file', 'error');
                event.target.value = '';
                return;
            }

            this.importing = true;
            try {
                const formData = new FormData();
                formData.append('file', file);

                const response = await fetch('/CMCdemo/backend/api/bank-reconciliation/import', {
                    method: 'POST',
                    body: formData
                });

                if (response.status === 401) {
                    window.location.href = '/CMCdemo/frontend/pages/auth/login.php';
                    throw new Error('Session expired');
                }

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Import failed');
                }

                showToast(result.message || `Imported ${result.data?.count || 0} entries`, 'success');
                this.loadBatches();
                this.loadBatchList();
                this.loadEntries(1);
            } catch (e) {
                showToast(e.message, 'error');
            }

            this.importing = false;
            event.target.value = '';
        },

        // ── Match Panel ──

        openMatchPanel(entry) {
            this.matchingEntry = entry;
            this.selectedPaymentId = null;
            this.searchResults = [];

            // Pre-fill search with the entry's amount and check number
            this.paymentSearch = {
                amount: entry.amount || '',
                check_number: entry.check_number || '',
                date_from: '',
                date_to: ''
            };

            this.showMatchPanel = true;

            // Auto-search on open
            this.$nextTick(() => this.searchPayments());
        },

        async searchPayments() {
            this.searchingPayments = true;
            this.selectedPaymentId = null;

            try {
                const params = buildQueryString({
                    amount: this.paymentSearch.amount,
                    check_number: this.paymentSearch.check_number,
                    date_from: this.paymentSearch.date_from,
                    date_to: this.paymentSearch.date_to
                });

                const res = await api.get('bank-reconciliation/search-payments' + params);
                this.searchResults = res.data || [];
            } catch (e) {
                showToast(e.message, 'error');
            }

            this.searchingPayments = false;
        },

        clearPaymentSearch() {
            this.paymentSearch = { amount: '', check_number: '', date_from: '', date_to: '' };
            this.searchResults = [];
            this.selectedPaymentId = null;
        },

        async confirmMatch() {
            if (!this.matchingEntry || !this.selectedPaymentId) return;

            this.matchingInProgress = true;
            try {
                await api.put(`bank-reconciliation/${this.matchingEntry.id}/match`, {
                    payment_id: this.selectedPaymentId
                });

                showToast('Entry matched successfully', 'success');
                this.showMatchPanel = false;
                this.loadEntries();
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.matchingInProgress = false;
        },

        // ── Entry Actions ──

        async unmatchEntry(entryId) {
            if (!confirm('Are you sure you want to unmatch this entry?')) return;

            try {
                await api.put(`bank-reconciliation/${entryId}/unmatch`);
                showToast('Entry unmatched', 'success');
                this.loadEntries();
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async ignoreEntry(entryId) {
            if (!confirm('Mark this entry as ignored?')) return;

            try {
                await api.put(`bank-reconciliation/${entryId}/ignore`, {});
                showToast('Entry ignored', 'success');
                this.loadEntries();
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async unignoreEntry(entryId) {
            try {
                await api.put(`bank-reconciliation/${entryId}/unmatch`);
                showToast('Entry restored to unmatched', 'success');
                this.loadEntries();
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        // ── Batch Actions ──

        async deleteBatch(batchId, unmatchedCount) {
            if (!confirm(`Delete ${unmatchedCount} unmatched entries from batch ${batchId}? Matched entries will be preserved.`)) return;

            try {
                await api.delete(`bank-reconciliation/delete-batch?batch_id=${encodeURIComponent(batchId)}`);
                showToast('Unmatched entries deleted', 'success');
                this.loadBatches();
                this.loadBatchList();
                this.loadEntries();
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        // ── Helpers ──

        statusBadge(status) {
            const map = {
                unmatched: 'bg-red-100 text-red-700',
                matched:   'bg-green-100 text-green-700',
                ignored:   'bg-gray-100 text-gray-500'
            };
            return map[status] || 'bg-gray-100 text-gray-600';
        },

        formatDateTime(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                + ' ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        },

        paginationPages() {
            const total = this.pagination.totalPages;
            const current = this.pagination.page;
            if (total <= 7) {
                return Array.from({ length: total }, (_, i) => i + 1);
            }

            const pages = [];
            pages.push(1);

            if (current > 3) pages.push('...');

            const start = Math.max(2, current - 1);
            const end = Math.min(total - 1, current + 1);
            for (let i = start; i <= end; i++) pages.push(i);

            if (current < total - 2) pages.push('...');

            pages.push(total);
            return pages;
        }
    };
}
