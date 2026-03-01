<!-- All sp- styles loaded from shared sp-design-system.css -->

<style>
.prov-row { border-left: 3px solid transparent; cursor: pointer; transition: all .1s; }
.prov-row:hover, .prov-row-active { background: rgba(201,168,76,.05) !important; border-left-color: #C9A84C; }
.db-row { border-left: 3px solid transparent; cursor: pointer; transition: all .1s; }
.db-row:hover, .db-row-active { background: rgba(201,168,76,.05) !important; border-left-color: #C9A84C; }

/* ── Edit Provider Modal ── */
.epm { width: 600px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.22); overflow: hidden; background: #fff; }
.epm-header { background: #0F1B2D; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; }
.epm-header h3 { font-size: 15px; font-weight: 600; color: #fff; margin: 0; }
.epm-close { background: none; border: none; color: rgba(255,255,255,.4); cursor: pointer; padding: 4px; transition: color .15s; }
.epm-close:hover { color: rgba(255,255,255,.8); }
.epm-body { padding: 24px; display: flex; flex-direction: column; gap: 20px; max-height: 70vh; overflow-y: auto; }
.epm-body::-webkit-scrollbar { width: 4px; }
.epm-body::-webkit-scrollbar-track { background: transparent; }
.epm-body::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
.epm-section { display: flex; align-items: center; gap: 10px; margin: 0; }
.epm-section::before, .epm-section::after { content: ''; flex: 1; height: 1px; background: var(--border, #d0cdc5); }
.epm-section span { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .1em; white-space: nowrap; }
.epm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
.epm-req { color: var(--gold, #C9A84C); }
.epm-input, .epm-select, .epm-textarea {
    width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 6px;
    padding: 8px 11px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
}
.epm-input:focus, .epm-select:focus, .epm-textarea:focus {
    border-color: var(--gold, #C9A84C); background: #fff;
    box-shadow: 0 0 0 3px rgba(201,168,76,.1);
}
.epm-select {
    appearance: none; cursor: pointer; padding-right: 30px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center;
}
.epm-textarea { resize: vertical; min-height: 80px; }
.epm-check-card {
    flex: 1; display: flex; align-items: center; gap: 8px; cursor: pointer;
    border: 1.5px solid var(--border, #d0cdc5); border-radius: 6px; padding: 9px 12px;
    background: #fafafa; font-size: 13px; color: #3D4F63; transition: border-color .15s;
}
.epm-check-card:hover { border-color: var(--gold, #C9A84C); }
.epm-check-card input[type="checkbox"] { accent-color: var(--gold, #C9A84C); width: 15px; height: 15px; cursor: pointer; }
.epm-contact-row {
    display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
    padding: 8px 10px; border-radius: 6px; background: #fafaf8; border: 1px solid var(--border, #d0cdc5);
}
.epm-contact-row .epm-input, .epm-contact-row .epm-select { background: #fff; padding: 6px 9px; font-size: 12px; }
.epm-contact-row .epm-select { padding-right: 26px; }
.epm-empty-contacts {
    border: 1.5px dashed var(--border, #d0cdc5); border-radius: 8px; padding: 16px;
    text-align: center; font-size: 12px; color: var(--muted, #8a8a82);
}
.epm-primary-btn {
    font-size: 10px; padding: 3px 8px; border-radius: 4px; font-weight: 700;
    border: none; cursor: pointer; white-space: nowrap; transition: all .15s;
}
.epm-primary-btn.active { background: #FEF3C7; color: #B45309; }
.epm-primary-btn.inactive { background: #f3f3f0; color: #8a8a82; }
.epm-primary-btn.inactive:hover { background: #e8e8e4; }
.epm-remove-btn { background: none; border: none; color: #ccc; cursor: pointer; padding: 2px; transition: color .15s; }
.epm-remove-btn:hover { color: #ef4444; }
.epm-footer { padding: 16px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
.epm-btn-cancel {
    background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
    padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
}
.epm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
.epm-btn-submit {
    background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px;
    padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
    box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s;
}
.epm-btn-submit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
.epm-btn-submit:disabled { opacity: .6; cursor: not-allowed; }
.epm-add-contact {
    background: none; border: none; font-size: 11px; font-weight: 700;
    color: var(--gold, #C9A84C); cursor: pointer; padding: 0; transition: opacity .15s;
}
.epm-add-contact:hover { opacity: .7; }

/* ── Provider Detail Modal ── */
.pdm { width: 640px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
.pdm-header { background: #0F1B2D; padding: 18px 24px; display: flex; align-items: flex-start; justify-content: space-between; }
.pdm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; }
.pdm-subtitle { font-size: 12px; font-weight: 500; color: var(--gold, #C9A84C); }
.pdm-badge { display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: .05em; }
.pdm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
.pdm-close:hover { color: rgba(255,255,255,.75); }
.pdm-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; max-height: 70vh; overflow-y: auto; }
.pdm-body::-webkit-scrollbar { width: 4px; }
.pdm-body::-webkit-scrollbar-track { background: transparent; }
.pdm-body::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
.pdm-section { display: flex; align-items: center; gap: 10px; margin: 0; }
.pdm-section::before, .pdm-section::after { content: ''; flex: 1; height: 1px; background: var(--border, #d0cdc5); }
.pdm-section span { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .1em; white-space: nowrap; }
.pdm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
.pdm-value { font-size: 13px; color: #1a2535; line-height: 1.4; }
.pdm-value.empty { color: var(--muted, #8a8a82); }
.pdm-card { background: #fafafa; border: 1px solid var(--border, #d0cdc5); border-radius: 7px; padding: 10px 12px; }
.pdm-card-lg .pdm-value { font-size: 18px; font-weight: 700; font-family: 'IBM Plex Mono', monospace; }
.pdm-contact-row {
    display: flex; align-items: center; gap: 8px; padding: 8px 10px;
    border-radius: 6px; background: #fafaf8; border: 1px solid var(--border, #d0cdc5); margin-bottom: 4px;
}
.pdm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
.pdm-btn-edit {
    background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px;
    padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
    box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s; flex: 1; justify-content: center;
}
.pdm-btn-edit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
.pdm-btn-delete {
    background: #dc2626; color: #fff; border: none; border-radius: 7px;
    padding: 9px 18px; font-size: 13px; font-weight: 700; cursor: pointer;
    box-shadow: 0 2px 8px rgba(220,38,38,.3); display: flex; align-items: center; gap: 6px; transition: all .15s;
}
.pdm-btn-delete:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(220,38,38,.4); }

/* ── Insurance Company Modal ── */
.icm { width: 560px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
.icm-header { background: #0F1B2D; padding: 18px 24px; display: flex; align-items: flex-start; justify-content: space-between; }
.icm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; }
.icm-subtitle { font-size: 12px; font-weight: 500; color: var(--gold, #C9A84C); }
.icm-badge { display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: .05em; }
.icm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
.icm-close:hover { color: rgba(255,255,255,.75); }
.icm-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; max-height: 70vh; overflow-y: auto; }
.icm-body::-webkit-scrollbar { width: 4px; }
.icm-body::-webkit-scrollbar-track { background: transparent; }
.icm-body::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
.icm-section { display: flex; align-items: center; gap: 10px; margin: 0; }
.icm-section::before, .icm-section::after { content: ''; flex: 1; height: 1px; background: var(--border, #d0cdc5); }
.icm-section span { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .1em; white-space: nowrap; }
.icm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
.icm-req { color: var(--gold, #C9A84C); }
.icm-value { font-size: 13px; color: #1a2535; line-height: 1.4; }
.icm-value.empty { color: var(--muted, #8a8a82); }
.icm-card { background: #fafafa; border: 1px solid var(--border, #d0cdc5); border-radius: 7px; padding: 10px 12px; }
.icm-input, .icm-select, .icm-textarea {
    width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
    padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
}
.icm-input:focus, .icm-select:focus, .icm-textarea:focus {
    border-color: var(--gold, #C9A84C); background: #fff;
    box-shadow: 0 0 0 3px rgba(201,168,76,.1);
}
.icm-select {
    appearance: none; cursor: pointer; padding-right: 30px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center;
}
.icm-textarea { resize: vertical; min-height: 70px; line-height: 1.5; }
.icm-adjuster-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 12px; border-radius: 6px; background: #fafaf8; border: 1px solid var(--border, #d0cdc5); margin-bottom: 4px;
}
.icm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
.icm-btn-cancel {
    background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
    padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
}
.icm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
.icm-btn-submit {
    background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px;
    padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
    box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s;
}
.icm-btn-submit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
.icm-btn-submit:disabled { opacity: .6; cursor: not-allowed; }
.icm-btn-edit {
    background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px;
    padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
    box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s; flex: 1; justify-content: center;
}
.icm-btn-edit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
.icm-btn-delete {
    background: #dc2626; color: #fff; border: none; border-radius: 7px;
    padding: 9px 18px; font-size: 13px; font-weight: 700; cursor: pointer;
    box-shadow: 0 2px 8px rgba(220,38,38,.3); display: flex; align-items: center; gap: 6px; transition: all .15s;
}
.icm-btn-delete:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(220,38,38,.4); }

/* ── Adjuster Modal ── */
.ajm { width: 520px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
.ajm-header { background: #0F1B2D; padding: 18px 24px; display: flex; align-items: flex-start; justify-content: space-between; }
.ajm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; }
.ajm-subtitle { font-size: 12px; font-weight: 500; color: var(--gold, #C9A84C); }
.ajm-badge { display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: .05em; }
.ajm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
.ajm-close:hover { color: rgba(255,255,255,.75); }
.ajm-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; max-height: 70vh; overflow-y: auto; }
.ajm-body::-webkit-scrollbar { width: 4px; }
.ajm-body::-webkit-scrollbar-track { background: transparent; }
.ajm-body::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
.ajm-section { display: flex; align-items: center; gap: 10px; margin: 0; }
.ajm-section::before, .ajm-section::after { content: ''; flex: 1; height: 1px; background: var(--border, #d0cdc5); }
.ajm-section span { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .1em; white-space: nowrap; }
.ajm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
.ajm-req { color: var(--gold, #C9A84C); }
.ajm-value { font-size: 13px; color: #1a2535; line-height: 1.4; }
.ajm-value.empty { color: var(--muted, #8a8a82); }
.ajm-card { background: #fafafa; border: 1px solid var(--border, #d0cdc5); border-radius: 7px; padding: 10px 12px; }
.ajm-input, .ajm-select, .ajm-textarea {
    width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
    padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
}
.ajm-input:focus, .ajm-select:focus, .ajm-textarea:focus {
    border-color: var(--gold, #C9A84C); background: #fff;
    box-shadow: 0 0 0 3px rgba(201,168,76,.1);
}
.ajm-select {
    appearance: none; cursor: pointer; padding-right: 30px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center;
}
.ajm-textarea { resize: vertical; min-height: 70px; line-height: 1.5; }
.ajm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
.ajm-btn-cancel {
    background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
    padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
}
.ajm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
.ajm-btn-submit {
    background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px;
    padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
    box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s;
}
.ajm-btn-submit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
.ajm-btn-submit:disabled { opacity: .6; cursor: not-allowed; }
.ajm-btn-edit {
    background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px;
    padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
    box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s; flex: 1; justify-content: center;
}
.ajm-btn-edit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
.ajm-btn-secondary {
    background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
    padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
}
.ajm-btn-secondary:hover { background: #f8f7f4; border-color: #ccc; }
.ajm-btn-delete {
    background: #dc2626; color: #fff; border: none; border-radius: 7px;
    padding: 9px 18px; font-size: 13px; font-weight: 700; cursor: pointer;
    box-shadow: 0 2px 8px rgba(220,38,38,.3); display: flex; align-items: center; gap: 6px; transition: all .15s;
}
.ajm-btn-delete:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(220,38,38,.4); }
</style>

<div x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') || 'providers' }">

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">

        <!-- Gold bar -->
        <div class="sp-gold-bar"></div>

        <!-- Header -->
        <div class="sp-header" style="flex-wrap:wrap; gap:16px;">
            <div style="flex:1;">
                <div class="sp-eyebrow">Case Management</div>
                <h1 class="sp-title">Database</h1>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="sp-toolbar">
            <div class="sp-tabs">
                <button class="sp-tab" :class="activeTab === 'providers' && 'on'" @click="activeTab = 'providers'">Providers</button>
                <button class="sp-tab" :class="activeTab === 'insurance' && 'on'" @click="activeTab = 'insurance'">Insurance</button>
                <button class="sp-tab" :class="activeTab === 'adjusters' && 'on'" @click="activeTab = 'adjusters'">Adjusters</button>
            </div>
        </div>

        <!-- ===================== PROVIDERS TAB ===================== -->
        <div id="providers-tab" x-show="activeTab === 'providers'" x-data="providersListPage()" x-init="loadData()">

            <!-- Toolbar Row -->
            <div style="padding:12px 24px; display:flex; flex-wrap:wrap; align-items:center; gap:8px;">
                <input type="text" x-model="search" @input.debounce.300ms="loadData()" placeholder="Search by name, phone, fax, or email..."
                       class="sp-search" style="flex:1; min-width:200px;">
                <select x-model="typeFilter" @change="loadData()" class="sp-select">
                    <option value="">All Types</option>
                    <option value="acupuncture">Acupuncture</option><option value="chiro">Chiropractor</option><option value="massage">Massage</option>
                    <option value="pain_management">Pain Management</option><option value="pt">Physical Therapy</option><option value="er">Emergency Room</option>
                    <option value="hospital">Hospital</option><option value="physician">Physician</option><option value="imaging">Imaging</option>
                    <option value="pharmacy">Pharmacy</option><option value="surgery_center">Surgery Center</option><option value="police">Police</option><option value="other">Other</option>
                </select>
                <select x-model="difficultyFilter" @change="loadData()" class="sp-select">
                    <option value="">All Difficulty</option><option value="easy">Easy</option><option value="medium">Medium</option><option value="hard">Hard</option>
                </select>
                <button @click="showCreateModal = true" class="sp-new-btn-navy">+ New Provider</button>
            </div>

            <!-- Stats Bar -->
            <div style="padding:0 24px 12px;">
                <div class="sp-stats" style="margin-left:0; display:inline-flex;">
                    <div class="sp-stat">
                        <div class="sp-stat-num" style="color:#1a2535;" x-text="items.length"></div>
                        <div class="sp-stat-label">Total Providers</div>
                    </div>
                    <div class="sp-stat">
                        <div class="sp-stat-num" style="color:#2E7D6B;" x-text="items.filter(p => p.type === 'chiro').length"></div>
                        <div class="sp-stat-label">Chiropractors</div>
                    </div>
                    <div class="sp-stat">
                        <div class="sp-stat-num" style="color:#3B6FD4;" x-text="items.filter(p => p.type === 'physician').length"></div>
                        <div class="sp-stat-label">Physicians</div>
                    </div>
                    <div class="sp-stat">
                        <div class="sp-stat-num" style="color:#B8973F;" x-text="(() => { const w = items.filter(p => p.avg_response_days > 0); return w.length ? Math.round(w.reduce((s,p) => s + Number(p.avg_response_days), 0) / w.length) + 'd' : '—'; })()"></div>
                        <div class="sp-stat-label">Avg Response</div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div style="overflow-x:auto;" x-init="initScrollContainer($el)">
                <table class="sp-table">
                    <thead><tr>
                        <th class="cursor-pointer select-none" @click="sort('name')">Provider Name</th>
                        <th class="cursor-pointer select-none" @click="sort('type')">Type</th>
                        <th>Phone</th><th>Fax</th><th>Email</th>
                        <th class="cursor-pointer select-none" @click="sort('preferred_method')">Preferred Method</th>
                        <th class="cursor-pointer select-none" @click="sort('difficulty_level')">Difficulty</th>
                        <th class="cursor-pointer select-none" @click="sort('avg_response_days')">Avg Response</th>
                    </tr></thead>
                    <tbody>
                        <template x-if="loading"><tr><td colspan="8" class="sp-empty"><div class="spinner" style="margin:0 auto;"></div></td></tr></template>
                        <template x-if="!loading && items.length === 0"><tr><td colspan="8" class="sp-empty">No providers found</td></tr></template>
                        <template x-for="p in items" :key="p.id">
                            <tr @click="viewProvider(p.id)" class="prov-row" :class="[selectedProvider?.id === p.id ? 'prov-row-active' : '', p.is_suspicious == 1 ? 'bg-blue-50' : '']">
                                <td style="font-weight:600;" :style="p.is_suspicious == 1 ? 'color:#2563EB' : 'color:#7d693c'" x-text="p.name"></td>
                                <td><span style="font-size:12px; color:#9ca3af;" x-text="getProviderTypeLabel(p.type)"></span></td>
                                <td style="white-space:nowrap;" x-text="formatPhoneNumber(p.phone)"></td>
                                <td style="white-space:nowrap;" x-text="formatPhoneNumber(p.fax)"></td>
                                <td style="font-size:12px; white-space:nowrap;" x-text="p.email || '-'"></td>
                                <td><span style="font-size:12px;" x-text="getRequestMethodLabel(p.preferred_method)"></span></td>
                                <td><span class="status-badge" :class="'difficulty-' + p.difficulty_level" x-text="p.difficulty_level"></span></td>
                                <td class="sp-mono" x-text="p.avg_response_days ? p.avg_response_days + ' days' : '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div style="padding:10px 24px; border-top:1px solid #e8e4dc; font-size:13px; color:#9ca3af;">
                Showing <span x-text="items.length"></span> provider<span x-text="items.length === 1 ? '' : 's'"></span>
            </div>

            <!-- Provider Detail Modal -->
            <div x-show="selectedProvider" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @keydown.escape.window="selectedProvider = null" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
                <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="selectedProvider = null"></div>
                <div x-show="selectedProvider" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     @click.stop class="pdm relative z-10">
                    <template x-if="selectedProvider"><div>
                        <div class="pdm-header">
                            <div style="flex:1; padding-right:16px;">
                                <h3 x-text="selectedProvider.name"></h3>
                                <div style="display:flex; align-items:center; gap:8px; margin-top:8px; flex-wrap:wrap;">
                                    <span class="pdm-badge" style="background:rgba(255,255,255,.12); color:rgba(255,255,255,.7);" x-text="getProviderTypeLabel(selectedProvider.type)"></span>
                                    <template x-if="selectedProvider.difficulty_level"><span class="pdm-badge" :style="getDifficultyStyle(selectedProvider.difficulty_level)" x-text="selectedProvider.difficulty_level"></span></template>
                                    <template x-if="selectedProvider.uses_third_party == 1"><span class="pdm-badge" style="background:rgba(201,168,76,.15); color:#C9A84C;">ChartSwap</span></template>
                                </div>
                            </div>
                            <button type="button" class="pdm-close" @click="selectedProvider = null"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <div class="pdm-body">
                            <div class="pdm-section"><span>Contact Information</span></div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                <div class="pdm-card"><p class="pdm-label">Phone</p><p class="pdm-value" :class="!selectedProvider.phone && 'empty'" x-text="formatPhoneNumber(selectedProvider.phone)"></p></div>
                                <div class="pdm-card"><p class="pdm-label">Fax</p><p class="pdm-value" :class="!selectedProvider.fax && 'empty'" x-text="formatPhoneNumber(selectedProvider.fax)"></p></div>
                                <div class="pdm-card" style="grid-column:span 2;"><p class="pdm-label">Email</p><p class="pdm-value" style="word-break:break-all;" :class="!selectedProvider.email && 'empty'" x-text="selectedProvider.email || '—'"></p></div>
                                <template x-if="selectedProvider.address || selectedProvider.city"><div class="pdm-card" style="grid-column:span 2;"><p class="pdm-label">Address</p><div class="pdm-value"><p x-show="selectedProvider.address" x-text="selectedProvider.address"></p><p x-show="selectedProvider.city || selectedProvider.state || selectedProvider.zip"><span x-text="selectedProvider.city || ''"></span><span x-show="selectedProvider.city && selectedProvider.state">, </span><span x-text="selectedProvider.state || ''"></span><span x-show="selectedProvider.zip"> </span><span x-text="selectedProvider.zip || ''"></span></p></div></div></template>
                            </div>
                            <div class="pdm-section"><span>Stats</span></div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                <div class="pdm-card pdm-card-lg"><p class="pdm-label">Avg Response</p><p class="pdm-value" :style="{ color: getAvgColor(selectedProvider.avg_response_days) }" x-text="selectedProvider.avg_response_days ? selectedProvider.avg_response_days + 'd' : '—'"></p></div>
                                <div class="pdm-card pdm-card-lg"><p class="pdm-label">Preferred Method</p><p class="pdm-value" x-text="getRequestMethodLabel(selectedProvider.preferred_method)"></p></div>
                            </div>
                            <template x-if="selectedProvider.contacts && selectedProvider.contacts.length > 0"><div>
                                <div class="pdm-section"><span>Department Contacts</span></div>
                                <div><template x-for="(contact, idx) in selectedProvider.contacts" :key="idx"><div class="pdm-contact-row">
                                    <span style="font-weight:700; flex-shrink:0; min-width:100px; font-size:12px;" x-text="contact.department"></span>
                                    <span class="pdm-badge" :style="getContactTypeStyle(contact.contact_type)" x-text="contact.contact_type"></span>
                                    <span style="color:#3D4F63; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; min-width:0;" x-text="contact.contact_value"></span>
                                    <template x-if="contact.is_primary == 1"><span style="flex-shrink:0; margin-left:auto; font-size:10px; font-weight:700; color:#C9A84C;">PRIMARY</span></template>
                                </div></template></div>
                            </div></template>
                        </div>
                        <div class="pdm-footer">
                            <button @click="editProvider = { ...selectedProvider, contacts: selectedProvider.contacts || [], no_record_fee: selectedProvider.charges_record_fee == 0 }; showProviderModal = true; selectedProvider = null" class="pdm-btn-edit">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Edit Provider
                            </button>
                            <button @click="deleteProvider(selectedProvider.id, selectedProvider.name)" class="pdm-btn-delete">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg> Delete
                            </button>
                        </div>
                    </div></template>
                </div>
            </div>

            <!-- Create Provider Modal -->
            <template x-if="showCreateModal"><div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeCreateModal()">
                <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="closeCreateModal()"></div>
                <form @submit.prevent="createProvider()" class="epm relative z-10" @click.stop>
                    <div class="epm-header"><h3>New Provider</h3><button type="button" class="epm-close" @click="closeCreateModal()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                    <div class="epm-body">
                        <div class="epm-section"><span>Basic Info</span></div>
                        <div style="display:flex; gap:12px;"><div style="flex:3; position:relative;"><label class="epm-label">Provider Name <span class="epm-req">*</span></label><input type="text" x-model="newProvider.name" @input="searchProviderName()" @focus="showNameDropdown && nameSearchResults.length > 0" required class="epm-input" autocomplete="off"><div x-show="showNameDropdown" @click.outside="showNameDropdown = false" style="position:absolute; z-index:20; width:100%; margin-top:4px; background:#fff; border:1.5px solid var(--border,#d0cdc5); border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.12); max-height:200px; overflow-y:auto;"><template x-for="pr in nameSearchResults" :key="pr.id"><button type="button" @click="selectExistingProvider(pr)" style="width:100%; text-align:left; background:none; border:none; padding:9px 14px; font-size:13px; color:#1a2535; cursor:pointer; display:flex; justify-content:space-between; align-items:center; transition:background .1s;" onmouseover="this.style.background='rgba(201,168,76,.06)'" onmouseout="this.style.background='none'"><span x-text="pr.name"></span><span style="font-size:11px; color:#8a8a82;" x-text="pr.type"></span></button></template></div></div><div style="flex:2;"><label class="epm-label">Type <span class="epm-req">*</span></label><select x-model="newProvider.type" required class="epm-select"><option value="acupuncture">Acupuncture</option><option value="chiro">Chiropractor</option><option value="massage">Massage</option><option value="pain_management">Pain Mgmt</option><option value="pt">Physical Therapy</option><option value="er">Emergency Room</option><option value="hospital">Hospital</option><option value="physician">Physician</option><option value="imaging">Imaging Center</option><option value="pharmacy">Pharmacy</option><option value="surgery_center">Surgery Center</option><option value="police">Police</option><option value="other">Other</option></select></div><div style="flex:1.5;"><label class="epm-label">Difficulty</label><select x-model="newProvider.difficulty_level" class="epm-select"><option value="easy">Easy</option><option value="medium">Medium</option><option value="hard">Hard</option></select></div></div>
                        <div class="epm-section"><span>Address</span></div>
                        <div><label class="epm-label">Street Address</label><input type="text" x-model="newProvider.address" class="epm-input"></div>
                        <div style="display:flex; gap:12px;"><div style="flex:3;"><label class="epm-label">City</label><input type="text" x-model="newProvider.city" class="epm-input"></div><div style="flex:1;"><label class="epm-label">State</label><input type="text" x-model="newProvider.state" maxlength="2" placeholder="WA" class="epm-input" style="text-transform:uppercase;"></div><div style="flex:1;"><label class="epm-label">ZIP</label><input type="text" x-model="newProvider.zip" maxlength="10" placeholder="98036" class="epm-input"></div></div>
                        <div class="epm-section"><span>Contact</span></div>
                        <div style="display:flex; gap:12px;"><div style="flex:1;"><label class="epm-label">Phone</label><input type="text" x-model="newProvider.phone" class="epm-input"></div><div style="flex:1;"><label class="epm-label">Fax</label><input type="text" x-model="newProvider.fax" class="epm-input"></div><div style="flex:2;"><label class="epm-label">Email</label><input type="email" x-model="newProvider.email" class="epm-input"></div></div>
                        <div style="display:flex; gap:12px;"><div style="flex:1;"><label class="epm-label">Preferred Method</label><select x-model="newProvider.preferred_method" class="epm-select"><option value="fax">Fax</option><option value="email">Email</option><option value="portal">Portal</option><option value="phone">Phone</option><option value="mail">Mail</option></select></div><div style="flex:2;"><label class="epm-label">Portal URL</label><input type="url" x-model="newProvider.portal_url" class="epm-input" placeholder="https://..."></div></div>
                        <div style="display:flex; gap:12px;"><label class="epm-check-card"><input type="checkbox" x-model="newProvider.uses_third_party"><span>Uses third party</span></label><label class="epm-check-card"><input type="checkbox" x-model="newProvider.no_record_fee"><span>No record fee</span></label></div>
                        <template x-if="newProvider.uses_third_party"><div style="display:flex; gap:12px;"><div style="flex:1;"><label class="epm-label">Third Party Name</label><input type="text" x-model="newProvider.third_party_name" class="epm-input"></div><div style="flex:1;"><label class="epm-label">Third Party Contact</label><input type="text" x-model="newProvider.third_party_contact" class="epm-input"></div></div></template>
                        <div style="display:flex; align-items:center; justify-content:space-between;"><span class="epm-label" style="margin-bottom:0;">Department Contacts</span><button type="button" @click="addContact(newProvider)" class="epm-add-contact">+ Add Contact</button></div>
                        <template x-for="(contact, idx) in newProvider.contacts" :key="idx"><div class="epm-contact-row"><input type="text" x-model="contact.department" placeholder="Department" class="epm-input" style="flex:2;"><select x-model="contact.contact_type" class="epm-select" style="flex:1;"><option value="email">Email</option><option value="fax">Fax</option><option value="phone">Phone</option><option value="portal">Portal</option></select><input type="text" x-model="contact.contact_value" :placeholder="contact.contact_type === 'email' ? 'Email address' : contact.contact_type === 'fax' ? 'Fax number' : contact.contact_type === 'phone' ? 'Phone number' : 'Portal URL'" class="epm-input" style="flex:2;"><button type="button" @click="setPrimary(newProvider, idx)" class="epm-primary-btn" :class="contact.is_primary == 1 ? 'active' : 'inactive'" x-text="contact.is_primary == 1 ? 'PRIMARY' : 'Set'"></button><button type="button" @click="removeContact(newProvider, idx)" class="epm-remove-btn"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div></template>
                        <template x-if="newProvider.contacts.length === 0"><div class="epm-empty-contacts">No department contacts added. Click "+ Add Contact" to add one.</div></template>
                        <div class="epm-section"><span>Notes</span></div>
                        <textarea x-model="newProvider.notes" class="epm-textarea" placeholder="Optional notes..."></textarea>
                    </div>
                    <div class="epm-footer"><button type="button" @click="closeCreateModal()" class="epm-btn-cancel">Cancel</button><button type="submit" :disabled="saving" class="epm-btn-submit"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg><span x-text="saving ? 'Creating...' : 'Create Provider'"></span></button></div>
                </form>
            </div></template>

            <!-- Edit Provider Modal -->
            <template x-if="showProviderModal"><div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeEditModal()">
                <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="closeEditModal()"></div>
                <form @submit.prevent="updateProvider()" class="epm relative z-10" @click.stop>
                    <div class="epm-header"><h3>Edit Provider</h3><button type="button" class="epm-close" @click="closeEditModal()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                    <div class="epm-body">
                        <div class="epm-section"><span>Basic Info</span></div>
                        <div style="display:flex; gap:12px;"><div style="flex:3;"><label class="epm-label">Provider Name <span class="epm-req">*</span></label><input type="text" x-model="editProvider.name" required class="epm-input"></div><div style="flex:2;"><label class="epm-label">Type <span class="epm-req">*</span></label><select x-model="editProvider.type" required class="epm-select"><option value="acupuncture">Acupuncture</option><option value="chiro">Chiropractor</option><option value="massage">Massage</option><option value="pain_management">Pain Mgmt</option><option value="pt">Physical Therapy</option><option value="er">Emergency Room</option><option value="hospital">Hospital</option><option value="physician">Physician</option><option value="imaging">Imaging Center</option><option value="pharmacy">Pharmacy</option><option value="surgery_center">Surgery Center</option><option value="police">Police</option><option value="other">Other</option></select></div><div style="flex:1;"><label class="epm-label">Difficulty</label><select x-model="editProvider.difficulty_level" class="epm-select"><option value="easy">Easy</option><option value="medium">Medium</option><option value="hard">Hard</option></select></div></div>
                        <div class="epm-section"><span>Address</span></div>
                        <div><label class="epm-label">Street Address</label><input type="text" x-model="editProvider.address" class="epm-input"></div>
                        <div style="display:flex; gap:12px;"><div style="flex:3;"><label class="epm-label">City</label><input type="text" x-model="editProvider.city" class="epm-input"></div><div style="flex:1;"><label class="epm-label">State</label><input type="text" x-model="editProvider.state" maxlength="2" placeholder="WA" class="epm-input" style="text-transform:uppercase;"></div><div style="flex:1;"><label class="epm-label">ZIP</label><input type="text" x-model="editProvider.zip" maxlength="10" placeholder="98036" class="epm-input"></div></div>
                        <div class="epm-section"><span>Contact</span></div>
                        <div style="display:flex; gap:12px;"><div style="flex:1;"><label class="epm-label">Phone</label><input type="text" x-model="editProvider.phone" class="epm-input"></div><div style="flex:1;"><label class="epm-label">Fax</label><input type="text" x-model="editProvider.fax" class="epm-input"></div><div style="flex:2;"><label class="epm-label">Email</label><input type="email" x-model="editProvider.email" class="epm-input"></div></div>
                        <div style="display:flex; gap:12px;"><div style="flex:1;"><label class="epm-label">Preferred Method</label><select x-model="editProvider.preferred_method" class="epm-select"><option value="fax">Fax</option><option value="email">Email</option><option value="portal">Portal</option><option value="phone">Phone</option><option value="mail">Mail</option></select></div><div style="flex:2;"><label class="epm-label">Portal URL</label><input type="url" x-model="editProvider.portal_url" class="epm-input" placeholder="https://..."></div></div>
                        <div style="display:flex; gap:12px;"><label class="epm-check-card"><input type="checkbox" x-model="editProvider.uses_third_party"><span>Uses third party</span></label><label class="epm-check-card"><input type="checkbox" x-model="editProvider.no_record_fee"><span>No record fee</span></label></div>
                        <template x-if="editProvider.uses_third_party"><div style="display:flex; gap:12px;"><div style="flex:1;"><label class="epm-label">Third Party Name</label><input type="text" x-model="editProvider.third_party_name" class="epm-input"></div><div style="flex:1;"><label class="epm-label">Third Party Contact</label><input type="text" x-model="editProvider.third_party_contact" class="epm-input"></div></div></template>
                        <div style="display:flex; align-items:center; justify-content:space-between;"><span class="epm-label" style="margin-bottom:0;">Department Contacts</span><button type="button" @click="addContact(editProvider)" class="epm-add-contact">+ Add Contact</button></div>
                        <template x-for="(contact, idx) in editProvider.contacts" :key="idx"><div class="epm-contact-row"><input type="text" x-model="contact.department" placeholder="Department" class="epm-input" style="flex:2;"><select x-model="contact.contact_type" class="epm-select" style="flex:1;"><option value="email">Email</option><option value="fax">Fax</option><option value="phone">Phone</option><option value="portal">Portal</option></select><input type="text" x-model="contact.contact_value" :placeholder="contact.contact_type === 'email' ? 'Email address' : contact.contact_type === 'fax' ? 'Fax number' : contact.contact_type === 'phone' ? 'Phone number' : 'Portal URL'" class="epm-input" style="flex:2;"><button type="button" @click="setPrimary(editProvider, idx)" class="epm-primary-btn" :class="contact.is_primary == 1 ? 'active' : 'inactive'" x-text="contact.is_primary == 1 ? 'PRIMARY' : 'Set'"></button><button type="button" @click="removeContact(editProvider, idx)" class="epm-remove-btn"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div></template>
                        <template x-if="editProvider.contacts.length === 0"><div class="epm-empty-contacts">No department contacts added. Click "+ Add Contact" to add one.</div></template>
                        <div class="epm-section"><span>Notes</span></div>
                        <textarea x-model="editProvider.notes" class="epm-textarea" placeholder="Optional notes..."></textarea>
                    </div>
                    <div class="epm-footer"><button type="button" @click="closeEditModal()" class="epm-btn-cancel">Cancel</button><button type="submit" :disabled="saving" class="epm-btn-submit"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span x-text="saving ? 'Saving...' : 'Update Provider'"></span></button></div>
                </form>
            </div></template>
        </div>

        <!-- ===================== INSURANCE TAB ===================== -->
        <div x-show="activeTab === 'insurance'" x-cloak x-data="insuranceListPage()" x-init="loadData()">

            <!-- Toolbar Row -->
            <div style="padding:12px 24px; display:flex; flex-wrap:wrap; align-items:center; gap:8px;">
                <input type="text" x-model="search" @input.debounce.300ms="loadData()" placeholder="Search by name, phone, fax, or email..."
                       class="sp-search" style="flex:1; min-width:200px;">
                <select x-model="typeFilter" @change="loadData()" class="sp-select">
                    <option value="">All Types</option><option value="auto">Auto</option><option value="health">Health</option><option value="workers_comp">Worker's Comp</option><option value="liability">Liability</option><option value="um_uim">UM/UIM</option><option value="government">Government</option><option value="other">Other</option>
                </select>
                <button @click="showCreateModal = true" class="sp-new-btn-navy">+ New Insurance Co.</button>
            </div>

            <!-- Table -->
            <div style="overflow-x:auto;" x-init="initScrollContainer($el)">
                <table class="sp-table">
                    <thead><tr>
                        <th class="cursor-pointer select-none" @click="sort('name')">Company Name</th>
                        <th class="cursor-pointer select-none" @click="sort('type')">Type</th>
                        <th>Phone</th><th>Fax</th><th>Email</th><th>Adjusters</th>
                        <th class="cursor-pointer select-none" @click="sort('city')">City/State</th>
                    </tr></thead>
                    <tbody>
                        <template x-if="loading"><tr><td colspan="7" class="sp-empty"><div class="spinner" style="margin:0 auto;"></div></td></tr></template>
                        <template x-if="!loading && items.length === 0"><tr><td colspan="7" class="sp-empty">No insurance companies found</td></tr></template>
                        <template x-for="c in items" :key="c.id">
                            <tr @click="viewCompany(c.id)" class="db-row" :class="selectedCompany?.id === c.id ? 'db-row-active' : ''">
                                <td style="font-weight:600; color:#7d693c;" x-text="c.name"></td>
                                <td><span class="sp-stage" :style="getTypeColor(c.type)" x-text="getInsuranceTypeLabel(c.type)"></span></td>
                                <td style="white-space:nowrap; font-size:13px;" x-text="c.phone || '-'"></td>
                                <td style="white-space:nowrap; font-size:13px;" x-text="c.fax || '-'"></td>
                                <td style="font-size:12px; white-space:nowrap;" x-text="c.email || '-'"></td>
                                <td style="text-align:center;"><span style="font-size:12px; font-weight:600;" x-text="c.adjuster_count || '0'"></span></td>
                                <td style="font-size:13px; white-space:nowrap;" x-text="[c.city, c.state].filter(Boolean).join(', ') || '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div style="padding:10px 24px; border-top:1px solid #e8e4dc; font-size:13px; color:#9ca3af;">
                Showing <span x-text="items.length"></span> compan<span x-text="items.length === 1 ? 'y' : 'ies'"></span>
            </div>

            <!-- Insurance Detail Modal -->
            <div x-show="selectedCompany" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @keydown.escape.window="selectedCompany = null">
                <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="selectedCompany = null"></div>
                <div @click.stop class="icm relative z-10"><template x-if="selectedCompany"><div>
                    <div class="icm-header"><div style="flex:1; padding-right:16px;"><h3 x-text="selectedCompany.name"></h3><div style="margin-top:6px;"><span class="icm-badge" style="background:rgba(255,255,255,.12); color:rgba(255,255,255,.7);" x-text="getInsuranceTypeLabel(selectedCompany.type)"></span></div></div><button type="button" class="icm-close" @click="selectedCompany = null"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                    <div class="icm-body">
                        <div class="icm-section"><span>Contact Information</span></div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <div class="icm-card"><p class="icm-label">Phone</p><p class="icm-value" x-text="selectedCompany.phone || '—'"></p></div>
                            <div class="icm-card"><p class="icm-label">Fax</p><p class="icm-value" x-text="selectedCompany.fax || '—'"></p></div>
                            <div class="icm-card" style="grid-column:span 2;"><p class="icm-label">Email</p><p class="icm-value" style="word-break:break-all;" x-text="selectedCompany.email || '—'"></p></div>
                            <template x-if="selectedCompany.website"><div class="icm-card" style="grid-column:span 2;"><p class="icm-label">Website</p><p class="icm-value" style="word-break:break-all;" x-text="selectedCompany.website"></p></div></template>
                            <template x-if="selectedCompany.address || selectedCompany.city"><div class="icm-card" style="grid-column:span 2;"><p class="icm-label">Address</p><div class="icm-value"><p x-text="selectedCompany.address || ''"></p><p x-text="[selectedCompany.city, selectedCompany.state].filter(Boolean).join(', ') + (selectedCompany.zip ? ' ' + selectedCompany.zip : '')"></p></div></div></template>
                        </div>
                        <template x-if="selectedCompany.adjusters && selectedCompany.adjusters.length > 0"><div><div class="icm-section"><span>Adjusters</span></div><div><template x-for="adj in selectedCompany.adjusters" :key="adj.id"><div class="icm-adjuster-row"><span style="font-size:13px; font-weight:600; color:#1a2535;" x-text="adj.last_name + ', ' + adj.first_name"></span><div style="display:flex; align-items:center; gap:8px;"><span style="font-size:11px; color:var(--muted,#8a8a82);" x-text="adj.title || ''"></span><span class="icm-badge" :style="adj.is_active == 1 ? 'background:#dcfce7; color:#15803d;' : 'background:#f3f4f6; color:#6b7280;'" x-text="adj.is_active == 1 ? 'Active' : 'Inactive'"></span></div></div></template></div></div></template>
                        <template x-if="selectedCompany.notes"><div><div class="icm-section"><span>Notes</span></div><p style="font-size:13px; color:#3D4F63; line-height:1.5;" x-text="selectedCompany.notes"></p></div></template>
                    </div>
                    <div class="icm-footer">
                        <button @click="openEditModal()" class="icm-btn-edit"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Edit</button>
                        <button @click="deleteCompany(selectedCompany.id, selectedCompany.name)" class="icm-btn-delete"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg> Delete</button>
                    </div>
                </div></template></div>
            </div>

            <!-- Create/Edit Insurance Modal -->
            <template x-if="showCreateModal || showEditModal"><div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="showCreateModal ? closeCreateModal() : closeEditModal()">
                <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showCreateModal ? closeCreateModal() : closeEditModal()"></div>
                <form @submit.prevent="showCreateModal ? createCompany() : updateCompany()" class="icm relative z-10" @click.stop style="display:flex; flex-direction:column; max-height:90vh;">
                    <div class="icm-header" style="flex-shrink:0;"><div><h3 x-text="showEditModal ? 'Edit Insurance Company' : 'New Insurance Company'"></h3><p class="icm-subtitle" x-text="showEditModal ? 'Update company details' : 'Add a new insurance company'"></p></div><button type="button" class="icm-close" @click="showCreateModal ? closeCreateModal() : closeEditModal()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                    <div class="icm-body" style="flex:1; min-height:0;">
                        <div class="icm-section"><span>Basic Info</span></div>
                        <div style="display:flex; gap:12px;"><div style="flex:1;"><label class="icm-label">Company Name <span class="icm-req">*</span></label><input type="text" x-model="showEditModal ? editCompany.name : newCompany.name" required class="icm-input"></div><div style="flex:1;"><label class="icm-label">Type <span class="icm-req">*</span></label><select x-model="showEditModal ? editCompany.type : newCompany.type" required class="icm-select"><option value="auto">Auto</option><option value="health">Health</option><option value="workers_comp">Worker's Comp</option><option value="liability">Liability</option><option value="um_uim">UM/UIM</option><option value="government">Government</option><option value="other">Other</option></select></div></div>
                        <div class="icm-section"><span>Contact</span></div>
                        <div style="display:flex; gap:12px;"><div style="flex:1;"><label class="icm-label">Phone</label><input type="text" x-model="showEditModal ? editCompany.phone : newCompany.phone" class="icm-input"></div><div style="flex:1;"><label class="icm-label">Fax</label><input type="text" x-model="showEditModal ? editCompany.fax : newCompany.fax" class="icm-input"></div><div style="flex:1;"><label class="icm-label">Email</label><input type="email" x-model="showEditModal ? editCompany.email : newCompany.email" class="icm-input"></div></div>
                        <div class="icm-section"><span>Address</span></div>
                        <div><label class="icm-label">Street Address</label><input type="text" x-model="showEditModal ? editCompany.address : newCompany.address" class="icm-input"></div>
                        <div style="display:flex; gap:12px;"><div style="flex:3;"><label class="icm-label">City</label><input type="text" x-model="showEditModal ? editCompany.city : newCompany.city" class="icm-input"></div><div style="flex:1;"><label class="icm-label">State</label><input type="text" x-model="showEditModal ? editCompany.state : newCompany.state" maxlength="2" class="icm-input" style="text-transform:uppercase;"></div><div style="flex:1.5;"><label class="icm-label">ZIP</label><input type="text" x-model="showEditModal ? editCompany.zip : newCompany.zip" maxlength="10" class="icm-input"></div></div>
                        <div class="icm-section"><span>Other</span></div>
                        <div><label class="icm-label">Website</label><input type="url" x-model="showEditModal ? editCompany.website : newCompany.website" class="icm-input" placeholder="https://..."></div>
                        <div><label class="icm-label">Notes</label><textarea x-model="showEditModal ? editCompany.notes : newCompany.notes" class="icm-textarea" placeholder="Optional notes..."></textarea></div>
                    </div>
                    <div class="icm-footer" style="flex-shrink:0;"><button type="button" @click="showCreateModal ? closeCreateModal() : closeEditModal()" class="icm-btn-cancel">Cancel</button><button type="submit" :disabled="saving" class="icm-btn-submit"><template x-if="!showEditModal"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></template><template x-if="showEditModal"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></template><span x-text="saving ? 'Saving...' : (showEditModal ? 'Update' : 'Create')"></span></button></div>
                </form>
            </div></template>
        </div>

        <!-- ===================== ADJUSTERS TAB ===================== -->
        <div x-show="activeTab === 'adjusters'" x-cloak x-data="adjustersListPage()" x-init="init()">

            <!-- Toolbar Row -->
            <div style="padding:12px 24px; display:flex; flex-wrap:wrap; align-items:center; gap:8px;">
                <input type="text" x-model="search" @input.debounce.300ms="loadData()" placeholder="Search by name, email, or insurance company..."
                       class="sp-search" style="flex:1; min-width:200px;">
                <select x-model="companyFilter" @change="loadData()" class="sp-select">
                    <option value="">All Companies</option>
                    <template x-for="co in insuranceCompanies" :key="co.id"><option :value="co.id" x-text="co.name"></option></template>
                </select>
                <select x-model="typeFilter" @change="loadData()" class="sp-select">
                    <option value="">All Types</option><option value="pip">PIP</option><option value="um">UM</option><option value="uim">UIM</option><option value="3rd_party">3rd Party</option><option value="liability">Liability</option><option value="pd">PD</option><option value="bi">BI</option>
                </select>
                <select x-model="activeFilter" @change="loadData()" class="sp-select">
                    <option value="">All Status</option><option value="1">Active</option><option value="0">Inactive</option>
                </select>
                <button @click="showCreateModal = true" class="sp-new-btn-navy">+ New Adjuster</button>
            </div>

            <!-- Table -->
            <div style="overflow-x:auto;" x-init="initScrollContainer($el)">
                <table class="sp-table">
                    <thead><tr>
                        <th class="cursor-pointer select-none" @click="sort('last_name')">Name</th>
                        <th class="cursor-pointer select-none" @click="sort('title')">Title</th>
                        <th>Type</th>
                        <th class="cursor-pointer select-none" @click="sort('insurance_company_name')">Insurance Company</th>
                        <th>Phone</th>
                        <th class="cursor-pointer select-none" @click="sort('email')">Email</th>
                        <th>Status</th>
                    </tr></thead>
                    <tbody>
                        <template x-if="loading"><tr><td colspan="7" class="sp-empty"><div class="spinner" style="margin:0 auto;"></div></td></tr></template>
                        <template x-if="!loading && items.length === 0"><tr><td colspan="7" class="sp-empty">No adjusters found</td></tr></template>
                        <template x-for="a in items" :key="a.id">
                            <tr @click="viewAdjuster(a.id)" class="db-row" :class="selectedAdjuster?.id === a.id ? 'db-row-active' : ''">
                                <td style="font-weight:600; color:#7d693c;" x-text="a.last_name + ', ' + a.first_name"></td>
                                <td style="font-size:13px; color:#6b7280;" x-text="a.title || '-'"></td>
                                <td><span x-show="a.adjuster_type" class="sp-stage" style="background:#eff6ff; color:#2563eb; font-size:11px; padding:1px 6px;" x-text="getTypeLabel(a.adjuster_type)"></span><span x-show="!a.adjuster_type" style="color:#9ca3af;">-</span></td>
                                <td style="font-size:13px;" x-text="a.insurance_company_name || '-'"></td>
                                <td style="white-space:nowrap; font-size:13px;" x-text="a.phone || '-'"></td>
                                <td style="font-size:12px; white-space:nowrap;" x-text="a.email || '-'"></td>
                                <td><span class="sp-stage" :style="a.is_active == 1 ? 'background:#dcfce7; color:#15803d;' : 'background:#f3f4f6; color:#6b7280;'" style="font-size:11px; padding:1px 6px;" x-text="a.is_active == 1 ? 'Active' : 'Inactive'"></span></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div style="padding:10px 24px; border-top:1px solid #e8e4dc; font-size:13px; color:#9ca3af;">
                Showing <span x-text="items.length"></span> adjuster<span x-text="items.length === 1 ? '' : 's'"></span>
            </div>

            <!-- Adjuster Detail Modal -->
            <div x-show="selectedAdjuster" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @keydown.escape.window="selectedAdjuster = null">
                <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="selectedAdjuster = null"></div>
                <div @click.stop class="ajm relative z-10"><template x-if="selectedAdjuster"><div>
                    <div class="ajm-header"><div style="flex:1; padding-right:16px;"><h3 x-text="selectedAdjuster.first_name + ' ' + selectedAdjuster.last_name"></h3><div style="display:flex; align-items:center; gap:8px; margin-top:6px;"><template x-if="selectedAdjuster.title"><span class="ajm-subtitle" x-text="selectedAdjuster.title"></span></template><span class="ajm-badge" :style="selectedAdjuster.is_active == 1 ? 'background:#dcfce7; color:#15803d;' : 'background:#f3f4f6; color:#6b7280;'" x-text="selectedAdjuster.is_active == 1 ? 'Active' : 'Inactive'"></span></div></div><button type="button" class="ajm-close" @click="selectedAdjuster = null"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                    <div class="ajm-body">
                        <div class="ajm-section"><span>Details</span></div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <div class="ajm-card"><p class="ajm-label">Type</p><p class="ajm-value" x-text="getTypeLabel(selectedAdjuster.adjuster_type)"></p></div>
                            <div class="ajm-card"><p class="ajm-label">Insurance Company</p><p class="ajm-value" x-text="selectedAdjuster.insurance_company_name || '—'"></p></div>
                            <div class="ajm-card"><p class="ajm-label">Phone</p><p class="ajm-value" x-text="selectedAdjuster.phone || '—'"></p></div>
                            <div class="ajm-card"><p class="ajm-label">Fax</p><p class="ajm-value" x-text="selectedAdjuster.fax || '—'"></p></div>
                            <div class="ajm-card" style="grid-column:span 2;"><p class="ajm-label">Email</p><p class="ajm-value" style="word-break:break-all;" x-text="selectedAdjuster.email || '—'"></p></div>
                        </div>
                        <template x-if="selectedAdjuster.notes"><div><div class="ajm-section"><span>Notes</span></div><p style="font-size:13px; color:#3D4F63; line-height:1.5;" x-text="selectedAdjuster.notes"></p></div></template>
                    </div>
                    <div class="ajm-footer">
                        <button @click="openEditModal()" class="ajm-btn-edit"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Edit</button>
                        <button @click="toggleActive(selectedAdjuster.id, selectedAdjuster.is_active)" class="ajm-btn-secondary" x-text="selectedAdjuster.is_active == 1 ? 'Deactivate' : 'Activate'"></button>
                        <button @click="deleteAdjuster(selectedAdjuster.id, selectedAdjuster.first_name + ' ' + selectedAdjuster.last_name)" class="ajm-btn-delete"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg> Delete</button>
                    </div>
                </div></template></div>
            </div>

            <!-- Create/Edit Adjuster Modal -->
            <template x-if="showCreateModal || showEditModal"><div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="showCreateModal ? closeCreateModal() : closeEditModal()">
                <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showCreateModal ? closeCreateModal() : closeEditModal()"></div>
                <form @submit.prevent="showCreateModal ? createAdjuster() : updateAdjuster()" class="ajm relative z-10" @click.stop>
                    <div class="ajm-header"><div><h3 x-text="showEditModal ? 'Edit Adjuster' : 'New Adjuster'"></h3><p class="ajm-subtitle" x-text="showEditModal ? 'Update adjuster details' : 'Add a new adjuster'"></p></div><button type="button" class="ajm-close" @click="showCreateModal ? closeCreateModal() : closeEditModal()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                    <div class="ajm-body">
                        <div class="ajm-section"><span>Basic Info</span></div>
                        <div style="display:flex; gap:12px;"><div style="flex:1;"><label class="ajm-label">First Name <span class="ajm-req">*</span></label><input type="text" x-model="showEditModal ? editAdjuster.first_name : newAdjuster.first_name" required class="ajm-input"></div><div style="flex:1;"><label class="ajm-label">Last Name <span class="ajm-req">*</span></label><input type="text" x-model="showEditModal ? editAdjuster.last_name : newAdjuster.last_name" required class="ajm-input"></div></div>
                        <div style="display:flex; gap:12px;"><div style="flex:1;"><label class="ajm-label">Title</label><input type="text" x-model="showEditModal ? editAdjuster.title : newAdjuster.title" class="ajm-input" placeholder="e.g., Claims Adjuster"></div><div style="flex:1;"><label class="ajm-label">Type</label><select x-model="showEditModal ? editAdjuster.adjuster_type : newAdjuster.adjuster_type" class="ajm-select"><option value="">None</option><option value="pip">PIP</option><option value="um">UM</option><option value="uim">UIM</option><option value="3rd_party">3rd Party</option><option value="liability">Liability</option><option value="pd">PD</option><option value="bi">BI</option></select></div></div>
                        <div><label class="ajm-label">Insurance Company</label><select x-model="showEditModal ? editAdjuster.insurance_company_id : newAdjuster.insurance_company_id" class="ajm-select"><option value="">None</option><template x-for="co in insuranceCompanies" :key="co.id"><option :value="co.id" x-text="co.name"></option></template></select></div>
                        <div class="ajm-section"><span>Contact</span></div>
                        <div style="display:flex; gap:12px;"><div style="flex:1;"><label class="ajm-label">Phone</label><input type="text" x-model="showEditModal ? editAdjuster.phone : newAdjuster.phone" class="ajm-input"></div><div style="flex:1;"><label class="ajm-label">Fax</label><input type="text" x-model="showEditModal ? editAdjuster.fax : newAdjuster.fax" class="ajm-input"></div></div>
                        <div><label class="ajm-label">Email</label><input type="email" x-model="showEditModal ? editAdjuster.email : newAdjuster.email" class="ajm-input"></div>
                        <div class="ajm-section"><span>Notes</span></div>
                        <div><textarea x-model="showEditModal ? editAdjuster.notes : newAdjuster.notes" class="ajm-textarea" placeholder="Optional notes..."></textarea></div>
                    </div>
                    <div class="ajm-footer"><button type="button" @click="showCreateModal ? closeCreateModal() : closeEditModal()" class="ajm-btn-cancel">Cancel</button><button type="submit" :disabled="saving" class="ajm-btn-submit"><template x-if="!showEditModal"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></template><template x-if="showEditModal"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></template><span x-text="saving ? 'Saving...' : (showEditModal ? 'Update' : 'Create')"></span></button></div>
                </form>
            </div></template>
        </div>

    </div><!-- /sp-card -->

</div>
