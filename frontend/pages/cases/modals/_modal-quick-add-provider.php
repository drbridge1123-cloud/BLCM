    <style>
    /* ── Quick Add Provider Modal ── */
    .qap { width: 500px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
    .qap-header { background: #0F1B2D; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; }
    .qap-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; }
    .qap-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
    .qap-close:hover { color: rgba(255,255,255,.75); }
    .qap-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; max-height: 70vh; overflow-y: auto; }
    .qap-body::-webkit-scrollbar { width: 4px; }
    .qap-body::-webkit-scrollbar-track { background: transparent; }
    .qap-body::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
    .qap-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
    .qap-req { color: var(--gold, #C9A84C); }
    .qap-input, .qap-select {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
    }
    .qap-input:focus, .qap-select:focus {
        border-color: var(--gold, #C9A84C); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .qap-input::placeholder { color: #c5c5c5; }
    .qap-select {
        appearance: none; cursor: pointer; padding-right: 30px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 10px center;
    }
    .qap-section { display: flex; align-items: center; gap: 10px; margin: 0; }
    .qap-section::before, .qap-section::after { content: ''; flex: 1; height: 1px; background: var(--border, #d0cdc5); }
    .qap-section span { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .1em; white-space: nowrap; }
    .qap-hint { font-size: 11px; color: var(--muted, #8a8a82); }
    .qap-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
    .qap-btn-cancel {
        background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
    }
    .qap-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
    .qap-btn-submit {
        background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px;
        padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
        box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s;
    }
    .qap-btn-submit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
    .qap-btn-submit:disabled { opacity: .55; cursor: not-allowed; }
    </style>

    <!-- Quick-Add Provider Modal -->
    <div x-show="showQuickAddProvider" class="fixed inset-0 z-[60] flex items-center justify-center p-4"
        style="display:none;" @keydown.escape.window="showQuickAddProvider && closeQuickAddProvider()">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="closeQuickAddProvider()"></div>
        <form @submit.prevent="submitQuickAddProvider()" class="qap relative z-10" @click.stop>

            <!-- Header -->
            <div class="qap-header">
                <h3>Quick Add Provider</h3>
                <button type="button" class="qap-close" @click="closeQuickAddProvider()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="qap-body">

                <!-- Name + Type -->
                <div style="display:grid; grid-template-columns:1fr 140px; gap:12px;">
                    <div>
                        <label class="qap-label">Provider Name <span class="qap-req">*</span></label>
                        <input type="text" x-model="quickAddForm.name" required class="qap-input"
                            placeholder="e.g. USC Keck Hospital" x-ref="quickAddName">
                    </div>
                    <div>
                        <label class="qap-label">Type <span class="qap-req">*</span></label>
                        <select x-model="quickAddForm.type" class="qap-select">
                            <option value="hospital">Hospital</option>
                            <option value="er">Emergency Room</option>
                            <option value="chiro">Chiropractor</option>
                            <option value="imaging">Imaging Center</option>
                            <option value="physician">Physician</option>
                            <option value="surgery_center">Surgery Center</option>
                            <option value="pharmacy">Pharmacy</option>
                            <option value="acupuncture">Acupuncture</option>
                            <option value="massage">Massage</option>
                            <option value="pain_management">Pain Mgmt</option>
                            <option value="pt">Physical Therapy</option>
                            <option value="police">Police</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Contact -->
                <div class="qap-section"><span>Contact</span></div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label class="qap-label">Phone</label>
                        <input type="text" x-model="quickAddForm.phone" class="qap-input" placeholder="(xxx) xxx-xxxx">
                    </div>
                    <div>
                        <label class="qap-label">Fax</label>
                        <input type="text" x-model="quickAddForm.fax" class="qap-input" placeholder="(xxx) xxx-xxxx">
                    </div>
                </div>
                <div>
                    <label class="qap-label">Email</label>
                    <input type="email" x-model="quickAddForm.email" class="qap-input" placeholder="records@provider.com">
                </div>

                <!-- Address -->
                <div class="qap-section"><span>Address</span></div>
                <div>
                    <label class="qap-label">Street Address</label>
                    <input type="text" x-model="quickAddForm.address" class="qap-input" placeholder="Street address">
                </div>
                <div style="display:grid; grid-template-columns:1fr 80px 90px; gap:12px;">
                    <div>
                        <label class="qap-label">City</label>
                        <input type="text" x-model="quickAddForm.city" class="qap-input">
                    </div>
                    <div>
                        <label class="qap-label">State</label>
                        <input type="text" x-model="quickAddForm.state" class="qap-input" maxlength="2" placeholder="CA" style="text-transform:uppercase;">
                    </div>
                    <div>
                        <label class="qap-label">Zip</label>
                        <input type="text" x-model="quickAddForm.zip" class="qap-input" maxlength="10" placeholder="90001">
                    </div>
                </div>

                <p class="qap-hint">You can add more details (contacts, portal URL, etc.) later from the Providers page.</p>
            </div>

            <!-- Footer -->
            <div class="qap-footer">
                <button type="button" @click="closeQuickAddProvider()" class="qap-btn-cancel">Cancel</button>
                <button type="submit" :disabled="quickAddSaving || !quickAddForm.name.trim()" class="qap-btn-submit">
                    <template x-if="quickAddSaving">
                        <span class="spinner" style="width:14px; height:14px; border-width:2px;"></span>
                    </template>
                    <svg x-show="!quickAddSaving" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Create Provider
                </button>
            </div>
        </form>
    </div>
