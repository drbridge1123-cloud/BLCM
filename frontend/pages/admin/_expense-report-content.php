<!-- All sp- styles loaded from shared sp-design-system.css -->

<div x-data="expenseReportPage()" x-init="init()">

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">
        <div class="sp-gold-bar"></div>

        <!-- Compact Header: Title | Breakdowns | Export -->
        <div style="padding:18px 24px; border-bottom:1px solid #f5f2ee; display:flex; align-items:center; gap:14px;">

            <!-- ① Title -->
            <div style="flex-shrink:0; min-width:130px;">
                <div style="font-size:9px; font-weight:700; color:#C9A84C; text-transform:uppercase; letter-spacing:.14em;">Admin</div>
                <h1 style="font-size:17px; font-weight:700; color:#1a2535; letter-spacing:-.02em; margin:0;">Expense Report</h1>
            </div>

            <!-- v-divider -->
            <div style="width:1px; align-self:stretch; background:#e8e4dc; margin:4px 0; flex-shrink:0;"></div>

            <!-- ② Analysis Groups -->
            <div style="display:flex; gap:8px; flex:1; min-width:0;">

                <!-- By Category -->
                <div style="flex:1; border:1px solid #e8e4dc; border-radius:9px; padding:8px 12px;">
                    <div style="font-size:7px; font-weight:700; color:#C9A84C; text-transform:uppercase; letter-spacing:.12em; margin-bottom:6px;">By Category</div>
                    <template x-if="summary.by_category && summary.by_category.length > 0">
                        <div>
                            <template x-for="(cat, idx) in summary.by_category" :key="cat.expense_category">
                                <div style="display:flex; justify-content:space-between; align-items:center; padding:2px 0;" :style="idx > 0 ? 'border-top:1px solid #f5f2ee;' : ''">
                                    <div>
                                        <span style="font-size:10.5px; font-weight:500; color:#1a2535;" x-text="getCategoryLabel(cat.expense_category)"></span>
                                        <span style="font-size:9px; font-family:'IBM Plex Mono',monospace; color:#8a8a82; margin-left:2px;" x-text="'(' + cat.count + ')'"></span>
                                    </div>
                                    <span style="font-size:10.5px; font-family:'IBM Plex Mono',monospace; font-weight:600; color:#1a2535;" x-text="formatCurrency(cat.total_paid)"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!summary.by_category || summary.by_category.length === 0"><span style="font-size:10px; color:#9ca3af;">—</span></template>
                </div>

                <!-- By Staff (wider, 2-col grid) -->
                <div style="flex:1.6; border:1px solid #e8e4dc; border-radius:9px; padding:8px 12px;">
                    <div style="font-size:7px; font-weight:700; color:#C9A84C; text-transform:uppercase; letter-spacing:.12em; margin-bottom:6px;">By Staff</div>
                    <template x-if="summary.by_staff && summary.by_staff.length > 0">
                        <div style="display:grid; grid-template-columns:1fr 1fr; column-gap:12px;">
                            <template x-for="s in summary.by_staff" :key="s.paid_by">
                                <div style="display:flex; align-items:center; gap:5px; padding:2px 0;">
                                    <span style="width:17px; height:17px; border-radius:50%; background:#f5f2ee; border:1px solid #e8e4dc; display:flex; align-items:center; justify-content:center; font-size:7.5px; font-weight:700; color:#8a8a82; flex-shrink:0;" x-text="(s.staff_name || '?').charAt(0)"></span>
                                    <span style="font-size:10.5px; font-weight:500; color:#1a2535; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="s.staff_name || 'Unknown'"></span>
                                    <span style="font-size:9px; font-family:'IBM Plex Mono',monospace; color:#8a8a82; flex-shrink:0;" x-text="'(' + s.count + ')'"></span>
                                    <span style="font-size:10.5px; font-family:'IBM Plex Mono',monospace; font-weight:600; color:#1a2535; flex-shrink:0;" x-text="formatCurrency(s.total_paid)"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!summary.by_staff || summary.by_staff.length === 0"><span style="font-size:10px; color:#9ca3af;">—</span></template>
                </div>

                <!-- By Type -->
                <div style="flex:1; border:1px solid #e8e4dc; border-radius:9px; padding:8px 12px;">
                    <div style="font-size:7px; font-weight:700; color:#C9A84C; text-transform:uppercase; letter-spacing:.12em; margin-bottom:6px;">By Type</div>
                    <template x-if="summary.by_payment_type && summary.by_payment_type.length > 0">
                        <div>
                            <template x-for="(t, idx) in summary.by_payment_type" :key="t.payment_type">
                                <div style="display:flex; justify-content:space-between; align-items:center; padding:2px 0;" :style="idx > 0 ? 'border-top:1px solid #f5f2ee;' : ''">
                                    <div>
                                        <span style="font-size:10.5px; font-weight:500; color:#1a2535;" x-text="getPaymentTypeLabel(t.payment_type)"></span>
                                        <span style="font-size:9px; font-family:'IBM Plex Mono',monospace; color:#8a8a82; margin-left:2px;" x-text="'(' + t.count + ')'"></span>
                                    </div>
                                    <span style="font-size:10.5px; font-family:'IBM Plex Mono',monospace; font-weight:600; color:#1a2535;" x-text="formatCurrency(t.total_paid)"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!summary.by_payment_type || summary.by_payment_type.length === 0"><span style="font-size:10px; color:#9ca3af;">—</span></template>
                </div>
            </div>

        </div>

        <!-- Filters -->
        <div style="padding:0 24px 12px;">
            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px;">
                <input type="text" x-model="search" @input.debounce.300ms="loadData(1)" placeholder="Search case #, client, provider, check #..." class="sp-search" style="flex:1; min-width:200px;">
                <div style="display:flex; align-items:center; gap:4px;">
                    <span style="font-size:11px; color:#9ca3af;">From</span>
                    <input type="date" x-model="dateFrom" @change="loadData(1)" class="sp-search" style="width:auto;">
                </div>
                <div style="display:flex; align-items:center; gap:4px;">
                    <span style="font-size:11px; color:#9ca3af;">To</span>
                    <input type="date" x-model="dateTo" @change="loadData(1)" class="sp-search" style="width:auto;">
                </div>
                <select x-model="categoryFilter" @change="loadData(1)" class="sp-select">
                    <option value="">All Categories</option>
                    <option value="mr_cost">MR Cost</option>
                    <option value="litigation">Litigation</option>
                    <option value="other">Other</option>
                </select>
                <select x-model="paymentTypeFilter" @change="loadData(1)" class="sp-select">
                    <option value="">All Types</option>
                    <option value="check">Check</option>
                    <option value="card">Card</option>
                    <option value="cash">Cash</option>
                    <option value="wire">Wire</option>
                    <option value="other">Other</option>
                </select>
                <select x-model="staffFilter" @change="loadData(1)" class="sp-select">
                    <option value="">All Staff</option>
                    <template x-for="s in staffList" :key="s.id">
                        <option :value="s.id" x-text="s.full_name"></option>
                    </template>
                </select>
                <button @click="resetFilters()" class="sp-btn" x-show="hasActiveFilters()">Reset</button>
            </div>
        </div>

        <!-- Loading -->
        <template x-if="loading"><div class="sp-loading"><div class="spinner"></div></div></template>

        <!-- Table -->
        <template x-if="!loading">
            <div style="overflow-x:auto;" x-init="initScrollContainer($el)">
                <table class="sp-table sp-table-compact">
                    <thead><tr>
                        <th class="cursor-pointer select-none" @click="sort('payment_date')">Date</th>
                        <th class="cursor-pointer select-none" @click="sort('case_number')">Case #</th>
                        <th>Client</th>
                        <th class="cursor-pointer select-none" @click="sort('provider_name')">Provider</th>
                        <th>Description</th>
                        <th class="cursor-pointer select-none" @click="sort('expense_category')">Category</th>
                        <th class="cursor-pointer select-none" style="text-align:right;" @click="sort('billed_amount')">Billed</th>
                        <th class="cursor-pointer select-none" style="text-align:right;" @click="sort('paid_amount')">Paid</th>
                        <th class="cursor-pointer select-none" @click="sort('payment_type')">Type</th>
                        <th>Check #</th>
                        <th>Paid By</th>
                    </tr></thead>
                    <tbody>
                        <template x-if="items.length === 0"><tr><td colspan="11" class="sp-empty">No payments found</td></tr></template>
                        <template x-for="item in items" :key="item.id">
                            <tr style="cursor:pointer;" @click="goToCase(item.case_id)">
                                <td class="sp-mono" style="white-space:nowrap;" x-text="formatDate(item.payment_date)"></td>
                                <td><span style="color:#C9A84C; font-weight:500;" x-text="item.case_number"></span></td>
                                <td style="max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="item.client_name"></td>
                                <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="item.provider_name || item.linked_provider_name || '-'"></td>
                                <td style="max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#6b7280;" x-text="item.description || '-'"></td>
                                <td>
                                    <span class="sp-stage" :style="item.expense_category === 'mr_cost' ? 'background:rgba(59,130,246,.1); color:#2563eb;' : item.expense_category === 'litigation' ? 'background:rgba(139,92,246,.1); color:#7c3aed;' : 'background:#f3f4f6; color:#6b7280;'" x-text="getCategoryLabel(item.expense_category)"></span>
                                </td>
                                <td class="sp-mono" style="text-align:right;" x-text="formatCurrency(item.billed_amount)"></td>
                                <td class="sp-mono" style="text-align:right; color:#15803d; font-weight:500;" x-text="formatCurrency(item.paid_amount)"></td>
                                <td style="font-size:12px;" x-text="getPaymentTypeLabel(item.payment_type)"></td>
                                <td style="font-size:12px; color:#6b7280;" x-text="item.check_number || '-'"></td>
                                <td style="font-size:12px; color:#6b7280; max-width:100px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="item.paid_by_name || '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                    <template x-if="items.length > 0">
                        <tfoot>
                            <tr style="background:#fafaf8; font-weight:600; border-top:2px solid #e8e4dc;">
                                <td colspan="6" style="text-align:right; color:#6b7280; font-size:12px;">Page Totals:</td>
                                <td class="sp-mono" style="text-align:right;" x-text="formatCurrency(pageTotalBilled)"></td>
                                <td class="sp-mono" style="text-align:right; color:#15803d;" x-text="formatCurrency(pageTotalPaid)"></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </template>
                </table>
            </div>
        </template>
    </div>

</div>
