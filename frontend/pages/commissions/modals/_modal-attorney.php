<!-- Attorney Commission Detail Modal -->
<div x-show="showAttorneyModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45);" @click.self="showAttorneyModal = false">
    <div style="background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22); width:100%; max-width:580px; max-height:90vh; overflow:hidden; display:flex; flex-direction:column;" @click.stop>

        <!-- Header -->
        <div style="background:#0F1B2D; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">Attorney Commission Detail</h3>
            <button @click="showAttorneyModal = false" style="background:none; border:none; color:rgba(255,255,255,.4); cursor:pointer; font-size:20px;">&times;</button>
        </div>

        <!-- Body -->
        <div style="padding:24px; overflow-y:auto; display:flex; flex-direction:column; gap:16px;">

            <!-- Case Info (read-only) -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Case Number</label>
                    <div style="padding:8px 12px; background:#fafaf8; border:1px solid #e8e4dc; border-radius:8px; font-size:13px; font-weight:600; color:#1a2535;" x-text="attForm.case_number"></div>
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Client Name</label>
                    <div style="padding:8px 12px; background:#fafaf8; border:1px solid #e8e4dc; border-radius:8px; font-size:13px; color:#1a2535;" x-text="attForm.client_name"></div>
                </div>
            </div>

            <!-- Phase + Resolution Type -->
            <div style="display:flex; align-items:center; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Phase</label>
                    <span class="sp-phase" :class="attForm._phaseClass" x-text="attForm._phaseLabel" style="display:inline-block;"></span>
                </div>
                <template x-if="attForm.resolution_type">
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Resolution Type</label>
                        <div style="padding:5px 10px; background:#f0eee8; border-radius:6px; font-size:12px; color:#1a2535; font-weight:500;" x-text="attForm.resolution_type"></div>
                    </div>
                </template>
                <div style="margin-left:auto;">
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Commission Rate</label>
                    <div style="padding:5px 10px; background:rgba(26,158,106,.08); border-radius:6px; font-size:12px; color:#1a9e6a; font-weight:700;" x-text="attForm._commRate + '%'"></div>
                </div>
            </div>

            <!-- ===== SETTLEMENT CALCULATION ===== -->
            <div style="padding:16px; background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px;">
                <div style="font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:12px;">Settlement Calculation</div>

                <!-- Row 1: Settled + Pre-Suit Offer (if litigation 33%) -->
                <div style="display:grid; gap:12px;" :style="attForm._showPresuit ? 'grid-template-columns:1fr 1fr' : 'grid-template-columns:1fr 1fr'">
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Settled ($)</label>
                        <input type="number" x-model.number="attForm.settled" @input="recalcAttComm()" step="0.01" min="0" class="sp-search" style="width:100%;">
                    </div>
                    <template x-if="attForm._showPresuit">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Pre-Suit Offer ($)</label>
                            <div style="padding:8px 12px; background:#fff; border:1px solid #e8e4dc; border-radius:8px; font-family:'IBM Plex Mono',monospace; font-size:13px; color:#1a2535;" x-text="'$' + fmt(attForm.presuit_offer)"></div>
                        </div>
                    </template>
                    <template x-if="!attForm._showPresuit">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Fee Rate</label>
                            <div style="padding:8px 12px; background:#fff; border:1px solid #e8e4dc; border-radius:8px; font-family:'IBM Plex Mono',monospace; font-size:13px; color:#1a2535;" x-text="attForm.fee_rate + '%'"></div>
                        </div>
                    </template>
                </div>

                <!-- Row 2: Difference + Fee Rate (only for presuit cases) -->
                <template x-if="attForm._showPresuit">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:12px;">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Difference</label>
                            <div style="padding:8px 12px; background:#fff; border:1px solid #e8e4dc; border-radius:8px; font-family:'IBM Plex Mono',monospace; font-size:13px; color:#1a2535;" x-text="'$' + fmt(attCalcDifference())"></div>
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Fee Rate</label>
                            <div style="padding:8px 12px; background:#fff; border:1px solid #e8e4dc; border-radius:8px; font-family:'IBM Plex Mono',monospace; font-size:13px; color:#1a2535;" x-text="attForm.fee_rate + '%'"></div>
                        </div>
                    </div>
                </template>

                <!-- Calculation flow arrow -->
                <div style="text-align:center; color:#c4c0b6; font-size:11px; margin:8px 0;">&#9660;</div>

                <!-- Row 3: Legal Fee → Disc. Legal Fee → Commission -->
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Legal Fee</label>
                        <div style="padding:8px 10px; background:#fff; border:1px solid #e8e4dc; border-radius:8px; font-family:'IBM Plex Mono',monospace; font-size:12px; color:#1a2535;" x-text="'$' + fmt(attCalcLegalFee())"></div>
                    </div>
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Disc. Legal Fee</label>
                        <input type="number" x-model.number="attForm.discounted_legal_fee" @input="recalcAttCommFromDLF()" step="0.01" min="0" class="sp-search" style="width:100%; font-size:12px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; color:#C9A84C; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Commission</label>
                        <div style="padding:8px 10px; background:rgba(26,158,106,.06); border:1px solid rgba(26,158,106,.2); border-radius:8px; font-family:'IBM Plex Mono',monospace; font-size:12px; font-weight:700; color:#1a9e6a;" x-text="'$' + fmt(attForm.commission)"></div>
                    </div>
                </div>

                <!-- Formula description -->
                <div style="margin-top:8px; font-size:10px; color:#a8a49c; font-style:italic;" x-text="attForm._formulaDesc"></div>
            </div>

            <!-- ===== UIM SECTION ===== -->
            <template x-if="attForm._hasUim">
                <div style="padding:16px; background:rgba(99,102,241,.03); border:1px solid rgba(99,102,241,.15); border-radius:10px;">
                    <div style="font-size:9.5px; font-weight:700; color:#6366f1; text-transform:uppercase; letter-spacing:.08em; margin-bottom:12px;">UIM Settlement (5%)</div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">UIM Settled ($)</label>
                            <input type="number" x-model.number="attForm.uim_settled" @input="recalcUimComm()" step="0.01" min="0" class="sp-search" style="width:100%;">
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">UIM Legal Fee</label>
                            <div style="padding:8px 12px; background:#fff; border:1px solid rgba(99,102,241,.12); border-radius:8px; font-family:'IBM Plex Mono',monospace; font-size:13px; color:#1a2535;" x-text="'$' + fmt(attCalcUimLegalFee())"></div>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:12px;">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">UIM Disc. Legal Fee</label>
                            <input type="number" x-model.number="attForm.uim_discounted_legal_fee" @input="recalcUimCommFromDLF()" step="0.01" min="0" class="sp-search" style="width:100%;">
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#C9A84C; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">UIM Commission</label>
                            <div style="padding:8px 12px; background:rgba(99,102,241,.06); border:1px solid rgba(99,102,241,.15); border-radius:8px; font-family:'IBM Plex Mono',monospace; font-size:13px; font-weight:700; color:#6366f1;" x-text="'$' + fmt(attForm.uim_commission)"></div>
                        </div>
                    </div>

                    <div style="margin-top:8px; font-size:10px; color:#a8a49c; font-style:italic;">UIM Disc. Legal Fee × 5% = UIM Commission</div>
                </div>
            </template>

            <!-- Total Commission -->
            <div style="padding:14px 16px; background:rgba(26,158,106,.06); border:1px solid rgba(26,158,106,.2); border-radius:10px; display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:9.5px; font-weight:700; color:rgba(26,158,106,.7); text-transform:uppercase; letter-spacing:.08em;">Total Commission</span>
                <span style="font-size:20px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#1a9e6a;" x-text="'$' + fmt((attForm.commission || 0) + (attForm.uim_commission || 0))"></span>
            </div>

            <!-- Management Fields -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Month</label>
                    <input type="text" x-model="attForm.month" placeholder="e.g. Feb. 2026" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Status</label>
                    <select x-model="attForm.status" class="sp-select" style="width:100%;">
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:20px;">
                <label style="display:flex; align-items:center; gap:6px; font-size:13px; color:#1a2535; cursor:pointer;">
                    <input type="checkbox" x-model="attForm.check_received" style="accent-color:#C9A84C;"> Check Received
                </label>
            </div>

            <div>
                <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Note</label>
                <textarea x-model="attForm.note" rows="2" class="sp-search" style="width:100%; resize:none;"></textarea>
            </div>

        </div>

        <!-- Footer -->
        <div style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
            <button @click="showAttorneyModal = false" class="sp-btn">Cancel</button>
            <button @click="saveAttorneyCase()" :disabled="saving" class="sp-new-btn-navy">
                <span x-text="saving ? 'Saving...' : 'Save Changes'"></span>
            </button>
        </div>

    </div>
</div>
