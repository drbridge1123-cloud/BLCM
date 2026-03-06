<!-- Add/Edit Item Modal -->
<div x-show="showAddModal || showEditModal" class="sp-modal-overlay" style="display:none;" @keydown.escape.window="closeModals()" @click="closeModals()">
    <div class="sp-modal-box" style="max-width:672px" @click.stop>
        <div class="sp-modal-header">
            <div class="sp-modal-title" x-text="showEditModal ? 'Edit Item' : 'Add Item'"></div>
            <button type="button" class="sp-modal-close" @click="closeModals()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="sp-modal-body" style="max-height:calc(90vh - 140px);overflow-y:auto;">
            <template x-if="showAddModal">
                <div>
                    <label class="sp-form-label">Search Case</label>
                    <div class="relative">
                        <input type="text" x-model="caseSearch" @input.debounce.300ms="searchCases()" @focus="showCaseDropdown = caseResults.length > 0" @click.away="showCaseDropdown = false" placeholder="Type client name or case #..." class="sp-search">
                        <template x-if="form.case_number && form.client_name">
                            <div class="mt-2 px-3 py-2 bg-gold/10 border border-gold/30 rounded-lg flex items-center justify-between">
                                <span class="text-sm"><span class="font-medium" x-text="form.client_name"></span> <span class="text-v2-text-light" x-text="'#' + form.case_number"></span></span>
                                <button @click="clearCaseSelection()" class="text-xs text-red-500 hover:text-red-700">Clear</button>
                            </div>
                        </template>
                        <template x-if="showCaseDropdown && caseResults.length > 0">
                            <div class="absolute z-10 w-full mt-1 bg-white border border-v2-card-border rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="c in caseResults" :key="c.id">
                                    <div @click="selectCase(c)" class="px-3 py-2 hover:bg-gold/10 cursor-pointer text-sm border-b border-v2-card-border last:border-0">
                                        <span class="font-medium" x-text="c.client_name"></span><span class="text-v2-text-light ml-2" x-text="'#' + c.case_number"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            <template x-if="showEditModal">
                <div class="mrt-row">
                    <div><label class="sp-form-label">Client Name <span class="mrt-req">*</span></label><input type="text" x-model="form.client_name" class="sp-search"></div>
                    <div><label class="sp-form-label">Case #</label><input type="text" x-model="form.case_number" class="sp-search" style="background:var(--bg);" readonly></div>
                </div>
            </template>
            <div class="mrt-row">
                <div class="relative">
                    <label class="sp-form-label">Insurance Carrier <span class="mrt-req">*</span></label>
                    <input type="text" x-model="form.insurance_carrier" @input.debounce.300ms="searchCarriers()" @focus="if(form.insurance_carrier.length >= 2) searchCarriers()" @click.away="showCarrierDropdown = false" autocomplete="off" class="sp-search" placeholder="Type to search...">
                    <template x-if="showCarrierDropdown && carrierResults.length > 0">
                        <div class="absolute z-10 w-full mt-1 bg-white border border-v2-card-border rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="c in carrierResults" :key="c.id">
                                <div @click="selectCarrier(c)" class="px-3 py-2 hover:bg-gold/10 cursor-pointer text-sm border-b border-v2-card-border last:border-0">
                                    <span class="font-medium" x-text="c.name"></span><span class="text-xs text-v2-text-light ml-2" x-text="[c.email, formatPhoneNumber(c.fax)].filter(Boolean).join(' | ') || ''"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                <div><label class="sp-form-label">Assigned To</label><select x-model="form.assigned_to" class="sp-select"><option value="">Select...</option><template x-for="s in staffList" :key="s.id"><option :value="s.id" x-text="s.display_name || s.full_name"></option></template></select></div>
            </div>
            <div class="mrt-row">
                <div><label class="sp-form-label">Carrier Email</label><input type="email" x-model="form.carrier_contact_email" class="sp-search" placeholder="claims@carrier.com"></div>
                <div><label class="sp-form-label">Carrier Fax</label><input type="text" x-model="form.carrier_contact_fax" @blur="autoFormatPhone($el)" class="sp-search" placeholder="(xxx) xxx-xxxx"></div>
            </div>
            <div class="mrt-row">
                <div><label class="sp-form-label">Claim Number</label><input type="text" x-model="form.claim_number" class="sp-search" placeholder="e.g., 123456789"></div>
                <div><label class="sp-form-label">Member ID</label><input type="text" x-model="form.member_id" class="sp-search" placeholder="e.g., UZ065914201"></div>
            </div>
            <div><label class="sp-form-label">Note</label><textarea x-model="form.note" rows="2" class="sp-search" style="resize:vertical;min-height:70px;line-height:1.5"></textarea></div>
        </div>
        <div class="sp-modal-footer">
            <button @click="closeModals()" class="sp-btn">Cancel</button>
            <button @click="saveItem()" :disabled="saving" class="sp-new-btn"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span x-text="saving ? 'Saving...' : (showEditModal ? 'Update' : 'Create')"></span></button>
        </div>
    </div>
