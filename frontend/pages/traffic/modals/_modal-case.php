<!-- Create/Edit Traffic Case Modal -->
<div x-show="showCaseModal" x-cloak class="sp-modal-overlay" @click.self="showCaseModal = false">
    <div class="sp-modal-box" @click.stop>
        <div class="sp-modal-header">
            <h3 class="sp-modal-title" x-text="caseForm.id ? 'Edit Traffic Case' : 'New Traffic Case'"></h3>
            <button @click="showCaseModal = false" class="sp-modal-close">&times;</button>
        </div>
        <div class="sp-modal-body">
            <!-- Client Info -->
            <div class="sp-form-grid-2">
                <div>
                    <label class="sp-form-label">Client Name *</label>
                    <input type="text" x-model="caseForm.client_name" required class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label class="sp-form-label">Phone</label>
                    <input type="text" x-model="caseForm.client_phone" @blur="autoFormatPhone($el)" class="sp-search" style="width:100%;">
                </div>
            </div>
            <!-- Case Details -->
            <div class="sp-form-grid-3">
                <div>
                    <label class="sp-form-label">Court</label>
                    <input type="text" x-model="caseForm.court" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label class="sp-form-label">Court Date</label>
                    <input type="date" x-model="caseForm.court_date" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label class="sp-form-label">Charge</label>
                    <input type="text" x-model="caseForm.charge" class="sp-search" style="width:100%;">
                </div>
            </div>
            <div class="sp-form-grid-3">
                <div>
                    <label class="sp-form-label">Case #</label>
                    <input type="text" x-model="caseForm.case_number" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label class="sp-form-label">Citation Issued</label>
                    <input type="date" x-model="caseForm.citation_issued_date" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label class="sp-form-label">NOA Sent</label>
                    <input type="date" x-model="caseForm.noa_sent_date" class="sp-search" style="width:100%;">
                </div>
            </div>
            <div>
                <label class="sp-form-label">Prosecutor Offer</label>
                <input type="text" x-model="caseForm.prosecutor_offer" class="sp-search" style="width:100%;">
            </div>
            <!-- Resolution -->
            <div class="sp-form-grid-2">
                <div>
                    <label class="sp-form-label">Disposition</label>
                    <select x-model="caseForm.disposition" class="sp-select" style="width:100%;">
                        <option value="pending">Pending</option><option value="dismissed">Dismissed ($150)</option><option value="amended">Amended ($100)</option><option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="sp-form-label">Commission</label>
                    <div style="padding:8px 12px; background:rgba(21,128,61,.06); border:1px solid rgba(21,128,61,.2); border-radius:8px; font-size:13px; font-family:'IBM Plex Mono',monospace; font-weight:700; color:#15803d;"
                         x-text="'$' + getCommissionAmount(caseForm.disposition)"></div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:20px;">
                <label style="display:flex; align-items:center; gap:6px; font-size:13px; color:#1a2535; cursor:pointer;">
                    <input type="checkbox" x-model="caseForm.discovery" style="accent-color:#C9A84C;"> Discovery Received
                </label>
            </div>
            <div>
                <label class="sp-form-label">Note</label>
                <textarea x-model="caseForm.note" rows="2" class="sp-search" style="width:100%; resize:none;"></textarea>
            </div>
        </div>
        <div class="sp-modal-footer">
            <button @click="showCaseModal = false" class="sp-btn">Cancel</button>
            <button @click="saveCase()" :disabled="saving" class="sp-new-btn-navy">
                <span x-text="saving ? 'Saving...' : (caseForm.id ? 'Save Changes' : 'Create Case')"></span>
            </button>
        </div>
    </div>
</div>
