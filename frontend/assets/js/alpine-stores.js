/**
 * BLCM - Alpine.js Global Stores
 */

document.addEventListener('alpine:init', () => {

    // Auth Store
    Alpine.store('auth', {
        user: null,
        loading: true,

        async load() {
            try {
                const res = await api.get('auth/me');
                this.user = res.data;
                // Push unread count to messages store (avoids separate auth/me call)
                Alpine.store('messages').unreadCount = res.data?.unread_messages || 0;
            } catch (e) {
                this.user = null;
            }
            this.loading = false;
        },

        get isAdmin() { return this.user?.role === 'admin'; },
        get isManager() { return this.user?.role === 'manager'; },
        get isAttorney() { return this.user?.role === 'attorney'; },
        get isParalegal() { return this.user?.role === 'paralegal'; },
        get isBilling() { return this.user?.role === 'billing'; },
        get isStaff() { return ['paralegal', 'billing'].includes(this.user?.role); },

        hasPermission(perm) {
            if (!this.user) return false;
            if (this.user.role === 'admin') return true;
            return (this.user.permissions || []).includes(perm);
        },

        async logout() {
            try { await api.post('auth/logout'); } catch (e) {}
            window.location.href = '/blcm/frontend/pages/auth/login.php';
        }
    });

    // Messages Store (initial count comes from auth store — no duplicate auth/me call)
    Alpine.store('messages', {
        unreadCount: 0,
        _interval: null,

        init() {
            // Initial unreadCount is set by auth.load() — no fetch needed here
            // Periodic refresh reuses same endpoint and updates both stores
            this._interval = setInterval(() => this._refresh(), 30000);
        },

        async _refresh() {
            try {
                const res = await api.get('auth/me');
                this.unreadCount = res.data?.unread_messages || 0;
                // Keep auth store in sync
                if (res.data) Alpine.store('auth').user = res.data;
            } catch (e) {}
        }
    });

    // Staff Store (cached user list — avoids redundant /api/users calls)
    Alpine.store('staff', {
        _list: null,
        _fetchedAt: 0,
        _pending: null,
        CACHE_TTL: 5 * 60 * 1000, // 5 minutes

        async getList() {
            if (this._list && (Date.now() - this._fetchedAt) < this.CACHE_TTL) {
                return this._list;
            }
            // Deduplicate concurrent calls — return same promise
            if (this._pending) return this._pending;
            this._pending = api.get('users?active_only=1').then(res => {
                this._list = res.data || [];
                this._fetchedAt = Date.now();
                this._pending = null;
                return this._list;
            }).catch(e => {
                this._pending = null;
                throw e;
            });
            return this._pending;
        },

        invalidate() {
            this._list = null;
            this._fetchedAt = 0;
            this._pending = null;
        }
    });

    // Sidebar Store
    Alpine.store('sidebar', {
        collapsed: localStorage.getItem('blcm_sidebar_collapsed') === 'true',

        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('blcm_sidebar_collapsed', this.collapsed);
        }
    });
});
