<!-- Settle Litigation Modal -->
<div x-show="showSettleLitModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45);" @click.self="showSettleLitModal = false">
    <div style="background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22); width:100%; max-width:640px; max-height:90vh; overflow:hidden; display:flex; flex-direction:column;" @click.stop>
        <div style="background:#0F1B2D; padding:18px 24px; flex-shrink:0;">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">Settle Litigation Case</h3>
            <div style="font-size:12px; color:#C9A84C; margin-top:4px;">
                <span x-text="settlingCase?.case_number" style="font-weight:600;"></span> — <span x-text="settlingCase?.client_name"></span>
                <span style="margin-left:8px; color:rgba(255,255,255,.5);">Pre-Suit Offer: <span style="color:#fff; font-weight:600;" x-text="'$' + (settlingCase?.presuit_offer || 0).toLocaleString()"></span></span>
            </div>
        </div>
        <div style="padding:24px; overflow-y:auto; display:flex; flex-direction:column; gap:16px;">
            <!-- Commission mode: full form -->
            <template x-if="hasCommission">
                <div style="display:flex; flex-direction:column; gap:16px;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Resolution Type *</label>
                            <select x-model="settleLitForm.resolution_type" class="sp-select" style="width:100%;">
                                <option value="">-- Select --</option>
                                <optgroup label="33.33% (Pre-Suit Deducted)">
                                    <option value="No Offer Settle">No Offer Settle</option>
                                    <option value="File and Bump">File and Bump</option>
                                    <option value="Post Deposition Settle">Post Deposition Settle</option>
                                    <option value="Mediation">Mediation</option>
                                    <option value="Settled Post Arbitration">Settled Post Arbitration</option>
                                    <option value="Settlement Conference">Settlement Conference</option>
                                </optgroup>
                                <optgroup label="40% (No Deduction)">
                                    <option value="Arbitration Award">Arbitration Award</option>
                                    <option value="Beasley">Beasley</option>
                                </optgroup>
                                <optgroup label="Variable (Manual)">
                                    <option value="Co-Counsel">Co-Counsel</option>
                                    <option value="Other">Other</option>
                                </optgroup>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Settled Amount ($) *</label>
                            <input type="number" x-model.number="settleLitForm.settled" step="0.01" min="0" required class="sp-search" style="width:100%;">
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;" x-show="settleLitForm.resolution_type">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Difference</label>
                            <div style="padding:8px 12px; background:#fafaf8; border:1px solid #e8e4dc; border-radius:8px; font-size:13px; font-family:'IBM Plex Mono',monospace; color:#1a2535;"
                                 x-text="'$' + getLitDifference().toLocaleString(undefined, {minimumFractionDigits: 2})"></div>
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Fee Rate</label>
                            <div style="padding:8px 12px; background:#fafaf8; border:1px solid #e8e4dc; border-radius:8px; font-size:13px; font-family:'IBM Plex Mono',monospace; font-weight:600; color:#1a2535;"
                                 x-text="getLitFeeRate(settleLitForm.resolution_type) != null ? getLitFeeRate(settleLitForm.resolution_type) + '%' : 'Manual'"></div>
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Comm. Rate</label>
                            <div style="padding:8px 12px; background:#fafaf8; border:1px solid #e8e4dc; border-radius:8px; font-size:13px; font-family:'IBM Plex Mono',monospace; font-weight:600; color:#1a2535;"
                                 x-text="getLitCommRate() + '%'"></div>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Disc. Legal Fee ($) *</label>
                            <input type="number" x-model.number="settleLitForm.discounted_legal_fee" step="0.01" min="0" required class="sp-search" style="width:100%;">
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Commission</label>
                            <div style="padding:8px 12px; background:rgba(21,128,61,.06); border:1px solid rgba(21,128,61,.2); border-radius:8px; font-size:13px; font-family:'IBM Plex Mono',monospace; font-weight:700; color:#15803d;"
                                 x-text="'$' + calcLitCommission(settleLitForm.discounted_legal_fee, getLitCommRate()).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></div>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;" x-show="isVariableType(settleLitForm.resolution_type)">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Manual Fee Rate (%)</label>
                            <input type="number" x-model.number="settleLitForm.manual_fee_rate" step="0.01" min="0" max="100" class="sp-search" style="width:100%;">
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Manual Commission Rate (%)</label>
                            <input type="number" x-model.number="settleLitForm.manual_commission_rate" step="0.01" min="0" max="100" class="sp-search" style="width:100%;">
                        </div>
                    </div>
                    <div x-show="settleLitForm.resolution_type && !isVariableType(settleLitForm.resolution_type)">
                        <label style="display:flex; align-items:center; gap:6px; font-size:13px; color:#ea580c; font-weight:500; cursor:pointer;">
                            <input type="checkbox" x-model="settleLitForm.fee_rate_override" style="accent-color:#ea580c;"> Override fee rate
                        </label>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Month</label>
                            <input type="text" x-model="settleLitForm.month" placeholder="e.g. Feb. 2026" class="sp-search" style="width:100%;">
                        </div>
                        <div x-show="settleLitForm.fee_rate_override || isVariableType(settleLitForm.resolution_type)">
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Note *</label>
                            <input type="text" x-model="settleLitForm.note" placeholder="Required when overriding" class="sp-search" style="width:100%;">
                        </div>
                    </div>
                </div>
            </template>
            <!-- No commission mode: simple form -->
            <template x-if="!hasCommission">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Settled Amount ($) *</label>
                    <input type="number" x-model.number="settleLitForm.settled" step="0.01" min="0" required class="sp-search" style="width:100%;">
                </div>
            </template>
            <div style="display:flex; align-items:center; gap:20px;">
                <label style="display:flex; align-items:center; gap:6px; font-size:13px; color:#1a2535; cursor:pointer;">
                    <input type="checkbox" x-model="settleLitForm.check_received" style="accent-color:#C9A84C;"> Check Received
                </label>
                <label style="display:flex; align-items:center; gap:6px; font-size:13px; color:#7c3aed; font-weight:500; cursor:pointer;">
                    <input type="checkbox" x-model="settleLitForm.is_policy_limit" style="accent-color:#7c3aed;"> Policy Limit &rarr; UIM
                </label>
            </div>
        </div>
        <div style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
            <button @click="showSettleLitModal = false" class="sp-btn">Cancel</button>
            <button @click="settleLitigation()" class="sp-new-btn-navy">Settle Case</button>
        </div>
    </div>
</div>
