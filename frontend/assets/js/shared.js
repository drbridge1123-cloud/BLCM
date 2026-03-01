/**
 * CMC Shared List Page Base Mixin
 * Provides common pagination, filtering, sorting for all list pages
 */
function listPageBase(apiEndpoint, options = {}) {
    const config = {
        defaultSort: options.defaultSort || 'created_at',
        defaultDir: options.defaultDir || 'desc',
        perPage: options.perPage || 25,
        filtersToParams: options.filtersToParams || function() { return {}; },
    };

    return {
        items: [],
        loading: true,
        search: '',
        sortBy: config.defaultSort,
        sortDir: config.defaultDir,
        pagination: null,

        async loadData(page = 1) {
            this.loading = true;
            try {
                const filterParams = config.filtersToParams.call(this);
                const params = buildQueryString({
                    search: this.search,
                    sort_by: this.sortBy,
                    sort_dir: this.sortDir,
                    page,
                    per_page: config.perPage,
                    ...filterParams
                });
                const res = await api.get(apiEndpoint + params);
                this.items = res.data || [];
                this.pagination = res.pagination || null;
                if (res.summary) this.summary = res.summary;
                if (res.staff) this.staffList = res.staff;
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.loading = false;
        },

        sort(column) {
            if (this.sortBy === column) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = column;
                this.sortDir = 'asc';
            }
            this.loadData(1);
        },

        goToPage(page) {
            if (page >= 1 && (!this.pagination || page <= this.pagination.total_pages)) {
                this.loadData(page);
            }
        },

        resetFilters() {
            this.search = '';
            this.sortBy = config.defaultSort;
            this.sortDir = config.defaultDir;
            if (typeof this._resetPageFilters === 'function') {
                this._resetPageFilters();
            }
            this.loadData(1);
        },

        hasActiveFilters() {
            const base = !!this.search;
            if (typeof this._hasPageFilters === 'function') {
                return base || this._hasPageFilters();
            }
            return base;
        }
    };
}
