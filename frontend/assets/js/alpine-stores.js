/**
 * CMC - Alpine.js Global Stores
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
            window.location.href = '/CMCdemo/frontend/pages/auth/login.php';
        }
    });

    // Messages Store
    Alpine.store('messages', {
        unreadCount: 0,
        _interval: null,

        init() {
            this.load();
            this._interval = setInterval(() => this.load(), 30000);
        },

        async load() {
            try {
                const res = await api.get('auth/me');
                this.unreadCount = res.data?.unread_messages || 0;
            } catch (e) {}
        }
    });

    // Sidebar Store
    Alpine.store('sidebar', {
        collapsed: localStorage.getItem('cmc_sidebar_collapsed') === 'true',

        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('cmc_sidebar_collapsed', this.collapsed);
        }
    });
});
