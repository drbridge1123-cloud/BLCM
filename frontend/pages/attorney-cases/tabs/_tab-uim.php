<!-- UIM Table -->
<div style="overflow-x:auto;">
    <table class="sp-table">
        <thead>
            <tr>
                <th>Case #</th>
                <th>Client</th>
                <th class="right">BI Settled</th>
                <th>UIM Start Date</th>
                <th>Due Deadline</th>
                <th class="center">Duration</th>
                <th class="center" style="min-width:240px">UIM Progress (D · N · S)</th>
                <th class="right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <template x-if="loading">
                <tr><td colspan="8" class="sp-loading">Loading UIM cases...</td></tr>
            </template>
            <template x-if="!loading && filteredUimCases.length === 0">
                <tr><td colspan="8" class="sp-empty">No UIM cases found</td></tr>
            </template>
            <template x-for="c in paginatedUimCases" :key="c.id">
                <tr @click="openEdit(c)">

                    <!-- Case # -->
                    <td><span class="sp-case-num" x-text="c.case_number"></span></td>

                    <!-- Client -->
                    <td><span class="sp-client" x-text="c.client_name"></span></td>

                    <!-- BI Settled -->
                    <td style="text-align:right">
                        <span class="sp-mono-lg" x-text="c.settled ? formatCurrency(c.settled) : '—'"></span>
                    </td>

                    <!-- UIM Start Date -->
                    <td>
                        <span class="sp-mono" x-text="spFormatDate(c.uim_start_date)"></span>
                    </td>

                    <!-- Due Deadline -->
                    <td>
                        <span :class="c.deadline_days_remaining != null && c.deadline_days_remaining < 30 ? 'sp-deadline-over' : 'sp-deadline-normal'"
                              x-text="spFormatDate(c.demand_deadline)"></span>
                    </td>

                    <!-- Duration -->
                    <td style="text-align:center">
                        <span class="sp-duration-num" x-text="uimDuration(c)"></span><span class="sp-duration-unit">d</span>
                    </td>

                    <!-- UIM Progress (D · N · S) -->
                    <td style="text-align:center" @click.stop>
                        <div class="sp-progress" style="justify-content:center; gap:8px; min-width:240px;">
                            <!-- UIM Demand -->
                            <div class="sp-step" style="min-width:52px" @click="toggleDate(c.id, 'uim_demand_out_date', c.uim_demand_out_date)">
                                <span class="sp-step-label">UIM Demand</span>
                                <template x-if="c.uim_demand_out_date">
                                    <span class="sp-step-date" :class="uimStepDateClass(c, 'uim_demand_out_date')"
                                          x-text="spShort(c.uim_demand_out_date)"></span>
                                </template>
                                <template x-if="!c.uim_demand_out_date">
                                    <span class="sp-step-empty">Not set</span>
                                </template>
                                <span class="sp-dot" :class="uimDotClass(c, 'uim_demand_out_date')"></span>
                            </div>
                            <div class="sp-sep" :class="c.uim_demand_out_date ? 'ssep-done' : ''" style="width:12px"></div>

                            <!-- UIM Negotiate -->
                            <div class="sp-step" style="min-width:52px" @click="toggleDate(c.id, 'uim_negotiate_date', c.uim_negotiate_date)">
                                <span class="sp-step-label">UIM Negotiate</span>
                                <template x-if="c.uim_negotiate_date">
                                    <span class="sp-step-date" :class="uimStepDateClass(c, 'uim_negotiate_date')"
                                          x-text="spShort(c.uim_negotiate_date)"></span>
                                </template>
                                <template x-if="!c.uim_negotiate_date">
                                    <span class="sp-step-empty">Not set</span>
                                </template>
                                <span class="sp-dot" :class="uimDotClass(c, 'uim_negotiate_date')"></span>
                            </div>
                            <div class="sp-sep" :class="c.uim_negotiate_date ? 'ssep-done' : ''" style="width:12px"></div>

                            <!-- UIM Settled -->
                            <div class="sp-step" style="min-width:52px; cursor:default">
                                <span class="sp-step-label">UIM Settled</span>
                                <template x-if="c.uim_settled_date">
                                    <span class="sp-step-date settled" x-text="spShort(c.uim_settled_date)"></span>
                                </template>
                                <template x-if="!c.uim_settled_date">
                                    <span class="sp-step-empty">Not yet</span>
                                </template>
                                <span class="sp-dot" :class="c.uim_settled_date ? 'dot-settled' : 'dot-emp'"></span>
                            </div>
                        </div>
                    </td>

                    <!-- Actions -->
                    <td style="text-align:right" @click.stop>
                        <div class="sp-actions">
                            <button class="sp-act sp-act-green" @click="openSettleUim(c)">
                                <span class="sp-tip">Settle</span>✓
                            </button>
                            <button x-show="isAdmin" class="sp-act sp-act-red" @click="deleteCase(c.id)">
                                <span class="sp-tip">Delete</span>🗑
                            </button>
                        </div>
                    </td>

                </tr>
            </template>
        </tbody>
    </table>
</div>

