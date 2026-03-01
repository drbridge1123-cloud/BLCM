<aside class="sidebar fixed left-0 top-0 bg-navy text-white z-30 flex flex-col"
       :class="{ 'collapsed': $store.sidebar.collapsed }">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 py-4 border-b border-navy-border">
        <div class="w-8 h-8 bg-gold rounded-lg flex items-center justify-center font-bold text-sm text-navy flex-shrink-0">
            CM
        </div>
        <span class="sidebar-text font-semibold text-lg">CMC</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-4 overflow-y-auto">
        <ul class="space-y-1 px-3">
            <!-- Dashboard -->
            <li>
                <a href="/CMC/frontend/pages/dashboard/index.php"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'dashboard' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            <!-- Cases -->
            <li>
                <a href="/CMC/frontend/pages/cases/index.php"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'cases' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="sidebar-text">Cases</span>
                </a>
            </li>
            <!-- Database (Providers) -->
            <li>
                <a href="/CMC/frontend/pages/providers/index.php"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'providers' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="sidebar-text">Database</span>
                </a>
            </li>
            <!-- Messages -->
            <li>
                <a href="/CMC/frontend/pages/messages/index.php"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'messages' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>"
                   x-data>
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <span class="sidebar-text flex items-center gap-2">
                        Messages
                        <template x-if="$store.messages && $store.messages.unreadCount > 0">
                            <span class="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full leading-none"
                                  x-text="$store.messages.unreadCount > 9 ? '9+' : $store.messages.unreadCount"></span>
                        </template>
                    </span>
                </a>
            </li>
            <!-- Templates -->
            <li>
                <a href="/CMC/frontend/pages/admin/templates.php"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'templates' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="sidebar-text">Templates</span>
                </a>
            </li>
        </ul>

        <!-- Trackers Section -->
        <template x-if="$store.auth.hasPermission('prelitigation_tracker') || $store.auth.hasPermission('mr_tracker') || $store.auth.hasPermission('attorney_cases') || $store.auth.hasPermission('accounting_tracker')">
            <div>
                <div class="border-t border-navy-border my-4 mx-3"></div>
                <div class="px-5 mb-2">
                    <span class="sidebar-text text-xs font-semibold text-slate-500 uppercase tracking-wider">Trackers</span>
                </div>
                <ul class="space-y-1 px-3">
                    <template x-if="$store.auth.hasPermission('prelitigation_tracker')">
                        <li>
                            <a href="/CMC/frontend/pages/prelitigation-tracker/index.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'prelitigation_tracker' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span class="sidebar-text">Prelitigation</span>
                            </a>
                        </li>
                    </template>
                    <template x-if="$store.auth.hasPermission('mr_tracker')">
                        <li>
                            <a href="/CMC/frontend/pages/mr-tracker/index.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'mr_tracker' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                <span class="sidebar-text">Billing Tracker</span>
                            </a>
                        </li>
                    </template>
                    <template x-if="$store.auth.hasPermission('attorney_cases')">
                        <li>
                            <a href="/CMC/frontend/pages/attorney-cases/index.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'attorney_cases' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                </svg>
                                <span class="sidebar-text">Attorney Tracker</span>
                            </a>
                        </li>
                    </template>
                    <template x-if="$store.auth.hasPermission('accounting_tracker')">
                        <li>
                            <a href="/CMC/frontend/pages/accounting-tracker/index.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'accounting_tracker' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span class="sidebar-text">Accounting</span>
                            </a>
                        </li>
                    </template>
                </ul>
            </div>
        </template>

        <!-- Legal Section -->
        <template x-if="$store.auth.hasPermission('traffic') || $store.auth.hasPermission('referrals')">
            <div>
                <div class="border-t border-navy-border my-4 mx-3"></div>
                <div class="px-5 mb-2">
                    <span class="sidebar-text text-xs font-semibold text-slate-500 uppercase tracking-wider">Legal</span>
                </div>
                <ul class="space-y-1 px-3">
                    <template x-if="$store.auth.hasPermission('traffic')">
                        <li>
                            <a href="/CMC/frontend/pages/traffic/index.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'traffic' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span class="sidebar-text">Traffic</span>
                            </a>
                        </li>
                    </template>
                    <template x-if="$store.auth.hasPermission('referrals')">
                        <li>
                            <a href="/CMC/frontend/pages/referrals/index.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'referrals' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="sidebar-text">Referrals</span>
                            </a>
                        </li>
                    </template>
                </ul>
            </div>
        </template>

        <!-- Finance Section -->
        <template x-if="$store.auth.hasPermission('commissions') || $store.auth.hasPermission('expense_report') || $store.auth.hasPermission('bank_reconciliation')">
            <div>
                <div class="border-t border-navy-border my-4 mx-3"></div>
                <div class="px-5 mb-2">
                    <span class="sidebar-text text-xs font-semibold text-slate-500 uppercase tracking-wider">Finance</span>
                </div>
                <ul class="space-y-1 px-3">
                    <template x-if="$store.auth.hasPermission('commissions')">
                        <li>
                            <a href="/CMC/frontend/pages/commissions/index.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'commissions' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="sidebar-text">Commissions</span>
                            </a>
                        </li>
                    </template>
                    <template x-if="$store.auth.hasPermission('expense_report')">
                        <li>
                            <a href="/CMC/frontend/pages/admin/expense-report.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'expense_report' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span class="sidebar-text">Expense Report</span>
                            </a>
                        </li>
                    </template>
                    <template x-if="$store.auth.hasPermission('bank_reconciliation')">
                        <li>
                            <a href="/CMC/frontend/pages/admin/bank-reconciliation.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'bank_reconciliation' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span class="sidebar-text">Reconciliation</span>
                            </a>
                        </li>
                    </template>
                </ul>
            </div>
        </template>

        <!-- Settings Section -->
        <template x-if="$store.auth.hasPermission('users') || $store.auth.hasPermission('activity_log') || $store.auth.hasPermission('data_management') || $store.auth.hasPermission('reports') || $store.auth.hasPermission('goals')">
            <div>
                <div class="border-t border-navy-border my-4 mx-3"></div>
                <div class="px-5 mb-2">
                    <span class="sidebar-text text-xs font-semibold text-slate-500 uppercase tracking-wider">Settings</span>
                </div>
                <ul class="space-y-1 px-3">
                    <template x-if="$store.auth.hasPermission('reports')">
                        <li>
                            <a href="/CMC/frontend/pages/reports/index.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'reports' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span class="sidebar-text">Reports</span>
                            </a>
                        </li>
                    </template>
                    <template x-if="$store.auth.hasPermission('goals')">
                        <li>
                            <a href="/CMC/frontend/pages/reports/goals.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'goals' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                                <span class="sidebar-text">Goals</span>
                            </a>
                        </li>
                    </template>
                    <template x-if="$store.auth.hasPermission('users')">
                        <li>
                            <a href="/CMC/frontend/pages/admin/users.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'users' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span class="sidebar-text">Users</span>
                            </a>
                        </li>
                    </template>
                    <template x-if="$store.auth.hasPermission('activity_log')">
                        <li>
                            <a href="/CMC/frontend/pages/admin/activity-log.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'activity_log' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                <span class="sidebar-text">Activity Log</span>
                            </a>
                        </li>
                    </template>
                    <template x-if="$store.auth.hasPermission('data_management')">
                        <li>
                            <a href="/CMC/frontend/pages/admin/data-management.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'data_management' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                </svg>
                                <span class="sidebar-text">Data Management</span>
                            </a>
                        </li>
                    </template>
                </ul>
            </div>
        </template>
    </nav>

    <!-- User info at bottom -->
    <div class="border-t border-navy-border px-4 py-3" x-data x-show="!$store.auth.loading">
        <template x-if="$store.auth.user">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gold flex items-center justify-center text-sm font-semibold text-navy flex-shrink-0"
                     x-text="$store.auth.user?.full_name?.charAt(0) || 'U'"></div>
                <div class="sidebar-text flex-1 min-w-0">
                    <div class="text-sm font-medium truncate" x-text="$store.auth.user?.full_name || 'User'"></div>
                    <div class="text-xs text-slate-400 capitalize" x-text="$store.auth.user?.role || 'user'"></div>
                </div>
                <button @click="$store.auth.logout()" title="Logout"
                        class="sidebar-text p-1.5 text-slate-400 hover:text-red-400 hover:bg-navy-light rounded transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Collapse toggle -->
    <button @click="$store.sidebar.toggle()"
            class="absolute -right-3 top-20 w-6 h-6 bg-navy-light rounded-full flex items-center justify-center text-slate-300 hover:bg-navy-border shadow-md">
        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': $store.sidebar.collapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
</aside>
