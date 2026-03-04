<aside class="sidebar fixed left-0 top-0 z-30 flex flex-col"
       :class="{ 'collapsed': $store.sidebar.collapsed }"
       style="background:#161616; border-right:1px solid rgba(255,255,255,.07);">

    <!-- Logo -->
    <div style="padding:18px 18px 16px; border-bottom:1px solid rgba(255,255,255,.07); display:flex; align-items:center; gap:10px;">
        <div style="width:32px; height:32px; border-radius:8px; background:linear-gradient(135deg,#C9A84C,#e8c96e); display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; color:#1a1a1a; flex-shrink:0;">BL</div>
        <span class="sidebar-text" style="font-size:15px; font-weight:700; color:#efefef;">Bridge Law</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto">
        <div class="sb-nav">

            <!-- ── Top (no section label) ── -->
            <a href="/CMCdemo/frontend/pages/dashboard/index.php" class="sb-item <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="sidebar-text sb-label">Dashboard</span>
            </a>

            <a href="/CMCdemo/frontend/pages/providers/index.php" class="sb-item <?= in_array(($currentPage ?? ''), ['providers','templates']) ? 'active' : '' ?>">
                <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span class="sidebar-text sb-label">Database</span>
            </a>

            <a href="/CMCdemo/frontend/pages/messages/index.php" class="sb-item <?= ($currentPage ?? '') === 'messages' ? 'active' : '' ?>" x-data>
                <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                <span class="sidebar-text sb-label" style="flex:1;">Messages</span>
                <template x-if="$store.messages && $store.messages.unreadCount > 0">
                    <span class="sb-badge sb-badge-red" x-text="$store.messages.unreadCount > 9 ? '9+' : $store.messages.unreadCount"></span>
                </template>
            </a>

            <!-- ── CASES ── -->
            <div class="sb-section-label">Cases</div>

            <template x-if="$store.auth.hasPermission('cases')">
                <a href="/CMCdemo/frontend/pages/bl-cases/index.php" class="sb-item <?= ($currentPage ?? '') === 'cases' ? 'active' : '' ?>">
                    <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="sidebar-text sb-label">BL Cases</span>
                </a>
            </template>

            <template x-if="$store.auth.hasPermission('traffic')">
                <a href="/CMCdemo/frontend/pages/traffic/index.php" class="sb-item <?= ($currentPage ?? '') === 'traffic' ? 'active' : '' ?>">
                    <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="sidebar-text sb-label">Traffic Cases</span>
                </a>
            </template>

            <template x-if="$store.auth.hasPermission('referrals')">
                <a href="/CMCdemo/frontend/pages/referrals/index.php" class="sb-item <?= ($currentPage ?? '') === 'referrals' ? 'active' : '' ?>">
                    <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="sidebar-text sb-label">Referrals</span>
                </a>
            </template>

            <!-- ── TRACKERS ── -->
            <template x-if="$store.auth.hasPermission('prelitigation_tracker') || $store.auth.hasPermission('mr_tracker') || $store.auth.hasPermission('attorney_cases') || $store.auth.hasPermission('accounting_tracker')">
                <div class="sb-section-label">Trackers</div>
            </template>

            <template x-if="$store.auth.hasPermission('prelitigation_tracker')">
                <a href="/CMCdemo/frontend/pages/prelitigation/index.php" class="sb-item <?= ($currentPage ?? '') === 'prelitigation_tracker' ? 'active' : '' ?>">
                    <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span class="sidebar-text sb-label">Prelitigation</span>
                </a>
            </template>

            <template x-if="$store.auth.hasPermission('mr_tracker')">
                <a href="/CMCdemo/frontend/pages/billing/index.php" class="sb-item <?= ($currentPage ?? '') === 'mr_tracker' ? 'active' : '' ?>">
                    <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span class="sidebar-text sb-label">Billing</span>
                </a>
            </template>

            <template x-if="$store.auth.hasPermission('attorney_cases')">
                <a href="/CMCdemo/frontend/pages/attorney/index.php" class="sb-item <?= ($currentPage ?? '') === 'attorney_cases' ? 'active' : '' ?>">
                    <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    <span class="sidebar-text sb-label">Attorney</span>
                </a>
            </template>

            <template x-if="$store.auth.hasPermission('accounting_tracker')">
                <a href="/CMCdemo/frontend/pages/accounting/index.php" class="sb-item <?= in_array(($currentPage ?? ''), ['accounting_tracker','bank_reconciliation','expense_report']) ? 'active' : '' ?>">
                    <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span class="sidebar-text sb-label">Accounting</span>
                </a>
            </template>

            <!-- ── FINANCE ── -->
            <template x-if="$store.auth.hasPermission('commissions')">
                <div class="sb-section-label">Finance</div>
            </template>

            <template x-if="$store.auth.hasPermission('commissions')">
                <a href="/CMCdemo/frontend/pages/commissions/index.php" class="sb-item <?= ($currentPage ?? '') === 'commissions' ? 'active' : '' ?>">
                    <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="sidebar-text sb-label">Commissions</span>
                </a>
            </template>

            <!-- ── ADMIN ── -->
            <template x-if="$store.auth.hasPermission('reports') || $store.auth.hasPermission('goals') || $store.auth.hasPermission('users') || $store.auth.hasPermission('activity_log')">
                <div class="sb-section-label">Admin</div>
            </template>

            <template x-if="$store.auth.hasPermission('reports')">
                <a href="/CMCdemo/frontend/pages/reports/index.php" class="sb-item <?= ($currentPage ?? '') === 'reports' ? 'active' : '' ?>">
                    <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span class="sidebar-text sb-label">Performance</span>
                </a>
            </template>

            <template x-if="$store.auth.hasPermission('goals')">
                <a href="/CMCdemo/frontend/pages/reports/goals.php" class="sb-item <?= ($currentPage ?? '') === 'goals' ? 'active' : '' ?>">
                    <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span class="sidebar-text sb-label">Goals</span>
                </a>
            </template>

            <template x-if="$store.auth.hasPermission('users')">
                <a href="/CMCdemo/frontend/pages/admin/users.php" class="sb-item <?= in_array(($currentPage ?? ''), ['users','data_management']) ? 'active' : '' ?>">
                    <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="sidebar-text sb-label">Admin Control</span>
                </a>
            </template>

            <template x-if="$store.auth.hasPermission('activity_log')">
                <a href="/CMCdemo/frontend/pages/admin/activity-log.php" class="sb-item <?= ($currentPage ?? '') === 'activity_log' ? 'active' : '' ?>">
                    <svg class="sb-icon" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span class="sidebar-text sb-label">Activity Log</span>
                </a>
            </template>

        </div>
    </nav>

    <!-- User info at bottom -->
    <div style="padding:12px 10px; border-top:1px solid rgba(255,255,255,.07);" x-data x-show="!$store.auth.loading">
        <template x-if="$store.auth.user">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:32px; height:32px; border-radius:50%; background:rgba(201,168,76,.12); border:1px solid rgba(201,168,76,.25); display:flex; align-items:center; justify-content:center; color:#C9A84C; font-size:12px; font-weight:700; flex-shrink:0;"
                     x-text="($store.auth.user?.display_name || $store.auth.user?.full_name)?.charAt(0) || 'U'"></div>
                <div class="sidebar-text" style="flex:1; min-width:0;">
                    <div style="font-size:12px; font-weight:600; color:#efefef; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="($store.auth.user?.display_name || $store.auth.user?.full_name || 'User') + ' (' + ($store.auth.user?.role ? $store.auth.user.role.charAt(0).toUpperCase() + $store.auth.user.role.slice(1) : 'User') + ')'"></div>
                    <div style="font-size:10px; color:rgba(255,255,255,.28);" x-text="$store.auth.user?.role || 'user'"></div>
                </div>
                <button @click="$store.auth.logout()" title="Logout"
                        class="sidebar-text" style="background:none; border:none; padding:4px; cursor:pointer; flex-shrink:0; opacity:.4; transition:opacity .15s;"
                        onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='.4'">
                    <svg width="14" height="14" fill="none" stroke="#efefef" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Collapse toggle -->
    <button @click="$store.sidebar.toggle()"
            style="position:absolute; right:-12px; top:80px; width:24px; height:24px; background:#2a2a2a; border:1px solid rgba(255,255,255,.1); border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,.3);"
            onmouseover="this.style.background='#3a3a3a'" onmouseout="this.style.background='#2a2a2a'">
        <svg width="14" height="14" fill="none" stroke="rgba(255,255,255,.5)" viewBox="0 0 24 24" style="transition:transform .3s;" :style="$store.sidebar.collapsed ? 'transform:rotate(180deg)' : ''">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
</aside>
