<!-- Top Offer Modal -->
<div x-show="showTopOfferModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45);" @click.self="showTopOfferModal = false">
    <div style="background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22); width:100%; max-width:480px; max-height:90vh; overflow:hidden; display:flex; flex-direction:column;" @click.stop>
        <div style="background:#0F1B2D; padding:18px 24px; flex-shrink:0;">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">Submit Top Offer</h3>
            <div style="font-size:12px; color:#C9A84C; margin-top:4px;">
                <span x-text="settlingCase?.case_number" style="font-weight:600;"></span> — <span x-text="settlingCase?.client_name"></span>
            </div>
        </div>
        <div style="padding:24px; overflow-y:auto; display:flex; flex-direction:column; gap:16px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Top Offer Amount ($) *</label>
                    <input type="number" x-model.number="topOfferForm.top_offer_amount" step="0.01" min="0" required class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Assign To</label>
                    <select x-model="topOfferForm.assignee_id" class="sp-select" style="width:100%;">
                        <option value="">Select...</option>
                        <template x-for="u in users" :key="u.id"><option :value="u.id" x-text="u.name"></option></template>
                    </select>
                </div>
            </div>
            <div>
                <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Note</label>
                <textarea x-model="topOfferForm.note" rows="3" placeholder="Optional note..." class="sp-search" style="width:100%; resize:none;"></textarea>
            </div>
        </div>
        <div style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
            <button @click="showTopOfferModal = false" class="sp-btn">Cancel</button>
            <button @click="submitTopOffer()" class="sp-new-btn-navy">Submit Top Offer</button>
        </div>
    </div>
</div>
