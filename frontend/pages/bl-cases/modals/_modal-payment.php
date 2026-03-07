    <style>
    /* ── Log Payment Modal (unique styles) ── */
    .lpm-subtitle { font-size: 12px; font-weight: 500; color: var(--gold, #C9A84C); margin-top: 2px; }
    .lpm-req { color: var(--gold, #C9A84C); }
    .lpm-input, .lpm-select {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
    }
    .lpm-input:focus, .lpm-select:focus {
        border-color: var(--gold, #C9A84C); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .lpm-input::placeholder { color: #c5c5c5; }
    .lpm-input.lpm-mono { font-family: 'IBM Plex Mono', monospace; font-weight: 600; }
    .lpm-input.lpm-date { font-family: 'IBM Plex Mono', monospace; font-size: 12.5px; }
    .lpm-select {
        appearance: none; cursor: pointer; padding-right: 30px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 10px center;
    }
    .lpm-textarea {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
        resize: vertical; min-height: 80px; line-height: 1.5;
    }
    .lpm-textarea:focus {
        border-color: var(--gold, #C9A84C); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .lpm-textarea::placeholder { color: #c5c5c5; }
    .lpm-search-wrap { position: relative; }
    .lpm-search-wrap .lpm-search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #bbb; pointer-events: none; z-index: 1; }
    .lpm-search-wrap .lpm-input { padding-left: 34px; padding-right: 2rem; }
    .lpm-search-wrap .lpm-clear-btn {
        position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
        background: none; border: none; color: #bbb; cursor: pointer; padding: 2px; transition: color .15s;
    }
    .lpm-search-wrap .lpm-clear-btn:hover { color: #888; }
    .lpm-dropdown {
        position: absolute; z-index: 10; width: 100%; margin-top: 4px; background: #fff;
        border: 1.5px solid var(--border, #d0cdc5); border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,.12); max-height: 200px; overflow-y: auto;
    }
    .lpm-dropdown::-webkit-scrollbar { width: 4px; }
    .lpm-dropdown::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
    .lpm-dropdown-item {
        width: 100%; text-align: left; background: none; border: none; padding: 9px 14px;
        font-size: 13px; color: #1a2535; cursor: pointer; display: flex; justify-content: space-between; align-items: center;
        transition: background .1s;
    }
    .lpm-dropdown-item:hover { background: rgba(201,168,76,.06); }
    .lpm-dropdown-item .lpm-type-label { font-size: 11px; color: var(--muted, #8a8a82); }
    .lpm-dropdown-create {
        width: 100%; text-align: left; background: none; border: none; padding: 9px 14px;
        font-size: 13px; font-weight: 600; color: var(--gold, #C9A84C); cursor: pointer;
        display: flex; align-items: center; gap: 6px; border-top: 1px solid var(--border, #d0cdc5);
        transition: background .1s;
    }
    .lpm-dropdown-create:hover { background: rgba(201,168,76,.06); }
    .lpm-hint { font-size: 11px; color: var(--muted, #8a8a82); margin-top: 4px; }
    .lpm-linked { font-size: 11px; color: var(--gold, #C9A84C); margin-top: 4px; display: flex; align-items: center; gap: 4px; }
    .lpm-amount-wrap { position: relative; }
    .lpm-amount-wrap .lpm-dollar { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); font-size: 13px; font-weight: 600; color: #8a8a82; pointer-events: none; font-family: 'IBM Plex Mono', monospace; }
    .lpm-amount-wrap .lpm-input { padding-left: 24px; font-family: 'IBM Plex Mono', monospace; font-weight: 600; }
    .lpm-file-card {
        border: 1.5px dashed var(--border, #d0cdc5); border-radius: 7px; padding: 14px 16px;
        background: #fafafa; transition: all .15s; display: flex; align-items: center; gap: 12px;
    }
    .lpm-file-card:hover { border-color: rgba(201,168,76,.5); background: #fff; }
    .lpm-file-choose {
        background: #0F1B2D; color: #fff; border: none; border-radius: 5px;
        padding: 7px 14px; font-size: 12px; font-weight: 600; cursor: pointer; white-space: nowrap;
        transition: background .15s;
    }
    .lpm-file-choose:hover { background: #1a2d45; }
    .lpm-file-name { font-size: 12.5px; color: #aaa; font-style: italic; }
    .lpm-file-attached {
        display: flex; align-items: center; gap: 8px; padding: 10px 14px;
        background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 7px;
    }
    .lpm-file-attached span { font-size: 12.5px; font-weight: 500; color: #166534; }
    .lpm-file-attached .lpm-file-remove {
        margin-left: auto; background: none; border: none; color: #f87171; cursor: pointer; padding: 2px; transition: color .15s;
    }
    .lpm-file-attached .lpm-file-remove:hover { color: #dc2626; }
    .lpm-file-hint { font-size: 10.5px; color: var(--muted, #8a8a82); margin-top: 6px; }
    .lpm-btn-cancel {
        background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
    }
    .lpm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
    .lpm-btn-submit {
        background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px;
        padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
        box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s;
    }
    .lpm-btn-submit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
    .lpm-btn-submit:disabled { opacity: .55; cursor: not-allowed; }
    /* Split panel */
    .lpm-split-panel {
        border: 1.5px solid rgba(201,168,76,.3); background: rgba(201,168,76,.04);
        border-radius: 8px;
    }
    .lpm-split-inner { padding: 12px 16px; }
    .lpm-split-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
    .lpm-split-toggle { display: flex; align-items: center; gap: 8px; cursor: pointer; }
    .lpm-split-toggle input[type="checkbox"] { accent-color: var(--gold, #C9A84C); width: 15px; height: 15px; cursor: pointer; }
    .lpm-split-toggle span { font-size: 13px; font-weight: 600; color: var(--gold, #C9A84C); }
    .lpm-split-count { font-size: 11px; color: var(--muted, #8a8a82); }
    .lpm-split-case { display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 5px; font-size: 13px; }
    .lpm-split-case input[type="checkbox"] { accent-color: var(--gold, #C9A84C); width: 14px; height: 14px; cursor: pointer; }
    .lpm-split-case.current { background: rgba(201,168,76,.08); }
    .lpm-split-case .lpm-case-tag { font-size: 10px; color: var(--muted, #8a8a82); }
    .lpm-split-calc {
        display: flex; align-items: center; gap: 8px; padding: 8px 12px; margin-top: 10px;
        background: rgba(201,168,76,.1); border: 1px solid rgba(201,168,76,.2); border-radius: 7px;
        font-size: 13px; font-weight: 600; color: var(--gold, #C9A84C);
    }
    .lpm-norecord-notice {
        padding: 9px 14px; border-radius: 7px; font-size: 12.5px; font-weight: 500;
        background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;
    }
    </style>

    <!-- Payment Modal -->
    <div x-show="showPaymentModal" class="sp-modal-overlay"
        style="display:none;" @keydown.escape.window="showPaymentModal && (showPaymentModal = false)">
        <div class="fixed inset-0" @click="showPaymentModal = false"></div>
        <form @submit.prevent="submitPayment()" class="sp-modal-box relative z-10" @click.stop>

            <!-- Header -->
            <div class="sp-modal-header">
                <div>
                    <h3 class="sp-modal-title" x-text="paymentForm.id ? 'Edit Payment' : 'Log Payment'"></h3>
                    <div class="lpm-subtitle" x-text="paymentForm.provider_name || 'Case-level cost'"></div>
                </div>
                <button type="button" class="sp-modal-close" @click="showPaymentModal = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="sp-modal-body">

                <!-- Provider Search -->
                <div>
                    <label class="sp-form-label">Provider</label>
                    <div class="lpm-search-wrap">
                        <span class="lpm-search-icon">🔍</span>
                        <input type="text" x-model="paymentProviderSearch"
                            @input.debounce.300ms="searchPaymentProviders()"
                            @focus="if((paymentProviderSearch || '').length >= 2) searchPaymentProviders()"
                            placeholder="Search provider or leave empty for case-level cost..."
                            class="lpm-input">
                        <button type="button" x-show="(paymentProviderSearch || '').length > 0"
                            @click="clearPaymentProvider()" class="lpm-clear-btn">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <div x-show="showPaymentProviderDropdown" @click.outside="showPaymentProviderDropdown = false" class="lpm-dropdown">
                            <template x-for="pr in paymentProviderResults" :key="pr.id">
                                <button type="button" @click="selectPaymentProvider(pr)" class="lpm-dropdown-item">
                                    <span x-text="pr.name"></span>
                                    <span class="lpm-type-label" x-text="getProviderTypeLabel(pr.type)"></span>
                                </button>
                            </template>
                            <button type="button" @click="openQuickAddProvider('payment')" class="lpm-dropdown-create">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Create "<span x-text="paymentProviderSearch"></span>"
                            </button>
                        </div>
                    </div>
                    <p x-show="paymentForm.case_provider_id" class="lpm-linked">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Linked to case provider
                    </p>
                    <p x-show="paymentProviderSearch && !paymentForm.case_provider_id && paymentForm.provider_name" class="lpm-hint">Provider not linked to this case</p>
                </div>

                <!-- Details -->
                <div class="sp-form-section">Details</div>
                <div class="sp-form-grid-2">
                    <div style="flex:2;">
                        <label class="sp-form-label">Description</label>
                        <input type="text" x-model="paymentForm.description" class="lpm-input"
                            placeholder="Record Fee, Police Report, etc.">
                    </div>
                    <div style="flex:1;">
                        <label class="sp-form-label">Category</label>
                        <select x-model="paymentForm.expense_category"
                            @change="$nextTick(() => { if(paymentForm.expense_category === 'litigation' && !paymentForm.id) fetchRelatedCases(); else resetSplitState(); })"
                            class="lpm-select">
                            <option value="mr_cost">Records Fee</option>
                            <option value="litigation">Litigation</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Litigation Cost Split Panel -->
                <div x-show="showSplitPanel"
                    class="lpm-split-panel">
                    <div class="lpm-split-inner">
                        <div class="lpm-split-header">
                            <label class="lpm-split-toggle">
                                <input type="checkbox" x-model="splitEnabled"
                                    @change="if(splitEnabled && splitSelectedCaseIds.length === 0) { splitSelectedCaseIds = [parseInt(caseId), ...relatedCases.map(c => c.id)]; }">
                                <span>Split cost across related cases</span>
                            </label>
                            <span class="lpm-split-count"
                                x-text="(relatedCases.length + 1) + ' cases with #' + (caseData?.case_number || '')"></span>
                        </div>
                        <div x-show="splitEnabled" x-transition>
                            <div style="display:flex; flex-direction:column; gap:4px; margin-bottom:10px;">
                                <label class="lpm-split-case current">
                                    <input type="checkbox" checked disabled>
                                    <span style="font-weight:500;" x-text="caseData?.client_name || 'Current case'"></span>
                                    <span class="lpm-case-tag">(current)</span>
                                </label>
                                <template x-for="rc in relatedCases" :key="rc.id">
                                    <label class="lpm-split-case" style="cursor:pointer;">
                                        <input type="checkbox"
                                            :checked="splitSelectedCaseIds.includes(rc.id)"
                                            @change="toggleSplitCase(rc.id)">
                                        <span x-text="rc.client_name"></span>
                                        <span class="lpm-case-tag"
                                            x-text="rc.client_dob ? '(DOB: ' + formatDate(rc.client_dob) + ')' : ''"></span>
                                    </label>
                                </template>
                            </div>
                            <div class="lpm-split-calc">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>
                                    $<span x-text="(paymentForm.billed_amount || 0).toFixed(2)"></span>
                                    &divide; <span x-text="splitSelectedCaseIds.length"></span>
                                    = <strong>$<span x-text="splitPerPersonAmount().toFixed(2)"></span></strong>
                                    per person
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading related cases -->
                <div x-show="loadingRelatedCases" class="lpm-hint" style="display:flex; align-items:center; gap:6px;">
                    <span class="spinner" style="width:14px; height:14px; border-width:2px;"></span>
                    Checking for related cases...
                </div>

                <!-- Amounts & Dates -->
                <div class="sp-form-section">Amounts &amp; Dates</div>

                <!-- No Record Fee notice -->
                <template x-if="paymentForm._noRecordFee">
                    <div class="lpm-norecord-notice">This provider does not charge a record fee. Payment fields are hidden.</div>
                </template>

                <div :style="paymentForm._noRecordFee
                    ? 'display:grid; grid-template-columns:1fr 1fr; gap:12px;'
                    : 'display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:12px;'">
                    <div>
                        <label class="sp-form-label">Billed Amount</label>
                        <div class="lpm-amount-wrap">
                            <span class="lpm-dollar">$</span>
                            <input type="number" step="0.01" min="0" x-model.number="paymentForm.billed_amount" class="lpm-input">
                        </div>
                    </div>
                    <div x-show="!paymentForm._noRecordFee">
                        <label class="sp-form-label">Paid Amount</label>
                        <div class="lpm-amount-wrap">
                            <span class="lpm-dollar">$</span>
                            <input type="number" step="0.01" min="0" x-model.number="paymentForm.paid_amount"
                                class="lpm-input">
                        </div>
                    </div>
                    <div>
                        <label class="sp-form-label">Date <span class="lpm-req">*</span></label>
                        <input type="date" x-model="paymentForm.payment_date" required class="lpm-input lpm-date">
                    </div>
                    <div x-show="!paymentForm._noRecordFee">
                        <label class="sp-form-label">Paid Date</label>
                        <input type="date" x-model="paymentForm.paid_date" class="lpm-input lpm-date">
                    </div>
                </div>

                <!-- Payment Info -->
                <template x-if="!paymentForm._noRecordFee">
                    <div style="display:flex; flex-direction:column; gap:16px;">
                        <div class="sp-form-section">Payment Info</div>
                        <div class="sp-form-grid-3">
                            <div>
                                <label class="sp-form-label">Payment Type</label>
                                <select x-model="paymentForm.payment_type" @change="autoFillCardNumber()" class="lpm-select">
                                    <option value="">Select...</option>
                                    <option value="check">Check</option>
                                    <option value="card">Card</option>
                                    <option value="cash">Cash</option>
                                    <option value="wire">Wire</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div x-show="paymentForm.payment_type === 'check' || paymentForm.payment_type === 'card'">
                                <label class="sp-form-label" x-text="paymentForm.payment_type === 'card' ? 'Card Last 4' : 'Check #'"></label>
                                <input type="text" x-model="paymentForm.check_number" class="lpm-input lpm-mono"
                                    :placeholder="paymentForm.payment_type === 'card' ? 'Last 4 digits' : 'Check number'"
                                    :readonly="paymentForm.payment_type === 'card' && paymentForm.check_number !== ''"
                                    :style="paymentForm.payment_type === 'card' && paymentForm.check_number !== '' ? 'background:#f9f9f6;color:var(--gold);' : ''"
                                    maxlength="paymentForm.payment_type === 'card' ? 4 : 50">
                            </div>
                            <div :class="paymentForm.payment_type !== 'check' && paymentForm.payment_type !== 'card' ? 'col-span-2' : ''">
                                <label class="sp-form-label">Paid By</label>
                                <select x-model="paymentForm.paid_by" @change="autoFillCardNumber()" class="lpm-select">
                                    <option value="">Select staff...</option>
                                    <template x-for="u in staffList" :key="u.id">
                                        <option :value="u.id" x-text="u.display_name || u.full_name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Receipt / Invoice -->
                <div class="sp-form-section">Receipt / Invoice</div>
                <div x-data="{ uploading: false }">
                    <template x-if="paymentForm.receipt_document_id">
                        <div class="lpm-file-attached">
                            <svg width="16" height="16" fill="none" stroke="#166534" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span x-text="paymentForm.receipt_file_name || 'Receipt attached'"></span>
                            <button type="button" @click="paymentForm.receipt_document_id = null; paymentForm.receipt_file_name = ''" class="lpm-file-remove">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                    <template x-if="!paymentForm.receipt_document_id">
                        <div>
                            <div class="lpm-file-card" @click="$refs.lpmFileInput.click()" style="cursor:pointer;">
                                <span class="lpm-file-choose">Choose File</span>
                                <span class="lpm-file-name" x-text="uploading ? 'Uploading...' : 'No file chosen'"></span>
                            </div>
                            <input type="file" x-ref="lpmFileInput" accept=".pdf,.jpg,.jpeg,.png,.tif,.tiff"
                                @change="uploadReceipt($event)" :disabled="uploading" style="display:none;">
                            <div class="lpm-file-hint">PDF, JPG, PNG, TIF (optional)</div>
                        </div>
                    </template>
                </div>

                <!-- Notes -->
                <div class="sp-form-section">Notes</div>
                <div>
                    <textarea x-model="paymentForm.notes" class="lpm-textarea"
                        placeholder="Additional notes..."></textarea>
                </div>

            </div>

            <!-- Footer -->
            <div class="sp-modal-footer">
                <button type="button" @click="showPaymentModal = false" class="lpm-btn-cancel">Cancel</button>
                <button type="submit" :disabled="saving" class="lpm-btn-submit">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="paymentForm.id ? 'Update Payment' : 'Log Payment'"></span>
                </button>
            </div>
        </form>
    </div>
