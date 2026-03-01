<!-- All sp- styles loaded from shared sp-design-system.css -->

<div x-data="bankReconciliationPage()">

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">
        <div class="sp-gold-bar"></div>
        <div class="sp-header" style="flex-wrap:wrap; gap:16px;">
            <div style="flex:1;">
                <div class="sp-eyebrow">Admin</div>
                <h1 class="sp-title">Bank Reconciliation</h1>
            </div>
            <div class="sp-stats">
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#1a2535;" x-text="summary.total_entries"></div>
                    <div class="sp-stat-label">Total</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#dc2626;" x-text="summary.unmatched_count"></div>
                    <div class="sp-stat-label">Unmatched</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#16a34a;" x-text="summary.matched_count"></div>
                    <div class="sp-stat-label">Matched</div>
                </div>
                <div class="sp-stat">
                    <div class="sp-stat-num" style="color:#9ca3af;" x-text="summary.ignored_count"></div>
                    <div class="sp-stat-label">Ignored</div>
                </div>
            </div>
        </div>

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

</div>
