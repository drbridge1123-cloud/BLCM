<!-- Treatment Complete Modal -->
<div x-show="showTxCompleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none;" @keydown.escape.window="showTxCompleteModal && (showTxCompleteModal = false)">
    <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showTxCompleteModal = false"></div>
    <div class="ecm relative z-10" style="width:400px;" @click.stop>

        <!-- Header -->
        <div class="ecm-header">
            <h3>Mark Treatment Complete</h3>
            <button type="button" class="ecm-close" @click="showTxCompleteModal = false">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="ecm-body">
            <!-- Provider name -->
            <div style="background:#f8f7f4; border-radius:7px; padding:10px 13px;">
                <div style="font-size:12.5px; color:#1a2535; display:flex; align-items:center; gap:6px;">
                    <svg width="14" height="14" fill="none" stroke="#C9A84C" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="txCompleteProvider?.provider_name" style="font-weight:500;"></span>
                </div>
            </div>

            <!-- Treatment Completed checkbox -->
            <label class="ecm-check-card" style="padding:10px 14px; cursor:pointer;" :class="{ checked: txCompleteChecked }">
                <input type="checkbox" x-model="txCompleteChecked">
                <span style="font-weight:600;">Treatment Completed</span>
            </label>

            <!-- Treatment End Date -->
            <div>
                <label class="ecm-label">Treatment End Date <span class="ecm-req">*</span></label>
                <input type="date" x-model="txCompleteDate" class="ecm-input"
                       :max="new Date().toISOString().split('T')[0]">
            </div>
        </div>

        <!-- Footer -->
        <div class="ecm-footer">
            <button type="button" @click="showTxCompleteModal = false" class="ecm-btn-cancel">Cancel</button>
            <button type="button" @click="saveTreatmentComplete()"
                    :disabled="txCompleteSaving || !txCompleteChecked || !txCompleteDate"
                    class="ecm-btn-submit">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span x-text="txCompleteSaving ? 'Saving...' : 'Confirm'"></span>
            </button>
        </div>
    </div>
</div>
