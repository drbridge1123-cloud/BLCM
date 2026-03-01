<!-- Litigation Table -->
<div style="overflow-x:auto;">
    <table class="sp-table">
        <thead>
            <tr>
                <th>Case #</th>
                <th>Client</th>
                <th class="center">Type</th>
                <th class="center">Lit. Start</th>
                <th class="center">Duration</th>
                <th class="right">Pre-Suit Offer</th>
                <th class="center">Stage / Status</th>
                <th class="right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <template x-if="loading">
                <tr><td colspan="8" class="sp-loading">Loading litigation cases...</td></tr>
            </template>
            <template x-if="!loading && filteredLitCases.length === 0">
                <tr><td colspan="8" class="sp-empty">No litigation cases found</td></tr>
            </template>
            <template x-for="c in paginatedLitCases" :key="c.id">
                <tr @click="openEdit(c)">

                    <!-- Case # -->
                    <td><span class="sp-case-num" x-text="c.case_number"></span></td>

                    <!-- Client -->
                    <td><span class="sp-client" x-text="c.client_name"></span></td>

                    <!-- Type -->
                    <td style="text-align:center">
                        <span class="sp-date-sub" style="font-size:12px" x-text="c.case_type || 'Auto'"></span>
                    </td>

                    <!-- Lit. Start -->
                    <td style="text-align:center">
                        <span class="sp-mono" x-text="spFormatDate(c.litigation_start_date)"></span>
                    </td>

                    <!-- Duration -->
                    <td style="text-align:center">
                        <template x-if="litDuration(c) < 0">
                            <span>
                                <span class="sp-duration-neg" x-text="litDuration(c)"></span><span class="sp-duration-unit">d</span>
                            </span>
                        </template>
                        <template x-if="litDuration(c) >= 0">
                            <span>
                                <span class="sp-duration-num" x-text="litDuration(c)"></span><span class="sp-duration-unit">d</span>
                            </span>
                        </template>
                    </td>

                    <!-- Pre-Suit Offer -->
                    <td style="text-align:right">
                        <span class="sp-mono-lg" x-text="c.presuit_offer ? formatCurrency(c.presuit_offer) : '—'"></span>
                    </td>

                    <!-- Stage / Status -->
                    <td style="text-align:center">
                        <span class="sp-stage" :class="litStageClass(c.litigation_status)"
                              x-text="litStageLabel(c.litigation_status)"></span>
                    </td>

                    <!-- Actions -->
                    <td style="text-align:right" @click.stop>
                        <div class="sp-actions">
                            <button class="sp-act sp-act-green" @click="openSettleLit(c)">
                                <span class="sp-tip">Settle</span>✓
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

