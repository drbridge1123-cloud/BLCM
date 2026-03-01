    <style>
    /* ── Log Receipt Modal ── */
    .rcm { width: 560px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
    .rcm-header { background: #0F1B2D; padding: 18px 24px 16px; display: flex; align-items: flex-start; justify-content: space-between; }
    .rcm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; line-height: 1.3; }
    .rcm-header .rcm-subtitle { font-size: 12px; font-weight: 500; color: var(--gold, #C9A84C); margin-top: 2px; }
    .rcm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; margin-top: 2px; }
    .rcm-close:hover { color: rgba(255,255,255,.75); }
    .rcm-body { padding: 24px; display: flex; flex-direction: column; gap: 18px; max-height: 70vh; overflow-y: auto; }
    .rcm-body::-webkit-scrollbar { width: 4px; }
    .rcm-body::-webkit-scrollbar-track { background: transparent; }
    .rcm-body::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
    .rcm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
    .rcm-req { color: var(--gold, #C9A84C); }
    .rcm-input, .rcm-select {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 6px;
        padding: 8px 11px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
    }
    .rcm-input:focus, .rcm-select:focus {
        border-color: var(--gold, #C9A84C); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .rcm-input.rcm-mono { font-family: 'IBM Plex Mono', monospace; }
    .rcm-select {
        appearance: none; cursor: pointer; padding-right: 30px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 10px center;
    }
    .rcm-textarea {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 6px;
        padding: 8px 11px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
        resize: vertical; min-height: 60px;
    }
    .rcm-textarea:focus {
        border-color: var(--gold, #C9A84C); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .rcm-section { display: flex; align-items: center; gap: 10px; margin: 0; }
    .rcm-section::before, .rcm-section::after { content: ''; flex: 1; height: 1px; background: var(--border, #d0cdc5); }
    .rcm-section span { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .1em; white-space: nowrap; }
    .rcm-item-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .rcm-item-card {
        display: flex; align-items: center; gap: 9px; cursor: pointer;
        border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px; padding: 10px 13px;
        background: #fafafa; font-size: 13px; color: #3D4F63; transition: all .15s;
    }
    .rcm-item-card:hover { border-color: rgba(201,168,76,.5); }
    .rcm-item-card.checked { border-color: var(--gold, #C9A84C); background: rgba(201,168,76,.06); }
    .rcm-item-card input[type="checkbox"] { accent-color: var(--gold, #C9A84C); width: 15px; height: 15px; cursor: pointer; flex-shrink: 0; }
    .rcm-complete-card {
        display: flex; align-items: center; gap: 9px;
        border: 1.5px solid rgba(26,158,106,.2); border-radius: 7px; padding: 10px 14px;
        background: #f0faf6; transition: all .15s;
    }
    .rcm-complete-card.checked { border-color: rgba(26,158,106,.5); }
    .rcm-complete-card input[type="checkbox"] { accent-color: #1a9e6a; width: 17px; height: 17px; cursor: pointer; flex-shrink: 0; }
    .rcm-complete-card span { font-size: 12.5px; font-weight: 600; color: #1a9e6a; cursor: pointer; }
    .rcm-file-wrap { position: relative; }
    .rcm-file-wrap .rcm-file-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); font-size: 14px; pointer-events: none; }
    .rcm-file-wrap .rcm-input { padding-left: 32px; font-family: 'IBM Plex Mono', monospace; font-size: 12px; }
    .rcm-norecord-card {
        display: flex; align-items: center; gap: 9px; cursor: pointer;
        border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px; padding: 10px 13px;
        background: #fff; transition: all .15s;
    }
    .rcm-norecord-card:hover { border-color: rgba(231,76,60,.27); }
    .rcm-norecord-card input[type="checkbox"] { accent-color: #e74c3c; width: 15px; height: 15px; cursor: pointer; flex-shrink: 0; }
    .rcm-norecord-card span { font-size: 13px; font-weight: 500; color: #e74c3c; }
    .rcm-norecord-panel {
        background: #FEF2F2; border: 1.5px solid #FECACA; border-radius: 8px; padding: 16px 18px;
        display: flex; flex-direction: column; gap: 14px;
    }
    .rcm-norecord-panel .rcm-nr-header {
        display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #991B1B;
    }
    .rcm-norecord-panel .rcm-nr-header input[type="checkbox"] { accent-color: #DC2626; width: 17px; height: 17px; cursor: pointer; }
    .rcm-norecord-panel .rcm-select { border-color: #FECACA; }
    .rcm-norecord-panel .rcm-select:focus { border-color: #f87171; box-shadow: 0 0 0 3px rgba(220,38,38,.1); }
    .rcm-norecord-panel .rcm-textarea { border-color: #FECACA; }
    .rcm-norecord-panel .rcm-textarea:focus { border-color: #f87171; box-shadow: 0 0 0 3px rgba(220,38,38,.1); }
    .rcm-norecord-panel .rcm-label { color: #991B1B; }
    .rcm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: space-between; align-items: center; }
    .rcm-btn-hold {
        background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 6px 14px; cursor: pointer; transition: all .15s;
        display: flex; flex-direction: column; align-items: center; gap: 2px;
    }
    .rcm-btn-hold:hover { border-color: var(--gold, #C9A84C); }
    .rcm-btn-hold:disabled { opacity: .5; cursor: not-allowed; }
    .rcm-btn-hold .rcm-hold-icon { font-size: 14px; line-height: 1; }
    .rcm-btn-hold .rcm-hold-label { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .05em; }
    .rcm-btn-cancel {
        background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
    }
    .rcm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
    .rcm-btn-submit {
        background: #1a9e6a; color: #fff; border: none; border-radius: 7px;
        padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
        box-shadow: 0 2px 8px rgba(26,158,106,.3); display: flex; align-items: center; gap: 6px; transition: all .15s;
    }
    .rcm-btn-submit:hover { filter: brightness(1.08); box-shadow: 0 4px 12px rgba(26,158,106,.4); }
    .rcm-btn-submit:disabled { opacity: .55; cursor: not-allowed; }
    .rcm-btn-submit.rcm-btn-danger { background: #DC2626; box-shadow: 0 2px 8px rgba(220,38,38,.3); }
    .rcm-btn-submit.rcm-btn-danger:hover { box-shadow: 0 4px 12px rgba(220,38,38,.4); }
    </style>

    <!-- Receipt Modal -->
    <div x-show="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;" @keydown.escape.window="showReceiptModal && (showReceiptModal = false)">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showReceiptModal = false"></div>
        <form @submit.prevent="submitReceipt()" class="rcm relative z-10" @click.stop>

            <!-- Header -->
            <div class="rcm-header">
                <div>
                    <h3>Log Receipt</h3>
                    <div class="rcm-subtitle" x-text="currentProvider?.provider_name"></div>
                </div>
                <button type="button" class="rcm-close" @click="showReceiptModal = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="rcm-body">

                <!-- No Records Panel (shown when toggled) -->
                <div x-show="newReceipt.no_records" x-transition class="rcm-norecord-panel">
                    <label class="rcm-nr-header">
                        <input type="checkbox" x-model="newReceipt.no_records">
                        No Records Available
                    </label>
                    <div>
                        <label class="rcm-label">Reason <span class="rcm-req">*</span></label>
                        <select x-model="newReceipt.no_records_reason" class="rcm-select">
                            <option value="">Select reason...</option>
                            <option value="no_treatment">No treatment records for this period</option>
                            <option value="patient_not_found">Patient not found in system</option>
                            <option value="records_destroyed">Records destroyed (retention expired)</option>
                            <option value="provider_closed">Provider closed / out of business</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="rcm-label">Details</label>
                        <textarea x-model="newReceipt.no_records_detail" rows="2" class="rcm-textarea"
                            placeholder="Additional details (who confirmed, date, etc.)..."></textarea>
                    </div>
                </div>

                <!-- Normal receipt fields (hidden when no_records) -->
                <template x-if="!newReceipt.no_records">
                    <div style="display:flex; flex-direction:column; gap:18px;">

                        <!-- Date + Method -->
                        <div style="display:flex; gap:12px;">
                            <div style="flex:2;">
                                <label class="rcm-label">Received Date <span class="rcm-req">*</span></label>
                                <input type="date" x-model="newReceipt.received_date" required class="rcm-input rcm-mono">
                            </div>
                            <div style="flex:1;">
                                <label class="rcm-label">Method <span class="rcm-req">*</span></label>
                                <select x-model="newReceipt.received_method" required class="rcm-select">
                                    <option value="fax">Fax</option>
                                    <option value="email">Email</option>
                                    <option value="portal">Portal</option>
                                    <option value="mail">Mail</option>
                                    <option value="in_person">In Person</option>
                                </select>
                            </div>
                        </div>

                        <!-- Received Items -->
                        <div class="rcm-section"><span>Received Items</span></div>
                        <div class="rcm-item-grid">
                            <label class="rcm-item-card" :class="{ checked: newReceipt.has_medical_records }">
                                <input type="checkbox" x-model="newReceipt.has_medical_records"> Medical Records
                            </label>
                            <label class="rcm-item-card" :class="{ checked: newReceipt.has_billing }">
                                <input type="checkbox" x-model="newReceipt.has_billing"> Billing
                            </label>
                            <label class="rcm-item-card" :class="{ checked: newReceipt.has_chart }">
                                <input type="checkbox" x-model="newReceipt.has_chart"> Chart Notes
                            </label>
                            <label class="rcm-item-card" :class="{ checked: newReceipt.has_imaging }">
                                <input type="checkbox" x-model="newReceipt.has_imaging"> Imaging
                            </label>
                        </div>

                        <!-- All records complete -->
                        <label class="rcm-complete-card" :class="{ checked: newReceipt.is_complete }">
                            <input type="checkbox" x-model="newReceipt.is_complete">
                            <span>All records complete</span>
                        </label>

                        <!-- Incomplete reason -->
                        <div x-show="!newReceipt.is_complete" x-transition>
                            <label class="rcm-label">Incomplete Reason</label>
                            <textarea x-model="newReceipt.incomplete_reason" rows="2" class="rcm-textarea" placeholder="Describe what's missing..."></textarea>
                        </div>

                        <!-- File Location -->
                        <div>
                            <label class="rcm-label">File Location (SharePoint Path)</label>
                            <div class="rcm-file-wrap">
                                <span class="rcm-file-icon">📁</span>
                                <input type="text" x-model="newReceipt.file_location" class="rcm-input" placeholder="\\sharepoint\cases\...">
                            </div>
                        </div>

                        <!-- No Records toggle -->
                        <label class="rcm-norecord-card">
                            <input type="checkbox" x-model="newReceipt.no_records">
                            <span>🚫 Provider has no records available</span>
                        </label>

                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="rcm-footer">
                <div>
                    <button x-show="!newReceipt.no_records" type="button" @click="setProviderOnHold()" :disabled="saving" class="rcm-btn-hold">
                        <span class="rcm-hold-icon">⏸</span>
                        <span class="rcm-hold-label">On Hold</span>
                    </button>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <button type="button" @click="showReceiptModal = false" class="rcm-btn-cancel">Cancel</button>
                    <!-- Normal receipt submit -->
                    <button x-show="!newReceipt.no_records" type="submit" :disabled="saving" class="rcm-btn-submit">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Log Receipt
                    </button>
                    <!-- No records submit -->
                    <button x-show="newReceipt.no_records" type="submit" :disabled="saving || !newReceipt.no_records_reason" class="rcm-btn-submit rcm-btn-danger">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        Mark No Records
                    </button>
                </div>
            </div>
        </form>
    </div>
