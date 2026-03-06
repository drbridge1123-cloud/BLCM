<!-- New Traffic Request Modal -->
<div x-show="showRequestModal" x-cloak class="sp-modal-overlay" @click.self="showRequestModal = false">
    <div class="sp-modal-box" @click.stop>
        <div class="sp-modal-header">
            <h3 class="sp-modal-title">New Traffic Request</h3>
            <button @click="showRequestModal = false" class="sp-modal-close">&times;</button>
        </div>
        <div class="sp-modal-body">
            <div class="sp-form-grid-2">
                <div>
                    <label class="sp-form-label">Client Name *</label>
                    <input type="text" x-model="requestForm.client_name" required class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label class="sp-form-label">Phone</label>
                    <input type="text" x-model="requestForm.client_phone" @blur="autoFormatPhone($el)" class="sp-search" style="width:100%;">
                </div>
            </div>
            <div class="sp-form-grid-2">
                <div>
                    <label class="sp-form-label">Court</label>
                    <input type="text" x-model="requestForm.court" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label class="sp-form-label">Court Date</label>
                    <input type="date" x-model="requestForm.court_date" class="sp-search" style="width:100%;">
                </div>
            </div>
            <div class="sp-form-grid-2">
                <div>
                    <label class="sp-form-label">Charge</label>
                    <input type="text" x-model="requestForm.charge" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label class="sp-form-label">Case #</label>
                    <input type="text" x-model="requestForm.case_number" class="sp-search" style="width:100%;">
                </div>
            </div>
            <div>
                <label class="sp-form-label">Note</label>
                <textarea x-model="requestForm.note" rows="2" class="sp-search" style="width:100%; resize:none;"></textarea>
            </div>
        </div>
        <div class="sp-modal-footer">
            <button @click="showRequestModal = false" class="sp-btn">Cancel</button>
            <button @click="submitRequest()" :disabled="saving" class="sp-new-btn-navy">
                <span x-text="saving ? 'Sending...' : 'Send Request'"></span>
            </button>
        </div>
    </div>
</div>
