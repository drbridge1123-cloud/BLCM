<!-- All sp- styles loaded from shared sp-design-system.css -->
<style>
.acct-row-overdue { background-color: #fef2f2; border-left: 4px solid #ef4444; }
.acct-row-overdue:hover { background-color: #fee2e2 !important; }
.acct-row-warning { background-color: #fffbeb; border-left: 4px solid #f59e0b; }
.acct-row-warning:hover { background-color: #fef3c7 !important; }
.acct-src-badge { font-size:9px; padding:1px 6px; border-radius:4px; font-weight:600; letter-spacing:.3px; }
.acct-case-link { cursor:pointer; text-decoration:none; color:#C9A84C; font-weight:600; }
.acct-case-link:hover { text-decoration:underline; }
.acct-src-case { background:rgba(37,99,235,.08); color:#2563eb; }
.acct-src-attorney { background:rgba(124,92,191,.08); color:#7C5CBF; }
/* Top-level page tabs */
.acct-page-tabs { display:flex; gap:0; margin-bottom:18px; border-bottom:2px solid #e8e4dc; }
.acct-page-tab { padding:10px 22px; font-size:13px; font-weight:600; color:#8a8a82; background:none; border:none; cursor:pointer; position:relative; font-family:'IBM Plex Sans',sans-serif; transition:color .15s; }
.acct-page-tab:hover { color:#1a2535; }
.acct-page-tab.active { color:#C9A84C; }
.acct-page-tab.active::after { content:''; position:absolute; bottom:-2px; left:0; right:0; height:2px; background:#C9A84C; border-radius:1px; }
</style>

<!-- Page header row -->
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
    <div>
        <template x-if="typeof fromCaseDetail !== 'undefined' && fromCaseDetail">
            <a x-cloak :href="fromCaseDetailUrl"
               style="display:inline-flex; align-items:center; gap:4px; font-size:11px; color:#8a8a82; text-decoration:none; margin-bottom:4px;">&larr; Case Detail</a>
        </template>
        <div class="sp-eyebrow">Case Management</div>
        <h1 class="sp-title" style="font-size:16px;">Accounting</h1>
    </div>
</div>

<!-- ═══ Page-Level Tabs ═══ -->
<div x-data="{ acctTab: 'tracker' }">

    <div class="acct-page-tabs">
        <button class="acct-page-tab" :class="acctTab === 'tracker' && 'active'" @click="acctTab = 'tracker'">Tracker</button>
        <template x-if="$store.auth.hasPermission('bank_reconciliation')">
            <button class="acct-page-tab" :class="acctTab === 'reconciliation' && 'active'" @click="acctTab = 'reconciliation'">Reconciliation</button>
        </template>
        <template x-if="$store.auth.hasPermission('expense_report')">
            <button class="acct-page-tab" :class="acctTab === 'expense' && 'active'" @click="acctTab = 'expense'">Cost Expense Report</button>
        </template>
    </div>

    <!-- ══════════════════════════════════════════════ -->
    <!-- TAB 1: Accounting Tracker                     -->
    <!-- ══════════════════════════════════════════════ -->
    <template x-if="acctTab === 'tracker'">
    <div>
        <div x-data="accountingTrackerPage()">

            <!-- Staff Tabs -->
            <?php include __DIR__ . '/../../components/_staff-tabs.php'; ?>

            <!-- Pending Case Assignments -->
            <?php include __DIR__ . '/../../components/_pending-assignments.php'; ?>

            <!-- ═══ Unified Card ═══ -->
            <div class="sp-card">

                <!-- Gold bar -->
                <div class="sp-gold-bar"></div>

                <!-- Toolbar -->
                <div class="sp-toolbar" style="flex-wrap:wrap;">
                    <div class="sp-tabs">
                        <button class="sp-tab" :class="activeFilter === '' && 'on'" @click="toggleFilter('')">All
                            <span class="sp-tab-count" style="background:rgba(37,99,235,.1); color:#2563eb;" x-text="summary.total ?? 0"></span>
                        </button>
                        <button class="sp-tab" :class="activeFilter === 'overdue' && 'on'" @click="toggleFilter('overdue')">Overdue
                            <span class="sp-tab-count" style="background:rgba(231,76,60,.1); color:#e74c3c;" x-text="summary.overdue ?? 0"></span>
                        </button>
                        <button class="sp-tab" :class="activeFilter === 'pending' && 'on'" @click="toggleFilter('pending')">Pending
                            <span class="sp-tab-count" style="background:rgba(217,119,6,.1); color:#d97706;" x-text="summary.pending ?? 0"></span>
                        </button>
                    </div>
                </div>

                <!-- Search -->
                <div class="sp-toolbar">
                    <input type="text" x-model="search" @input.debounce.300ms="loadData(1)" placeholder="Search case # or client name..."
                           class="sp-search" style="width:280px;">
                    <button @click="resetFilters()" class="sp-btn" x-show="search || activeFilter">Reset</button>
                </div>

                <!-- Loading -->
                <div x-show="loading" class="sp-loading">Loading...</div>

                <!-- Table -->
                <div x-show="!loading" x-cloak style="overflow-x:auto;">
                    <table class="sp-table">
                        <thead>
                            <tr>
                                <th style="cursor:pointer;" @click="sort('case_number')">Case #</th>
                                <th style="cursor:pointer;" @click="sort('client_name')">Client</th>
                                <th style="cursor:pointer;" @click="sort('settlement_amount')">Settlement</th>
                                <th>Atty Fee</th>
                                <th class="center" style="cursor:pointer;" @click="sort('days_in_accounting')">Days</th>
                                <th>Disbursed</th>
                                <th>Remaining</th>
                                <th class="center">Pending</th>
                                <th>File Location</th>
                                <th style="cursor:pointer;" @click="sort('assigned_name')">Assigned</th>
                                <th class="center">Actions</th>
                            </tr>
                        </thead>
                        <tbody x-show="items.length === 0">
                            <tr style="cursor:default;"><td colspan="11" class="sp-empty">No accounting cases found</td></tr>
                        </tbody>
                        <template x-for="item in items" :key="_itemKey(item)">
                            <tbody>
                            <tr style="cursor:pointer;"
                                :class="{
                                    'acct-row-overdue': item.is_overdue && expandedId !== _itemKey(item),
                                    'acct-row-warning': !item.is_overdue && item.days_in_accounting >= 5 && expandedId !== _itemKey(item)
                                }"
                                :style="expandedId === _itemKey(item) ? 'box-shadow:inset 0 0 0 2px #C9A84C; background:#fff;' : ''"
                                @click="toggleExpand(item)">
                                <td @click.stop>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <a @click.prevent="goToCase(item)"
                                           class="sp-case-num acct-case-link"
                                           x-text="item.case_number"></a>
                                        <span class="acct-src-badge" :class="item.source_type === 'attorney' ? 'acct-src-attorney' : 'acct-src-case'"
                                              x-text="item.source_type === 'attorney' ? 'ATT' : 'MR'"></span>
                                    </div>
                                </td>
                                <td><span class="sp-client" style="font-size:12px; max-width:150px; display:inline-block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="item.client_name"></span></td>
                                <td><span class="sp-mono" style="font-weight:600;" x-text="'$' + formatNumber(item.settlement_amount)"></span></td>
                                <td><span class="sp-mono" style="color:#8a8a82;" x-text="'$' + formatNumber(item.attorney_fee)"></span></td>
                                <td style="text-align:center;">
                                    <span class="sp-days-badge"
                                          :class="item.is_overdue ? 'sp-days-over' : item.days_in_accounting >= 5 ? 'sp-days-warn' : 'sp-days-ok'"
                                          x-text="item.days_in_accounting + 'd'"></span>
                                </td>
                                <td><span class="sp-mono" x-text="'$' + formatNumber(item.total_disbursed)"></span></td>
                                <td><span class="sp-mono" :style="item.remaining > 0 ? 'color:#D97706; font-weight:600;' : 'color:#1a9e6a;'" x-text="'$' + formatNumber(item.remaining)"></span></td>
                                <td style="text-align:center;">
                                    <template x-if="item.pending_count > 0"><span class="sp-status sp-status-unpaid" x-text="item.pending_count"></span></template>
                                    <template x-if="item.pending_count === 0"><span class="sp-dash">—</span></template>
                                </td>
                                <td><span style="font-size:12px; color:#8a8a82; max-width:120px; display:inline-block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="item.file_location || '—'"></span></td>
                                <td><span style="font-size:12px; color:#8a8a82; max-width:100px; display:inline-block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="item.assigned_name || '—'"></span></td>
                                <td @click.stop>
                                    <div class="sp-actions">
                                        <button @click="openDisbursementModal(item)" class="sp-act sp-act-gold">
                                            <span>$</span>
                                            <span class="sp-tip">Disburse</span>
                                        </button>
                                        <button @click="openCloseModal(item)" class="sp-act sp-act-green">
                                            <span>&#10003;</span>
                                            <span class="sp-tip">Close Case</span>
                                        </button>
                                        <button @click="goToCase(item)" class="sp-act sp-act-blue">
                                            <span>&#8599;</span>
                                            <span class="sp-tip">Open Case</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Expanded Disbursement History -->
                            <tr x-show="expandedId === _itemKey(item)">
                                <td colspan="11" style="padding:0 !important; border-top:none !important;">
                                    <div style="background:#fafaf8; border-top:1px solid #f5f2ee; border-bottom:2px solid rgba(201,168,76,.3); padding:16px 24px;">
                                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                                            <div style="display:flex; align-items:center; gap:12px;">
                                                <span style="font-size:12px; font-weight:700; color:#1a2535; font-family:'IBM Plex Sans',sans-serif;">Disbursements</span>
                                                <span style="font-size:11px; color:#8a8a82;" x-text="disbursementHistory.length + ' item(s)'"></span>
                                                <span class="sp-mono" style="font-weight:600;" x-text="'Total: $' + formatNumber(disbursementHistory.reduce((s, d) => s + (d.status !== 'void' ? parseFloat(d.amount) : 0), 0))"></span>
                                            </div>
                                            <button @click="openDisbursementModal(item)" class="sp-new-btn" style="padding:5px 12px; font-size:11px;">+ Add Disbursement</button>
                                        </div>
                                        <template x-if="disbursementHistory.length === 0">
                                            <p class="sp-empty" style="padding:16px 0;">No disbursements yet.</p>
                                        </template>
                                        <template x-if="disbursementHistory.length > 0">
                                            <div style="display:flex; flex-direction:column; gap:6px;">
                                                <template x-for="disb in disbursementHistory" :key="disb.id">
                                                    <div style="display:flex; align-items:center; justify-content:space-between; background:#fff; border-radius:8px; border:1px solid #e8e4dc; padding:8px 14px;"
                                                         :style="disb.status === 'void' ? 'opacity:.5;' : ''">
                                                        <div style="display:flex; align-items:center; gap:12px; font-size:12px;">
                                                            <span class="sp-stage" style="font-size:9px; padding:2px 8px; background:rgba(138,138,130,.08); color:#8a8a82; border:1px solid rgba(138,138,130,.15);" x-text="(disb.disbursement_type || '').replace(/_/g, ' ')"></span>
                                                            <span style="font-weight:600; color:#1a2535;" x-text="disb.payee_name"></span>
                                                            <span class="sp-mono" :style="disb.status === 'void' ? 'text-decoration:line-through; color:#e74c3c;' : 'font-weight:600;'" x-text="'$' + formatNumber(parseFloat(disb.amount))"></span>
                                                            <span class="sp-status"
                                                                  :class="{
                                                                      'sp-status-unpaid': disb.status === 'pending',
                                                                      'sp-status-in-progress': disb.status === 'issued',
                                                                      'sp-status-paid': disb.status === 'cleared',
                                                                      'sp-status-rejected': disb.status === 'void'
                                                                  }"
                                                                  x-text="disb.status"></span>
                                                            <template x-if="disb.check_number"><span style="font-size:11px; color:#8a8a82;" x-text="'Check #' + disb.check_number"></span></template>
                                                            <template x-if="disb.payment_date"><span style="font-size:11px; color:#8a8a82;" x-text="formatDate(disb.payment_date)"></span></template>
                                                        </div>
                                                        <div style="display:flex; align-items:center; gap:4px;" @click.stop>
                                                            <template x-if="disb.status === 'pending'">
                                                                <button @click="updateDisbStatus(disb.id, 'issued')" class="sp-act sp-act-blue" style="width:auto; padding:0 8px; font-size:10px; height:22px;">Issue</button>
                                                            </template>
                                                            <template x-if="disb.status === 'issued'">
                                                                <button @click="updateDisbStatus(disb.id, 'cleared')" class="sp-act sp-act-green" style="width:auto; padding:0 8px; font-size:10px; height:22px;">Clear</button>
                                                            </template>
                                                            <template x-if="disb.status !== 'void' && disb.status !== 'cleared'">
                                                                <button @click="updateDisbStatus(disb.id, 'void')" class="sp-act sp-act-red" style="width:auto; padding:0 8px; font-size:10px; height:22px;">Void</button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </table>
                </div>

            </div><!-- /sp-card -->

            <!-- ═══ Add Disbursement Modal ═══ -->
            <div x-show="showDisbursementModal" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showDisbursementModal = false">
                <div class="sp-card" style="width:100%; max-width:520px; margin:16px;" @click.stop>
                    <div class="sp-gold-bar"></div>
                    <div class="sp-header" style="padding:16px 20px;">
                        <h3 class="sp-title" style="font-size:15px; flex:1;">Add Disbursement</h3>
                        <button @click="showDisbursementModal = false" style="color:#8a8a82; font-size:18px; cursor:pointer; background:none; border:none;">&times;</button>
                    </div>
                    <div style="padding:16px 20px; display:flex; flex-direction:column; gap:14px;">
                        <div style="font-size:12px; color:#8a8a82;" x-text="disbForm._label"></div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Type *</label>
                                <select x-model="disbForm.disbursement_type" class="sp-select" style="width:100%; padding:8px 12px;">
                                    <option value="">Select...</option>
                                    <option value="client_payment">Client Payment</option>
                                    <option value="provider_payment">Provider Payment</option>
                                    <option value="attorney_fee">Attorney Fee</option>
                                    <option value="mr_cost_reimbursement">MR Cost Reimbursement</option>
                                    <option value="lien_payment">Lien Payment</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Amount *</label>
                                <input type="number" step="0.01" x-model="disbForm.amount" class="sp-search" style="width:100%; padding:8px 12px;" placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Payee Name *</label>
                            <input type="text" x-model="disbForm.payee_name" class="sp-search" style="width:100%; padding:8px 12px;" placeholder="Payee name...">
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Check #</label>
                                <input type="text" x-model="disbForm.check_number" class="sp-search" style="width:100%; padding:8px 12px;">
                            </div>
                            <div>
                                <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Method</label>
                                <select x-model="disbForm.payment_method" class="sp-select" style="width:100%; padding:8px 12px;">
                                    <option value="">Select...</option>
                                    <option value="check">Check</option>
                                    <option value="wire">Wire</option>
                                    <option value="ach">ACH</option>
                                    <option value="cash">Cash</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Payment Date</label>
                                <input type="date" x-model="disbForm.payment_date" class="sp-search" style="width:100%; padding:8px 12px;">
                            </div>
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Notes</label>
                            <textarea x-model="disbForm.notes" rows="2" class="sp-search" style="width:100%; padding:8px 12px; resize:none;" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:8px; padding:12px 20px; background:#fafaf8; border-top:1px solid #f5f2ee;">
                        <button @click="showDisbursementModal = false" class="sp-btn">Cancel</button>
                        <button @click="submitDisbursement()" :disabled="saving" class="sp-new-btn">
                            <span x-show="!saving">Add Disbursement</span>
                            <span x-show="saving">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ═══ Close Case Modal ═══ -->
            <div x-show="showCloseModal" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showCloseModal = false">
                <div class="sp-card" style="width:100%; max-width:420px; margin:16px;" @click.stop>
                    <div class="sp-gold-bar"></div>
                    <div class="sp-header" style="padding:16px 20px;">
                        <h3 class="sp-title" style="font-size:15px; flex:1;">Close Case</h3>
                        <button @click="showCloseModal = false" style="color:#8a8a82; font-size:18px; cursor:pointer; background:none; border:none;">&times;</button>
                    </div>
                    <div style="padding:16px 20px; display:flex; flex-direction:column; gap:14px;">
                        <div style="background:rgba(37,99,235,.04); border:1px solid rgba(37,99,235,.15); border-radius:8px; padding:12px 16px; font-size:12px; color:#2563eb;">
                            Closing <strong x-text="closeForm._label"></strong>. This will move the case to <strong>Closed</strong> status.
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">File Location *</label>
                            <input type="text" x-model="closeForm.file_location" class="sp-search" style="width:100%; padding:8px 12px;" placeholder="e.g., Cabinet A, Shelf 3, Box 12">
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Note (optional)</label>
                            <textarea x-model="closeForm.note" rows="2" class="sp-search" style="width:100%; padding:8px 12px; resize:none;" placeholder="Optional note..."></textarea>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:8px; padding:12px 20px; background:#fafaf8; border-top:1px solid #f5f2ee;">
                        <button @click="showCloseModal = false" class="sp-btn">Cancel</button>
                        <button @click="submitClose()" :disabled="saving" class="sp-new-btn" style="background:#1a9e6a;">
                            <span x-show="!saving">Close Case</span>
                            <span x-show="saving">Processing...</span>
                        </button>
                    </div>
                </div>
            </div>

        </div><!-- /accountingTrackerPage -->
    </div>
    </template><!-- /tracker tab -->

    <!-- ══════════════════════════════════════════════ -->
    <!-- TAB 2: Bank Reconciliation                    -->
    <!-- ══════════════════════════════════════════════ -->
    <template x-if="acctTab === 'reconciliation'">
    <div>
        <div x-data="bankReconciliationPage()">

            <!-- ═══ Unified Card ═══ -->
            <div class="sp-card">
                <div class="sp-gold-bar"></div>
                <!-- Tabs -->
                <div class="sp-toolbar">
                    <div class="sp-tabs">
                        <button class="sp-tab" :class="tab === 'entries' && 'on'" @click="tab = 'entries'">Entries</button>
                        <button class="sp-tab" :class="tab === 'batches' && 'on'" @click="tab = 'batches'; loadBatches()">Import Batches</button>
                    </div>
                </div>

                <!-- ═══ ENTRIES TAB ═══ -->
                <div x-show="tab === 'entries'" x-cloak>

                    <!-- Filter Bar -->
                    <div style="padding:12px 24px; display:flex; flex-wrap:wrap; align-items:center; gap:8px;">
                        <input type="text" x-model="search" @input.debounce.300ms="loadEntries(1)" placeholder="Search description, check #..." class="sp-search" style="width:220px;">
                        <select x-model="statusFilter" @change="loadEntries(1)" class="sp-select">
                            <option value="">All Statuses</option>
                            <option value="unmatched">Unmatched</option>
                            <option value="matched">Matched</option>
                            <option value="ignored">Ignored</option>
                        </select>
                        <select x-model="batchFilter" @change="loadEntries(1)" class="sp-select">
                            <option value="">All Batches</option>
                            <template x-for="b in batchList" :key="b.batch_id">
                                <option :value="b.batch_id" x-text="b.batch_id + ' (' + b.total_entries + ')'"></option>
                            </template>
                        </select>
                        <input type="date" x-model="dateFrom" @change="loadEntries(1)" class="sp-search" style="width:auto;" title="Date from">
                        <input type="date" x-model="dateTo" @change="loadEntries(1)" class="sp-search" style="width:auto;" title="Date to">
                        <button @click="clearFilters()" class="sp-btn" x-show="search || statusFilter || batchFilter || dateFrom || dateTo">Clear</button>
                    </div>

                    <!-- Entries Table -->
                    <table class="sp-table sp-table-compact">
                        <thead><tr>
                            <th>Date</th><th>Description</th><th style="text-align:right;">Amount</th>
                            <th>Check #</th><th>Ref #</th><th style="text-align:center;">Status</th>
                            <th>Matched Payment</th><th style="text-align:center;">Actions</th>
                        </tr></thead>
                        <tbody>
                            <template x-if="loading"><tr><td colspan="8" class="sp-empty">Loading...</td></tr></template>
                            <template x-if="!loading && entries.length === 0"><tr><td colspan="8" class="sp-empty">No entries found</td></tr></template>
                            <template x-for="entry in entries" :key="entry.id">
                                <tr>
                                    <td class="sp-mono" style="white-space:nowrap;" x-text="formatDate(entry.transaction_date)"></td>
                                    <td style="max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" :title="entry.description" x-text="entry.description"></td>
                                    <td class="sp-mono" style="text-align:right; font-weight:500; white-space:nowrap;" x-text="formatCurrency(entry.amount)"></td>
                                    <td x-text="entry.check_number || '-'"></td>
                                    <td x-text="entry.reference_number || '-'"></td>
                                    <td style="text-align:center;">
                                        <span class="sp-stage" :style="entry.reconciliation_status === 'matched' ? 'background:#dcfce7; color:#15803d;' : entry.reconciliation_status === 'unmatched' ? 'background:#fee2e2; color:#dc2626;' : 'background:#f3f4f6; color:#6b7280;'" x-text="entry.reconciliation_status"></span>
                                    </td>
                                    <td>
                                        <template x-if="entry.reconciliation_status === 'matched' && entry.matched_payment_id">
                                            <span style="color:#15803d; font-size:12px;">
                                                Payment #<span x-text="entry.matched_payment_id"></span>
                                                <template x-if="entry.payment_check_number">
                                                    <span style="color:#9ca3af;"> - Chk <span x-text="entry.payment_check_number"></span></span>
                                                </template>
                                            </span>
                                        </template>
                                        <template x-if="entry.reconciliation_status !== 'matched'"><span style="color:#d1d5db;">-</span></template>
                                    </td>
                                    <td style="text-align:center;">
                                        <div class="sp-actions" style="justify-content:center;">
                                            <template x-if="entry.reconciliation_status === 'unmatched'">
                                                <div style="display:flex; gap:4px;">
                                                    <button @click="openMatchPanel(entry)" class="sp-act sp-act-gold">Match</button>
                                                    <button @click="ignoreEntry(entry.id)" class="sp-act">Ignore</button>
                                                </div>
                                            </template>
                                            <template x-if="entry.reconciliation_status === 'matched'">
                                                <button @click="unmatchEntry(entry.id)" class="sp-act sp-act-red">Unmatch</button>
                                            </template>
                                            <template x-if="entry.reconciliation_status === 'ignored'">
                                                <button @click="unignoreEntry(entry.id)" class="sp-act sp-act-blue">Restore</button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                </div>

                <!-- ═══ BATCHES TAB ═══ -->
                <div x-show="tab === 'batches'" x-cloak>

                    <!-- Batches Table -->
                    <table class="sp-table sp-table-compact">
                        <thead><tr>
                            <th>Batch ID</th><th>Import Date</th><th>Imported By</th>
                            <th style="text-align:center;">Total</th>
                            <th style="text-align:center; color:#16a34a;">Matched</th>
                            <th style="text-align:center; color:#dc2626;">Unmatched</th>
                            <th style="text-align:center; color:#9ca3af;">Ignored</th>
                            <th style="text-align:right;">Total Amount</th>
                            <th style="text-align:center;">Actions</th>
                        </tr></thead>
                        <tbody>
                            <template x-if="batchesLoading"><tr><td colspan="9" class="sp-empty">Loading...</td></tr></template>
                            <template x-if="!batchesLoading && batches.length === 0"><tr><td colspan="9" class="sp-empty">No batches found. Import a CSV to get started.</td></tr></template>
                            <template x-for="b in batches" :key="b.batch_id">
                                <tr>
                                    <td class="sp-mono" style="font-size:11px;" x-text="b.batch_id"></td>
                                    <td class="sp-mono" x-text="formatDateTime(b.imported_at)"></td>
                                    <td x-text="b.imported_by_name"></td>
                                    <td style="text-align:center; font-weight:500;" x-text="b.total_entries"></td>
                                    <td style="text-align:center; color:#16a34a; font-weight:500;" x-text="b.matched_count"></td>
                                    <td style="text-align:center; color:#dc2626; font-weight:500;" x-text="b.unmatched_count"></td>
                                    <td style="text-align:center; color:#9ca3af;" x-text="b.ignored_count"></td>
                                    <td class="sp-mono" style="text-align:right; font-weight:500;" x-text="formatCurrency(b.total_amount)"></td>
                                    <td style="text-align:center;">
                                        <div class="sp-actions" style="justify-content:center;">
                                            <button @click="filterByBatch(b.batch_id)" class="sp-act sp-act-gold">View</button>
                                            <button @click="deleteBatch(b.batch_id, b.unmatched_count)" class="sp-act sp-act-red" x-show="b.unmatched_count > 0">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div><!-- /sp-card -->

            <!-- ═══ MATCH PANEL (SLIDE-OUT) ═══ -->
            <div x-show="showMatchPanel" x-cloak
                 class="fixed inset-0 z-50 flex justify-end" style="background:rgba(0,0,0,.35);" @click.self="showMatchPanel = false"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <div style="width:100%; max-width:640px; background:#fff; box-shadow:-8px 0 32px rgba(0,0,0,.15); display:flex; flex-direction:column; height:100%;"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                     @click.stop>

                    <!-- Panel Header -->
                    <div style="background:#0F1B2D; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                        <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">Match Bank Entry</h3>
                        <button @click="showMatchPanel = false" style="background:none; border:none; color:rgba(255,255,255,.4); cursor:pointer; font-size:20px;">&times;</button>
                    </div>

                    <!-- Bank Entry Details -->
                    <div style="padding:16px 24px; background:#fafaf8; border-bottom:1px solid #e8e4dc; flex-shrink:0;">
                        <div style="font-size:10px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:8px;">Bank Entry</div>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                            <div>
                                <div style="font-size:11px; color:#9ca3af;">Date</div>
                                <div class="sp-mono" style="font-size:13px; font-weight:500;" x-text="matchingEntry ? formatDate(matchingEntry.transaction_date) : ''"></div>
                            </div>
                            <div>
                                <div style="font-size:11px; color:#9ca3af;">Amount</div>
                                <div class="sp-mono" style="font-size:13px; font-weight:700; color:#1a2535;" x-text="matchingEntry ? formatCurrency(matchingEntry.amount) : ''"></div>
                            </div>
                            <div>
                                <div style="font-size:11px; color:#9ca3af;">Check #</div>
                                <div style="font-size:13px; font-weight:500;" x-text="matchingEntry ? (matchingEntry.check_number || '-') : ''"></div>
                            </div>
                        </div>
                        <div style="margin-top:8px;">
                            <div style="font-size:11px; color:#9ca3af;">Description</div>
                            <div style="font-size:13px;" x-text="matchingEntry ? matchingEntry.description : ''"></div>
                        </div>
                    </div>

                    <!-- Search Payments -->
                    <div style="padding:16px 24px; border-bottom:1px solid #e8e4dc; flex-shrink:0;">
                        <div style="font-size:10px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:10px;">Search Payments</div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <div>
                                <label style="display:block; font-size:11px; color:#9ca3af; margin-bottom:4px;">Amount</label>
                                <input type="number" step="0.01" x-model="paymentSearch.amount" class="sp-search" style="width:100%;">
                            </div>
                            <div>
                                <label style="display:block; font-size:11px; color:#9ca3af; margin-bottom:4px;">Check #</label>
                                <input type="text" x-model="paymentSearch.check_number" class="sp-search" style="width:100%;">
                            </div>
                            <div>
                                <label style="display:block; font-size:11px; color:#9ca3af; margin-bottom:4px;">Date From</label>
                                <input type="date" x-model="paymentSearch.date_from" class="sp-search" style="width:100%;">
                            </div>
                            <div>
                                <label style="display:block; font-size:11px; color:#9ca3af; margin-bottom:4px;">Date To</label>
                                <input type="date" x-model="paymentSearch.date_to" class="sp-search" style="width:100%;">
                            </div>
                        </div>
                        <div style="margin-top:10px; display:flex; align-items:center; gap:8px;">
                            <button @click="searchPayments()" class="sp-new-btn-navy" style="padding:7px 16px; font-size:12px;">Search</button>
                            <button @click="clearPaymentSearch()" class="sp-btn">Clear</button>
                            <span x-show="searchingPayments" style="font-size:12px; color:#9ca3af;">Searching...</span>
                        </div>
                    </div>

                    <!-- Payment Results -->
                    <div style="flex:1; overflow-y:auto; padding:16px 24px;">
                        <div style="font-size:10px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:10px;">
                            Results <span style="color:#9ca3af;" x-text="'(' + searchResults.length + ')'"></span>
                        </div>

                        <template x-if="searchResults.length === 0 && !searchingPayments">
                            <div class="sp-empty" style="padding:32px 0;">Search for payments to find a match. Try searching by amount or check number.</div>
                        </template>

                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <template x-for="pmt in searchResults" :key="pmt.id">
                                <div style="border-radius:8px; padding:12px 14px; cursor:pointer; transition:all .15s;"
                                     :style="selectedPaymentId === pmt.id ? 'border:1.5px solid #C9A84C; background:rgba(201,168,76,.05);' : 'border:1.5px solid #e8e4dc; background:#fff;'"
                                     @click="selectedPaymentId = pmt.id">
                                    <div style="display:flex; align-items:center; justify-content:space-between;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <div style="width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all .15s;"
                                                 :style="selectedPaymentId === pmt.id ? 'border:2px solid #C9A84C; background:#C9A84C;' : 'border:2px solid #e8e4dc;'">
                                                <span x-show="selectedPaymentId === pmt.id" style="color:#fff; font-size:10px; font-weight:700;">✓</span>
                                            </div>
                                            <div>
                                                <div style="font-size:13px; font-weight:500;">
                                                    <span class="sp-mono" x-text="formatCurrency(pmt.amount)"></span>
                                                    <span style="color:#d1d5db; margin:0 4px;">|</span>
                                                    <span class="sp-mono" style="color:#6b7280;" x-text="formatDate(pmt.payment_date)"></span>
                                                </div>
                                                <div style="font-size:11px; color:#9ca3af; margin-top:2px;">
                                                    <template x-if="pmt.case_number"><span>Case <span style="font-weight:500;" x-text="pmt.case_number"></span></span></template>
                                                    <template x-if="pmt.client_name"><span> - <span x-text="pmt.client_name"></span></span></template>
                                                    <template x-if="pmt.check_number"><span style="margin-left:8px; color:#9ca3af;">Chk #<span x-text="pmt.check_number"></span></span></template>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="text-align:right; font-size:11px; color:#9ca3af;">
                                            <div>ID: <span x-text="pmt.id"></span></div>
                                            <template x-if="pmt.paid_by_name"><div x-text="pmt.paid_by_name"></div></template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Panel Footer -->
                    <div style="padding:14px 24px; border-top:1px solid #e8e4dc; background:#fafaf8; display:flex; align-items:center; justify-content:flex-end; gap:10px; flex-shrink:0;">
                        <button @click="showMatchPanel = false" class="sp-btn">Cancel</button>
                        <button @click="confirmMatch()" :disabled="!selectedPaymentId || matchingInProgress" class="sp-new-btn-navy" style="opacity:1;" :style="(!selectedPaymentId || matchingInProgress) ? 'opacity:.5; cursor:not-allowed;' : ''">
                            <span x-text="matchingInProgress ? 'Matching...' : 'Confirm Match'"></span>
                        </button>
                    </div>
                </div>
            </div>

        </div><!-- /bankReconciliationPage -->
    </div>
    </template><!-- /reconciliation tab -->

    <!-- ══════════════════════════════════════════════ -->
    <!-- TAB 3: Cost Expense Report                    -->
    <!-- ══════════════════════════════════════════════ -->
    <template x-if="acctTab === 'expense'">
    <div>
        <div x-data="expenseReportPage()">

            <!-- ═══ Unified Card ═══ -->
            <div class="sp-card">
                <div class="sp-gold-bar"></div>

                <!-- Compact Header: Breakdowns | Export -->
                <div style="padding:18px 24px; border-bottom:1px solid #f5f2ee; display:flex; align-items:center; gap:14px;">

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
                                <option :value="s.id" x-text="s.display_name || s.full_name"></option>
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

        </div><!-- /expenseReportPage -->
    </div>
    </template><!-- /expense tab -->

</div><!-- /acctTab wrapper -->
