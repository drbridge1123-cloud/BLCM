<!-- Cost Ledger Panel -->
<div class="cost-panel c1-section" data-panel>
    <!-- Header -->
    <div class="cost-header c1-section-header" :class="showCostLedger && 'is-open'" @click="showCostLedger = !showCostLedger; if(showCostLedger) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
        <div class="cost-header-left">
            <span class="c1-num c1-num-gold">05</span>
            <span class="cost-header-title">Cost Ledger</span>
            <template x-if="allCosts.length > 0">
                <span class="panel-count" x-text="allCosts.length"></span>
            </template>
            <template x-if="allCostsTotal.billed > 0">
                <span style="color:var(--mbr-text); font-size:12px; font-family:'IBM Plex Mono',monospace; font-weight:700;"
                    x-text="'Total: $' + allCostsTotal.billed.toLocaleString('en-US', {minimumFractionDigits:2})"></span>
            </template>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <button @click.stop="printCostLedger()" class="cost-btn">
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
            <button @click.stop="openCostModal()" class="cost-btn-primary">
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Cost
            </button>
        </div>
    </div>

    <!-- Body -->
    <div x-show="showCostLedger" x-collapse>
        <div class="cost-body">
            <!-- Empty state -->
            <template x-if="allCosts.length === 0">
                <div class="cost-empty">No costs recorded yet</div>
            </template>

            <!-- Table -->
            <template x-if="allCosts.length > 0">
                <div style="overflow-x:auto;">
                    <table class="cost-table">
                        <thead>
                            <tr>
                                <th style="width:8%;padding-left:16px;">Date</th>
                                <th style="width:17%;">Provider</th>
                                <th style="width:14%;">Description</th>
                                <th style="width:7%;text-align:center;">Category</th>
                                <th style="width:8%;text-align:right;">Billed</th>
                                <th style="width:5%;text-align:center;">Type</th>
                                <th style="width:8%;text-align:center;">Card/Check #</th>
                                <th style="width:7%;">Staff</th>
                                <th style="width:8%;text-align:right;">Paid</th>
                                <th style="width:8%;">Paid Date</th>
                                <th style="width:5%;text-align:center;">Actions</th>
                            </tr>
                        </thead>
                        <template x-for="group in getCostGroups()" :key="group.category">
                            <tbody>
                                <tr class="cost-section-header">
                                    <td colspan="11" x-text="group.label"></td>
                                </tr>
                                <template x-for="cost in group.costs" :key="cost.id">
                                    <tr>
                                        <td style="padding-left:16px;" x-text="formatDate(cost.payment_date)"></td>
                                        <td style="font-weight:500;" x-text="cost.linked_provider_name || cost.provider_name || '-'"></td>
                                        <td style="color:var(--mbr-muted);">
                                            <span x-text="cost.description || '-'"></span>
                                            <template x-if="cost.split_group_id">
                                                <span class="ml-1" style="display:inline-block; font-size:10px; font-weight:600; padding:1px 6px; border-radius:4px; background:rgba(201,168,76,.12); color:var(--gold);"
                                                    x-text="'Split ' + cost.split_count + '-way ($' + parseFloat(cost.split_total).toFixed(2) + ')'"></span>
                                            </template>
                                        </td>
                                        <td style="text-align:center;">
                                            <span class="cost-category-badge"
                                                :class="'cost-cat-' + (cost.expense_category || 'other')"
                                                x-text="getCategoryLabel(cost.expense_category)"></span>
                                        </td>
                                        <td class="cost-money" style="text-align:right;" x-text="'$' + parseFloat(cost.billed_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></td>
                                        <td class="capitalize" style="text-align:center;" x-text="cost.payment_type || '-'"></td>
                                        <td style="text-align:center;" x-text="cost.check_number || '-'"></td>
                                        <td x-text="cost.paid_by_name || '-'"></td>
                                        <td class="cost-money cost-money-paid" style="text-align:right;" x-text="'$' + parseFloat(cost.paid_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></td>
                                        <td x-text="cost.paid_date ? formatDate(cost.paid_date) : '-'"></td>
                                        <td style="text-align:center;">
                                            <div style="display:flex;gap:2px;justify-content:center;">
                                                <button @click="editCostEntry(cost)" title="Edit" class="icon-btn icon-btn-sm">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </button>
                                                <button @click="deleteCostEntry(cost)" title="Delete" class="icon-btn icon-btn-danger icon-btn-sm">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr class="cost-subtotal-row">
                                    <td colspan="4" style="text-align:right;padding-right:10px;text-transform:uppercase;font-size:10px;letter-spacing:0.1em;color:rgba(255,255,255,0.35);font-weight:700;font-family:'IBM Plex Sans',sans-serif;" x-text="group.label + ' Total'"></td>
                                    <td style="text-align:right;font-family:'IBM Plex Mono',monospace;" x-text="'$' + group.totalBilled.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></td>
                                    <td colspan="3"></td>
                                    <td style="text-align:right;color:var(--gold);font-weight:600;font-family:'IBM Plex Mono',monospace;" x-text="'$' + group.totalPaid.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tbody>
                        </template>
                    </table>
                </div>
            </template>
        </div>
    </div>
