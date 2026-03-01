<!-- INI Staff Assignment Modal -->
<div x-show="showIniStaffModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none;" @keydown.escape.window="showIniStaffModal && (showIniStaffModal = false)">
    <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showIniStaffModal = false"></div>
    <div class="ecm relative z-10" style="width:440px;" @click.stop>

        <!-- Header -->
        <div class="ecm-header">
            <h3>Activate Provider for Requesting</h3>
            <button type="button" class="ecm-close" @click="showIniStaffModal = false">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="ecm-body">
            <!-- Providers list -->
            <div style="background:#f8f7f4; border-radius:7px; padding:10px 13px;">
                <p style="font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin:0 0 6px;">
                    Providers to activate
                </p>
                <template x-for="p in providers.filter(p => iniProviderIds.length > 0 ? iniProviderIds.includes(p.id) : p.overall_status === 'treating')" :key="p.id">
                    <div style="font-size:12.5px; color:#1a2535; padding:3px 0; display:flex; align-items:center; gap:6px;">
                        <svg width="14" height="14" fill="none" stroke="#C9A84C" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="p.provider_name"></span>
                    </div>
                </template>
            </div>

            <!-- Record Types -->
            <div>
                <label class="ecm-label">Record Types to Request</label>
                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                    <label class="ecm-check-card" style="flex:0 0 auto; padding:7px 12px;" :class="{ checked: iniRecordTypes.request_mr }">
                        <input type="checkbox" x-model="iniRecordTypes.request_mr"> <span>MR</span>
                    </label>
                    <label class="ecm-check-card" style="flex:0 0 auto; padding:7px 12px;" :class="{ checked: iniRecordTypes.request_bill }">
                        <input type="checkbox" x-model="iniRecordTypes.request_bill"> <span>Bill</span>
                    </label>
                    <label class="ecm-check-card" style="flex:0 0 auto; padding:7px 12px;" :class="{ checked: iniRecordTypes.request_chart }">
                        <input type="checkbox" x-model="iniRecordTypes.request_chart"> <span>Chart</span>
                    </label>
                    <label class="ecm-check-card" style="flex:0 0 auto; padding:7px 12px;" :class="{ checked: iniRecordTypes.request_img }">
                        <input type="checkbox" x-model="iniRecordTypes.request_img"> <span>Img</span>
                    </label>
                    <label class="ecm-check-card" style="flex:0 0 auto; padding:7px 12px;" :class="{ checked: iniRecordTypes.request_op }">
                        <input type="checkbox" x-model="iniRecordTypes.request_op"> <span>OP</span>
                    </label>
                </div>
            </div>

            <!-- Staff selection -->
            <div>
                <label class="ecm-label">Assign To <span class="ecm-req">*</span></label>
                <select x-model="iniSelectedStaff" class="ecm-select">
                    <option value="">Select staff member...</option>
                    <template x-for="s in staffList" :key="s.id">
                        <option :value="s.id" x-text="s.full_name"></option>
                    </template>
                </select>
            </div>

            <p style="font-size:11.5px; color:#8a8a82; margin:0;">
                30-day deadline will be set automatically. Cost Ledger and MBR will be updated.
            </p>
        </div>

        <!-- Footer -->
        <div class="ecm-footer">
            <button type="button" @click="showIniStaffModal = false" class="ecm-btn-cancel">Cancel</button>
            <button type="button" @click="confirmIniActivation()" :disabled="iniActivating || !iniSelectedStaff" class="ecm-btn-submit">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span x-text="iniActivating ? 'Activating...' : 'Activate'"></span>
            </button>
        </div>
    </div>
</div>