</div>

<!-- Health Request Modal -->
<div x-show="showRequestModal" class="sp-modal-overlay" style="display:none;" @keydown.escape.window="showRequestModal = false" @click="showRequestModal = false">
    <div class="sp-modal-box" style="max-width:512px" @click.stop>
        <div class="sp-modal-header"><div><div class="sp-modal-title">New Request</div><div class="mrt-subtitle" x-text="reqForm._carrierLabel"></div></div><button type="button" class="sp-modal-close" @click="showRequestModal = false"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
        <div class="sp-modal-body">
            <div class="mrt-row">
                <div><label class="sp-form-label">Request Date <span class="mrt-req">*</span></label><input type="date" x-model="reqForm.request_date" class="sp-search"></div>
                <div><label class="sp-form-label">Method <span class="mrt-req">*</span></label><select x-model="reqForm.request_method" @change="updateRecipient()" class="sp-select"><option value="">Select...</option><option value="email">Email</option><option value="fax">Fax</option><option value="portal">Portal</option><option value="phone">Phone</option><option value="mail">Mail</option></select></div>
            </div>
            <div class="mrt-row">
                <div><label class="sp-form-label">Type</label><select x-model="reqForm.request_type" class="sp-select"><option value="initial">Initial</option><option value="follow_up">Follow Up</option><option value="re_request">Re-Request</option></select></div>
                <div><label class="sp-form-label">Send To</label><input type="text" x-model="reqForm.sent_to" class="sp-search" placeholder="Email or fax #"></div>
            </div>
            <div><label class="sp-form-label">Template</label><select x-model="reqForm.template_id" @change="onTemplateChange()" class="sp-select"><option value="">Default (no template)</option><template x-for="t in hlTemplates" :key="t.id"><option :value="t.id" x-text="t.name + (t.is_default ? ' (Default)' : '')"></option></template></select></div>
            <template x-if="reqForm._showSettlement">
                <div class="mt-3 p-4 bg-amber-50 border border-amber-200 rounded-lg space-y-3">
                    <p class="text-xs font-semibold text-amber-700 uppercase">Settlement Information</p>
                    <div class="mrt-row">
                        <div><label class="sp-form-label">Settlement Amount</label><input type="number" step="0.01" x-model="reqForm.template_data.settlement_amount" class="sp-search" placeholder="$0.00"></div>
                        <div><label class="sp-form-label">Settlement Date</label><input type="date" x-model="reqForm.template_data.settlement_date" class="sp-search"></div>
                    </div>
                    <div class="mrt-row">
                        <div><label class="sp-form-label">Attorney's Fees</label><input type="number" step="0.01" x-model="reqForm.template_data.attorney_fees" class="sp-search" placeholder="$0.00"></div>
                        <div><label class="sp-form-label">Costs</label><input type="number" step="0.01" x-model="reqForm.template_data.costs" class="sp-search" placeholder="$0.00"></div>
                    </div>
                </div>
            </template>
            <div><label class="sp-form-label">Notes</label><textarea x-model="reqForm.notes" rows="2" class="sp-search" style="resize:vertical;min-height:70px;line-height:1.5"></textarea></div>
        </div>
        <div class="sp-modal-footer">
            <button @click="showRequestModal = false" class="sp-btn">Cancel</button>
            <button @click="submitRequest()" :disabled="saving" class="sp-new-btn"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg><span x-text="saving ? 'Creating...' : 'Create Request'"></span></button>
        </div>
    </div>
