<!-- Attorney Commissions Table -->
<div style="overflow-x:auto;">
    <table class="sp-table sp-table-compact">
        <thead>
            <tr>
                <th class="sortable" :class="sortColumn === 'case_number' && 'sorted'" @click="sortBy('case_number')">Case #<span class="sort-icon" x-text="sortIcon('case_number')"></span></th>
                <th class="sortable" :class="sortColumn === 'client_name' && 'sorted'" @click="sortBy('client_name')">Client<span class="sort-icon" x-text="sortIcon('client_name')"></span></th>
                <th class="center sortable" :class="sortColumn === '_phase' && 'sorted'" @click="sortBy('_phase')">Phase<span class="sort-icon" x-text="sortIcon('_phase')"></span></th>
                <th class="right sortable" :class="sortColumn === 'settled' && 'sorted'" @click="sortBy('settled')">Settled<span class="sort-icon" x-text="sortIcon('settled')"></span></th>
                <th class="right sortable" :class="sortColumn === 'discounted_legal_fee' && 'sorted'" @click="sortBy('discounted_legal_fee')">Legal Fee<span class="sort-icon" x-text="sortIcon('discounted_legal_fee')"></span></th>
                <th class="right sortable" :class="sortColumn === 'commission' && 'sorted'" @click="sortBy('commission')">Commission<span class="sort-icon" x-text="sortIcon('commission')"></span></th>
                <th class="right sortable" :class="sortColumn === 'uim_settled' && 'sorted'" @click="sortBy('uim_settled')">UIM Settled<span class="sort-icon" x-text="sortIcon('uim_settled')"></span></th>
                <th class="right sortable" :class="sortColumn === 'uim_commission' && 'sorted'" @click="sortBy('uim_commission')">UIM Comm.<span class="sort-icon" x-text="sortIcon('uim_commission')"></span></th>
                <th class="right sortable" :class="sortColumn === '_total_comm' && 'sorted'" @click="sortBy('_total_comm')">Total Comm.<span class="sort-icon" x-text="sortIcon('_total_comm')"></span></th>
                <th class="center sortable" :class="sortColumn === 'month' && 'sorted'" @click="sortBy('month')">Month<span class="sort-icon" x-text="sortIcon('month')"></span></th>
                <th class="center sortable" :class="sortColumn === 'check_received' && 'sorted'" @click="sortBy('check_received')">Check<span class="sort-icon" x-text="sortIcon('check_received')"></span></th>
                <th class="center sortable" :class="sortColumn === 'status' && 'sorted'" @click="sortBy('status')">Status<span class="sort-icon" x-text="sortIcon('status')"></span></th>
            </tr>
        </thead>
        <tbody>
            <template x-if="loading">
                <tr><td colspan="12" class="sp-loading">Loading attorney commissions...</td></tr>
            </template>
            <template x-if="!loading && filteredAttorneyCases.length === 0">
                <tr><td colspan="12" class="sp-empty">No attorney commissions found</td></tr>
            </template>
            <template x-for="c in paginatedAttorneyCases" :key="c.id">
                <tr @click="openAttorneyModal(c)" style="cursor:pointer">

                    <!-- Case # -->
                    <td><span class="sp-case-num" x-text="c.case_number"></span></td>

                    <!-- Client -->
                    <td><span class="sp-client" x-text="c.client_name"></span></td>

                    <!-- Phase -->
                    <td style="text-align:center">
                        <span class="sp-phase" :class="attorneyPhaseClass(c)"
                              x-text="attorneyPhaseLabel(c)"></span>
                    </td>

                    <!-- Settled -->
                    <td style="text-align:right">
                        <span class="sp-mono" x-text="formatCurrency(c.settled)"></span>
                    </td>

                    <!-- Legal Fee -->
                    <td style="text-align:right">
                        <span class="sp-mono" x-text="formatCurrency(c.discounted_legal_fee)"></span>
                    </td>

                    <!-- Commission -->
                    <td style="text-align:right">
                        <span :class="parseFloat(c.commission) > 0 ? 'sp-total-comm' : 'sp-comm-zero'"
                              :style="parseFloat(c.commission) > 0 ? 'color:#1a9e6a' : 'color:#8a8a82'"
                              x-text="'$' + fmt(c.commission)"></span>
                    </td>

                    <!-- UIM Settled -->
                    <td style="text-align:right">
                        <template x-if="c.uim_settled">
                            <span class="sp-mono" x-text="formatCurrency(c.uim_settled)"></span>
                        </template>
                        <template x-if="!c.uim_settled">
                            <span class="sp-dash">—</span>
                        </template>
                    </td>

                    <!-- UIM Comm. -->
                    <td style="text-align:right">
                        <template x-if="c.uim_commission">
                            <span :style="parseFloat(c.uim_commission) > 0 ? 'color:#1a9e6a' : ''"
                                  x-text="'$' + fmt(c.uim_commission)"></span>
                        </template>
                        <template x-if="!c.uim_commission">
                            <span class="sp-dash">—</span>
                        </template>
                    </td>

                    <!-- Total Comm. -->
                    <td style="text-align:right">
                        <span class="sp-total-comm" style="color:#1a9e6a; font-weight:700"
                              x-text="'$' + fmt((parseFloat(c.commission) || 0) + (parseFloat(c.uim_commission) || 0))"></span>
                    </td>

                    <!-- Month -->
                    <td style="text-align:center">
                        <span class="sp-month" x-text="c.month || '—'"></span>
                    </td>

                    <!-- Check -->
                    <td style="text-align:center" @click.stop>
                        <button @click="toggleAttorneyCheck(c)"
                                :class="c.check_received == 1 ? 'ec-check-received' : 'ec-check-pending'"
                                style="background:none; border:none; cursor:pointer"
                                x-text="c.check_received == 1 ? 'Received' : 'Pending'"></button>
                    </td>

                    <!-- Status -->
                    <td style="text-align:center">
                        <span class="sp-status" :class="(c.status === 'paid') ? 'sp-status-paid' : 'sp-status-unpaid'"
                              x-text="c.status || 'unpaid'"></span>
                    </td>

                </tr>
            </template>
        </tbody>
    </table>
</div>
