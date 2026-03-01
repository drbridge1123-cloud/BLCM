<!-- Demand Table -->
<div style="overflow-x:auto;">
    <table class="sp-table">
        <thead>
            <tr>
                <th>Case #</th>
                <th>Client</th>
                <th>Assigned</th>
                <th>Deadline</th>
                <th class="center">Days Left</th>
                <th class="center" style="min-width:235px">D · N · T · S</th>
                <th class="center">Stage</th>
                <th class="right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <template x-if="loading">
                <tr><td colspan="8" class="sp-loading">Loading demand cases...</td></tr>
            </template>
            <template x-if="!loading && filteredDemandCases.length === 0">
                <tr><td colspan="8" class="sp-empty">No demand cases found</td></tr>
            </template>
            <template x-for="c in paginatedDemandCases" :key="c.id">
                <tr @click="openEdit(c)">

                    <!-- Case # -->
                    <td><span class="sp-case-num" x-text="c.case_number"></span></td>

                    <!-- Client -->
                    <td><span class="sp-client" x-text="c.client_name"></span></td>

                    <!-- Assigned -->
                    <td>
                        <div class="sp-date-main" x-text="spFormatDate(c.assigned_date)"></div>
                        <div class="sp-date-sub" x-text="c.case_type || 'Auto'"></div>
                    </td>

                    <!-- Deadline -->
                    <td>
                        <span :class="c.deadline_days_remaining < 0 ? 'sp-deadline-over' : 'sp-deadline-normal'"
                              x-text="spFormatDate(c.demand_deadline)"></span>
                    </td>

                    <!-- Days Left -->
                    <td style="text-align:center">
                        <span class="sp-days-badge"
                              :class="spDaysBadgeClass(c.deadline_days_remaining)"
                              x-text="c.deadline_days_remaining != null ? c.deadline_days_remaining : '—'"></span>
                    </td>

                    <!-- D · N · T · S Progress -->
                    <td style="text-align:center" @click.stop>
                        <div class="sp-progress" style="justify-content:center">
                            <div class="sp-step" @click="toggleDate(c.id, 'demand_out_date', c.demand_out_date)">
                                <span class="sp-step-label">Demand</span>
                                <span class="sp-step-date" :class="spStepDateClass(c, 'demand_out_date')"
                                      x-text="c.demand_out_date ? spShort(c.demand_out_date) : '—'"></span>
                                <span class="sp-dot" :class="spDotClass(c, 'demand_out_date')"></span>
                            </div>
                            <div class="sp-sep" :class="c.demand_out_date ? 'ssep-done' : ''"></div>
                            <div class="sp-step" @click="toggleDate(c.id, 'negotiate_date', c.negotiate_date)">
                                <span class="sp-step-label">Negotiate</span>
                                <span class="sp-step-date" :class="spStepDateClass(c, 'negotiate_date')"
                                      x-text="c.negotiate_date ? spShort(c.negotiate_date) : '—'"></span>
                                <span class="sp-dot" :class="spDotClass(c, 'negotiate_date')"></span>
                            </div>
                            <div class="sp-sep" :class="c.negotiate_date ? 'ssep-done' : ''"></div>
                            <div class="sp-step" @click="openTopOffer(c)">
                                <span class="sp-step-label">Top Offer</span>
                                <span class="sp-step-date" :class="spStepDateClass(c, 'top_offer_date')"
                                      x-text="c.top_offer_date ? spShort(c.top_offer_date) : '—'"></span>
                                <span class="sp-dot" :class="spDotClass(c, 'top_offer_date')"></span>
                            </div>
                            <div class="sp-sep" :class="c.top_offer_date ? 'ssep-done' : ''"></div>
                            <div class="sp-step" style="cursor:default">
                                <span class="sp-step-label">Settled</span>
                                <span class="sp-step-date" :class="c.demand_settled_date ? 'settled' : ''"
                                      x-text="c.demand_settled_date ? spShort(c.demand_settled_date) : '—'"></span>
                                <span class="sp-dot" :class="c.demand_settled_date ? 'dot-settled' : 'dot-emp'"></span>
                            </div>
                        </div>
                    </td>

                    <!-- Stage -->
                    <td style="text-align:center">
                        <span class="sp-stage" :class="spStageClass(c.stage)"
                              x-text="spStageLabel(c.stage)"></span>
                    </td>

                    <!-- Actions -->
                    <td style="text-align:right" @click.stop>
                        <div class="sp-actions">
                            <button class="sp-act sp-act-gold" @click="openTopOffer(c)">
                                <span class="sp-tip">Set Top Offer</span>💰
                            </button>
                            <button class="sp-act sp-act-green" @click="openSettleDemand(c)">
                                <span class="sp-tip">Settle</span>✓
                            </button>
                            <button class="sp-act sp-act-blue" @click="openToLit(c)">
                                <span class="sp-tip">To Litigation</span>⚖
                            </button>
                            <button class="sp-act sp-act-muted" @click="openEdit(c)">
                                <span class="sp-tip">Edit</span>✏
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

