<!-- INI Staff Assignment Modal -->
<div x-show="showIniStaffModal" class="sp-modal-overlay"
     style="display:none;" @keydown.escape.window="showIniStaffModal && (showIniStaffModal = false)">
    <div class="fixed inset-0" @click="showIniStaffModal = false"></div>
    <div class="sp-modal-box sp-modal-box-sm relative z-10" @click.stop>

        <!-- Header -->
        <div class="sp-modal-header">
            <h3 class="sp-modal-title">Activate Provider for Requesting</h3>
            <button type="button" class="sp-modal-close" @click="showIniStaffModal = false">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="sp-modal-body">
            <!-- Providers list -->
            <div style="background:#f8f7f4; border-radius:7px; padding:10px 13px;">
                <p class="sp-form-label" style="margin:0 0 6px;">
                    Providers to activate
                </p>
                <template x-for="p in providers.filter(p => iniProviderIds.length > 0 ? iniProviderIds.includes(p.id) : ['treating','treatment_complete'].includes(p.overall_status))" :key="p.id">
                    <div style="font-size:12.5px; color:#1a2535; padding:3px 0; display:flex; align-items:center; gap:6px;">
                        <svg width="14" height="14" fill="none" stroke="#C9A84C" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="p.provider_name"></span>
                    </div>
                </template>
            </div>

            <!-- Record Types -->
            <div>
                <label class="sp-form-label">Record Types to Request</label>
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
                </div>
            </div>

            <!-- Staff selection -->
            <div>
                <label class="sp-form-label">Assign To <span class="ecm-req">*</span></label>
                <select x-model="iniSelectedStaff" class="ecm-select">
                    <option value="">Select staff member...</option>
                    <template x-for="s in staffList" :key="s.id">
                        <option :value="s.id" x-text="s.display_name || s.full_name"></option>
                    </template>
                </select>
            </div>

            <!-- Case Status -->
            <div>
                <label class="sp-form-label">Case Status</label>
                <select x-model="iniChangeStatus" class="ecm-select">
                    <option value="">Keep current status</option>
                    <option value="ini">1. Treatment</option>
                    <option value="rec">2. Records</option>
                    <option value="verification">3. Verification</option>
                    <option value="rfd">4. Demand</option>
                    <option value="neg">5. Negotiate</option>
                    <option value="lit">6. Litigation</option>
                    <option value="fbc">7. Final Review</option>
                    <option value="accounting">8. Settlement</option>
                </select>
                <p x-show="iniChangeStatus && iniChangeStatus !== caseData?.status" style="font-size:11px; color:#C9A84C; margin:4px 0 0;">
                    Status will change from <strong x-text="getPipelineLabel()"></strong> → <strong x-text="{ini:'1. Treatment',rec:'2. Records',verification:'3. Verification',rfd:'4. Demand',neg:'5. Negotiate',lit:'6. Litigation',fbc:'7. Final Review',accounting:'8. Settlement'}[iniChangeStatus]"></strong>
                </p>
            </div>

            <!-- Notes -->
            <div>
                <label class="sp-form-label">Notes</label>
                <textarea x-model="iniNotes" class="ecm-input" rows="2" style="resize:none;" placeholder="Optional notes..."></textarea>
            </div>

            <p style="font-size:11.5px; color:#8a8a82; margin:0;">
                30-day deadline will be set automatically. Cost Ledger and MBR will be updated.
            </p>
        </div>

        <!-- Footer -->
        <div class="sp-modal-footer">
            <button type="button" @click="showIniStaffModal = false" class="ecm-btn-cancel">Cancel</button>
            <button type="button" @click="confirmIniActivation()" :disabled="iniActivating || !iniSelectedStaff" class="ecm-btn-submit">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span x-text="iniActivating ? 'Activating...' : 'Activate'"></span>
            </button>
        </div>
    </div>
</div>