</div>

<!-- Health Preview & Send Modal -->
<div x-show="showSendModal" class="sp-modal-overlay" style="display:none;" @keydown.escape.window="showSendModal = false" @click="showSendModal = false">
    <div class="sp-modal-box" style="max-width:896px" @click.stop>
        <div class="sp-modal-header"><div><div class="sp-modal-title">Preview & Send</div><div class="mrt-subtitle" x-text="previewData.carrier + ' via ' + previewData.method"></div></div><button type="button" class="sp-modal-close" @click="showSendModal = false"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
        <div class="flex-1 overflow-auto p-6"><iframe :srcdoc="previewData.letter_html" class="w-full border rounded-lg" style="height:500px;"></iframe></div>
        <div class="sp-modal-footer">
            <div class="flex items-center gap-4 w-full">
                <label class="sp-form-label" style="margin-bottom:0;white-space:nowrap;">Recipient:</label>
                <input type="text" x-model="previewData.recipient" class="sp-search flex-1">
                <button @click="confirmAndSend()" :disabled="sending || !previewData.recipient" class="mrt-btn-send"
                        :style="previewData.method === 'email' ? 'background:#0d9488;' : 'background:#7c3aed;'" style="flex-shrink:0;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span x-text="sending ? 'Sending...' : (previewData.method === 'email' ? 'Send Email' : 'Send Fax')"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div x-show="showImportModal" class="sp-modal-overlay" style="display:none;" @keydown.escape.window="showImportModal = false; importFile = null; importResult = null;" @click="showImportModal = false; importFile = null; importResult = null;">
    <div class="sp-modal-box" style="max-width:512px" @click.stop>
        <div class="sp-modal-header"><div class="sp-modal-title">Import CSV</div><button type="button" class="sp-modal-close" @click="showImportModal = false; importFile = null; importResult = null;"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
        <div class="sp-modal-body">
            <div class="border-2 border-dashed border-v2-card-border rounded-lg p-8 text-center" :class="dragover ? 'border-gold bg-gold/5' : ''" @dragover.prevent="dragover = true" @dragleave="dragover = false" @drop.prevent="dragover = false; importFile = $event.dataTransfer.files[0]">
                <template x-if="!importFile"><div><p class="text-sm text-v2-text-mid mb-2">Drag & drop CSV, or</p><label class="px-4 py-2 text-sm bg-gold text-navy font-semibold rounded-lg cursor-pointer hover:bg-gold/90">Browse <input type="file" accept=".csv" class="hidden" @change="importFile = $event.target.files[0]"></label></div></template>
                <template x-if="importFile"><div><p class="text-sm font-medium" x-text="importFile.name"></p><button @click="importFile = null" class="text-xs text-red-500 mt-1">Remove</button></div></template>
            </div>
            <template x-if="importResult"><div class="p-3 rounded-lg bg-green-50 border border-green-200 text-sm"><p x-text="'Items: ' + importResult.items_created + ', Requests: ' + importResult.requests_created + ', Skipped: ' + importResult.skipped"></p></div></template>
        </div>
        <div class="sp-modal-footer">
            <button @click="showImportModal = false; importFile = null; importResult = null;" class="sp-btn">Close</button>
            <button @click="doImport()" :disabled="!importFile || importing" class="sp-new-btn"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg><span x-text="importing ? 'Importing...' : 'Import'"></span></button>
        </div>
    </div>
</div>
