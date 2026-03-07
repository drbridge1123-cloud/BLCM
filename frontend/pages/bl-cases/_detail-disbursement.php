<div class="disb-panel c1-section" data-panel x-data="disbursementPanel(caseId)">
    <!-- Header -->
    <div class="disb-header c1-section-header" :class="open && 'is-open'" @click="open = !open; if(open) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
        <div class="disb-header-left">
            <span class="c1-num c1-num-gold">08</span>
            <span class="disb-header-title">Settlement & Disbursement</span>
            <template x-if="calculated && (calculated.clientNet + (bestOffers['dv'] || 0)) !== 0">
                <span style="color:var(--gold); font-size:12px; font-family:'IBM Plex Mono',monospace; font-weight:600;"
                    x-text="'Client Net: $' + (calculated.clientNet + (bestOffers['dv'] || 0)).toLocaleString('en-US', {minimumFractionDigits:2})"></span>
            </template>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <button @click.stop="printDisbursement()" class="disb-print-btn">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
        </div>
    </div>

    <!-- Body -->
    <div x-show="open" x-collapse>
        <div class="disb-body">
            <template x-if="loading">
                <div style="text-align:center; padding:24px; color:var(--mbr-muted);">Loading...</div>
            </template>

            <template x-if="!loading">
                <div>
                    <!-- Summary Cards -->
                    <div class="disb-summary-grid">
                        <div class="disb-summary-card">
                            <div class="disb-summary-label">Settlement</div>
                            <div class="disb-summary-value" x-text="formatCurrency(calculated?.gross || 0)"></div>
                        </div>
                        <div class="disb-summary-card">
                            <div class="disb-summary-label">Attorney Fees</div>
                            <div class="disb-summary-value" style="color:var(--mbr-red);" x-text="formatCurrency(calculated?.fee || 0)"></div>
                        </div>
                        <div class="disb-summary-card">
                            <div class="disb-summary-label">Deductions</div>
                            <div class="disb-summary-value" style="color:var(--mbr-red);" x-text="formatCurrency(calculated?.totalDeductions || 0)"></div>
                        </div>
                        <div class="disb-summary-card highlight">
                            <div class="disb-summary-label">Client Net</div>
                            <div class="disb-summary-value" x-text="formatCurrency((calculated?.clientNet || 0) + (bestOffers['dv'] || 0))"></div>
                        </div>
                    </div>

                    <!-- Settings — single row -->
                    <div class="disb-settings">
                        <div class="disb-settings-group">
                            <span class="disb-settings-label">Fee:</span>
                            <button class="disb-fee-btn" :class="settings.attorney_fee_percent < 0.34 ? 'active' : ''"
                                @click="setFeePercent(1/3)">&#8531; 33.33%</button>
                            <button class="disb-fee-btn" :class="settings.attorney_fee_percent >= 0.34 ? 'active' : ''"
                                @click="setFeePercent(0.4)">40%</button>
                        </div>
                        <div class="disb-settings-group">
                            <span class="disb-settings-label">Coverage:</span>
                            <label class="disb-checkbox">
                                <input type="checkbox" x-model="settings.coverage_3rd_party" @change="onSettingsChange()">
                                3rd Party
                            </label>
                            <label class="disb-checkbox">
                                <input type="checkbox" x-model="settings.coverage_um" @change="onSettingsChange()">
                                UM
                            </label>
                            <label class="disb-checkbox">
                                <input type="checkbox" x-model="settings.coverage_uim" @change="onSettingsChange()">
                                UIM
                            </label>
                        </div>
                        <div class="disb-settings-group">
                            <span class="disb-settings-label">Limit:</span>
                            <label class="disb-checkbox">
                                <input type="checkbox" x-model="settings.policy_limit" @change="onSettingsChange()">
                                3rd Party
                            </label>
                            <label class="disb-checkbox">
                                <input type="checkbox" x-model="settings.um_uim_limit" @change="onSettingsChange()">
                                UM/UIM
                            </label>
                        </div>
                        <div class="disb-settings-group">
                            <span class="disb-settings-label">PIP:</span>
                            <input type="text" class="disb-amount-input" style="width:110px; background:#f4f3f0; cursor:default;"
                                :value="formatCurrency(settings.pip_subrogation_amount)" disabled
                                title="MBR PIP Paid Total">
                            <span style="font-size:12px; color:var(--mbr-muted);">via</span>
                            <input type="text" class="disb-amount-input" style="width:140px; background:#f4f3f0; cursor:default;"
                                :value="settings.pip_insurance_company || '-'" disabled
                                title="From Contacts PIP adjuster">
                        </div>
                    </div>

                    <!-- Two-column: Statement (left) + Methods (right) -->
                    <div class="disb-two-col">
                        <!-- LEFT: Disbursement Statement -->
                        <div class="disb-statement">
                            <div class="disb-statement-header">Disbursement Statement</div>
                            <table>
                                <template x-for="(line, idx) in disbursementLines" :key="idx">
                                    <tr :class="{
                                        'disb-section-row': line.section,
                                        'disb-total-row': line.isTotal,
                                    }">
                                        <td x-text="line.label" :style="line.indent ? 'padding-left:32px' : ''"></td>
                                        <td class="disb-amount" style="width:160px;"
                                            :class="{
                                                'negative': line.amount < 0 && !line.isTotal,
                                                'positive': line.amount > 0 && !line.section && !line.isTotal,
                                            }"
                                            x-text="line.section ? '' : formatCurrency(line.amount)">
                                        </td>
                                    </tr>
                                </template>
                            </table>
                        </div>

                        <!-- RIGHT: Method Cards stacked -->
                        <template x-if="showMahler() || showHamm()">
                            <div class="disb-method-stack">
                                <!-- Mahler: 3rd Party + PIP -->
                                <template x-if="showMahler()">
                                    <div class="disb-method-card" :class="settings.settlement_method === 'mahler' ? 'selected' : ''"
                                        @click="selectMethod('mahler')">
                                        <div class="disb-method-name">Mahler Formula</div>

                                        <!-- GROSS SETTLEMENT -->
                                        <div class="disb-method-detail" style="font-weight:700; text-transform:uppercase; font-size:11px; letter-spacing:0.3px; margin-top:6px;">
                                            <span>Gross Settlement</span>
                                            <span x-text="formatCurrency(mahlerCalc.gross)"></span>
                                        </div>

                                        <!-- LEGAL FEE & EXPENSES -->
                                        <div class="disb-method-detail" style="font-weight:700; text-transform:uppercase; font-size:11px; letter-spacing:0.3px; margin-top:8px;">
                                            <span>Legal Fee & Expenses</span>
                                            <span x-text="formatCurrency(mahlerCalc.afe)"></span>
                                        </div>
                                        <div class="disb-method-detail" style="padding-left:12px; font-size:11px; color:var(--mbr-muted);">
                                            <span x-text="'Attorney\'s Fee (' + (settings.attorney_fee_percent >= 0.34 ? '40' : '33.33') + '% of Gross)'"></span>
                                            <span x-text="formatCurrency(mahlerCalc.fee)"></span>
                                        </div>
                                        <template x-if="mahlerCalc.recordsFee > 0">
                                            <div class="disb-method-detail" style="padding-left:12px; font-size:11px; color:var(--mbr-muted);">
                                                <span>Records Fee</span>
                                                <span x-text="formatCurrency(mahlerCalc.recordsFee)"></span>
                                            </div>
                                        </template>
                                        <template x-if="mahlerCalc.litigationFee > 0">
                                            <div class="disb-method-detail" style="padding-left:12px; font-size:11px; color:var(--mbr-muted);">
                                                <span>Litigation Fee</span>
                                                <span x-text="formatCurrency(mahlerCalc.litigationFee)"></span>
                                            </div>
                                        </template>

                                        <!-- TOTAL PIP PAYMENT -->
                                        <div class="disb-method-detail" style="font-weight:700; text-transform:uppercase; font-size:11px; letter-spacing:0.3px; margin-top:8px;">
                                            <span x-text="'Total PIP Payment' + (settings.pip_insurance_company ? ' → ' + settings.pip_insurance_company : '')"></span>
                                            <span x-text="formatCurrency(mahlerCalc.pip)"></span>
                                        </div>
                                        <div class="disb-method-detail" style="padding-left:12px; font-size:11px; color:var(--mbr-muted);">
                                            <span>PIP Percentage of Gross</span>
                                            <span x-text="(mahlerCalc.pipPercent * 100).toFixed(2) + '%'"></span>
                                        </div>
                                        <div class="disb-method-detail" style="padding-left:12px; font-size:11px; color:var(--mbr-green);">
                                            <span>Client Credit for Attorney Fee & Cost</span>
                                            <span x-text="formatCurrency(mahlerCalc.clientCredit)"></span>
                                        </div>

                                        <!-- SUBROGATION LIEN AMOUNT = PIP - Client Credit -->
                                        <div class="disb-method-detail" style="font-weight:700; text-transform:uppercase; font-size:11px; letter-spacing:0.3px; margin-top:8px;">
                                            <span>Subrogation Lien Amount</span>
                                            <span x-text="formatCurrency(mahlerCalc.subrogationLien)"></span>
                                        </div>

                                        <!-- Bottom: Subrogation Lien + Print -->
                                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;padding-top:8px;border-top:2px solid var(--border);">
                                            <div class="disb-method-net" style="margin:0;padding:0;border:none; color:#C62828;"
                                                x-text="'Subrogation Lien: ' + formatCurrency(mahlerCalc.subrogationLien)"></div>
                                            <button @click.stop="printMethod('mahler')" class="disb-method-print" title="Print Mahler">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <!-- Hamm/Winters/Matsyuk -->
                                <template x-if="showHamm()">
                                    <div class="disb-method-card" :class="settings.settlement_method === 'hamm' ? 'selected' : ''"
                                        @click="selectMethod('hamm')">
                                        <div class="disb-method-name">Hamm/Winters/Matsyuk Formula</div>

                                        <!-- GROSS SETTLEMENT -->
                                        <div class="disb-method-detail" style="font-weight:700; text-transform:uppercase; font-size:11px; letter-spacing:0.3px; margin-top:6px;">
                                            <span>Gross Settlement (All Sources)</span>
                                            <span x-text="formatCurrency(hammCalc.gross)"></span>
                                        </div>

                                        <!-- LEGAL FEE & EXPENSES -->
                                        <div class="disb-method-detail" style="font-weight:700; text-transform:uppercase; font-size:11px; letter-spacing:0.3px; margin-top:8px;">
                                            <span>Legal Fee & Expenses</span>
                                            <span x-text="formatCurrency(hammCalc.afe)"></span>
                                        </div>
                                        <div class="disb-method-detail" style="padding-left:12px; font-size:11px; color:var(--mbr-muted);">
                                            <span x-text="'Attorney\'s Fee (' + (settings.attorney_fee_percent >= 0.34 ? '40' : '33.33') + '% of Gross)'"></span>
                                            <span x-text="formatCurrency(hammCalc.fee)"></span>
                                        </div>
                                        <template x-if="hammCalc.recordsFee > 0">
                                            <div class="disb-method-detail" style="padding-left:12px; font-size:11px; color:var(--mbr-muted);">
                                                <span>Records Fee</span>
                                                <span x-text="formatCurrency(hammCalc.recordsFee)"></span>
                                            </div>
                                        </template>
                                        <template x-if="hammCalc.litigationFee > 0">
                                            <div class="disb-method-detail" style="padding-left:12px; font-size:11px; color:var(--mbr-muted);">
                                                <span>Litigation Fee</span>
                                                <span x-text="formatCurrency(hammCalc.litigationFee)"></span>
                                            </div>
                                        </template>

                                        <!-- TOTAL PIP PAYMENT -->
                                        <div class="disb-method-detail" style="font-weight:700; text-transform:uppercase; font-size:11px; letter-spacing:0.3px; margin-top:8px;">
                                            <span x-text="'Total PIP Payment' + (settings.pip_insurance_company ? ' → ' + settings.pip_insurance_company : '')"></span>
                                            <span x-text="formatCurrency(hammCalc.pip)"></span>
                                        </div>
                                        <div class="disb-method-detail" style="padding-left:12px; font-size:11px; color:var(--mbr-muted);">
                                            <span>PIP / Gross Settlement Ratio</span>
                                            <span x-text="(hammCalc.pipRatio * 100).toFixed(2) + '%'"></span>
                                        </div>
                                        <div class="disb-method-detail" style="padding-left:12px; font-size:11px; color:var(--mbr-green);">
                                            <span>Client Credit for Attorney Fee & Cost</span>
                                            <span x-text="formatCurrency(hammCalc.clientCredit)"></span>
                                        </div>

                                        <!-- Client Credit -->
                                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;padding-top:8px;border-top:2px solid var(--border);">
                                            <div class="disb-method-net" style="margin:0;padding:0;border:none; color:#C62828;"
                                                x-text="'Client Credit: ' + formatCurrency(hammCalc.clientCredit)"></div>
                                            <button @click.stop="printMethod('hamm')" class="disb-method-print" title="Print Hamm">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
