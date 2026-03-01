<!-- All sp- styles loaded from shared sp-design-system.css -->

<div x-data="reportsPage()">

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">

        <!-- Gold bar -->
        <div class="sp-gold-bar"></div>

        <!-- Header -->
        <div class="sp-header" style="flex-wrap:wrap; gap:16px;">
            <div style="flex:1;">
                <div class="sp-eyebrow">Analytics</div>
                <h1 class="sp-title">Reports & Analytics</h1>
            </div>
            <select x-model="selectedYear" @change="loadReport()" class="sp-select">
                <template x-for="y in yearOptions" :key="y">
                    <option :value="y" x-text="y"></option>
                </template>
            </select>
        </div>

        <!-- Toolbar with tabs -->
        <div class="sp-toolbar">
            <div class="sp-tabs">
                <template x-for="tab in tabs" :key="tab.key">
                    <button class="sp-tab" :class="activeTab === tab.key && 'on'"
                            @click="switchTab(tab.key)" x-text="tab.label"></button>
                </template>
            </div>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="sp-loading">
            <p>Loading report...</p>
        </div>

        <!-- ═══ Commission Summary Tab ═══ -->
        <div x-show="activeTab === 'commission' && !loading" x-cloak>

            <!-- KPI Cards -->
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px; padding:16px 24px;">
                <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:12px 16px; text-align:center;">
                    <div style="font-size:8.5px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#8a8a82; font-family:'IBM Plex Sans',sans-serif; margin-bottom:4px;">Total Settled</div>
                    <div class="sp-mono-lg" x-text="formatCurrency(report.commission?.total_settled || 0)"></div>
                </div>
                <div style="background:rgba(26,158,106,.04); border:1px solid rgba(26,158,106,.2); border-radius:10px; padding:12px 16px; text-align:center;">
                    <div style="font-size:8.5px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:rgba(26,158,106,.7); font-family:'IBM Plex Sans',sans-serif; margin-bottom:4px;">Total Commission</div>
                    <div style="font-family:'IBM Plex Mono',monospace; font-size:16px; font-weight:700; color:#1a9e6a;" x-text="formatCurrency(report.commission?.total_commission || 0)"></div>
                </div>
                <div style="background:rgba(37,99,235,.04); border:1px solid rgba(37,99,235,.2); border-radius:10px; padding:12px 16px; text-align:center;">
                    <div style="font-size:8.5px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:rgba(37,99,235,.6); font-family:'IBM Plex Sans',sans-serif; margin-bottom:4px;">Cases Settled</div>
                    <div style="font-family:'IBM Plex Mono',monospace; font-size:16px; font-weight:700; color:#2563eb;" x-text="report.commission?.total_cases || 0"></div>
                </div>
                <div style="background:rgba(124,92,191,.04); border:1px solid rgba(124,92,191,.2); border-radius:10px; padding:12px 16px; text-align:center;">
                    <div style="font-size:8.5px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:rgba(124,92,191,.6); font-family:'IBM Plex Sans',sans-serif; margin-bottom:4px;">Avg Commission</div>
                    <div style="font-family:'IBM Plex Mono',monospace; font-size:16px; font-weight:700; color:#7C5CBF;" x-text="formatCurrency(report.commission?.avg_commission || 0)"></div>
                </div>
            </div>

            <!-- Monthly Breakdown -->
            <div style="padding:0 24px 16px;">
                <div style="font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#8a8a82; font-family:'IBM Plex Sans',sans-serif; margin-bottom:8px;">Monthly Breakdown</div>
            </div>
            <table class="sp-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th class="right">Cases</th>
                        <th class="right">Settled</th>
                        <th class="right">Legal Fee</th>
                        <th class="right">Commission</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="m in report.commission?.monthly || []" :key="m.month">
                        <tr style="cursor:default;">
                            <td><span class="sp-month" style="font-size:12px; color:#1a2535; font-weight:500;" x-text="m.month"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" x-text="m.cases"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" x-text="formatCurrency(m.settled)"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" x-text="formatCurrency(m.legal_fee)"></span></td>
                            <td style="text-align:right;"><span class="sp-comm" x-text="formatCurrency(m.commission)"></span></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div x-show="!report.commission?.monthly?.length" class="sp-empty">No data for this period</div>

            <!-- By Employee -->
            <div style="padding:16px 24px 0;">
                <div style="font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#8a8a82; font-family:'IBM Plex Sans',sans-serif; margin-bottom:8px;">By Employee</div>
            </div>
            <table class="sp-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th class="right">Cases</th>
                        <th class="right">Commission</th>
                        <th class="right">Paid</th>
                        <th class="right">Unpaid</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="e in report.commission?.by_employee || []" :key="e.employee_name">
                        <tr style="cursor:default;">
                            <td><span class="sp-client" x-text="e.employee_name"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" x-text="e.cases"></span></td>
                            <td style="text-align:right;"><span class="sp-comm" x-text="formatCurrency(e.total_commission)"></span></td>
                            <td style="text-align:right;"><span style="font-family:'IBM Plex Mono',monospace; font-size:12px; font-weight:600; color:#2563eb;" x-text="formatCurrency(e.paid)"></span></td>
                            <td style="text-align:right;"><span style="font-family:'IBM Plex Mono',monospace; font-size:12px; font-weight:600; color:#D97706;" x-text="formatCurrency(e.unpaid)"></span></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div x-show="!report.commission?.by_employee?.length" class="sp-empty">No data</div>

        </div>

        <!-- ═══ Attorney Cases Tab ═══ -->
        <div x-show="activeTab === 'attorney' && !loading" x-cloak>

            <!-- KPI Cards -->
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px; padding:16px 24px;">
                <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:12px 16px; text-align:center;">
                    <div style="font-size:8.5px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#8a8a82; font-family:'IBM Plex Sans',sans-serif; margin-bottom:4px;">Total Settled</div>
                    <div class="sp-mono-lg" x-text="formatCurrency(report.attorney?.total_settled || 0)"></div>
                </div>
                <div style="background:rgba(26,158,106,.04); border:1px solid rgba(26,158,106,.2); border-radius:10px; padding:12px 16px; text-align:center;">
                    <div style="font-size:8.5px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:rgba(26,158,106,.7); font-family:'IBM Plex Sans',sans-serif; margin-bottom:4px;">Legal Fee</div>
                    <div style="font-family:'IBM Plex Mono',monospace; font-size:16px; font-weight:700; color:#1a9e6a;" x-text="formatCurrency(report.attorney?.total_legal_fee || 0)"></div>
                </div>
                <div style="background:rgba(37,99,235,.04); border:1px solid rgba(37,99,235,.2); border-radius:10px; padding:12px 16px; text-align:center;">
                    <div style="font-size:8.5px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:rgba(37,99,235,.6); font-family:'IBM Plex Sans',sans-serif; margin-bottom:4px;">Commission</div>
                    <div style="font-family:'IBM Plex Mono',monospace; font-size:16px; font-weight:700; color:#2563eb;" x-text="formatCurrency(report.attorney?.total_commission || 0)"></div>
                </div>
                <div style="background:rgba(217,119,6,.04); border:1px solid rgba(217,119,6,.2); border-radius:10px; padding:12px 16px; text-align:center;">
                    <div style="font-size:8.5px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:rgba(217,119,6,.7); font-family:'IBM Plex Sans',sans-serif; margin-bottom:4px;">Avg Days (Demand)</div>
                    <div style="font-family:'IBM Plex Mono',monospace; font-size:16px; font-weight:700; color:#D97706;" x-text="(report.attorney?.avg_demand_days || 0) + 'd'"></div>
                </div>
            </div>

            <!-- Phase & Attorney Breakdown side-by-side -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; padding:0 24px 16px;">

                <!-- By Phase -->
                <div style="border:1px solid #e8e4dc; border-radius:10px; overflow:hidden;">
                    <div style="padding:10px 16px; border-bottom:1px solid #f5f2ee;">
                        <span style="font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#8a8a82; font-family:'IBM Plex Sans',sans-serif;">By Phase</span>
                    </div>
                    <div style="padding:12px 16px;">
                        <template x-for="p in report.attorney?.by_phase || []" :key="p.phase">
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:6px 0;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="width:8px; height:8px; border-radius:50%; display:inline-block;"
                                          :style="'background:' + (p.phase === 'demand' ? '#C9A84C' : p.phase === 'litigation' ? '#7C5CBF' : p.phase === 'uim' ? '#2563eb' : '#1a9e6a')"></span>
                                    <span style="font-size:13px; font-weight:500; text-transform:capitalize; font-family:'IBM Plex Sans',sans-serif; color:#1a2535;" x-text="p.phase"></span>
                                </div>
                                <div style="display:flex; align-items:baseline; gap:4px;">
                                    <span class="sp-mono" style="font-weight:700;" x-text="p.count"></span>
                                    <span style="font-size:10px; color:#8a8a82;">cases</span>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div x-show="!report.attorney?.by_phase?.length" class="sp-empty" style="padding:24px 0;">No data</div>
                </div>

                <!-- By Attorney -->
                <div style="border:1px solid #e8e4dc; border-radius:10px; overflow:hidden;">
                    <div style="padding:10px 16px; border-bottom:1px solid #f5f2ee;">
                        <span style="font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#8a8a82; font-family:'IBM Plex Sans',sans-serif;">By Attorney</span>
                    </div>
                    <table class="sp-table sp-table-compact">
                        <thead>
                            <tr>
                                <th>Attorney</th>
                                <th class="right">Cases</th>
                                <th class="right">Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="a in report.attorney?.by_attorney || []" :key="a.attorney_name">
                                <tr style="cursor:default;">
                                    <td><span class="sp-client" x-text="a.attorney_name"></span></td>
                                    <td style="text-align:right;"><span class="sp-mono" x-text="a.cases"></span></td>
                                    <td style="text-align:right;"><span class="sp-comm" x-text="formatCurrency(a.commission)"></span></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div x-show="!report.attorney?.by_attorney?.length" class="sp-empty" style="padding:24px 0;">No data</div>
                </div>
            </div>

        </div>

        <!-- ═══ Performance Tab ═══ -->
        <div x-show="activeTab === 'performance' && !loading" x-cloak>

            <!-- Goals section header -->
            <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 24px 8px;">
                <div style="font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#8a8a82; font-family:'IBM Plex Sans',sans-serif;">
                    Employee Goals <span x-text="selectedYear"></span>
                </div>
                <button @click="showGoalModal = true" class="sp-new-btn" style="padding:5px 12px; font-size:11px;">Set Goals</button>
            </div>

            <table class="sp-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th class="right">Target Cases</th>
                        <th class="right">Actual Cases</th>
                        <th class="center">Progress</th>
                        <th class="right">Target Fee</th>
                        <th class="right">Actual Fee</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="g in report.goals || []" :key="g.user_id">
                        <tr style="cursor:default;">
                            <td><span class="sp-client" x-text="g.full_name"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" x-text="g.target_cases"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" x-text="g.actual_cases || 0"></span></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="flex:1; height:6px; background:#f0ede8; border-radius:3px; overflow:hidden;">
                                        <div style="height:100%; border-radius:3px; transition:width .3s;"
                                             :style="'width:' + Math.min(g.progress || 0, 100) + '%; background:' + (g.progress >= 100 ? '#1a9e6a' : g.progress >= 50 ? '#2563eb' : '#D97706')"></div>
                                    </div>
                                    <span style="font-family:'IBM Plex Mono',monospace; font-size:11px; font-weight:700; min-width:36px; text-align:right;"
                                          :style="'color:' + (g.progress >= 100 ? '#1a9e6a' : g.progress >= 50 ? '#2563eb' : '#D97706')"
                                          x-text="(g.progress || 0) + '%'"></span>
                                </div>
                            </td>
                            <td style="text-align:right;"><span class="sp-mono" style="color:#8a8a82;" x-text="formatCurrency(g.target_legal_fee)"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" x-text="formatCurrency(g.actual_legal_fee || 0)"></span></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div x-show="!report.goals?.length" class="sp-empty">No goals set for this year</div>

            <!-- Snapshots section header -->
            <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 24px 8px; border-top:1px solid #f5f2ee;">
                <div style="font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#8a8a82; font-family:'IBM Plex Sans',sans-serif;">Monthly Performance Snapshots</div>
                <button @click="generateSnapshots()" class="sp-btn" style="font-size:11px;"
                        :disabled="generating">
                    <span x-text="generating ? 'Generating...' : 'Generate Current Month'"></span>
                </button>
            </div>

            <table class="sp-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Month</th>
                        <th class="right">Cases Settled</th>
                        <th class="right">Commission</th>
                        <th class="right">Avg Demand Days</th>
                        <th class="right">Avg Lit Days</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="s in report.snapshots || []" :key="s.id">
                        <tr style="cursor:default;">
                            <td><span class="sp-client" x-text="s.full_name"></span></td>
                            <td><span class="sp-month" style="font-size:12px;" x-text="s.snapshot_month"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" x-text="s.cases_settled"></span></td>
                            <td style="text-align:right;"><span class="sp-comm" x-text="formatCurrency(s.total_commission)"></span></td>
                            <td style="text-align:right;">
                                <span x-show="s.avg_demand_days" class="sp-mono" x-text="s.avg_demand_days + 'd'"></span>
                                <span x-show="!s.avg_demand_days" class="sp-dash">—</span>
                            </td>
                            <td style="text-align:right;">
                                <span x-show="s.avg_litigation_days" class="sp-mono" x-text="s.avg_litigation_days + 'd'"></span>
                                <span x-show="!s.avg_litigation_days" class="sp-dash">—</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div x-show="!report.snapshots?.length" class="sp-empty">No snapshots generated yet</div>

        </div>

    </div><!-- /sp-card -->

    <!-- ═══ Goal Setting Modal ═══ -->
    <div x-show="showGoalModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="sp-card" style="width:100%; max-width:420px; margin:16px;" @click.outside="showGoalModal = false">
            <div class="sp-gold-bar"></div>
            <div class="sp-header" style="padding:16px 20px;">
                <h3 class="sp-title" style="font-size:15px; flex:1;">Set Employee Goal</h3>
                <button @click="showGoalModal = false" style="color:#8a8a82; font-size:18px; cursor:pointer; background:none; border:none;">&times;</button>
            </div>
            <div style="padding:16px 20px; display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Employee</label>
                    <select x-model="goalForm.user_id" class="sp-select" style="width:100%; padding:8px 12px;">
                        <option value="">Select employee...</option>
                        <template x-for="u in employees" :key="u.id">
                            <option :value="u.id" x-text="u.full_name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Year</label>
                    <input type="number" x-model="goalForm.year" class="sp-search" style="width:100%; padding:8px 12px;" />
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Target Cases</label>
                        <input type="number" x-model="goalForm.target_cases" class="sp-search" style="width:100%; padding:8px 12px;" />
                    </div>
                    <div>
                        <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Target Legal Fee</label>
                        <input type="number" step="0.01" x-model="goalForm.target_legal_fee" class="sp-search" style="width:100%; padding:8px 12px;" />
                    </div>
                </div>
                <div>
                    <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Notes</label>
                    <textarea x-model="goalForm.notes" rows="2" class="sp-search" style="width:100%; padding:8px 12px; resize:none;"></textarea>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; padding:12px 20px; background:#fafaf8; border-top:1px solid #f5f2ee;">
                <button @click="showGoalModal = false" class="sp-btn">Cancel</button>
                <button @click="saveGoal()" class="sp-new-btn"
                        :style="saving && 'opacity:.5; pointer-events:none;'">
                    <span x-text="saving ? 'Saving...' : 'Save Goal'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
