<!-- All sp- styles loaded from shared sp-design-system.css -->
<style>
/* Dashboard-specific styles */
.db-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 12px; }
.db-kpi {
    background: #fff; border-radius: 10px; border: 1px solid #e8e4dc;
    padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;
    transition: border-color .15s;
}
.db-kpi:hover { border-color: #C9A84C; }
.db-kpi-label {
    font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em;
    color: #8a8a82; font-family: 'IBM Plex Sans', sans-serif; margin-bottom: 2px;
}
.db-kpi-num {
    font-family: 'IBM Plex Mono', monospace; font-size: 22px; font-weight: 700; line-height: 1;
}
.db-kpi-icon { width: 20px; height: 20px; opacity: .5; }
.db-link-card {
    display: flex; align-items: center; justify-content: space-between;
    background: #fff; border-radius: 10px; border: 1px solid #e8e4dc;
    padding: 12px 16px; text-decoration: none; transition: all .15s;
}
.db-link-card:hover { border-color: #C9A84C; transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,.06); }
.db-link-sub { font-size: 10px; margin-top: 2px; display: flex; gap: 8px; }
.db-section {
    background: #fff; border-radius: 10px; border: 1px solid #e8e4dc; overflow: hidden;
}
.db-section-header {
    padding: 10px 16px; border-bottom: 1px solid #f5f2ee;
    display: flex; align-items: center; justify-content: space-between;
}
.db-section-title {
    font-size: 12px; font-weight: 700; color: #1a2535; font-family: 'IBM Plex Sans', sans-serif;
}
.db-section-badge {
    font-size: 9px; font-weight: 700; font-family: 'IBM Plex Mono', monospace;
    padding: 2px 7px; border-radius: 8px;
}
.db-list-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 16px; border-bottom: 1px solid #f5f2ee; text-decoration: none;
    transition: background .1s; color: inherit;
}
.db-list-item:hover { background: #fdfcf9; }
</style>

<div x-data="dashboardPage()">

    <!-- Pending Case Assignments -->
    <?php include __DIR__ . '/../../components/_pending-assignments.php'; ?>

    <!-- ═══ BLCM Link Cards (Attorney, Commission, Traffic, Referrals) ═══ -->
    <div class="db-kpi-grid">
        <template x-if="data.attorney_cases && $store.auth.hasPermission('attorney_cases')">
            <a href="/blcm/frontend/pages/attorney/index.php" class="db-link-card">
                <div>
                    <div class="db-kpi-label">Attorney Cases</div>
                    <div class="db-kpi-num" style="color:#1a2535; font-size:18px;" x-text="data.attorney_cases.active_count ?? '-'"></div>
                    <div class="db-link-sub">
                        <span style="color:#2563eb;" x-text="(data.attorney_cases.demand_count || 0) + ' demand'"></span>
                        <span style="color:#ea580c;" x-text="(data.attorney_cases.litigation_count || 0) + ' lit'"></span>
                    </div>
                </div>
                <svg class="db-kpi-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                </svg>
            </a>
        </template>
        <template x-if="data.prelitigation && $store.auth.hasPermission('prelitigation_tracker')">
            <a href="/blcm/frontend/pages/prelitigation/index.php" class="db-link-card">
                <div>
                    <div class="db-kpi-label">Prelitigation</div>
                    <div class="db-kpi-num" style="color:#1a2535; font-size:18px;" x-text="data.prelitigation.active_count ?? '-'"></div>
                    <div class="db-link-sub">
                        <span style="color:#2563eb;" x-text="(data.prelitigation.in_treatment || 0) + ' treating'"></span>
                        <span style="color:#1a9e6a;" x-text="(data.prelitigation.treatment_done || 0) + ' done'"></span>
                    </div>
                </div>
                <svg class="db-kpi-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </a>
        </template>
        <template x-if="data.billing && $store.auth.hasPermission('mr_tracker')">
            <a href="/blcm/frontend/pages/billing/index.php" class="db-link-card">
                <div>
                    <div class="db-kpi-label">Billing / MR</div>
                    <div class="db-kpi-num" style="color:#1a2535; font-size:18px;" x-text="data.billing.active_count ?? '-'"></div>
                    <div class="db-link-sub">
                        <span style="color:#2563eb;" x-text="(data.billing.requesting || 0) + ' requesting'"></span>
                        <span style="color:#ea580c;" x-text="(data.billing.follow_up || 0) + ' f/u'"></span>
                    </div>
                </div>
                <svg class="db-kpi-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </a>
        </template>
        <template x-if="data.accounting && $store.auth.hasPermission('accounting_tracker')">
            <a href="/blcm/frontend/pages/accounting/index.php" class="db-link-card">
                <div>
                    <div class="db-kpi-label">Accounting</div>
                    <div class="db-kpi-num" style="color:#1a2535; font-size:18px;" x-text="data.accounting.active_count ?? '-'"></div>
                    <div class="db-link-sub">
                        <span style="color:#C9A84C;" x-text="'$' + Number(data.accounting.total_settlement || 0).toLocaleString('en-US', {minimumFractionDigits:0})"></span>
                    </div>
                </div>
                <svg class="db-kpi-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </a>
        </template>
        <template x-if="data.commissions && $store.auth.hasPermission('commissions')">
            <a href="/blcm/frontend/pages/commissions/index.php" class="db-link-card">
                <div>
                    <div class="db-kpi-label">Commission</div>
                    <div style="font-family:'IBM Plex Mono',monospace; font-size:16px; font-weight:700; color:#C9A84C; line-height:1;" x-text="formatCurrency(data.commissions.total_commission)"></div>
                    <div class="db-link-sub">
                        <span style="color:#1a9e6a;" x-text="formatCurrency(data.commissions.paid_commission) + ' paid'"></span>
                    </div>
                </div>
                <svg class="db-kpi-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </a>
        </template>
        <template x-if="data.traffic && $store.auth.hasPermission('traffic')">
            <a href="/blcm/frontend/pages/traffic/index.php" class="db-link-card">
                <div>
                    <div class="db-kpi-label">Traffic Cases</div>
                    <div class="db-kpi-num" style="color:#1a2535; font-size:18px;" x-text="data.traffic.active_count ?? '-'"></div>
                    <div class="db-link-sub">
                        <span style="color:#8a8a82;" x-text="(data.traffic.resolved_count || 0) + ' resolved'"></span>
                    </div>
                </div>
                <svg class="db-kpi-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </a>
        </template>
        <template x-if="data.referrals && $store.auth.hasPermission('referrals')">
            <a href="/blcm/frontend/pages/referrals/index.php" class="db-link-card">
                <div>
                    <div class="db-kpi-label">Referrals</div>
                    <div class="db-kpi-num" style="color:#1a2535; font-size:18px;" x-text="data.referrals.total_entries ?? '-'"></div>
                    <div class="db-link-sub">
                        <span style="color:#8a8a82;" x-text="(data.referrals.month_count || 0) + ' this month'"></span>
                    </div>
                </div>
                <svg class="db-kpi-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
        </template>
    </div>

    <!-- ═══ Pending Requests Banner (Admin/Manager) ═══ -->
    <template x-if="data.pending_requests && (data.pending_requests.demand_requests > 0 || data.pending_requests.deadline_requests > 0)">
        <div class="db-section" style="margin-bottom:12px; border-color:rgba(217,119,6,.25);">
            <div class="db-section-header" style="background:rgba(217,119,6,.04);">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="color:#D97706; font-size:14px;">⚠</span>
                    <span class="db-section-title">Pending Requests</span>
                </div>
                <div style="display:flex; gap:6px;">
                    <template x-if="data.pending_requests.demand_requests > 0">
                        <span class="sp-status sp-status-unpaid" x-text="data.pending_requests.demand_requests + ' Demand'"></span>
                    </template>
                    <template x-if="data.pending_requests.deadline_requests > 0">
                        <span class="sp-status sp-status-in-progress" x-text="data.pending_requests.deadline_requests + ' Deadline'"></span>
                    </template>
                </div>
            </div>
            <div style="padding:8px 16px;">
                <a href="/blcm/frontend/pages/attorney/index.php" style="font-size:12px; color:#C9A84C; font-weight:600; text-decoration:none; font-family:'IBM Plex Sans',sans-serif;">Review pending requests →</a>
            </div>
        </div>
    </template>

    <!-- ═══ Escalation Alert Banner ═══ -->
    <template x-if="escalations.length > 0">
        <div class="db-section" style="margin-bottom:12px; border-color:rgba(231,76,60,.25);">
            <div class="db-section-header" style="background:rgba(231,76,60,.04);">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="color:#e74c3c; font-size:14px;">🔴</span>
                    <span class="db-section-title">Escalated Items</span>
                </div>
                <div style="display:flex; gap:6px;">
                    <template x-if="summary.escalation_admin > 0">
                        <span class="sp-status sp-status-rejected" x-text="summary.escalation_admin + ' Admin'"></span>
                    </template>
                    <template x-if="summary.escalation_action_needed > 0">
                        <span class="sp-status sp-status-unpaid" x-text="summary.escalation_action_needed + ' Action Needed'"></span>
                    </template>
                </div>
            </div>
            <div style="max-height:160px; overflow-y:auto;">
                <template x-for="item in escalations" :key="item.id">
                    <a :href="'/blcm/frontend/pages/bl-cases/detail.php?id=' + item.case_id" class="db-list-item">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span class="sp-status" :class="item.escalation_css" x-text="item.escalation_label"></span>
                            <span style="font-size:12px; font-weight:600; color:#1a2535;" x-text="item.provider_name"></span>
                            <span style="font-size:11px; color:#8a8a82;" x-text="item.case_number + ' - ' + item.client_name"></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:11px; color:#8a8a82;" x-text="item.assigned_name || 'Unassigned'"></span>
                            <span style="font-family:'IBM Plex Mono',monospace; font-size:11px; font-weight:700;"
                                  :style="'color:' + (item.escalation_tier === 'admin' ? '#e74c3c' : '#D97706')"
                                  x-text="item.days_past_deadline + 'd past deadline'"></span>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </template>

    <!-- ═══ Workload Section ═══ -->
    <div :style="'display:grid; gap:12px; margin-bottom:12px; align-items:stretch; grid-template-columns:' + (staffMetrics.view_type === 'team' && $store.auth.hasPermission('cases') ? '1fr 1fr' : '1fr')"

        <!-- Staff / Team Workload -->
        <div class="db-section">
            <div class="db-section-header">
                <span class="db-section-title">
                    <span x-show="staffMetrics.view_type === 'personal'">My Workload</span>
                    <span x-show="staffMetrics.view_type === 'team'">Team Workload</span>
                </span>
            </div>
            <div style="padding:12px 16px;">
                <template x-if="staffMetrics.view_type === 'personal'">
                    <div>
                        <div class="sp-tabs" style="justify-content:center; margin-bottom:10px;">
                            <span class="sp-tab on" style="cursor:default;">My Cases
                                <span class="sp-tab-count" x-text="staffMetrics.my_metrics?.my_cases || 0"></span>
                            </span>
                            <span class="sp-tab" style="cursor:default;">Records
                                <span class="sp-tab-count" style="background:rgba(37,99,235,.1); color:#2563eb;" x-text="staffMetrics.my_metrics?.my_providers || 0"></span>
                            </span>
                            <span class="sp-tab" style="cursor:default;">Followups
                                <span class="sp-tab-count" style="background:rgba(234,88,12,.12); color:#ea580c;" x-text="staffMetrics.my_metrics?.my_followup || 0"></span>
                            </span>
                            <span class="sp-tab" style="cursor:default;">Overdue
                                <span class="sp-tab-count" style="background:rgba(231,76,60,.12); color:#e74c3c;" x-text="staffMetrics.my_metrics?.my_overdue || 0"></span>
                            </span>
                        </div>
                        <div style="border-top:1px solid #f5f2ee; padding-top:8px; display:flex; gap:12px; font-size:10px; color:#8a8a82; font-family:'IBM Plex Sans',sans-serif;">
                            <span>Avg: <span style="font-weight:600;" x-text="staffMetrics.team_avg?.avg_cases || 0"></span> cases</span>
                            <span><span style="font-weight:600;" x-text="staffMetrics.team_avg?.avg_followup || 0"></span> f/u</span>
                            <span><span style="font-weight:600;" x-text="staffMetrics.team_avg?.avg_overdue || 0"></span> overdue</span>
                        </div>
                    </div>
                </template>
                <template x-if="staffMetrics.view_type === 'team'">
                    <div>
                        <div class="sp-tabs" style="justify-content:center; margin-bottom:10px;">
                            <span class="sp-tab on" style="cursor:default;">Total Cases
                                <span class="sp-tab-count" x-text="staffMetrics.totals?.total_cases || 0"></span>
                            </span>
                            <span class="sp-tab" style="cursor:default;">Records
                                <span class="sp-tab-count" style="background:rgba(37,99,235,.1); color:#2563eb;" x-text="staffMetrics.totals?.total_providers || 0"></span>
                            </span>
                            <span class="sp-tab" style="cursor:default;">Followups
                                <span class="sp-tab-count" style="background:rgba(234,88,12,.12); color:#ea580c;" x-text="staffMetrics.totals?.total_followup || 0"></span>
                            </span>
                            <span class="sp-tab" style="cursor:default;">Overdue
                                <span class="sp-tab-count" style="background:rgba(231,76,60,.12); color:#e74c3c;" x-text="staffMetrics.totals?.total_overdue || 0"></span>
                            </span>
                        </div>
                        <div style="border-top:1px solid #f5f2ee; padding-top:8px; max-height:400px; overflow-y:auto;">
                            <table class="sp-table sp-table-compact" style="font-size:11px;">
                                <thead>
                                    <tr>
                                        <th>Staff</th>
                                        <th class="center">Cases</th>
                                        <th class="center">Records</th>
                                        <th class="center">F/U</th>
                                        <th class="center">Overdue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="staff in staffMetrics.staff_metrics || []" :key="staff.id">
                                        <tr style="cursor:default;">
                                            <td style="font-size:11px;" x-text="staff.full_name"></td>
                                            <td style="text-align:center; font-size:11px;" x-text="staff.case_count"></td>
                                            <td style="text-align:center; font-size:11px;">
                                                <span :style="staff.provider_count > 0 ? 'color:#2563eb; font-weight:700;' : ''" x-text="staff.provider_count"></span>
                                            </td>
                                            <td style="text-align:center; font-size:11px;">
                                                <span :style="staff.followup_count > 0 ? 'color:#ea580c; font-weight:700;' : ''" x-text="staff.followup_count"></span>
                                            </td>
                                            <td style="text-align:center; font-size:11px;">
                                                <span :style="staff.overdue_count > 0 ? 'color:#e74c3c; font-weight:700;' : ''" x-text="staff.overdue_count"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Recent Cases — admin/manager only (next to Team Workload) -->
        <template x-if="staffMetrics.view_type === 'team' && $store.auth.hasPermission('cases')">
            <div class="db-section" style="display:flex; flex-direction:column;">
                <div class="db-section-header">
                    <span class="db-section-title">Recent Cases</span>
                    <a href="/blcm/frontend/pages/bl-cases/index.php" style="font-size:11px; color:#C9A84C; font-weight:600; text-decoration:none; font-family:'IBM Plex Sans',sans-serif;">View All →</a>
                </div>
                <div style="flex:1; overflow-y:auto;">
                    <table class="sp-table sp-table-compact" style="font-size:11px;">
                        <thead><tr><th>Case #</th><th>Client</th><th class="center">Progress</th><th>Status</th></tr></thead>
                        <tbody>
                            <template x-if="cases.length === 0">
                                <tr style="cursor:default;"><td colspan="4" class="sp-empty">No open cases</td></tr>
                            </template>
                            <template x-for="c in cases" :key="c.id">
                                <tr @click="window.location.href='/blcm/frontend/pages/bl-cases/detail.php?id='+c.id">
                                    <td><span class="sp-case-num" x-text="c.case_number"></span></td>
                                    <td><span class="sp-client" x-text="c.client_name"></span></td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:6px; justify-content:center;">
                                            <div style="width:40px; height:4px; background:#f0ede8; border-radius:3px; overflow:hidden;">
                                                <div style="height:100%; background:#1a9e6a; border-radius:3px;" :style="'width:' + (c.provider_total > 0 ? Math.round(c.provider_done/c.provider_total*100) : 0) + '%'"></div>
                                            </div>
                                            <span style="font-family:'IBM Plex Mono',monospace; font-size:9px; font-weight:600; color:#8a8a82;" x-text="c.provider_done + '/' + c.provider_total"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="sp-stage" :class="'sp-stage-' + ({collecting:'demand-write',verification:'demand-review',completed:'settled',rfd:'demand-sent',fbc:'demand-review',prelitigation:'litigation',accounting:'mediation',disbursement:'trial-set'}[c.status] || '')"
                                              x-text="getStatusLabel(c.status)"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </div>

    <!-- ═══ Follow-ups, Overdue & Recent Cases ═══ -->
    <template x-if="$store.auth.hasPermission('cases')">
        <div :style="'display:grid; gap:12px; margin-bottom:12px; grid-template-columns:' + (staffMetrics.view_type === 'personal' ? '1fr 1fr 1fr' : '1fr 1fr')">

            <!-- Recent Cases — staff only (1st column) -->
            <template x-if="staffMetrics.view_type === 'personal'">
                <div class="db-section" style="display:flex; flex-direction:column;">
                    <div class="db-section-header">
                        <span class="db-section-title">Recent Cases</span>
                        <a href="/blcm/frontend/pages/bl-cases/index.php" style="font-size:11px; color:#C9A84C; font-weight:600; text-decoration:none; font-family:'IBM Plex Sans',sans-serif;">View All →</a>
                    </div>
                    <div style="flex:1; max-height:208px; overflow-y:auto;">
                        <table class="sp-table sp-table-compact" style="font-size:11px;">
                            <thead><tr><th>Case #</th><th>Client</th><th>Status</th></tr></thead>
                            <tbody>
                                <template x-if="cases.length === 0">
                                    <tr style="cursor:default;"><td colspan="3" class="sp-empty">No open cases</td></tr>
                                </template>
                                <template x-for="c in cases" :key="c.id">
                                    <tr @click="window.location.href='/blcm/frontend/pages/bl-cases/detail.php?id='+c.id">
                                        <td><span class="sp-case-num" x-text="c.case_number"></span></td>
                                        <td><span class="sp-client" x-text="c.client_name"></span></td>
                                        <td>
                                            <span class="sp-stage" :class="'sp-stage-' + ({collecting:'demand-write',verification:'demand-review',completed:'settled',rfd:'demand-sent',fbc:'demand-review',prelitigation:'litigation',accounting:'mediation',disbursement:'trial-set'}[c.status] || '')"
                                                  x-text="getStatusLabel(c.status)"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <!-- Follow-ups Due -->
            <div class="db-section">
                <div class="db-section-header">
                    <span class="db-section-title">Follow-ups Due</span>
                    <span class="db-section-badge" style="background:rgba(234,88,12,.08); color:#ea580c; border:1px solid rgba(234,88,12,.15);" x-text="followups.length"></span>
                </div>
                <div style="max-height:208px; overflow-y:auto;">
                    <template x-if="followups.length === 0">
                        <div class="sp-empty" style="padding:24px 0;">No follow-ups due</div>
                    </template>
                    <template x-for="item in followups" :key="item.id">
                        <a :href="'/blcm/frontend/pages/bl-cases/detail.php?id=' + item.case_id" class="db-list-item">
                            <div>
                                <span style="font-size:12px; font-weight:600; color:#1a2535;" x-text="item.provider_name"></span>
                                <span style="font-size:10px; color:#8a8a82; margin-left:4px;" x-text="item.case_number + ' · ' + item.client_name"></span>
                            </div>
                            <span style="font-family:'IBM Plex Mono',monospace; font-size:10px; font-weight:700; color:#ea580c;" x-text="item.days_since_request + 'd ago'"></span>
                        </a>
                    </template>
                </div>
            </div>

            <!-- Overdue Items -->
            <div class="db-section">
                <div class="db-section-header">
                    <span class="db-section-title">Overdue Items</span>
                    <span class="db-section-badge" style="background:rgba(231,76,60,.08); color:#e74c3c; border:1px solid rgba(231,76,60,.15);" x-text="overdueItems.length"></span>
                </div>
                <div style="max-height:208px; overflow-y:auto;">
                    <template x-if="overdueItems.length === 0">
                        <div class="sp-empty" style="padding:24px 0;">No overdue items</div>
                    </template>
                    <template x-for="item in overdueItems" :key="item.id">
                        <a :href="'/blcm/frontend/pages/bl-cases/detail.php?id=' + item.case_id" class="db-list-item">
                            <div>
                                <span style="font-size:12px; font-weight:600; color:#1a2535;" x-text="item.provider_name"></span>
                                <span style="font-size:10px; color:#8a8a82; margin-left:4px;" x-text="item.case_number + ' · ' + item.client_name"></span>
                            </div>
                            <span style="font-family:'IBM Plex Mono',monospace; font-size:10px; font-weight:700; color:#e74c3c;" x-text="item.days_overdue + 'd overdue'"></span>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <!-- ═══ Upcoming Deadlines ═══ -->
    <template x-if="data.upcoming_deadlines && data.upcoming_deadlines.length > 0">
        <div class="db-section" style="margin-bottom:12px;">
            <div class="db-section-header">
                <span class="db-section-title">Upcoming Deadlines</span>
                <span style="font-size:10px; color:#8a8a82; font-family:'IBM Plex Sans',sans-serif;">Next 14 days</span>
            </div>
            <div style="max-height:208px; overflow-y:auto;">
                <template x-for="dl in data.upcoming_deadlines" :key="dl.id">
                    <div class="db-list-item">
                        <div style="min-width:0;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="dl.client_name"></div>
                            <div class="sp-case-num" x-text="dl.case_number"></div>
                        </div>
                        <span class="sp-days-badge"
                              :class="dl.days_remaining <= 3 ? 'sp-days-over' : dl.days_remaining <= 7 ? 'sp-days-warn' : 'sp-days-ok'"
                              x-text="dl.days_remaining + 'd'"></span>
                    </div>
                </template>
            </div>
        </div>
    </template>

</div>