</div>

<!-- Cost Import Preview Modal -->
<style>
.cim { width: 720px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
.cim-header { background: #0F1B2D; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; }
.cim-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; }
.cim-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
.cim-close:hover { color: rgba(255,255,255,.75); }
.cim-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
.cim-stats { display: flex; gap: 12px; }
.cim-stat { flex: 1; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 8px; padding: 10px 14px; text-align: center; }
.cim-stat .cim-stat-val { font-size: 18px; font-weight: 700; color: var(--text, #1a2535); font-family: 'IBM Plex Mono', monospace; }
.cim-stat .cim-stat-val.gold { color: var(--gold, #C9A84C); }
.cim-stat .cim-stat-label { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-top: 2px; }
.cim-table-wrap { max-height: 320px; overflow-y: auto; border: 1.5px solid var(--border, #d0cdc5); border-radius: 8px; }
.cim-table-wrap::-webkit-scrollbar { width: 4px; }
.cim-table-wrap::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
.cim-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
.cim-btn-cancel { background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px; padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s; }
.cim-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
.cim-btn-submit { background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px; padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s; }
.cim-btn-submit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
.cim-btn-submit:disabled { opacity: .55; cursor: not-allowed; }
</style>
<div x-show="showCostImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @keydown.escape.window="showCostImportModal && (showCostImportModal = false)">
    <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showCostImportModal = false"></div>
    <div class="cim relative z-10" @click.stop>
        <div class="cim-header">
            <h3>Import Cost Ledger Preview</h3>
            <button type="button" class="cim-close" @click="showCostImportModal = false">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="cim-body">
            <div class="cim-stats">
                <div class="cim-stat">
                    <div class="cim-stat-val" x-text="costImportSummary.count || 0"></div>
                    <div class="cim-stat-label">Entries</div>
                </div>
                <div class="cim-stat">
                    <div class="cim-stat-val" x-text="'$' + (costImportSummary.total_billed || 0).toFixed(2)"></div>
                    <div class="cim-stat-label">Total Billed</div>
                </div>
                <div class="cim-stat">
                    <div class="cim-stat-val gold" x-text="'$' + (costImportSummary.total_paid || 0).toFixed(2)"></div>
                    <div class="cim-stat-label">Total Paid</div>
                </div>
            </div>
            <div class="cim-table-wrap">
                <table class="w-full text-xs">
                    <thead class="sticky top-0 bg-white">
                        <tr style="border-bottom:1.5px solid var(--border, #d0cdc5);">
                            <th class="text-left px-3 py-2">Date</th>
                            <th class="text-left px-3 py-2">Provider</th>
                            <th class="text-left px-3 py-2">Description</th>
                            <th class="text-right px-3 py-2">Billed</th>
                            <th class="text-left px-3 py-2">Type</th>
                            <th class="text-left px-3 py-2">Staff</th>
                            <th class="text-right px-3 py-2">Paid</th>
                            <th class="text-left px-3 py-2">Paid Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, idx) in costImportPreview" :key="idx">
                            <tr style="border-bottom:1px solid #f5f4f0;">
                                <td class="px-3 py-1.5 whitespace-nowrap" x-text="row.original_date ? formatDate(row.original_date) : '-'"></td>
                                <td class="px-3 py-1.5">
                                    <div class="font-medium" x-text="row.provider_name || '-'"></div>
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium"
                                        :class="getCategoryClass(row.expense_category)"
                                        x-text="getCategoryLabel(row.expense_category)"></span>
                                </td>
                                <td class="px-3 py-1.5" x-text="row.description || '-'"></td>
                                <td class="px-3 py-1.5 text-right" x-text="'$' + row.billed_amount.toFixed(2)"></td>
                                <td class="px-3 py-1.5 capitalize" x-text="row.payment_type || '-'"></td>
                                <td class="px-3 py-1.5" x-text="row.paid_by_name || '-'"></td>
                                <td class="px-3 py-1.5 text-right font-semibold" style="color:#C9A84C;" x-text="'$' + row.paid_amount.toFixed(2)"></td>
                                <td class="px-3 py-1.5 whitespace-nowrap" x-text="row.paid_date ? formatDate(row.paid_date) : '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="cim-footer">
            <button type="button" @click="showCostImportModal = false" class="cim-btn-cancel">Cancel</button>
            <button type="button" @click="confirmCostImport()" :disabled="costImporting" class="cim-btn-submit">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span x-text="costImporting ? 'Importing...' : 'Import ' + (costImportSummary.count || 0) + ' Entries'"></span>
            </button>
        </div>
    </div>
</div>
