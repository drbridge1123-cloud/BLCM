<style>
.mrt-backdrop { background: rgba(0,0,0,.45); }
.mrt-modal { border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
.mrt-header { background: #0F1B2D; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; }
.mrt-title { font-size: 15px; font-weight: 700; color: #fff; }
.mrt-subtitle { font-size: 12px; font-weight: 500; color: var(--gold, #C9A84C); margin-top: 2px; }
.mrt-close { color: rgba(255,255,255,.35); background: none; border: none; cursor: pointer; padding: 0; display: flex; align-items: center; justify-content: center; transition: color .2s; }
.mrt-close:hover { color: rgba(255,255,255,.75); }
.mrt-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
.mrt-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
.mrt-label { font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; display: block; }
.mrt-label .mrt-req { color: var(--gold, #C9A84C); }
.mrt-input, .mrt-select, .mrt-textarea { width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px; padding: 9px 12px; font-size: 13px; outline: none; transition: border-color .2s, background .2s, box-shadow .2s; }
.mrt-input:focus, .mrt-select:focus, .mrt-textarea:focus { border-color: var(--gold, #C9A84C); background: #fff; box-shadow: 0 0 0 3px rgba(201,168,76,.1); }
.mrt-select { appearance: none; padding-right: 30px; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; }
.mrt-textarea { resize: vertical; min-height: 70px; line-height: 1.5; }
.mrt-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.mrt-btn-cancel { background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px; padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background .15s; }
.mrt-btn-cancel:hover { background: #f7f7f5; }
.mrt-btn-primary { background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px; padding: 9px 22px; font-size: 13px; font-weight: 700; box-shadow: 0 2px 8px rgba(201,168,76,.35); cursor: pointer; display: flex; align-items: center; gap: 6px; transition: opacity .15s; }
.mrt-btn-primary:hover { opacity: .92; }
.mrt-btn-primary:disabled { opacity: .5; cursor: not-allowed; }
.mrt-btn-send { background: #1a9e6a; color: #fff; border: none; border-radius: 7px; padding: 9px 22px; font-size: 13px; font-weight: 700; box-shadow: 0 2px 8px rgba(26,158,106,.3); cursor: pointer; display: flex; align-items: center; gap: 6px; transition: opacity .15s; }
.mrt-btn-send:hover { opacity: .92; }
.mrt-btn-send:disabled { opacity: .5; cursor: not-allowed; }
.psm { width: 800px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; max-height: 90vh; display: flex; flex-direction: column; }
.psm-header { background: #0F1B2D; padding: 18px 24px 16px; display: flex; align-items: flex-start; justify-content: space-between; flex-shrink: 0; }
.psm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; line-height: 1.3; }
.psm-header .psm-subtitle { font-size: 12px; font-weight: 500; color: var(--gold, #C9A84C); margin-top: 2px; }
.psm-header-actions { display: flex; align-items: center; gap: 10px; }
.psm-edit-btn { padding: 5px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; border: 1.5px solid rgba(255,255,255,.2); background: none; color: rgba(255,255,255,.6); cursor: pointer; display: flex; align-items: center; gap: 5px; transition: all .15s; }
.psm-edit-btn:hover { color: #fff; background: rgba(255,255,255,.1); }
.psm-edit-btn.active { color: #fff; background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.3); }
.psm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
.psm-close:hover { color: rgba(255,255,255,.75); }
.psm-toolbar { padding: 12px 24px; border-bottom: 1px solid var(--border, #d0cdc5); background: #fafafa; flex-shrink: 0; }
.psm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
.psm-input { width: 100%; background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px; padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit; }
.psm-input:focus { border-color: var(--gold, #C9A84C); box-shadow: 0 0 0 3px rgba(201,168,76,.1); }
.psm-input::placeholder { color: #c5c5c5; }
.psm-input[readonly] { background: #f5f5f0; color: var(--muted, #8a8a82); cursor: default; }
.psm-content { flex: 1; overflow-y: auto; padding: 16px 24px; }
.psm-iframe-wrap { border: 1.5px solid var(--border, #d0cdc5); border-radius: 8px; background: #fff; box-shadow: inset 0 1px 3px rgba(0,0,0,.05); overflow: hidden; transition: border-color .2s; }
.psm-iframe-wrap.editing { border-color: var(--gold, #C9A84C); box-shadow: inset 0 1px 3px rgba(0,0,0,.05), 0 0 0 3px rgba(201,168,76,.1); }
.psm-iframe-wrap iframe { width: 100%; border: 0; min-height: 600px; }
.psm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
.psm-footer-info { font-size: 12px; color: var(--muted, #8a8a82); display: flex; align-items: center; gap: 10px; }
.psm-footer-info .psm-reset-btn { text-decoration: underline; color: var(--muted, #8a8a82); background: none; border: none; cursor: pointer; font-size: 12px; }
.psm-footer-info .psm-modified { display: inline-flex; align-items: center; gap: 4px; color: #d97706; font-size: 11px; font-weight: 500; }
.psm-btn-cancel { background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px; padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s; }
.psm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
.psm-btn-send { background: #1a9e6a; color: #fff; border: none; border-radius: 7px; padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 8px rgba(26,158,106,.3); display: flex; align-items: center; gap: 6px; transition: all .15s; }
.psm-btn-send:hover { filter: brightness(1.08); }
.psm-btn-send:disabled { opacity: .55; cursor: not-allowed; }
</style>

<!-- MR Request Modal -->
<div x-show="showRequestModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @keydown.escape.window="showRequestModal = false">
    <div class="fixed inset-0 mrt-backdrop" @click="showRequestModal = false"></div>
    <div class="mrt-modal relative w-full max-w-lg z-10" @click.stop>
        <div class="mrt-header">
            <div><div class="mrt-title">New Request</div><div class="mrt-subtitle" x-text="reqForm._carrierLabel"></div></div>
            <button type="button" class="mrt-close" @click="showRequestModal = false"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="mrt-body">
            <div class="mrt-row">
                <div><label class="mrt-label">Request Date <span class="mrt-req">*</span></label><input type="date" x-model="reqForm.request_date" class="mrt-input"></div>
                <div><label class="mrt-label">Method <span class="mrt-req">*</span></label>
                    <select x-model="reqForm.request_method" @change="updateRecipient()" class="mrt-select"><option value="">Select...</option><option value="email">Email</option><option value="fax">Fax</option><option value="portal">Portal</option><option value="phone">Phone</option><option value="mail">Mail</option></select>
                </div>
            </div>
            <div class="mrt-row">
                <div><label class="mrt-label">Type</label><select x-model="reqForm.request_type" class="mrt-select"><option value="initial">Initial</option><option value="follow_up">Follow Up</option><option value="re_request">Re-Request</option></select></div>
                <div><label class="mrt-label">Send To</label><input type="text" x-model="reqForm.sent_to" class="mrt-input" placeholder="Email or fax #"></div>
            </div>
            <div><label class="mrt-label">Template</label><select x-model="reqForm.template_id" class="mrt-select"><template x-for="t in hlTemplates" :key="t.id"><option :value="t.id" x-text="t.name + (t.is_default ? ' (Default)' : '')"></option></template></select></div>
            <div><label class="mrt-label">Notes</label><textarea x-model="reqForm.notes" rows="2" class="mrt-textarea"></textarea></div>
            <!-- Document Attachments -->
            <div x-show="reqForm.request_method === 'email' || reqForm.request_method === 'fax'"
                 x-data="{ docs: [], selectedIds: [], docLoading: false, showDocs: false, uploading: false, _loaded: false,
                    async loadDocs() { if (!reqForm._caseId) return; this._loaded = true; this.docLoading = true; try { const res = await api.get('documents?case_id=' + reqForm._caseId); this.docs = res.success ? (Array.isArray(res.data) ? res.data : []) : []; } catch(e) { this.docs = []; } this.docLoading = false; },
                    toggleDoc(id) { const i = this.selectedIds.indexOf(id); if (i > -1) this.selectedIds.splice(i, 1); else this.selectedIds.push(id); reqForm.document_ids = [...this.selectedIds]; },
                    selectAllDocs() { this.selectedIds = this.docs.map(d => d.id); reqForm.document_ids = [...this.selectedIds]; },
                    clearDocs() { this.selectedIds = []; reqForm.document_ids = []; },
                    async quickUpload(event) { const file = event.target.files[0]; if (!file) return; this.uploading = true; try { const fd = new FormData(); fd.append('file', file); fd.append('case_id', reqForm._caseId); fd.append('document_type', 'other'); const res = await api.upload('documents/upload', fd); if (res.success && res.data) { await this.loadDocs(); this.selectedIds.push(res.data.id); reqForm.document_ids = [...this.selectedIds]; showToast('File uploaded & selected'); } } catch(e) { showToast(e.data?.message || 'Upload failed', 'error'); } this.uploading = false; event.target.value = ''; }
                 }" x-effect="if (showRequestModal && reqForm._caseId && !_loaded && !docLoading) loadDocs()">
                <button type="button" @click="showDocs = !showDocs" class="w-full flex items-center justify-between py-2 text-left">
                    <div class="flex items-center gap-2"><label class="mrt-label" style="margin:0;cursor:pointer;">Attachments</label><span x-show="selectedIds.length > 0" class="text-[10px] font-bold bg-gold/20 text-gold px-1.5 py-0.5 rounded-full" x-text="selectedIds.length + ' selected'"></span></div>
                    <svg class="w-4 h-4 text-v2-text-light transition-transform" :class="showDocs ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="showDocs" x-collapse>
                    <div class="flex items-center justify-between mb-2">
                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gold bg-gold/10 rounded-lg cursor-pointer hover:bg-gold/20"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg><span x-text="uploading ? 'Uploading...' : 'Upload'"></span><input type="file" class="hidden" @change="quickUpload($event)" :disabled="uploading" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.tiff,.xls,.xlsx"></label>
                        <div class="flex items-center gap-2 text-[11px]"><button type="button" @click="selectAllDocs()" :disabled="docs.length === 0" class="font-bold text-gold hover:opacity-70 disabled:opacity-30">Select All</button><span class="text-v2-text-light">|</span><button type="button" @click="clearDocs()" :disabled="selectedIds.length === 0" class="font-bold text-gold hover:opacity-70 disabled:opacity-30">Clear</button></div>
                    </div>
                    <div x-show="docLoading" class="text-center py-3 text-xs text-v2-text-light">Loading documents...</div>
                    <div x-show="!docLoading && docs.length === 0" class="text-center py-3 text-xs text-v2-text-light">No documents yet. Upload a file above.</div>
                    <div x-show="!docLoading && docs.length > 0" class="space-y-1 max-h-[160px] overflow-y-auto">
                        <template x-for="doc in docs" :key="doc.id">
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors text-sm" :class="selectedIds.includes(doc.id) ? 'border-gold bg-gold/5' : 'border-v2-card-border hover:bg-v2-bg'">
                                <input type="checkbox" :checked="selectedIds.includes(doc.id)" @change="toggleDoc(doc.id)" class="rounded">
                                <div class="flex-1 min-w-0"><div class="truncate text-v2-text" x-text="doc.original_file_name"></div><div class="text-[11px] text-v2-text-light" x-text="doc.file_size_formatted"></div></div>
                            </label>
                        </template>
                    </div>
                    <div class="mt-2 text-[10px] text-v2-text-light">For PDF field overlay, use <a :href="'/CMCdemo/frontend/pages/bl-cases/detail.php?id=' + reqForm._caseId" class="text-gold underline" target="_blank">Case Detail</a> &rarr; Documents</div>
                </div>
            </div>
        </div>
        <div class="mrt-footer">
            <button @click="showRequestModal = false" class="mrt-btn-cancel">Cancel</button>
            <button @click="submitRequest()" :disabled="saving" class="mrt-btn-primary"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg><span x-text="saving ? 'Creating...' : 'Create Request'"></span></button>
        </div>
    </div>
</div>

<!-- Log Receipt Modal -->
<div x-show="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @keydown.escape.window="showReceiptModal = false">
    <div class="fixed inset-0 mrt-backdrop" @click="showReceiptModal = false"></div>
    <div class="mrt-modal relative w-full max-w-lg z-10" @click.stop>
        <div class="mrt-header"><div><div class="mrt-title">Log Receipt</div><div class="mrt-subtitle" x-text="receiptForm._label"></div></div><button type="button" class="mrt-close" @click="showReceiptModal = false"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
        <div class="mrt-body">
            <div class="mrt-row">
                <div><label class="mrt-label">Received Date <span class="mrt-req">*</span></label><input type="date" x-model="receiptForm.received_date" class="mrt-input"></div>
                <div><label class="mrt-label">Received Via <span class="mrt-req">*</span></label><select x-model="receiptForm.received_method" class="mrt-select"><option value="">Select...</option><option value="email">Email</option><option value="fax">Fax</option><option value="portal">Portal</option><option value="mail">Mail</option><option value="other">Other</option></select></div>
            </div>
            <div class="mb-4"><label class="mrt-label mb-2">Records Received</label>
                <div class="grid grid-cols-2 gap-2">
                    <template x-if="receiptForm._needsMr"><label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors" :class="receiptForm.has_medical_records ? 'bg-green-50 border-green-300' : 'border-v2-card-border hover:bg-v2-bg'"><input type="checkbox" x-model="receiptForm.has_medical_records" class="rounded"><span class="text-sm">Medical Records</span></label></template>
                    <template x-if="receiptForm._needsBill"><label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors" :class="receiptForm.has_billing ? 'bg-green-50 border-green-300' : 'border-v2-card-border hover:bg-v2-bg'"><input type="checkbox" x-model="receiptForm.has_billing" class="rounded"><span class="text-sm">Billing</span></label></template>
                    <template x-if="receiptForm._needsChart"><label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors" :class="receiptForm.has_chart ? 'bg-green-50 border-green-300' : 'border-v2-card-border hover:bg-v2-bg'"><input type="checkbox" x-model="receiptForm.has_chart" class="rounded"><span class="text-sm">Chart Notes</span></label></template>
                    <template x-if="receiptForm._needsImg"><label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors" :class="receiptForm.has_imaging ? 'bg-green-50 border-green-300' : 'border-v2-card-border hover:bg-v2-bg'"><input type="checkbox" x-model="receiptForm.has_imaging" class="rounded"><span class="text-sm">Imaging</span></label></template>
                    <template x-if="receiptForm._needsOp"><label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors" :class="receiptForm.has_op_report ? 'bg-green-50 border-green-300' : 'border-v2-card-border hover:bg-v2-bg'"><input type="checkbox" x-model="receiptForm.has_op_report" class="rounded"><span class="text-sm">OP Report</span></label></template>
                </div>
            </div>
            <div class="mb-4"><label class="flex items-center gap-2 px-3 py-2.5 rounded-lg border cursor-pointer transition-colors" :class="receiptForm.is_complete ? 'bg-emerald-50 border-emerald-400' : 'border-v2-card-border hover:bg-v2-bg'"><input type="checkbox" x-model="receiptForm.is_complete" class="rounded"><span class="text-sm font-semibold" :class="receiptForm.is_complete ? 'text-emerald-700' : ''">All records received (mark complete)</span></label></div>
            <template x-if="!receiptForm.is_complete"><div class="mb-4"><label class="mrt-label">What's still missing?</label><input type="text" x-model="receiptForm.incomplete_reason" class="mrt-input" placeholder="e.g., Still waiting for billing records"></div></template>
            <div><label class="mrt-label">Notes</label><textarea x-model="receiptForm.notes" rows="2" class="mrt-textarea" placeholder="Optional notes..."></textarea></div>
        </div>
        <div class="mrt-footer">
            <button @click="showReceiptModal = false" class="mrt-btn-cancel">Cancel</button>
            <button @click="submitReceipt()" :disabled="saving" class="mrt-btn-primary" style="background:#059669;"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span x-text="saving ? 'Saving...' : 'Log Receipt'"></span></button>
        </div>
    </div>
</div>

<!-- Bulk Request Modal -->
<div x-show="showBulkRequestModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @keydown.escape.window="closeBulkRequestModal()">
    <div class="fixed inset-0 mrt-backdrop" @click="closeBulkRequestModal()"></div>
    <div class="mrt-modal relative w-full max-w-4xl z-10 max-h-[90vh] flex flex-col" @click.stop>
        <div class="mrt-header"><div><div class="mrt-title">Bulk Follow-Up Request</div></div><button type="button" class="mrt-close" @click="closeBulkRequestModal()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
        <div class="mrt-body" style="overflow-y:auto;">
            <template x-if="bulkRequestProviderName"><div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg"><p class="text-sm">Creating <strong x-text="bulkRequestForm.request_type"></strong> requests for <strong x-text="bulkRequestCases.length"></strong> case(s) from <strong x-text="bulkRequestProviderName" class="text-gold"></strong></p></div></template>
            <template x-if="bulkRequestError"><div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg"><p class="text-sm text-red-600" x-text="bulkRequestError"></p></div></template>
            <div class="mb-6 grid grid-cols-2 gap-4">
                <div><label class="mrt-label">Request Date</label><input type="date" x-model="bulkRequestForm.request_date" class="mrt-input"></div>
                <div><label class="mrt-label">Request Method</label><select x-model="bulkRequestForm.request_method" class="mrt-select"><option value="email">Email</option><option value="fax">Fax</option><option value="portal">Portal</option><option value="phone">Phone</option><option value="mail">Mail</option></select></div>
                <div><label class="mrt-label">Follow-up Date</label><input type="date" x-model="bulkRequestForm.followup_date" class="mrt-input"></div>
                <div><label class="mrt-label">Request Type</label><select x-model="bulkRequestForm.request_type" class="mrt-select"><option value="follow_up">Follow-Up</option><option value="re_request">Re-Request</option><option value="initial">Initial</option></select></div>
                <div class="col-span-2" x-show="bulkTemplates.length > 0"><label class="mrt-label">Letter Template</label><select x-model="bulkRequestForm.template_id" class="mrt-select"><option value="">Default (built-in)</option><template x-for="t in bulkTemplates" :key="t.id"><option :value="t.id" x-text="t.name + (t.is_default ? ' (Default)' : '')"></option></template></select></div>
            </div>
            <div class="mb-6"><label class="mrt-label">Notes (optional)</label><textarea x-model="bulkRequestForm.notes" rows="2" class="mrt-textarea" placeholder="Additional notes..."></textarea></div>
            <div class="mb-6"><h3 class="text-sm font-semibold text-v2-text mb-3">Cases & Recipients</h3>
                <div class="border border-v2-card-border rounded-lg overflow-hidden"><table class="w-full text-sm"><thead class="bg-v2-bg"><tr><th class="px-3 py-2 text-left">Case #</th><th class="px-3 py-2 text-left">Client</th><th class="px-3 py-2 text-left">Recipient</th><th class="px-3 py-2 w-20">Action</th></tr></thead><tbody>
                    <template x-for="(caseItem, index) in bulkRequestCases" :key="caseItem.id"><tr class="border-t border-v2-bg"><td class="px-3 py-2 font-medium text-gold" x-text="caseItem.case_number"></td><td class="px-3 py-2" x-text="caseItem.client_name"></td><td class="px-3 py-2"><input type="text" x-model="caseItem.recipient" class="mrt-input" placeholder="Auto-detect"></td><td class="px-3 py-2 text-center"><button @click="removeFromBulk(index)" class="text-red-600 hover:text-red-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></td></tr></template>
                </tbody></table></div>
            </div>
        </div>
        <div class="mrt-footer" style="justify-content:space-between;">
            <button @click="closeBulkRequestModal()" class="mrt-btn-cancel">Cancel</button>
            <div class="flex gap-3">
                <button @click="previewBulkRequests()" class="mrt-btn-cancel" style="border-color:var(--gold);color:var(--gold);">Preview All</button>
                <button @click="createAndSendBulkRequests()" :disabled="bulkRequestCases.length === 0" class="mrt-btn-primary"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>Create & Send</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Preview Modal -->
<div x-show="showBulkPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @keydown.escape.window="closeBulkPreviewModal()">
    <div class="fixed inset-0 mrt-backdrop" @click="closeBulkPreviewModal()"></div>
    <div class="mrt-modal relative w-full max-w-5xl z-10 max-h-[90vh] flex flex-col" @click.stop>
        <div class="mrt-header"><div><div class="mrt-title">Preview Bulk Requests</div><div class="mrt-subtitle">Combined letter for <span x-text="bulkPreviewCaseCount"></span> case(s) to <span x-text="bulkPreviewProviderName"></span></div></div><button type="button" class="mrt-close" @click="closeBulkPreviewModal()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
        <div class="mrt-body" style="flex:1;overflow-y:auto;"><div x-html="bulkPreviewHtml"></div></div>
        <div class="mrt-footer" style="justify-content:space-between;"><button @click="closeBulkPreviewModal()" class="mrt-btn-cancel">Close</button><button @click="confirmAndSendBulk()" class="mrt-btn-send"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>Send All</button></div>
    </div>
</div>

<!-- Preview & Send Modal -->
<div x-show="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @keydown.escape.window="showPreviewModal && closePreviewModal()">
    <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="closePreviewModal()"></div>
    <div class="psm relative z-10" @click.stop>
        <div class="psm-header">
            <div><h3 x-text="isEditingLetter ? 'Edit Request Letter' : 'Preview Request Letter'"></h3><div class="psm-subtitle">Sending via <span style="font-weight:600;" x-text="previewData.method === 'email' ? 'Email' : 'Fax'"></span> to <span style="font-weight:600;" x-text="previewData.provider_name"></span></div></div>
            <div class="psm-header-actions">
                <button @click="toggleLetterEdit()" class="psm-edit-btn" :class="isEditingLetter ? 'active' : ''"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg><span x-text="isEditingLetter ? 'Editing' : 'Edit Letter'"></span></button>
                <button class="psm-close" @click="closePreviewModal()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        </div>
        <div class="psm-toolbar"><div style="display:flex;gap:12px;"><div style="flex:1;"><label class="psm-label" x-text="previewData.method === 'email' ? 'Recipient Email' : 'Recipient Fax Number'"></label><input type="text" x-model="previewData.recipient" class="psm-input" :placeholder="previewData.method === 'email' ? 'provider@example.com' : '(212) 555-1234'"></div><div style="flex:1;" x-show="previewData.method === 'email'"><label class="psm-label">Subject</label><input type="text" x-model="previewData.subject" :readonly="!isEditingLetter" class="psm-input"></div></div></div>
        <div class="psm-content"><div class="psm-iframe-wrap" :class="isEditingLetter ? 'editing' : ''"><iframe x-ref="letterIframe" :srcdoc="previewData.letter_html"></iframe></div></div>
        <div class="psm-footer">
            <div class="psm-footer-info">
                <template x-if="previewData.send_status === 'failed'"><span style="color:#dc2626;">Previous attempt failed. You can retry.</span></template>
                <template x-if="isEditingLetter && originalLetterHtml"><button @click="resetLetterToOriginal()" class="psm-reset-btn">Reset to Original</button></template>
                <template x-if="originalLetterHtml && !isEditingLetter"><span class="psm-modified"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/></svg>Letter has been modified</span></template>
            </div>
            <div style="display:flex;gap:10px;">
                <button @click="closePreviewModal()" class="psm-btn-cancel">Cancel</button>
                <button @click="confirmAndSend()" :disabled="sending || !previewData.recipient" class="psm-btn-send"><template x-if="sending"><div class="spinner" style="width:15px;height:15px;border-width:2px;"></div></template><span x-text="sending ? 'Sending...' : (previewData.method === 'email' ? 'Send Email' : 'Send Fax')"></span></button>
            </div>
        </div>
    </div>
</div>
