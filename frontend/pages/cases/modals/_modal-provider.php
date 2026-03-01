    <style>
    /* ── Add Provider Modal ── */
    .apm { width: 540px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
    .apm-header { background: #0F1B2D; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; }
    .apm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; }
    .apm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
    .apm-close:hover { color: rgba(255,255,255,.75); }
    .apm-body { padding: 24px; display: flex; flex-direction: column; gap: 18px; max-height: 70vh; overflow-y: auto; }
    .apm-body::-webkit-scrollbar { width: 4px; }
    .apm-body::-webkit-scrollbar-track { background: transparent; }
    .apm-body::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
    .apm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
    .apm-req { color: var(--gold, #C9A84C); }
    .apm-input, .apm-select {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
    }
    .apm-input:focus, .apm-select:focus {
        border-color: var(--gold, #C9A84C); background: #fff;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .apm-input::placeholder { color: #c5c5c5; }
    .apm-input.apm-mono { font-family: 'IBM Plex Mono', monospace; font-size: 12.5px; }
    .apm-search-wrap { position: relative; }
    .apm-search-wrap .apm-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #bbb; pointer-events: none; z-index: 1; }
    .apm-search-wrap .apm-input { padding-left: 36px; }
    .apm-search-wrap .apm-selected-badge { font-size: 12.5px; font-weight: 500; color: var(--gold, #C9A84C); margin-top: 5px; display: flex; align-items: center; gap: 5px; }
    .apm-dropdown {
        position: absolute; z-index: 10; width: 100%; margin-top: 4px; background: #fff;
        border: 1.5px solid var(--border, #d0cdc5); border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,.12); max-height: 200px; overflow-y: auto;
    }
    .apm-dropdown::-webkit-scrollbar { width: 4px; }
    .apm-dropdown::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
    .apm-dropdown-item {
        width: 100%; text-align: left; background: none; border: none; padding: 9px 14px;
        font-size: 13px; color: #1a2535; cursor: pointer; display: flex; justify-content: space-between; align-items: center;
        transition: background .1s;
    }
    .apm-dropdown-item:hover { background: rgba(201,168,76,.06); }
    .apm-dropdown-item .apm-type-label { font-size: 11px; color: var(--muted, #8a8a82); }
    .apm-dropdown-create {
        width: 100%; text-align: left; background: none; border: none; padding: 9px 14px;
        font-size: 13px; font-weight: 600; color: var(--gold, #C9A84C); cursor: pointer;
        display: flex; align-items: center; gap: 6px; border-top: 1px solid var(--border, #d0cdc5);
        transition: background .1s;
    }
    .apm-dropdown-create:hover { background: rgba(201,168,76,.06); }
    .apm-section { display: flex; align-items: center; gap: 10px; margin: 0; }
    .apm-section::before, .apm-section::after { content: ''; flex: 1; height: 1px; background: var(--border, #d0cdc5); }
    .apm-section span { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .1em; white-space: nowrap; }
    .apm-type-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
    .apm-type-card {
        display: flex; align-items: center; gap: 7px; cursor: pointer;
        border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px; padding: 9px 12px;
        background: #fafafa; font-size: 12.5px; color: #3D4F63; transition: all .15s;
    }
    .apm-type-card:hover { border-color: rgba(201,168,76,.5); background: #fff; }
    .apm-type-card.checked { border-color: var(--gold, #C9A84C); background: rgba(201,168,76,.06); }
    .apm-type-card input[type="checkbox"] { accent-color: var(--gold, #C9A84C); width: 14px; height: 14px; cursor: pointer; flex-shrink: 0; }
    .apm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
    .apm-btn-cancel {
        background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
    }
    .apm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
    .apm-btn-submit {
        background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px;
        padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
        box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s;
    }
    .apm-btn-submit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
    .apm-btn-submit:disabled { opacity: .55; cursor: not-allowed; }
    </style>

    <!-- Add Provider Modal -->
    <div x-show="showAddProviderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;" @keydown.escape.window="showAddProviderModal && (showAddProviderModal = false)">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showAddProviderModal = false"></div>
        <form @submit.prevent="addProvider()" class="apm relative z-10" @click.stop>

            <!-- Header -->
            <div class="apm-header">
                <h3>Add Provider to Case</h3>
                <button type="button" class="apm-close" @click="showAddProviderModal = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="apm-body">

                <!-- Provider Search -->
                <div>
                    <label class="apm-label">Provider <span class="apm-req">*</span></label>
                    <div class="apm-search-wrap">
                        <span class="apm-search-icon">🔍</span>
                        <input type="text" x-model="providerSearch" @input.debounce.300ms="searchProviders()"
                            placeholder="Search provider..." class="apm-input">
                        <div x-show="showProviderDropdown" @click.outside="showProviderDropdown = false" class="apm-dropdown">
                            <template x-for="pr in providerResults" :key="pr.id">
                                <button type="button" @click="selectProvider(pr)" class="apm-dropdown-item">
                                    <span x-text="pr.name"></span>
                                    <span class="apm-type-label" x-text="getProviderTypeLabel(pr.type)"></span>
                                </button>
                            </template>
                            <button type="button" @click="openQuickAddProvider('provider')" class="apm-dropdown-create">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Create "<span x-text="providerSearch"></span>"
                            </button>
                        </div>
                        <p x-show="selectedProvider" class="apm-selected-badge">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="selectedProvider?.name"></span>
                        </p>
                    </div>
                </div>

                <!-- Record Types Needed -->
                <div class="apm-section"><span>Record Types Needed</span></div>
                <div class="apm-type-grid">
                    <label class="apm-type-card" :class="{ checked: newProvider.record_types.includes('medical_records') }">
                        <input type="checkbox" value="medical_records" x-model="newProvider.record_types"> Medical Records
                    </label>
                    <label class="apm-type-card" :class="{ checked: newProvider.record_types.includes('billing') }">
                        <input type="checkbox" value="billing" x-model="newProvider.record_types"> Billing
                    </label>
                    <label class="apm-type-card" :class="{ checked: newProvider.record_types.includes('chart') }">
                        <input type="checkbox" value="chart" x-model="newProvider.record_types"> Chart Notes
                    </label>
                    <label class="apm-type-card" :class="{ checked: newProvider.record_types.includes('imaging') }">
                        <input type="checkbox" value="imaging" x-model="newProvider.record_types"> Imaging
                    </label>
                    <label class="apm-type-card" :class="{ checked: newProvider.record_types.includes('op_report') }">
                        <input type="checkbox" value="op_report" x-model="newProvider.record_types"> Op Report
                    </label>
                </div>

                <!-- Deadline -->
                <div>
                    <label class="apm-label">Deadline</label>
                    <input type="date" x-model="newProvider.deadline" class="apm-input apm-mono">
                </div>

            </div>

            <!-- Footer -->
            <div class="apm-footer">
                <button type="button" @click="showAddProviderModal = false" class="apm-btn-cancel">Cancel</button>
                <button type="submit" :disabled="!selectedProvider || saving" class="apm-btn-submit">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Provider
                </button>
            </div>
        </form>
    </div>
