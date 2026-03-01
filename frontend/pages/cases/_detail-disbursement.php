<div class="disb-panel c1-section" data-panel x-data="disbursementPanel(caseId)" x-init="init()">
    <!-- Header -->
    <div class="disb-header c1-section-header" :class="open && 'is-open'" @click="open = !open; if(open) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
        <div class="disb-header-left">
            <span class="c1-num c1-num-gold">08</span>
            <span class="disb-header-title">Settlement & Disbursement</span>
            <template x-if="calculated && calculated.clientNet !== 0">
                <span style="color:var(--gold); font-size:12px; font-family:'IBM Plex Mono',monospace; font-weight:600;"
                    x-text="'Client Net: $' + calculated.clientNet.toLocaleString('en-US', {minimumFractionDigits:2})"></span>
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
                <div style="text-align:center; padding:24px; color:var(--mbds-muted);">Loading...</div>
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
                            <div class="disb-summary-value" style="color:var(--mbds-red);" x-text="formatCurrency(calculated?.fee || 0)"></div>
                        </div>
                        <div class="disb-summary-card">
                            <div class="disb-summary-label">Deductions</div>
                            <div class="disb-summary-value" style="color:var(--mbds-red);" x-text="formatCurrency(calculated?.totalDeductions || 0)"></div>
                        </div>
                        <div class="disb-summary-card highlight">
                            <div class="disb-summary-label">Client Net</div>
                            <div class="disb-summary-value" x-text="formatCurrency(calculated?.clientNet || 0)"></div>
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
                            <input type="number" step="0.01" class="disb-amount-input" style="width:110px;"
                                x-model.number="settings.pip_subrogation_amount"
                                @change="onSettingsChange()" placeholder="0.00">
                            <span style="font-size:12px; color:var(--mbds-muted);">via</span>
                            <input type="text" class="disb-amount-input" style="width:140px;"
                                x-model="settings.pip_insurance_company"
                                @change="onSettingsChange()" :placeholder="pipInfo?.pip1_name || 'Insurance Co.'">
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
                                        <div class="disb-method-name">Mahler Method</div>
                                        <div class="disb-method-desc">3rd Party + PIP Subrogation</div>
                                        <div class="disb-method-detail">
                                            <span>Gross (3rd Party)</span>
                                            <span x-text="formatCurrency(mahlerCalc.gross)"></span>
                                        </div>
                                        <div class="disb-method-detail">
                                            <span>Attorney Fee</span>
                                            <span x-text="'-' + formatCurrency(mahlerCalc.fee)"></span>
                                        </div>
                                        <div class="disb-method-detail">
                                            <span>Costs</span>
                                            <span x-text="'-' + formatCurrency(mahlerCalc.costs)"></span>
                                        </div>
                                        <div class="disb-method-detail">
                                            <span>Carrier Share</span>
                                            <span x-text="mahlerCalc.carrierShare > 0 ? '-' + formatCurrency(mahlerCalc.carrierShare) : 'Waived'"></span>
                                        </div>
                                        <div class="disb-method-detail">
                                            <span>Medical Balance</span>
                                            <span x-text="'-' + formatCurrency(mahlerCalc.medicalBalance)"></span>
                                        </div>
                                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;padding-top:8px;border-top:2px solid var(--border);">
                                            <div class="disb-method-net" style="margin:0;padding:0;border:none;"
                                                :style="mahlerCalc.clientNet >= 0 ? 'color:var(--mbds-green)' : 'color:var(--mbds-red)'"
                                                x-text="'Client Net: ' + formatCurrency(mahlerCalc.clientNet)"></div>
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
                                        <div class="disb-method-name">Hamm Method</div>
                                        <div class="disb-method-desc">Hamm/Winters/Matsyuk Formula</div>
                                        <div class="disb-method-detail">
                                            <span>Gross Settlement</span>
                                            <span x-text="formatCurrency(hammCalc.gross)"></span>
                                        </div>
                                        <div class="disb-method-detail">
                                            <span>Attorney Fee + Costs</span>
                                            <span x-text="'-' + formatCurrency(hammCalc.afe)"></span>
                                        </div>
                                        <div class="disb-method-detail">
                                            <span>Total PIP Payment</span>
                                            <span x-text="'-' + formatCurrency(hammCalc.pip)"></span>
                                        </div>
                                        <div class="disb-method-detail" style="font-size:11px; color:var(--mbds-muted);">
                                            <span x-text="'PIP Ratio: ' + (hammCalc.pipRatio * 100).toFixed(2) + '%'"></span>
                                        </div>
                                        <div class="disb-method-detail" style="color:var(--mbds-green);">
                                            <span>Client Credit (Hamm Fee)</span>
                                            <span x-text="'+' + formatCurrency(hammCalc.clientCredit)"></span>
                                        </div>
                                        <div class="disb-method-detail">
                                            <span>Medical Balance</span>
                                            <span x-text="'-' + formatCurrency(hammCalc.medicalBalance)"></span>
                                        </div>
                                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;padding-top:8px;border-top:2px solid var(--border);">
                                            <div class="disb-method-net" style="margin:0;padding:0;border:none;"
                                                :style="hammCalc.clientNet >= 0 ? 'color:var(--mbds-green)' : 'color:var(--mbds-red)'"
                                                x-text="'Client Net: ' + formatCurrency(hammCalc.clientNet)"></div>
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
