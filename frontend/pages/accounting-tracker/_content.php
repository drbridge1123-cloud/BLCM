<!-- All sp- styles loaded from shared sp-design-system.css -->
<style>
.acct-row-overdue { background-color: #fef2f2; border-left: 4px solid #ef4444; }
.acct-row-overdue:hover { background-color: #fee2e2 !important; }
.acct-row-warning { background-color: #fffbeb; border-left: 4px solid #f59e0b; }
.acct-row-warning:hover { background-color: #fef3c7 !important; }
.acct-src-badge { font-size:9px; padding:1px 6px; border-radius:4px; font-weight:600; letter-spacing:.3px; }
.acct-src-case { background:rgba(37,99,235,.08); color:#2563eb; }
.acct-src-attorney { background:rgba(124,92,191,.08); color:#7C5CBF; }
</style>

<div x-data="accountingTrackerPage()" x-init="init()">

    <!-- Page header row -->
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <div>
            <div class="sp-eyebrow">Case Management</div>
            <h1 class="sp-title" style="font-size:16px;">Accounting Tracker</h1>
        </div>
    </div>

    <!-- Staff Tabs -->
    <?php include __DIR__ . '/../../components/_staff-tabs.php'; ?>

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">

        <!-- Gold bar -->
        <div class="sp-gold-bar"></div>

        <!-- Header -->
        <div class="sp-header" style="flex-wrap:wrap; gap:16px;">
            <div class="sp-stats">
                <div class="sp-stat" style="cursor:pointer;" @click="toggleFilter('')" :style="activeFilter === '' ? 'box-shadow:0 0 0 2px #C9A84C;' : ''">
                    <div class="sp-stat-num" style="color:#1a2535;" x-text="summary.total ?? '-'"></div>
                    <div class="sp-stat-label">Total</div>
                </div>
                <div class="sp-stat" style="cursor:pointer;" @click="toggleFilter('overdue')" :style="activeFilter === 'overdue' ? 'box-shadow:0 0 0 2px #e74c3c;' : ''">
                    <div class="sp-stat-num" style="color:#e74c3c;" x-text="summary.overdue ?? '-'"></div>
                    <div class="sp-stat-label">Overdue (>7d)</div>
                </div>
                <div class="sp-stat" style="cursor:pointer;" @click="toggleFilter('pending')" :style="activeFilter === 'pending' ? 'box-shadow:0 0 0 2px #D97706;' : ''">
                    <div class="sp-stat-num" style="color:#D97706;" x-text="summary.pending ?? '-'"></div>
                    <div class="sp-stat-label">Pending</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#1a9e6a; font-size:14px;" x-text="'$' + formatNumber(summary.total_settlement ?? 0)"></div>
                    <div class="sp-stat-label">Total Settlement</div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
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
                        <td>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span class="sp-case-num" x-text="item.case_number"></span>
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

</div>
