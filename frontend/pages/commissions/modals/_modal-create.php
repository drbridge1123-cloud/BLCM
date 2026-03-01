<!-- Create Commission Modal -->
<div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45);" @click.self="showCreateModal = false">
    <div style="background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22); width:100%; max-width:540px; max-height:90vh; overflow:hidden; display:flex; flex-direction:column;" @click.stop>
        <div style="background:#0F1B2D; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">Add Commission</h3>
            <button @click="showCreateModal = false" style="background:none; border:none; color:rgba(255,255,255,.4); cursor:pointer; font-size:20px;">&times;</button>
        </div>
        <div style="padding:24px; overflow-y:auto; display:flex; flex-direction:column; gap:16px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Case Number *</label>
                    <input type="text" x-model="createForm.case_number" required class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Client Name *</label>
                    <input type="text" x-model="createForm.client_name" required class="sp-search" style="width:100%;">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Case Type</label>
                    <select x-model="createForm.case_type" class="sp-select" style="width:100%;">
                        <option value="Auto">Auto</option><option value="Slip & Fall">Slip & Fall</option><option value="Other">Other</option>
                    </select>
                </div>
                <div x-show="isAdmin">
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Employee</label>
                    <select x-model="createForm.employee_user_id" class="sp-select" style="width:100%;">
                        <option value="">-- Select --</option>
                        <template x-for="u in employees" :key="u.id"><option :value="u.id" x-text="u.full_name"></option></template>
                    </select>
                </div>
            </div>
            <!-- Settlement Section -->
            <div style="padding-top:16px; border-top:1px solid #e8e4dc;">
                <div style="font-size:9.5px; font-weight:700; color:#C9A84C; text-transform:uppercase; letter-spacing:.08em; margin-bottom:12px;">Settlement Details</div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Settled ($)</label>
                        <input type="number" x-model.number="createForm.settled" step="0.01" min="0" class="sp-search" style="width:100%;">
                    </div>
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Pre-Suit Offer ($)</label>
                        <input type="number" x-model.number="createForm.presuit_offer" step="0.01" min="0" class="sp-search" style="width:100%;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:12px;">
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Fee Rate (%)</label>
                        <select x-model="createForm.fee_rate" class="sp-select" style="width:100%;">
                            <option value="33.33">33.33%</option><option value="40">40%</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Month</label>
                        <input type="text" x-model="createForm.month" placeholder="e.g. Feb. 2025" class="sp-search" style="width:100%;">
                    </div>
                </div>
            </div>
            <!-- Options -->
            <div style="display:flex; align-items:center; gap:20px;">
                <label style="display:flex; align-items:center; gap:6px; font-size:13px; color:#1a2535; cursor:pointer;">
                    <input type="checkbox" x-model="createForm.is_marketing" style="accent-color:#C9A84C;"> Marketing (5%)
                </label>
                <label style="display:flex; align-items:center; gap:6px; font-size:13px; color:#1a2535; cursor:pointer;">
                    <input type="checkbox" x-model="createForm.check_received" style="accent-color:#C9A84C;"> Check Received
                </label>
            </div>
            <div>
                <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Note</label>
                <textarea x-model="createForm.note" rows="2" class="sp-search" style="width:100%; resize:none;"></textarea>
            </div>
        </div>
        <div style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
            <button @click="showCreateModal = false" class="sp-btn">Cancel</button>
            <button @click="createCommission()" :disabled="saving" class="sp-new-btn-navy">
                <span x-text="saving ? 'Saving...' : 'Add Commission'"></span>
            </button>
        </div>
    </div>
</div>
