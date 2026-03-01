<div class="neg-panel c1-section" data-panel x-data="negotiatePanel(caseId)" x-init="init()">
    <!-- Header -->
    <div class="neg-header c1-section-header" :class="open && 'is-open'" @click="open = !open; if(open) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
        <div class="neg-header-left">
            <span class="c1-num c1-num-gold">07</span>
            <span class="neg-header-title">Negotiate</span>
            <template x-if="activeCoverages.length > 0">
                <span class="neg-header-badge" x-text="activeCoverages.length + ' Coverage'"></span>
            </template>
            <template x-if="getTotalBestOffer() > 0">
                <span style="color:var(--gold); font-size:12px; font-family:'IBM Plex Mono',monospace; font-weight:600;"
                    x-text="'Best: $' + getTotalBestOffer().toLocaleString('en-US', {minimumFractionDigits:2})"></span>
            </template>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
        </div>
    </div>

    <!-- Body -->
    <div x-show="open" x-collapse>
        <div class="neg-body">
            <template x-if="loading">
                <div style="text-align:center; padding:24px; color:var(--mbds-muted);">Loading...</div>
            </template>

            <template x-if="!loading">
                <div>
                    <!-- Coverage type tabs -->
                    <div class="neg-coverage-tabs">
                        <template x-for="type in ['3rd_party','um','uim','dv']" :key="type">
                            <div class="neg-coverage-tab"
                                :class="activeCoverage === type ? 'active' : ''"
                                @click="activeCoverage = type">
                                <span x-text="getCoverageLabel(type)"></span>
                                <span class="tab-count" x-text="'(' + (coverageNegotiations[type]?.length || 0) + ')'"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Adjuster Info Card (per coverage) -->
                    <div class="neg-adjuster-card">
                        <div class="neg-adjuster-labels">
                            <div class="neg-adj-label">Insurance</div>
                            <div class="neg-adj-label">Adjuster Name</div>
                            <div class="neg-adj-label">Phone</div>
                            <div class="neg-adj-label">Fax</div>
                            <div class="neg-adj-label">Email</div>
                            <div class="neg-adj-label">Claim #</div>
                        </div>
                        <div class="neg-adjuster-inputs">
                            <input type="text" class="neg-adj-input" x-model="adjusterInfo[activeCoverage].insurance_company"
                                @input="saveAdjusterInfo()" placeholder="e.g. State Farm">
                            <input type="text" class="neg-adj-input" x-model="adjusterInfo[activeCoverage].party"
                                @input="saveAdjusterInfo()" placeholder="e.g. John Smith">
                            <input type="tel" class="neg-adj-input" x-model="adjusterInfo[activeCoverage].adjuster_phone"
                                @input="saveAdjusterInfo()" @blur="formatPhone('adjuster_phone')" placeholder="(555) 123-4567">
                            <input type="tel" class="neg-adj-input" x-model="adjusterInfo[activeCoverage].adjuster_fax"
                                @input="saveAdjusterInfo()" @blur="formatPhone('adjuster_fax')" placeholder="(555) 123-4567">
                            <input type="email" class="neg-adj-input" x-model="adjusterInfo[activeCoverage].adjuster_email"
                                @input="saveAdjusterInfo()" @blur="formatEmail()" placeholder="adjuster@insurance.com">
                            <input type="text" class="neg-adj-input" x-model="adjusterInfo[activeCoverage].claim_number"
                                @input="saveAdjusterInfo()" placeholder="CLM-123456">
                        </div>
                    </div>

                    <!-- Inline rounds table (always visible) -->
                    <div>
                        <table class="neg-rounds-table">
                            <thead>
                                <tr>
                                    <th style="width:40px">#</th>
                                    <th style="width:140px">Demand Date</th>
                                    <th style="width:140px; text-align:right">Demand</th>
                                    <th style="width:140px">Offer Date</th>
                                    <th style="width:140px; text-align:right">Offer</th>
                                    <th style="width:120px">Status</th>
                                    <th>Notes</th>
                                    <th style="width:60px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Existing rounds — inline editable -->
                                <template x-for="round in coverageNegotiations[activeCoverage]" :key="round.id">
                                    <tr>
                                        <td style="color:var(--mbds-muted); font-weight:600;" x-text="round.round_number"></td>
                                        <td><input type="date" class="neg-inline-input" x-model="round.demand_date" @change="inlineSaveRound(round)"></td>
                                        <td><input type="text" class="neg-inline-input neg-inline-money"
                                            :value="formatCurrency(round.demand_amount)"
                                            @focus="$event.target.value = round.demand_amount || ''"
                                            @blur="round.demand_amount = parseCurrency($event.target.value); $event.target.value = formatCurrency(round.demand_amount); inlineSaveRound(round)"
                                            placeholder="$0.00"></td>
                                        <td><input type="date" class="neg-inline-input" x-model="round.offer_date" @change="inlineSaveRound(round)"></td>
                                        <td><input type="text" class="neg-inline-input neg-inline-money"
                                            :value="formatCurrency(round.offer_amount)"
                                            @focus="$event.target.value = round.offer_amount || ''"
                                            @blur="round.offer_amount = parseCurrency($event.target.value); $event.target.value = formatCurrency(round.offer_amount); inlineSaveRound(round)"
                                            placeholder="$0.00"></td>
                                        <td>
                                            <select class="neg-inline-input" x-model="round.status" @change="inlineSaveRound(round)">
                                                <option value="pending">Pending</option>
                                                <option value="countered">Countered</option>
                                                <option value="accepted">Accepted</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="neg-inline-input" x-model="round.notes" @change="inlineSaveRound(round)" placeholder="Notes..."></td>
                                        <td style="text-align:center;">
                                            <button @click="deleteRound(round)" class="neg-action-btn neg-action-btn-del" title="Delete">
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <!-- New round row -->
                                <tr style="background:#fdfdfb;">
                                    <td style="color:var(--gold); font-weight:700;">+</td>
                                    <td><input type="date" class="neg-inline-input" x-model="roundForm.demand_date"></td>
                                    <td><input type="text" class="neg-inline-input neg-inline-money"
                                        :value="formatCurrency(roundForm.demand_amount)"
                                        @focus="$event.target.value = roundForm.demand_amount || ''"
                                        @blur="roundForm.demand_amount = parseCurrency($event.target.value); $event.target.value = formatCurrency(roundForm.demand_amount); autoFillDate(roundForm)"
                                        placeholder="$0.00"></td>
                                    <td><input type="date" class="neg-inline-input" x-model="roundForm.offer_date"></td>
                                    <td><input type="text" class="neg-inline-input neg-inline-money"
                                        :value="formatCurrency(roundForm.offer_amount)"
                                        @focus="$event.target.value = roundForm.offer_amount || ''"
                                        @blur="roundForm.offer_amount = parseCurrency($event.target.value); $event.target.value = formatCurrency(roundForm.offer_amount); autoFillDate(roundForm)"
                                        placeholder="$0.00"></td>
                                    <td>
                                        <select class="neg-inline-input" x-model="roundForm.status">
                                            <option value="pending">Pending</option>
                                            <option value="countered">Countered</option>
                                            <option value="accepted">Accepted</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="neg-inline-input" x-model="roundForm.notes" placeholder="Notes..."
                                        @keydown.enter="saveRound()"></td>
                                    <td style="text-align:center;">
                                        <button @click="saveRound()" class="neg-action-btn neg-action-btn-add" title="Add">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Best offer card -->
                        <template x-if="bestOffers[activeCoverage] > 0">
                            <div class="neg-best-offer">
                                <span class="neg-best-label">Best Offer — <span x-text="getCoverageLabel(activeCoverage)"></span></span>
                                <span class="neg-best-amount" x-text="'$' + bestOffers[activeCoverage].toLocaleString('en-US', {minimumFractionDigits:2})"></span>
                            </div>
                        </template>
                    </div>

                </div>
            </template>
        </div>

        <!-- Note Viewer Modal -->
        <template x-if="viewingNote">
            <div style="position:fixed; inset:0; z-index:9999; display:flex; align-items:center; justify-content:center;"
                 @click.self="viewingNote = null" @keydown.escape.window="viewingNote = null">
                <div style="position:fixed; inset:0; background:rgba(0,0,0,0.4);"></div>
                <div style="position:relative; background:#fff; border-radius:10px; box-shadow:0 8px 32px rgba(0,0,0,0.18); width:480px; max-width:90vw; max-height:70vh; display:flex; flex-direction:column;">
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-bottom:1px solid #d0cdc5;">
                        <span style="font-weight:700; font-size:14px; color:var(--navy);">
                            Round <span x-text="viewingNote.round"></span> Note
                            <span style="font-weight:400; color:var(--mbds-muted); font-size:12px; margin-left:6px;" x-text="'(' + viewingNote.coverage + ')'"></span>
                        </span>
                        <button @click="viewingNote = null" style="background:none; border:none; cursor:pointer; color:#999; font-size:20px; line-height:1;">&times;</button>
                    </div>
                    <div style="padding:18px; overflow-y:auto; font-size:14px; line-height:1.6; color:var(--mbds-text); white-space:pre-wrap; word-break:break-word;"
                         x-text="viewingNote.text"></div>
                </div>
            </div>
        </template>
    </div>
</div>
