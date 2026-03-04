function expenseReportPage() {
    return {
        ...listPageBase('expense-report/list', {
            defaultSort: 'payment_date',
            defaultDir: 'desc',
            perPage: 0,
            filtersToParams() {
                return {
                    date_from: this.dateFrom,
                    date_to: this.dateTo,
                    category: this.categoryFilter,
                    payment_type: this.paymentTypeFilter,
                    staff_id: this.staffFilter,
                };
            }
        }),

        // Page-specific state
        summary: {},
        staffList: [],
        dateFrom: '',
        dateTo: '',
        categoryFilter: '',
        paymentTypeFilter: '',
        staffFilter: '',

        get pageTotalBilled() {
            return this.items.reduce((sum, i) => sum + parseFloat(i.billed_amount || 0), 0);
        },

        get pageTotalPaid() {
            return this.items.reduce((sum, i) => sum + parseFloat(i.paid_amount || 0), 0);
        },

        async init() {
            await this.loadData();
        },

        _resetPageFilters() {
            this.dateFrom = '';
            this.dateTo = '';
            this.categoryFilter = '';
            this.paymentTypeFilter = '';
            this.staffFilter = '';
        },

        _hasPageFilters() {
            return !!(this.dateFrom || this.dateTo || this.categoryFilter || this.paymentTypeFilter || this.staffFilter);
        },

        getCategoryLabel(cat) {
            const labels = { mr_cost: 'MR Cost', litigation: 'Litigation', other: 'Other' };
            return labels[cat] || cat || '-';
        },

        getPaymentTypeLabel(type) {
            const labels = { check: 'Check', card: 'Card', cash: 'Cash', wire: 'Wire', other: 'Other' };
            return labels[type] || type || '-';
        },

        goToCase(caseId) {
            if (caseId) {
                window.location.href = '/CMCdemo/frontend/pages/bl-cases/detail.php?id=' + caseId;
            }
        },

        exportCSV() {
            const params = buildQueryString({
                date_from: this.dateFrom,
                date_to: this.dateTo,
                category: this.categoryFilter,
                payment_type: this.paymentTypeFilter,
                staff_id: this.staffFilter,
                search: this.search,
            });
            window.open('/CMCdemo/backend/api/expense-report/export' + params, '_blank');
        },
    };
}
