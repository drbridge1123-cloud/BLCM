<!-- History Commissions Table -->
<div style="overflow-x:auto;">
    <table class="sp-table sp-table-compact">
        <thead>
            <tr>
                <th class="sortable" :class="sortColumn === 'case_number' && 'sorted'" @click="sortBy('case_number')">Case #<span class="sort-icon" x-text="sortIcon('case_number')"></span></th>
                <th class="sortable" :class="sortColumn === 'client_name' && 'sorted'" @click="sortBy('client_name')">Client Name<span class="sort-icon" x-text="sortIcon('client_name')"></span></th>
                <th class="right sortable" :class="sortColumn === 'settled' && 'sorted'" @click="sortBy('settled')">Settled<span class="sort-icon" x-text="sortIcon('settled')"></span></th>
                <th class="right sortable" :class="sortColumn === 'commission' && 'sorted'" @click="sortBy('commission')">Commission<span class="sort-icon" x-text="sortIcon('commission')"></span></th>
                <th class="center sortable" :class="sortColumn === 'month' && 'sorted'" @click="sortBy('month')">Month<span class="sort-icon" x-text="sortIcon('month')"></span></th>
                <th class="center sortable" :class="sortColumn === 'status' && 'sorted'" @click="sortBy('status')">Status<span class="sort-icon" x-text="sortIcon('status')"></span></th>
                <th class="center sortable" :class="sortColumn === 'check_received' && 'sorted'" @click="sortBy('check_received')">Check<span class="sort-icon" x-text="sortIcon('check_received')"></span></th>
                <template x-if="isAdmin">
                    <th class="sortable" :class="sortColumn === 'employee_name' && 'sorted'" @click="sortBy('employee_name')">Employee<span class="sort-icon" x-text="sortIcon('employee_name')"></span></th>
                </template>
            </tr>
        </thead>
        <tbody>
            <template x-if="loading">
                <tr><td colspan="8" class="sp-loading">Loading history...</td></tr>
            </template>
            <template x-if="!loading && paginatedHistoryCases.length === 0">
                <tr><td colspan="8" class="sp-empty">No history found</td></tr>
            </template>
            <template x-for="c in paginatedHistoryCases" :key="c.id">
                <tr @click="openEditModal(c, true)" style="cursor:pointer">
                    <!-- Case # -->
                    <td><span class="sp-case-num" x-text="c.case_number"></span></td>

                    <!-- Client Name -->
                    <td><span class="sp-client" x-text="c.client_name"></span></td>

                    <!-- Settled -->
                    <td style="text-align:right">
                        <span class="sp-mono" x-text="fmt(c.settled)"></span>
                    </td>

                    <!-- Commission -->
                    <td style="text-align:right">
                        <span class="sp-total-comm"
                              :style="c.status === 'paid' ? 'color:#1a9e6a' : 'color:#e74c3c; text-decoration:line-through'"
                              x-text="'$' + fmt(c.commission)"></span>
                    </td>

                    <!-- Month -->
                    <td style="text-align:center">
                        <span class="sp-month" x-text="c.month || '—'"></span>
                    </td>

                    <!-- Status -->
                    <td style="text-align:center">
                        <span class="sp-status"
                              :class="c.status === 'paid' ? 'sp-status-paid' : 'sp-status-rejected'"
                              x-text="c.status ? c.status.toUpperCase() : ''"></span>
                    </td>

                    <!-- Check -->
                    <td style="text-align:center">
                        <span :class="c.check_received == 1 ? 'ec-check-received' : 'ec-check-pending'"
                              x-text="c.check_received == 1 ? 'Received' : 'Pending'"></span>
                    </td>

                    <!-- Employee (admin only) -->
                    <template x-if="isAdmin">
                        <td>
                            <span class="sp-month" x-text="c.employee_name"></span>
                        </td>
                    </template>
                </tr>
            </template>
        </tbody>
    </table>
</div>

