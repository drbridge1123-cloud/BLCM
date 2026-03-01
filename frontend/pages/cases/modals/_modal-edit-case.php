    <style>
    /* ── Edit Case Modal ── */
    .ecm { width: 560px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
    .ecm-header { background: #0F1B2D; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; }
    .ecm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; }
    .ecm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
    .ecm-close:hover { color: rgba(255,255,255,.75); }
    .ecm-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; max-height: 70vh; overflow-y: auto; }
    .ecm-body::-webkit-scrollbar { width: 4px; }
    .ecm-body::-webkit-scrollbar-track { background: transparent; }
    .ecm-body::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
    .ecm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
    .ecm-req { color: var(--gold, #C9A84C); }
    .ecm-input, .ecm-select {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
    }
    .ecm-input:focus, .ecm-select:focus {
        border-color: var(--gold, #C9A84C); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .ecm-input::placeholder { color: #c5c5c5; }
    .ecm-input.ecm-mono { font-family: 'IBM Plex Mono', monospace; font-weight: 600; }
    .ecm-input.ecm-date { font-family: 'IBM Plex Mono', monospace; font-size: 12.5px; }
    .ecm-select {
        appearance: none; cursor: pointer; padding-right: 30px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 10px center;
    }
    .ecm-textarea {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
        resize: vertical; min-height: 90px; line-height: 1.5;
    }
    .ecm-textarea:focus {
        border-color: var(--gold, #C9A84C); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .ecm-textarea::placeholder { color: #c5c5c5; }
    .ecm-section { display: flex; align-items: center; gap: 10px; margin: 0; }
    .ecm-section::before, .ecm-section::after { content: ''; flex: 1; height: 1px; background: var(--border, #d0cdc5); }
    .ecm-section span { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .1em; white-space: nowrap; }
    .ecm-check-card {
        display: flex; align-items: center; gap: 9px; cursor: pointer;
        border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px; padding: 10px 13px;
        background: #fafafa; transition: all .15s;
    }
    .ecm-check-card:hover { border-color: rgba(201,168,76,.5); background: #fff; }
    .ecm-check-card.checked { border-color: var(--gold, #C9A84C); background: rgba(201,168,76,.06); }
    .ecm-check-card input[type="checkbox"] { accent-color: var(--gold, #C9A84C); width: 16px; height: 16px; cursor: pointer; flex-shrink: 0; }
    .ecm-check-card span { font-size: 12.5px; font-weight: 500; color: var(--text, #1a2535); }
    .ecm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
    .ecm-btn-cancel {
        background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
    }
    .ecm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
    .ecm-btn-submit {
        background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px;
        padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
        box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s;
    }
    .ecm-btn-submit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
    .ecm-btn-submit:disabled { opacity: .55; cursor: not-allowed; }
    </style>

    <!-- Edit Case Modal -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;" @keydown.escape.window="showEditModal && (showEditModal = false)">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showEditModal = false"></div>
        <form @submit.prevent="updateCase()" class="ecm relative z-10" @click.stop>

            <!-- Header -->
            <div class="ecm-header">
                <h3>Edit Case</h3>
                <button type="button" class="ecm-close" @click="showEditModal = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="ecm-body">

                <!-- Case Info -->
                <div class="ecm-section"><span>Case Info</span></div>
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="ecm-label">Case Number <span class="ecm-req">*</span></label>
                        <input type="text" x-model="editData.case_number" required class="ecm-input ecm-mono">
                    </div>
                    <div style="flex:1;">
                        <label class="ecm-label">Client Name <span class="ecm-req">*</span></label>
                        <input type="text" x-model="editData.client_name" required class="ecm-input">
                    </div>
                </div>
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="ecm-label">Date of Birth <span class="ecm-req">*</span></label>
                        <input type="date" x-model="editData.client_dob" required class="ecm-input ecm-date">
                    </div>
                    <div style="flex:1;">
                        <label class="ecm-label">Date of Injury <span class="ecm-req">*</span></label>
                        <input type="date" x-model="editData.doi" required class="ecm-input ecm-date">
                    </div>
                </div>

                <!-- Assignment -->
                <div class="ecm-section"><span>Assignment</span></div>
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="ecm-label">Attorney</label>
                        <input type="text" x-model="editData.attorney_name" class="ecm-input" placeholder="Select attorney...">
                    </div>
                    <div style="flex:1;">
                        <label class="ecm-label">Assigned To <span class="ecm-req">*</span></label>
                        <select x-model="editData.assigned_to" required class="ecm-select">
                            <option value="">Select...</option>
                            <template x-for="u in staffList" :key="u.id">
                                <option :value="u.id" x-text="u.full_name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Treating Completed -->
                <label class="ecm-check-card" :class="{ checked: !!parseInt(editData.ini_completed) }">
                    <input type="checkbox" :checked="!!parseInt(editData.ini_completed)"
                        @change="editData.ini_completed = $el.checked ? 1 : 0">
                    <span>Treating Completed</span>
                </label>

                <!-- Notes -->
                <div class="ecm-section"><span>Notes</span></div>
                <div>
                    <textarea x-model="editData.notes" class="ecm-textarea" placeholder="Optional notes..."></textarea>
                </div>

            </div>

            <!-- Footer -->
            <div class="ecm-footer">
                <button type="button" @click="showEditModal = false" class="ecm-btn-cancel">Cancel</button>
                <button type="submit" :disabled="saving" class="ecm-btn-submit">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Save
                </button>
            </div>
        </form>
    </div>
