    <style>
    /* ── Workflow Modals (shared) ── */
    .wfm { width: 460px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
    .wfm-header { padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; }
    .wfm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; }
    .wfm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
    .wfm-close:hover { color: rgba(255,255,255,.75); }
    .wfm-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
    .wfm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
    .wfm-label .wfm-hint { font-weight: 400; text-transform: none; }
    .wfm-input, .wfm-select {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
    }
    .wfm-input:focus, .wfm-select:focus {
        border-color: var(--gold, #C9A84C); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .wfm-select {
        appearance: none; cursor: pointer; padding-right: 30px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 10px center;
    }
    .wfm-textarea {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
        resize: vertical; min-height: 70px; line-height: 1.5;
    }
    .wfm-textarea:focus {
        border-color: var(--gold, #C9A84C); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .wfm-textarea::placeholder { color: #c5c5c5; }
    .wfm-status-bar {
        display: flex; align-items: center; gap: 12px; padding: 12px 16px;
        background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 8px;
    }
    .wfm-status-bar .wfm-from { font-size: 13px; font-weight: 600; color: var(--muted, #8a8a82); }
    .wfm-status-bar .wfm-to { font-size: 13px; font-weight: 700; }
    .wfm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
    .wfm-btn-cancel {
        background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
    }
    .wfm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
    .wfm-btn-submit {
        border: none; border-radius: 7px; padding: 9px 22px; font-size: 13px; font-weight: 700;
        cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all .15s; color: #fff;
    }
    .wfm-btn-submit:hover { filter: brightness(1.08); }
    .wfm-btn-submit:disabled { opacity: .55; cursor: not-allowed; }
    </style>

    <!-- Move Forward Modal -->
    <div x-show="showMoveForwardModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;" @keydown.escape.window="showMoveForwardModal && (showMoveForwardModal = false)">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showMoveForwardModal = false"></div>
        <form @submit.prevent="submitMoveForward()" class="wfm relative z-10" @click.stop>
            <div class="wfm-header" style="background:var(--gold, #C9A84C);">
                <h3 style="color:#0F1B2D;">Move Case Forward</h3>
                <button type="button" class="wfm-close" style="color:rgba(15,27,45,.4);" @click="showMoveForwardModal = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="wfm-body">
                <div class="wfm-status-bar">
                    <span class="wfm-from" x-text="caseData ? getStatusLabel(caseData.status) : ''"></span>
                    <svg width="18" height="18" fill="none" stroke="var(--gold, #C9A84C)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    <span class="wfm-to" style="color:var(--gold, #C9A84C);" x-text="moveForwardForm.target_status ? getStatusLabel(moveForwardForm.target_status) : ''"></span>
                </div>
                <div>
                    <label class="wfm-label">Note <span style="color:var(--gold);">*</span> <span class="wfm-hint">(min 5 chars)</span></label>
                    <textarea x-model="moveForwardForm.note" required rows="3" minlength="5" placeholder="Describe why this case is being moved forward..." class="wfm-textarea"></textarea>
                </div>
            </div>
            <div class="wfm-footer">
                <button type="button" @click="showMoveForwardModal = false" class="wfm-btn-cancel">Cancel</button>
                <button type="submit" :disabled="saving || moveForwardForm.note.trim().length < 5" class="wfm-btn-submit"
                    style="background:var(--gold, #C9A84C); color:#0F1B2D; box-shadow:0 2px 8px rgba(201,168,76,.35);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    Confirm & Move Forward
                </button>
            </div>
        </form>
    </div>

    <!-- Send Back Modal -->
    <div x-show="showSendBackModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;" @keydown.escape.window="showSendBackModal && (showSendBackModal = false)">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showSendBackModal = false"></div>
        <form @submit.prevent="submitSendBack()" class="wfm relative z-10" @click.stop>
            <div class="wfm-header" style="background:#ea580c;">
                <h3>Send Case Back</h3>
                <button type="button" class="wfm-close" @click="showSendBackModal = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="wfm-body">
                <div>
                    <label class="wfm-label">Send Back To <span style="color:var(--gold);">*</span></label>
                    <select x-model="sendBackForm.target_status" required class="wfm-select">
                        <option value="">Select status...</option>
                        <template x-for="s in (caseData && BACKWARD_TRANSITIONS[caseData.status] || [])" :key="s">
                            <option :value="s" x-text="getStatusLabel(s)"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="wfm-label">Reason <span style="color:var(--gold);">*</span></label>
                    <textarea x-model="sendBackForm.reason" required rows="3" placeholder="Explain why this case needs to be sent back..." class="wfm-textarea"></textarea>
                </div>
            </div>
            <div class="wfm-footer">
                <button type="button" @click="showSendBackModal = false" class="wfm-btn-cancel">Cancel</button>
                <button type="submit" :disabled="saving" class="wfm-btn-submit" style="background:#ea580c; box-shadow:0 2px 8px rgba(234,88,12,.3);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    Send Back
                </button>
            </div>
        </form>
    </div>
