<!-- Create/Edit Traffic Case Modal -->
<div x-show="showCaseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45);" @click.self="showCaseModal = false">
    <div style="background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22); width:100%; max-width:600px; max-height:90vh; overflow:hidden; display:flex; flex-direction:column;" @click.stop>
        <div style="background:#0F1B2D; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;" x-text="caseForm.id ? 'Edit Traffic Case' : 'New Traffic Case'"></h3>
            <button @click="showCaseModal = false" style="background:none; border:none; color:rgba(255,255,255,.4); cursor:pointer; font-size:20px;">&times;</button>
        </div>
        <div style="padding:24px; overflow-y:auto; display:flex; flex-direction:column; gap:16px;">
            <!-- Client Info -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Client Name *</label>
                    <input type="text" x-model="caseForm.client_name" required class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Phone</label>
                    <input type="text" x-model="caseForm.client_phone" class="sp-search" style="width:100%;">
                </div>
            </div>
            <!-- Case Details -->
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Court</label>
                    <input type="text" x-model="caseForm.court" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Court Date</label>
                    <input type="date" x-model="caseForm.court_date" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Charge</label>
                    <input type="text" x-model="caseForm.charge" class="sp-search" style="width:100%;">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Case #</label>
                    <input type="text" x-model="caseForm.case_number" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Citation Issued</label>
                    <input type="date" x-model="caseForm.citation_issued_date" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">NOA Sent</label>
                    <input type="date" x-model="caseForm.noa_sent_date" class="sp-search" style="width:100%;">
                </div>
            </div>
            <div>
                <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Prosecutor Offer</label>
                <input type="text" x-model="caseForm.prosecutor_offer" class="sp-search" style="width:100%;">
            </div>
            <!-- Resolution -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Disposition</label>
                    <select x-model="caseForm.disposition" class="sp-select" style="width:100%;">
                        <option value="pending">Pending</option><option value="dismissed">Dismissed ($150)</option><option value="amended">Amended ($100)</option><option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Commission</label>
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
                <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Note</label>
                <textarea x-model="caseForm.note" rows="2" class="sp-search" style="width:100%; resize:none;"></textarea>
            </div>
        </div>
        <div style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
            <button @click="showCaseModal = false" class="sp-btn">Cancel</button>
            <button @click="saveCase()" :disabled="saving" class="sp-new-btn-navy">
                <span x-text="saving ? 'Saving...' : (caseForm.id ? 'Save Changes' : 'Create Case')"></span>
            </button>
        </div>
    </div>
</div>
