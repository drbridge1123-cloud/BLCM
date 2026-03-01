    <style>
    /* ── Preview & Send Modal ── */
    .psm { width: 800px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; max-height: 90vh; display: flex; flex-direction: column; }
    .psm-header { background: #0F1B2D; padding: 18px 24px 16px; display: flex; align-items: flex-start; justify-content: space-between; flex-shrink: 0; }
    .psm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; line-height: 1.3; }
    .psm-header .psm-subtitle { font-size: 12px; font-weight: 500; color: var(--gold, #C9A84C); margin-top: 2px; }
    .psm-header-actions { display: flex; align-items: center; gap: 10px; }
    .psm-edit-btn {
        padding: 5px 12px; font-size: 12px; font-weight: 600; border-radius: 6px;
        border: 1.5px solid rgba(255,255,255,.2); background: none; color: rgba(255,255,255,.6);
        cursor: pointer; display: flex; align-items: center; gap: 5px; transition: all .15s;
    }
    .psm-edit-btn:hover { color: #fff; background: rgba(255,255,255,.1); }
    .psm-edit-btn.active { color: #fff; background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.3); }
    .psm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
    .psm-close:hover { color: rgba(255,255,255,.75); }
    .psm-toolbar { padding: 12px 24px; border-bottom: 1px solid var(--border, #d0cdc5); background: #fafafa; flex-shrink: 0; }
    .psm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
    .psm-input {
        width: 100%; background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
    }
    .psm-input:focus {
        border-color: var(--gold, #C9A84C); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .psm-input::placeholder { color: #c5c5c5; }
    .psm-input[readonly] { background: #f5f5f0; color: var(--muted, #8a8a82); cursor: default; }
    .psm-content { flex: 1; overflow-y: auto; padding: 16px 24px; }
    .psm-iframe-wrap {
        border: 1.5px solid var(--border, #d0cdc5); border-radius: 8px; background: #fff;
        box-shadow: inset 0 1px 3px rgba(0,0,0,.05); overflow: hidden; transition: border-color .2s;
    }
    .psm-iframe-wrap.editing { border-color: var(--gold, #C9A84C); box-shadow: inset 0 1px 3px rgba(0,0,0,.05), 0 0 0 3px rgba(201,168,76,.1); }
    .psm-iframe-wrap iframe { width: 100%; border: 0; min-height: 600px; }
    .psm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
    .psm-footer-info { font-size: 12px; color: var(--muted, #8a8a82); display: flex; align-items: center; gap: 10px; }
    .psm-footer-info .psm-reset-btn { text-decoration: underline; color: var(--muted, #8a8a82); background: none; border: none; cursor: pointer; font-size: 12px; }
    .psm-footer-info .psm-modified { display: inline-flex; align-items: center; gap: 4px; color: #d97706; font-size: 11px; font-weight: 500; }
    .psm-btn-cancel {
        background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
    }
    .psm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
    .psm-btn-send {
        background: #1a9e6a; color: #fff; border: none; border-radius: 7px;
        padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
        box-shadow: 0 2px 8px rgba(26,158,106,.3); display: flex; align-items: center; gap: 6px; transition: all .15s;
    }
    .psm-btn-send:hover { filter: brightness(1.08); box-shadow: 0 4px 12px rgba(26,158,106,.4); }
    .psm-btn-send:disabled { opacity: .55; cursor: not-allowed; }
    </style>

    <!-- Preview & Send Modal -->
    <div x-show="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;" @keydown.escape.window="showPreviewModal && closePreviewModal()">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="closePreviewModal()"></div>
        <div class="psm relative z-10" @click.stop>

            <!-- Header -->
            <div class="psm-header">
                <div>
                    <h3 x-text="isEditingLetter ? 'Edit Request Letter' : 'Preview Request Letter'"></h3>
                    <div class="psm-subtitle">
                        Sending via <span style="font-weight:600;" x-text="previewData.method === 'email' ? 'Email' : 'Fax'"></span>
                        to <span style="font-weight:600;" x-text="previewData.provider_name"></span>
                    </div>
                </div>
                <div class="psm-header-actions">
                    <button @click="toggleLetterEdit()" class="psm-edit-btn" :class="isEditingLetter ? 'active' : ''">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span x-text="isEditingLetter ? 'Editing' : 'Edit Letter'"></span>
                    </button>
                    <button class="psm-close" @click="closePreviewModal()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Toolbar (Recipient / Subject) -->
            <div class="psm-toolbar">
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="psm-label" x-text="previewData.method === 'email' ? 'Recipient Email' : 'Recipient Fax Number'"></label>
                        <input type="text" x-model="previewData.recipient" class="psm-input"
                            :placeholder="previewData.method === 'email' ? 'provider@example.com' : '(212) 555-1234'">
                    </div>
                    <div style="flex:1;" x-show="previewData.method === 'email'">
                        <label class="psm-label">Subject</label>
                        <input type="text" x-model="previewData.subject" :readonly="!isEditingLetter" class="psm-input">
                    </div>
                </div>
            </div>

            <!-- Letter Content -->
            <div class="psm-content">
                <div class="psm-iframe-wrap" :class="isEditingLetter ? 'editing' : ''">
                    <iframe x-ref="letterIframe" :srcdoc="previewData.letter_html"></iframe>
                </div>
            </div>

            <!-- Footer -->
            <div class="psm-footer">
                <div class="psm-footer-info">
                    <template x-if="previewData.send_status === 'failed'">
                        <span style="color:#dc2626;">Previous attempt failed. You can retry.</span>
                    </template>
                    <template x-if="isEditingLetter && originalLetterHtml">
                        <button @click="resetLetterToOriginal()" class="psm-reset-btn">Reset to Original</button>
                    </template>
                    <template x-if="originalLetterHtml && !isEditingLetter">
                        <span class="psm-modified">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/></svg>
                            Letter has been modified
                        </span>
                    </template>
                </div>
                <div style="display:flex; gap:10px;">
                    <button @click="closePreviewModal()" class="psm-btn-cancel">Cancel</button>
                    <button @click="confirmAndSend()" :disabled="sending || !previewData.recipient" class="psm-btn-send">
                        <template x-if="sending">
                            <div class="spinner" style="width:15px;height:15px;border-width:2px;"></div>
                        </template>
                        <span x-text="sending ? 'Sending...' : (previewData.method === 'email' ? 'Send Email' : 'Send Fax')"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
