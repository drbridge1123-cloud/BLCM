<!-- Active Commissions Table -->
<div style="overflow-x:auto;">
    <table class="sp-table sp-table-compact">
        <thead>
            <tr>
                <th class="sortable" :class="sortColumn === 'case_number' && 'sorted'" @click="sortBy('case_number')">Case #<span class="sort-icon" x-text="sortIcon('case_number')"></span></th>
                <th class="sortable" :class="sortColumn === 'client_name' && 'sorted'" @click="sortBy('client_name')">Client Name<span class="sort-icon" x-text="sortIcon('client_name')"></span></th>
                <th class="right sortable" :class="sortColumn === 'settled' && 'sorted'" @click="sortBy('settled')">Settled<span class="sort-icon" x-text="sortIcon('settled')"></span></th>
                <th class="right sortable" :class="sortColumn === 'presuit_offer' && 'sorted'" @click="sortBy('presuit_offer')">Pre-Suit<span class="sort-icon" x-text="sortIcon('presuit_offer')"></span></th>
                <th class="right sortable" :class="sortColumn === 'difference' && 'sorted'" @click="sortBy('difference')">Difference<span class="sort-icon" x-text="sortIcon('difference')"></span></th>
                <th class="right sortable" :class="sortColumn === 'legal_fee' && 'sorted'" @click="sortBy('legal_fee')">Legal Fee<span class="sort-icon" x-text="sortIcon('legal_fee')"></span></th>
                <th class="right sortable" :class="sortColumn === 'discounted_legal_fee' && 'sorted'" @click="sortBy('discounted_legal_fee')">Disc. LF<span class="sort-icon" x-text="sortIcon('discounted_legal_fee')"></span></th>
                <th class="right sortable" :class="sortColumn === 'commission' && 'sorted'" @click="sortBy('commission')">Commission<span class="sort-icon" x-text="sortIcon('commission')"></span></th>
                <th class="center sortable" :class="sortColumn === 'month' && 'sorted'" @click="sortBy('month')">Month<span class="sort-icon" x-text="sortIcon('month')"></span></th>
                <th class="center sortable" :class="sortColumn === 'status' && 'sorted'" @click="sortBy('status')">Status<span class="sort-icon" x-text="sortIcon('status')"></span></th>
                <th class="center sortable" :class="sortColumn === 'check_received' && 'sorted'" @click="sortBy('check_received')">Check<span class="sort-icon" x-text="sortIcon('check_received')"></span></th>
                <th class="right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <template x-if="loading">
                <tr><td colspan="12" class="sp-loading">Loading commissions...</td></tr>
            </template>
            <template x-if="!loading && paginatedActiveCases.length === 0">
                <tr><td colspan="12" class="sp-empty">No commissions found</td></tr>
            </template>
            <template x-for="c in paginatedActiveCases" :key="c.id">
                <tr :class="c.status === 'in_progress' ? 'ec-row-dim' : ''" @click="openEditModal(c)" style="cursor:pointer">

                    <!-- Case # -->
                    <td><span class="sp-case-num" x-text="c.case_number"></span></td>

                    <!-- Client Name -->
                    <td><span class="sp-client" x-text="c.client_name"></span></td>

                    <!-- Settled -->
                    <td style="text-align:right">
                        <span class="sp-mono" :style="parseFloat(c.settled) > 0 ? 'color:#1a2535' : 'color:#8a8a82'"
                              x-text="fmt(c.settled)"></span>
                    </td>

                    <!-- Pre-Suit -->
                    <td style="text-align:right">
                        <span class="sp-mono" style="color:#8a8a82" x-text="fmt(c.presuit_offer)"></span>
                    </td>

                    <!-- Difference -->
                    <td style="text-align:right">
                        <span class="sp-mono" :style="parseFloat(c.difference) > 0 ? 'color:#1a2535' : 'color:#8a8a82'"
                              x-text="fmt(c.difference)"></span>
                    </td>

                    <!-- Legal Fee -->
                    <td style="text-align:right">
                        <span class="sp-mono" :style="parseFloat(c.legal_fee) > 0 ? 'color:#1a2535' : 'color:#8a8a82'"
                              x-text="fmt(c.legal_fee)"></span>
                    </td>

                    <!-- Disc. LF -->
                    <td style="text-align:right">
                        <span class="sp-mono" :style="parseFloat(c.discounted_legal_fee) > 0 ? 'color:#1a2535' : 'color:#8a8a82'"
                              x-text="fmt(c.discounted_legal_fee)"></span>
                    </td>

                    <!-- Commission -->
                    <td style="text-align:right">
                        <span :class="parseFloat(c.commission) > 0 ? 'sp-total-comm' : 'sp-comm-zero'"
                              style="color: inherit"
                              :style="parseFloat(c.commission) > 0 ? 'color:#1a9e6a' : 'color:#8a8a82'"
                              x-text="'$' + fmt(c.commission)"></span>
                    </td>

                    <!-- Month -->
                    <td style="text-align:center">
                        <template x-if="c.month && c.month !== 'TBD'">
                            <span class="sp-month" x-text="c.month"></span>
                        </template>
                        <template x-if="!c.month || c.month === 'TBD'">
                            <span class="sp-month" style="color:#ccc; font-style:italic">TBD</span>
                        </template>
                    </td>

                    <!-- Status -->
                    <td style="text-align:center">
                        <span class="sp-status"
                              :class="c.status === 'unpaid' ? 'sp-status-unpaid' : 'sp-status-in-progress'"
                              x-text="c.status === 'in_progress' ? 'IN PROGRESS' : 'UNPAID'"></span>
                    </td>

                    <!-- Check -->
                    <td style="text-align:center" @click.stop>
                        <button @click="toggleCheck(c.id)"
                                :class="c.check_received == 1 ? 'ec-check-received' : 'ec-check-pending'"
                                style="background:none; border:none; cursor:pointer"
                                x-text="c.check_received == 1 ? 'Received' : 'Pending'"></button>
                    </td>

                    <!-- Actions -->
                    <td style="text-align:right" @click.stop>
                        <div class="sp-actions">
                            <button class="sp-act sp-act-muted" @click="openEditModal(c)">
                                <span class="sp-tip">Edit</span>✏
                            </button>
                            <button class="sp-act sp-act-red" @click="deleteCase(c.id)">
                                <span class="sp-tip">Delete</span>🗑
                            </button>
                        </div>
                    </td>

                </tr>
            </template>
        </tbody>
    </table>
</div>

