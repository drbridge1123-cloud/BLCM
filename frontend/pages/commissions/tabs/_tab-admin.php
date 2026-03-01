<!-- Admin Commission Management Tab -->

<!-- Admin Stats -->
<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; padding:16px 24px;">
    <div class="ec-kpi ec-kpi-amber">
        <div class="ec-kpi-label">Pending Approval</div>
        <div class="ec-kpi-num" x-text="stats.unpaid_count"></div>
        <div class="sp-month" style="margin-top:4px" x-text="formatCurrency(stats.unpaid_commission)"></div>
    </div>
    <div class="ec-kpi ec-kpi-green">
        <div class="ec-kpi-label">Approved (Paid)</div>
        <div class="ec-kpi-num" x-text="stats.paid_count"></div>
        <div class="sp-month" style="margin-top:4px" x-text="formatCurrency(stats.paid_commission)"></div>
    </div>
    <div class="ec-kpi" style="background:rgba(231,76,60,.04); border:1px solid rgba(231,76,60,.2);">
        <div class="ec-kpi-label" style="color:rgba(231,76,60,.7)">Rejected</div>
        <div class="ec-kpi-num" style="font-size:22px; color:#e74c3c" x-text="stats.rejected_count"></div>
    </div>
</div>

<!-- Pending Cases Header -->
<div style="display:flex; align-items:center; justify-content:space-between; padding:0 24px 12px;">
    <span style="font-size:13px; font-weight:700; color:#1a2535; font-family:'IBM Plex Sans',sans-serif">Pending Commissions (Unpaid)</span>
    <div style="display:flex; gap:6px;">
        <button class="sp-btn" style="background:rgba(26,158,106,.07); border-color:rgba(26,158,106,.3); color:#1a9e6a"
                @click="bulkApprove('approve')" :disabled="selectedIds.length === 0">
            Approve Selected (<span x-text="selectedIds.length"></span>)
        </button>
        <button class="sp-btn" style="background:rgba(231,76,60,.06); border-color:rgba(231,76,60,.2); color:#e74c3c"
                @click="bulkApprove('reject')" :disabled="selectedIds.length === 0">
            Reject Selected
        </button>
    </div>
</div>

<!-- Admin Table -->
<div style="overflow-x:auto;">
    <table class="sp-table sp-table-compact">
        <thead>
            <tr>
                <th style="width:32px; text-align:center">
                    <input type="checkbox" @change="toggleSelectAll($event)" style="cursor:pointer">
                </th>
                <th class="sortable" :class="sortColumn === 'employee_name' && 'sorted'" @click="sortBy('employee_name')">Employee<span class="sort-icon" x-text="sortIcon('employee_name')"></span></th>
                <th class="sortable" :class="sortColumn === 'case_number' && 'sorted'" @click="sortBy('case_number')">Case #<span class="sort-icon" x-text="sortIcon('case_number')"></span></th>
                <th class="sortable" :class="sortColumn === 'client_name' && 'sorted'" @click="sortBy('client_name')">Client Name<span class="sort-icon" x-text="sortIcon('client_name')"></span></th>
                <th class="right sortable" :class="sortColumn === 'settled' && 'sorted'" @click="sortBy('settled')">Settled<span class="sort-icon" x-text="sortIcon('settled')"></span></th>
                <th class="right sortable" :class="sortColumn === 'commission' && 'sorted'" @click="sortBy('commission')">Commission<span class="sort-icon" x-text="sortIcon('commission')"></span></th>
                <th class="center sortable" :class="sortColumn === 'month' && 'sorted'" @click="sortBy('month')">Month<span class="sort-icon" x-text="sortIcon('month')"></span></th>
                <th class="center sortable" :class="sortColumn === 'check_received' && 'sorted'" @click="sortBy('check_received')">Check<span class="sort-icon" x-text="sortIcon('check_received')"></span></th>
                <th class="center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <template x-if="adminCases.length === 0">
                <tr><td colspan="9" class="sp-empty">No pending commissions</td></tr>
            </template>
            <template x-for="c in _sortData(adminCases)" :key="c.id">
                <tr>
                    <td style="text-align:center" @click.stop>
                        <input type="checkbox" :value="c.id" x-model="selectedIds" style="cursor:pointer">
                    </td>

                    <!-- Employee -->
                    <td>
                        <span class="sp-client" style="font-size:12px" x-text="c.employee_name"></span>
                    </td>

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
                        <span class="sp-comm" x-text="'$' + fmt(c.commission)"></span>
                    </td>

                    <!-- Month -->
                    <td style="text-align:center">
                        <span class="sp-month" x-text="c.month || '—'"></span>
                    </td>

                    <!-- Check -->
                    <td style="text-align:center" @click.stop>
                        <button @click="toggleCheck(c.id)"
                                :class="c.check_received == 1 ? 'ec-check-received' : 'ec-check-pending'"
                                style="background:none; border:none; cursor:pointer"
                                x-text="c.check_received == 1 ? 'Received' : 'Pending'"></button>
                    </td>

                    <!-- Actions -->
                    <td style="text-align:center" @click.stop>
                        <div style="display:flex; align-items:center; justify-content:center; gap:6px;">
                            <button class="sp-btn" style="padding:3px 10px; font-size:10px; background:rgba(26,158,106,.07); border-color:rgba(26,158,106,.3); color:#1a9e6a"
                                    @click="approveCase(c.id, 'approve')"
                                    :style="c.check_received != 1 ? 'opacity:.4; cursor:not-allowed' : ''"
                                    :disabled="c.check_received != 1">Approve</button>
                            <button class="sp-btn" style="padding:3px 10px; font-size:10px; background:rgba(231,76,60,.06); border-color:rgba(231,76,60,.2); color:#e74c3c"
                                    @click="approveCase(c.id, 'reject')">Reject</button>
                        </div>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>

<!-- By Employee Summary -->
<div style="padding:24px;" x-show="stats.by_employee && stats.by_employee.length > 0">
    <span style="font-size:13px; font-weight:700; color:#1a2535; font-family:'IBM Plex Sans',sans-serif; display:block; margin-bottom:12px">Commission by Employee</span>
    <div style="overflow-x:auto;">
        <table class="sp-table sp-table-compact">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th class="right">Cases</th>
                    <th class="right">Total Settled</th>
                    <th class="right">Total Commission</th>
                    <th class="right">Pending</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="emp in stats.by_employee" :key="emp.employee_user_id">
                    <tr>
                        <td><span class="sp-client" x-text="emp.full_name"></span></td>
                        <td style="text-align:right"><span class="sp-mono" x-text="emp.case_count"></span></td>
                        <td style="text-align:right"><span class="sp-mono" x-text="formatCurrency(emp.total_settled)"></span></td>
                        <td style="text-align:right"><span class="sp-comm" x-text="formatCurrency(emp.total_commission)"></span></td>
                        <td style="text-align:right">
                            <template x-if="emp.pending_count > 0">
                                <span class="sp-status sp-status-unpaid" x-text="emp.pending_count"></span>
                            </template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
