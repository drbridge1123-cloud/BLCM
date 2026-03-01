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
.db-quick-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; padding: 10px; }
.db-quick-link {
    display: flex; align-items: center; gap: 8px; padding: 8px 12px;
    border-radius: 7px; font-size: 12px; font-weight: 500; color: #3D4F63;
    text-decoration: none; transition: background .1s; font-family: 'IBM Plex Sans', sans-serif;
}
.db-quick-link:hover { background: #fafaf8; }
.db-quick-icon { color: #C9A84C; font-size: 14px; }
</style>

<div x-data="dashboardPage()" x-init="init()">

    <!-- ═══ Top KPI Row: MR Metrics ═══ -->
    <div class="db-kpi-grid">
        <div class="db-kpi">
            <div>
                <div class="db-kpi-label">Open Cases</div>
                <div class="db-kpi-num" style="color:#1a2535;" x-text="summary.active_cases ?? '-'"></div>
            </div>
            <svg class="db-kpi-icon text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="db-kpi">
            <div>
                <div class="db-kpi-label">Requesting</div>
                <div class="db-kpi-num" style="color:#D97706;" x-text="summary.requesting_count ?? '-'"></div>
            </div>
            <svg class="db-kpi-icon" style="color:#D97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="db-kpi">
            <div>
                <div class="db-kpi-label">Follow-ups Due</div>
                <div class="db-kpi-num" style="color:#ea580c;" x-text="summary.followup_due ?? '-'"></div>
            </div>
            <svg class="db-kpi-icon" style="color:#ea580c;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="db-kpi">
            <div>
                <div class="db-kpi-label">Overdue</div>
                <div class="db-kpi-num" style="color:#e74c3c;" x-text="summary.overdue_count ?? '-'"></div>
            </div>
            <svg class="db-kpi-icon" style="color:#e74c3c;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
        </div>
    </div>

    <!-- ═══ CMC Link Cards (Attorney, Commission, Traffic, Referrals) ═══ -->
    <div class="db-kpi-grid">
        <template x-if="data.attorney_cases">
            <a href="/CMC/frontend/pages/attorney-cases/index.php" class="db-link-card">
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
        <template x-if="data.commissions">
            <a href="/CMC/frontend/pages/commissions/index.php" class="db-link-card">
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
        <template x-if="data.traffic">
            <a href="/CMC/frontend/pages/traffic/index.php" class="db-link-card">
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
        <template x-if="data.referrals">
            <a href="/CMC/frontend/pages/referrals/index.php" class="db-link-card">
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
                <a href="/CMC/frontend/pages/attorney-cases/index.php" style="font-size:12px; color:#C9A84C; font-weight:600; text-decoration:none; font-family:'IBM Plex Sans',sans-serif;">Review pending requests →</a>
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
                    <a :href="'/CMC/frontend/pages/cases/detail.php?id=' + item.case_id" class="db-list-item">
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

    <!-- ═══ Staff Workload & Quick Actions ═══ -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">

        <!-- Staff Workload -->
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
                        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:10px;">
                            <div style="text-align:center;">
                                <div style="font-family:'IBM Plex Mono',monospace; font-size:20px; font-weight:700; color:#1a2535;" x-text="staffMetrics.my_metrics?.my_cases || 0"></div>
                                <div class="db-kpi-label">My Cases</div>
                            </div>
                            <div style="text-align:center;">
                                <div style="font-family:'IBM Plex Mono',monospace; font-size:20px; font-weight:700; color:#ea580c;" x-text="staffMetrics.my_metrics?.my_followup || 0"></div>
                                <div class="db-kpi-label">Followups</div>
                            </div>
                            <div style="text-align:center;">
                                <div style="font-family:'IBM Plex Mono',monospace; font-size:20px; font-weight:700; color:#e74c3c;" x-text="staffMetrics.my_metrics?.my_overdue || 0"></div>
                                <div class="db-kpi-label">Overdue</div>
                            </div>
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
                        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:10px; text-align:center;">
                            <div>
                                <div style="font-family:'IBM Plex Mono',monospace; font-size:20px; font-weight:700; color:#1a2535;" x-text="staffMetrics.totals?.total_cases || 0"></div>
                                <div class="db-kpi-label">Total Cases</div>
                            </div>
                            <div>
                                <div style="font-family:'IBM Plex Mono',monospace; font-size:20px; font-weight:700; color:#ea580c;" x-text="staffMetrics.totals?.total_followup || 0"></div>
                                <div class="db-kpi-label">Followups</div>
                            </div>
                            <div>
                                <div style="font-family:'IBM Plex Mono',monospace; font-size:20px; font-weight:700; color:#e74c3c;" x-text="staffMetrics.totals?.total_overdue || 0"></div>
                                <div class="db-kpi-label">Overdue</div>
                            </div>
                        </div>
                        <div style="border-top:1px solid #f5f2ee; padding-top:8px; max-height:128px; overflow-y:auto;">
                            <table class="sp-table sp-table-compact" style="font-size:11px;">
                                <thead>
                                    <tr>
                                        <th>Staff</th>
                                        <th class="center">Cases</th>
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

        <!-- Quick Actions -->
        <div class="db-section">
            <div class="db-section-header">
                <span class="db-section-title">Quick Actions</span>
            </div>
            <div class="db-quick-grid">
                <a href="/CMC/frontend/pages/cases/index.php" class="db-quick-link">
                    <span class="db-quick-icon">+</span> New MR Case
                </a>
                <a href="/CMC/frontend/pages/attorney-cases/index.php" class="db-quick-link">
                    <span class="db-quick-icon">+</span> New Demand
                </a>
                <a href="/CMC/frontend/pages/commissions/index.php" class="db-quick-link">
                    <span class="db-quick-icon">+</span> Add Commission
                </a>
                <a href="/CMC/frontend/pages/referrals/index.php" class="db-quick-link">
                    <span class="db-quick-icon">+</span> New Referral
                </a>
                <a href="/CMC/frontend/pages/reports/index.php" class="db-quick-link">
                    <span style="color:#8a8a82; font-size:14px;">📊</span> View Reports
                </a>
                <a href="/CMC/frontend/pages/traffic/index.php" class="db-quick-link">
                    <span class="db-quick-icon">+</span> New Traffic
                </a>
            </div>
        </div>
    </div>

    <!-- ═══ Follow-ups & Overdue ═══ -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">

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
                    <a :href="'/CMC/frontend/pages/cases/detail.php?id=' + item.case_id" class="db-list-item">
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
                    <a :href="'/CMC/frontend/pages/cases/detail.php?id=' + item.case_id" class="db-list-item">
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

    <!-- ═══ Recent Cases ═══ -->
    <div class="sp-card">
        <div class="sp-gold-bar"></div>
        <div class="db-section-header" style="padding:12px 16px;">
            <span class="db-section-title">Recent Cases</span>
            <a href="/CMC/frontend/pages/cases/index.php" style="font-size:11px; color:#C9A84C; font-weight:600; text-decoration:none; font-family:'IBM Plex Sans',sans-serif;">View All →</a>
        </div>
        <div style="max-height:288px; overflow-y:auto;">
            <table class="sp-table sp-table-compact">
                <thead>
                    <tr>
                        <th>Case #</th>
                        <th>Client</th>
                        <th>Attorney</th>
                        <th class="center">Progress</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="cases.length === 0">
                        <tr style="cursor:default;"><td colspan="5" class="sp-empty">No open cases</td></tr>
                    </template>
                    <template x-for="c in cases" :key="c.id">
                        <tr @click="window.location.href='/CMC/frontend/pages/cases/detail.php?id='+c.id">
                            <td><span class="sp-case-num" x-text="c.case_number"></span></td>
                            <td><span class="sp-client" x-text="c.client_name"></span></td>
                            <td><span style="font-size:12px; color:#8a8a82;" x-text="c.attorney_name || '—'"></span></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px; justify-content:center;">
                                    <div style="width:60px; height:5px; background:#f0ede8; border-radius:3px; overflow:hidden;">
                                        <div style="height:100%; background:#1a9e6a; border-radius:3px;" :style="'width:' + (c.provider_total > 0 ? Math.round(c.provider_done/c.provider_total*100) : 0) + '%'"></div>
                                    </div>
                                    <span style="font-family:'IBM Plex Mono',monospace; font-size:10px; font-weight:600; color:#8a8a82;" x-text="c.provider_done + '/' + c.provider_total"></span>
                                </div>
                            </td>
                            <td>
                                <span class="sp-stage" :class="'sp-stage-' + ({collecting:'demand-write',verification:'demand-review',completed:'settled',rfd:'demand-sent',final_verification:'demand-review',prelitigation:'litigation',accounting:'mediation',disbursement:'trial-set'}[c.status] || '')"
                                      x-text="getStatusLabel(c.status)"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>
