    <style>
    /* ── Log Request Modal ── */
    .lrm { width: 580px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
    .lrm-header { background: #0F1B2D; padding: 18px 24px 16px; display: flex; align-items: flex-start; justify-content: space-between; }
    .lrm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; line-height: 1.3; }
    .lrm-header .lrm-subtitle { font-size: 12px; font-weight: 500; color: var(--gold, #C9A84C); margin-top: 2px; }
    .lrm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; margin-top: 2px; }
    .lrm-close:hover { color: rgba(255,255,255,.75); }
    .lrm-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; max-height: 70vh; overflow-y: auto; }
    .lrm-body::-webkit-scrollbar { width: 4px; }
    .lrm-body::-webkit-scrollbar-track { background: transparent; }
    .lrm-body::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
    .lrm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
    .lrm-req { color: var(--gold, #C9A84C); }
    .lrm-input, .lrm-select {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
    }
    .lrm-input:focus, .lrm-select:focus {
        border-color: var(--gold, #C9A84C); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .lrm-input::placeholder { color: #c5c5c5; }
    .lrm-input.lrm-date { font-family: 'IBM Plex Mono', monospace; font-size: 12.5px; }
    .lrm-select {
        appearance: none; cursor: pointer; padding-right: 30px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 10px center;
    }
    .lrm-textarea {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
        resize: vertical; min-height: 70px; line-height: 1.5;
    }
    .lrm-textarea:focus {
        border-color: var(--gold, #C9A84C); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .lrm-textarea::placeholder { color: #c5c5c5; }
    .lrm-section { display: flex; align-items: center; gap: 10px; margin: 0; }
    .lrm-section::before, .lrm-section::after { content: ''; flex: 1; height: 1px; background: var(--border, #d0cdc5); }
    .lrm-section span { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .1em; white-space: nowrap; }
    .lrm-hint { font-size: 10.5px; color: var(--muted, #8a8a82); margin-top: 4px; }
    .lrm-template-card {
        border: 1.5px solid var(--border, #d0cdc5); border-radius: 8px; padding: 14px 16px;
        background: #fafafa; display: flex; flex-direction: column; gap: 8px;
    }
    .lrm-template-card-header { display: flex; align-items: center; justify-content: space-between; }
    .lrm-template-card-header .lrm-preview-btn {
        font-size: 11px; font-weight: 700; color: var(--gold, #C9A84C); background: none; border: none;
        cursor: pointer; transition: opacity .15s;
    }
    .lrm-template-card-header .lrm-preview-btn:disabled { opacity: .4; cursor: not-allowed; }
    .lrm-template-card-header .lrm-preview-btn:not(:disabled):hover { opacity: .7; }
    .lrm-accordion { border: 1.5px solid var(--border, #d0cdc5); border-radius: 8px; overflow: hidden; }
    .lrm-accordion-toggle {
        width: 100%; display: flex; align-items: center; justify-content: space-between;
        background: #fafafa; border: none; padding: 10px 14px; cursor: pointer; transition: background .1s;
    }
    .lrm-accordion-toggle:hover { background: #f5f4f0; }
    .lrm-accordion-toggle .lrm-badge {
        font-size: 10px; font-weight: 700; color: var(--gold, #C9A84C);
        background: rgba(201,168,76,.12); padding: 1px 8px; border-radius: 9999px;
    }
    .lrm-accordion-chevron { width: 16px; height: 16px; transition: transform .2s; color: #8a8a82; }
    .lrm-accordion-chevron.open { transform: rotate(180deg); }
    .lrm-accordion-body { padding: 12px 14px; border-top: 1px solid var(--border, #d0cdc5); }
    .lrm-attach-card {
        display: flex; align-items: center; gap: 8px; cursor: pointer;
        padding: 8px 10px; border-radius: 6px; transition: background .1s;
    }
    .lrm-attach-card:hover { background: rgba(201,168,76,.04); }
    .lrm-attach-card.selected { background: rgba(201,168,76,.08); }
    .lrm-attach-card input[type="checkbox"] { accent-color: var(--gold, #C9A84C); width: 14px; height: 14px; cursor: pointer; }
    .lrm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
    .lrm-btn-cancel {
        background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
    }
    .lrm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
    .lrm-btn-submit {
        background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px;
        padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
        box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s;
    }
    .lrm-btn-submit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
    .lrm-btn-submit:disabled { opacity: .55; cursor: not-allowed; }
    </style>

    <!-- Request Modal -->
    <div x-show="showRequestModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;" @keydown.escape.window="showRequestModal && (showRequestModal = false)">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showRequestModal = false"></div>
        <form @submit.prevent="submitRequest()" class="lrm relative z-10" @click.stop>

            <!-- Header -->
            <div class="lrm-header">
                <div>
                    <h3>Log Record Request</h3>
                    <div class="lrm-subtitle" x-text="currentProvider?.provider_name"></div>
                </div>
                <button type="button" class="lrm-close" @click="showRequestModal = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="lrm-body">

                <!-- Department Contact -->
                <template x-if="currentProvider?.contacts?.length > 0">
                    <div>
                        <label class="lrm-label">Department</label>
                        <select x-model="newRequest.contact_id" @change="selectContact(newRequest.contact_id)" class="lrm-select">
                            <option value="">— Manual (use fields below) —</option>
                            <template x-for="c in currentProvider.contacts" :key="c.id">
                                <option :value="c.id" x-text="c.department + ' (' + c.contact_type + ': ' + c.contact_value + ')'"></option>
                            </template>
                        </select>
                    </div>
                </template>

                <!-- Date + Method + Follow-up -->
                <div class="lrm-section"><span>Schedule</span></div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                    <div>
                        <label class="lrm-label">Request Date <span class="lrm-req">*</span></label>
                        <input type="date" x-model="newRequest.request_date" required class="lrm-input lrm-date">
                    </div>
                    <div>
                        <label class="lrm-label">Method <span class="lrm-req">*</span></label>
                        <select x-model="newRequest.request_method" required @change="newRequest.contact_id = ''; updateSentToByMethod()" class="lrm-select">
                            <option value="email">Email</option>
                            <option value="fax">Fax</option>
                            <option value="portal">Portal</option>
                            <option value="phone">Phone</option>
                            <option value="mail">Mail</option>
                        </select>
                    </div>
                    <div>
                        <label class="lrm-label">Follow-up Date</label>
                        <input type="date" x-model="newRequest.next_followup_date" class="lrm-input lrm-date">
                        <div class="lrm-hint">Defaults to 7 days</div>
                    </div>
                </div>

                <!-- Type + Sent To -->
                <div class="lrm-section"><span>Details</span></div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;"
                    x-effect="$dispatch('auto-select-template', { type: newRequest.request_type })">
                    <div>
                        <label class="lrm-label">Type</label>
                        <select x-model="newRequest.request_type" class="lrm-select">
                            <option value="initial">Initial Request</option>
                            <option value="follow_up">Follow-Up</option>
                            <option value="re_request">Re-Request</option>
                            <option value="rfd">RFD</option>
                        </select>
                    </div>
                    <div>
                        <label class="lrm-label">Sent To</label>
                        <input type="text" x-model="newRequest.sent_to" class="lrm-input" placeholder="Email or fax number">
                    </div>
                </div>

                <!-- Template Selector -->
                <div x-data="templateSelector('medical_records')"
                    x-init="init(); $watch('selectedTemplateId', val => newRequest.template_id = val)"
                    @template-selected="newRequest.template_id = $event.detail.templateId"
                    @auto-select-template.window="
                        if ($event.detail.type === 'follow_up') {
                            const followUpTemplate = templates.find(t => t.name.toLowerCase().includes('follow-up'));
                            if (followUpTemplate) { selectedTemplateId = followUpTemplate.id; selectTemplate(followUpTemplate.id); }
                        } else if ($event.detail.type === 'initial') {
                            const defaultTemplate = templates.find(t => t.is_default === 1);
                            if (defaultTemplate) { selectedTemplateId = defaultTemplate.id; selectTemplate(defaultTemplate.id); }
                        }
                    ">
                    <div class="lrm-template-card">
                        <div class="lrm-template-card-header">
                            <label class="lrm-label" style="margin-bottom:0">Letter Template</label>
                            <button type="button" @click="previewSelectedTemplate()" :disabled="!selectedTemplateId" class="lrm-preview-btn">Preview</button>
                        </div>
                        <select x-model="selectedTemplateId" @change="selectTemplate($event.target.value)" class="lrm-select">
                            <option value="">Select template...</option>
                            <template x-for="template in templates" :key="template.id">
                                <option :value="template.id" x-text="template.name + (template.is_default ? ' (Default)' : '')"></option>
                            </template>
                        </select>
                        <div class="lrm-hint" x-show="selectedTemplate" x-text="selectedTemplate?.description"></div>
                    </div>

                    <!-- Template Preview Modal (nested) -->
                    <div x-show="showPreview" class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display:none;">
                        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="closePreview()"></div>
                        <div class="lrm relative z-10" style="width:700px;" @click.stop>
                            <div class="lrm-header">
                                <h3>Template Preview</h3>
                                <button type="button" class="lrm-close" @click="closePreview()">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div style="padding:24px; overflow-y:auto; max-height:calc(90vh - 8rem);">
                                <div class="prose max-w-none" x-html="previewHtml"></div>
                            </div>
                            <div class="lrm-footer">
                                <button type="button" @click="closePreview()" class="lrm-btn-cancel">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Accordion -->
                <div x-data="{ notesOpen: !!(newRequest.notes && newRequest.notes.trim()) }">
                    <div class="lrm-accordion">
                        <button type="button" class="lrm-accordion-toggle" @click="notesOpen = !notesOpen">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <label class="lrm-label" style="margin-bottom:0; pointer-events:none;">Notes</label>
                                <span x-show="newRequest.notes && newRequest.notes.trim().length > 0" class="lrm-badge">Added</span>
                            </div>
                            <svg class="lrm-accordion-chevron" :class="notesOpen ? 'open' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="notesOpen" x-collapse class="lrm-accordion-body">
                            <textarea x-model="newRequest.notes" rows="3" class="lrm-textarea" placeholder="Add notes about this request..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Document Attachments Accordion (email only) -->
                <div x-show="newRequest.request_method === 'email'"
                    x-data="{...documentSelector(caseId, currentProvider?.id), attachOpen: false}"
                    x-effect="if (selectedDocumentIds.length > 0) attachOpen = true"
                    x-init="init(); $watch('selectedDocumentIds', val => newRequest.document_ids = val); $watch('showRequestModal', val => { if (val) loadDocuments() })"
                    @documents-selected="newRequest.document_ids = $event.detail.documentIds"
                    @document-uploaded.window="loadDocuments()"
                    @document-generated.window="loadDocuments()"
                    @document-deleted.window="loadDocuments()">
                    <div class="lrm-accordion">
                        <button type="button" class="lrm-accordion-toggle" @click="attachOpen = !attachOpen">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <label class="lrm-label" style="margin-bottom:0; pointer-events:none;">Attachments</label>
                                <span x-show="selectedDocumentIds.length > 0" class="lrm-badge" x-text="selectedDocumentIds.length + ' selected'"></span>
                            </div>
                            <svg class="lrm-accordion-chevron" :class="attachOpen ? 'open' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="attachOpen" x-collapse class="lrm-accordion-body">
                            <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px; margin-bottom:10px;">
                                <button type="button" @click="selectAll()" :disabled="documents.length === 0"
                                    style="font-size:11px; font-weight:700; color:var(--gold); background:none; border:none; cursor:pointer;"
                                    :style="documents.length === 0 ? 'opacity:.4;cursor:not-allowed' : ''">Select All</button>
                                <span style="color:var(--muted);">|</span>
                                <button type="button" @click="deselectAll()" :disabled="selectedDocumentIds.length === 0"
                                    style="font-size:11px; font-weight:700; color:var(--gold); background:none; border:none; cursor:pointer;"
                                    :style="selectedDocumentIds.length === 0 ? 'opacity:.4;cursor:not-allowed' : ''">Clear</button>
                            </div>

                            <template x-if="loading">
                                <div style="text-align:center; padding:12px;">
                                    <div class="spinner" style="display:inline-block;"></div>
                                </div>
                            </template>

                            <template x-if="!loading && documents.length === 0">
                                <p class="lrm-hint" style="text-align:center; padding:12px;">No documents available.</p>
                            </template>

                            <template x-if="!loading && documents.length > 0">
                                <div style="display:flex; flex-direction:column; gap:4px;">
                                    <template x-for="doc in documents" :key="doc.id">
                                        <label class="lrm-attach-card" :class="isSelected(doc.id) ? 'selected' : ''">
                                            <input type="checkbox" :value="doc.id" @change="toggleDocument(doc.id)" :checked="isSelected(doc.id)">
                                            <div style="flex:1; min-width:0;">
                                                <div style="font-size:13px; font-weight:500; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="doc.original_file_name"></div>
                                                <div style="font-size:11px; color:var(--muted); margin-top:2px;" x-text="doc.file_size_formatted"></div>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="lrm-footer">
                <button type="button" @click="showRequestModal = false" class="lrm-btn-cancel">Cancel</button>
                <button type="submit" :disabled="saving" class="lrm-btn-submit">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Log Request
                </button>
            </div>
        </form>
    </div>
