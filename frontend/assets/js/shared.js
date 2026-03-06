/**
 * BLCM Shared Utilities & List Page Base Mixin
 * ─────────────────────────────────────────────
 * Global helpers and a reusable mixin for all list pages.
 *
 * Global functions (available everywhere):
 *   fmt(val)                  – format number to 2 decimals
 *   buildPageNumbers(pg)      – smart pagination array [1,'...',3,4,5,'...',10]
 *
 * Mixin usage:
 *   return {
 *       ...listPageBase('api-endpoint', { defaultSort: 'name', perPage: 50 }),
 *       // page-specific state & methods…
 *   };
 */

// ═══════════════════════════════════════════════════════════════
//  Global Utility Functions
// ═══════════════════════════════════════════════════════════════

/**
 * Format a number to 2 decimal places with locale separators.
 * Useful for currency amounts where you don't want the '$' prefix.
 * @param {number|string} val
 * @returns {string} e.g. "1,234.56"
 */
function fmt(val) {
    return parseFloat(val || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

/**
 * Build smart pagination page-number array with ellipsis.
 * Shows first page, last page, and ±2 pages around current.
 *
 * @param {object|null} pagination  – { current_page|page, total_pages }
 * @returns {Array}  e.g. [1, '...', 4, 5, 6, '...', 10]
 */
function buildPageNumbers(pagination) {
    if (!pagination) return [];
    const current = pagination.current_page || pagination.page || 1;
    const total   = pagination.total_pages || 1;
    const pages   = [];
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || Math.abs(i - current) <= 2) {
            pages.push(i);
        } else if (pages[pages.length - 1] !== '...') {
            pages.push('...');
        }
    }
    return pages;
}

// ═══════════════════════════════════════════════════════════════
//  List Page Base Mixin
// ═══════════════════════════════════════════════════════════════

/**
 * Shared mixin for server-paginated list pages.
 *
 * Provides: items, loading, search, sortBy, sortDir, pagination,
 *           loadData, sort, goToPage, paginationPages, handleSearch,
 *           resetFilters, hasActiveFilters.
 *
 * Page-specific hooks (define on your component):
 *   _resetPageFilters()  – called by resetFilters() to clear custom filters
 *   _hasPageFilters()    – return true if any custom filter is active
 *
 * @param {string} apiEndpoint               – e.g. 'referrals', 'billing/list'
 * @param {object} [options]
 * @param {string} [options.defaultSort='created_at']
 * @param {string} [options.defaultDir='desc']
 * @param {number} [options.perPage=50]
 * @param {function} [options.filtersToParams]  – returns extra query-param object
 */
function listPageBase(apiEndpoint, options = {}) {
    const config = {
        defaultSort:    options.defaultSort    || 'created_at',
        defaultDir:     options.defaultDir     || 'desc',
        perPage:        options.perPage        || 50,
        filtersToParams: options.filtersToParams || function () { return {}; },
    };

    return {
        // ── Reactive state ──────────────────────────────────────
        items:      [],
        loading:    true,
        search:     '',
        sortBy:     config.defaultSort,
        sortDir:    config.defaultDir,
        pagination: null,
        _searchTimer: null,

        // ── Data loading ────────────────────────────────────────
        async loadData(page = 1) {
            this.loading = true;
            try {
                const filterParams = config.filtersToParams.call(this);
                const params = buildQueryString({
                    search:   this.search,
                    sort_by:  this.sortBy,
                    sort_dir: this.sortDir,
                    page,
                    per_page: config.perPage,
                    ...filterParams,
                });
                const res  = await api.get(apiEndpoint + params);
                this.items = res.data || [];

                // Normalise pagination (APIs may use 'page' or 'current_page')
                const pg = res.pagination || null;
                this.pagination = pg
                    ? { ...pg, current_page: pg.current_page || pg.page }
                    : null;

                if (res.summary) this.summary   = res.summary;
                if (res.staff)   this.staffList  = res.staff;
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.loading = false;
        },

        // ── Sorting ─────────────────────────────────────────────
        sort(column) {
            if (this.sortBy === column) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy  = column;
                this.sortDir = 'asc';
            }
            this.loadData(1);
        },

        // ── Pagination ──────────────────────────────────────────
        goToPage(page) {
            if (page >= 1 && (!this.pagination || page <= this.pagination.total_pages)) {
                this.loadData(page);
            }
        },

        paginationPages() {
            return buildPageNumbers(this.pagination);
        },

        // ── Search (debounced 300 ms) ───────────────────────────
        handleSearch() {
            clearTimeout(this._searchTimer);
            this._searchTimer = setTimeout(() => this.loadData(1), 300);
        },

        // ── Filter reset ────────────────────────────────────────
        resetFilters() {
            this.search  = '';
            this.sortBy  = config.defaultSort;
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
        },
    };
}
