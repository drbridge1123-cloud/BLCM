<!-- Settle Demand Modal -->
<div x-show="showSettleDemandModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45);" @click.self="showSettleDemandModal = false">
    <div style="background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22); width:100%; max-width:520px; max-height:90vh; overflow:hidden; display:flex; flex-direction:column;" @click.stop>
        <div style="background:#0F1B2D; padding:18px 24px; flex-shrink:0;">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">Settle Demand Case</h3>
            <div style="font-size:12px; color:#C9A84C; margin-top:4px;">
                <span x-text="settlingCase?.case_number" style="font-weight:600;"></span> — <span x-text="settlingCase?.client_name"></span>
            </div>
        </div>
        <div style="padding:24px; overflow-y:auto; display:flex; flex-direction:column; gap:16px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div :style="hasCommission ? '' : 'grid-column:span 2;'">
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Settled Amount ($) *</label>
                    <input type="number" x-model.number="settleForm.settled" step="0.01" min="0" required class="sp-search" style="width:100%;">
                </div>
                <div x-show="hasCommission">
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Legal Fee (33.33%)</label>
                    <div style="padding:8px 12px; background:#fafaf8; border:1px solid #e8e4dc; border-radius:8px; font-size:13px; font-family:'IBM Plex Mono',monospace; color:#1a2535;"
                         x-text="'$' + ((settleForm.settled || 0) / 3).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></div>
                </div>
            </div>
            <template x-if="hasCommission">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Disc. Legal Fee ($) *</label>
                        <input type="number" x-model.number="settleForm.discounted_legal_fee" step="0.01" min="0" required class="sp-search" style="width:100%;">
                    </div>
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Commission (5%)</label>
                        <div style="padding:8px 12px; background:rgba(21,128,61,.06); border:1px solid rgba(21,128,61,.2); border-radius:8px; font-size:13px; font-family:'IBM Plex Mono',monospace; font-weight:700; color:#15803d;"
                             x-text="'$' + ((settleForm.discounted_legal_fee || 0) * 0.05).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></div>
                    </div>
                </div>
            </template>
            <template x-if="hasCommission">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Month</label>
                    <input type="text" x-model="settleForm.month" placeholder="e.g. Feb. 2025" class="sp-search" style="width:100%;">
                </div>
            </template>
            <div style="display:flex; align-items:center; gap:20px;">
                <label style="display:flex; align-items:center; gap:6px; font-size:13px; color:#1a2535; cursor:pointer;">
                    <input type="checkbox" x-model="settleForm.check_received" style="accent-color:#C9A84C;"> Check Received
                </label>
                <label style="display:flex; align-items:center; gap:6px; font-size:13px; color:#7c3aed; font-weight:500; cursor:pointer;">
                    <input type="checkbox" x-model="settleForm.is_policy_limit" style="accent-color:#7c3aed;"> Policy Limit &rarr; Move to UIM
                </label>
            </div>
        </div>
        <div style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
            <button @click="showSettleDemandModal = false" class="sp-btn">Cancel</button>
            <button @click="settleDemand()" class="sp-new-btn-navy">Settle Case</button>
        </div>
    </div>
</div>
