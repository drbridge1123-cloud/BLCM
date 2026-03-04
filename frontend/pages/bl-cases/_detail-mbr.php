            <!-- MBR Report Section -->
            <div class="mbr-panel c1-section" data-panel x-data="mbrPanel(caseId)" x-init="init()">

                <!-- Report Header Bar -->
                <div class="mbr-header c1-section-header" :class="mbrOpen && 'is-open'" @click="mbrOpen = !mbrOpen; if(mbrOpen) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
                    <div class="mbr-header-left">
                        <span class="c1-num c1-num-gold">06</span>
                        <span class="mbr-title">Medical Balance Report</span>
                        <template x-if="report">
                            <span class="mbr-badge"
                                :class="'mbr-badge-' + report.status"
                                x-text="getMbrStatusLabel(report.status)"></span>
                        </template>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <template x-if="report">
                            <button @click.stop="printMbr()" class="panel-btn-navy">
                                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Print
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Collapsible Body -->
                <div x-show="mbrOpen" x-collapse>

                    <!-- Loading -->
                    <template x-if="loading">
                        <div class="mbr-loading">
                            <div class="spinner"></div>
                        </div>
                    </template>

                    <template x-if="!loading && report">
                        <div>

                            <!-- Insurance Settings -->
                            <div class="mbr-section-label">
                                <svg fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 0L12 4L8 8L4 4Z" transform="translate(0,4)"/>
                                </svg>
                                <span>Insurance Settings</span>
                            </div>
                            <div class="mbr-insurance-body">
                                <div class="mbr-insurance-grid">
                                    <?php
                                    $insFields = [
                                        ['field' => 'pip1_name',    'label' => 'PIP #1',    'placeholder' => 'Search auto insurance...'],
                                        ['field' => 'pip2_name',    'label' => 'PIP #2',    'placeholder' => 'Optional...'],
                                        ['field' => 'health1_name', 'label' => 'Health #1', 'placeholder' => 'Search health insurance...'],
                                        ['field' => 'health2_name', 'label' => 'Health #2', 'placeholder' => 'Optional...'],
                                        ['field' => 'health3_name', 'label' => 'Health #3', 'placeholder' => 'Optional...'],
                                    ];
                                    foreach ($insFields as $f): ?>
                                    <div style="position:relative">
                                        <label class="mbr-field-label"><?= $f['label'] ?></label>
                                        <!-- Display value (click to open autocomplete) -->
                                        <div x-show="insAutoField !== '<?= $f['field'] ?>'" class="mbr-ins-display"
                                            :class="report?.status !== 'draft' ? 'disabled' : ''"
                                            @click="report?.status === 'draft' && openInsAuto('<?= $f['field'] ?>')">
                                            <span x-text="settings.<?= $f['field'] ?> || '<?= $f['placeholder'] ?>'"
                                                :style="!settings.<?= $f['field'] ?> ? 'color:#b0afa8' : ''"></span>
                                            <template x-if="settings.<?= $f['field'] ?> && report?.status === 'draft'">
                                                <button type="button" class="mbr-ins-clear" @click.stop="clearInsField('<?= $f['field'] ?>')" title="Clear">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </template>
                                        </div>
                                        <!-- Autocomplete input (shown when field is active) -->
                                        <div x-show="insAutoField === '<?= $f['field'] ?>'" style="position:relative" x-transition>
                                            <input type="text" id="ins-auto-input-<?= $f['field'] ?>"
                                                x-model="insAutoQuery"
                                                @input="searchInsurance()"
                                                @blur="closeInsAuto()"
                                                @keydown.escape="insAutoField = null"
                                                placeholder="Type to search..."
                                                class="mbr-field-input"
                                                style="padding-right:28px">
                                            <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);width:14px;height:14px;pointer-events:none;color:#b0afa8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            <!-- Results dropdown -->
                                            <div x-show="insAutoField === '<?= $f['field'] ?>' && (insAutoResults.length > 0 || insAutoQuery.length >= 1)"
                                                class="mbr-ins-dropdown">
                                                <template x-for="c in insAutoResults" :key="c.id">
                                                    <button type="button" @mousedown.prevent="selectInsurance(c)" class="mbr-ins-item">
                                                        <span x-text="c.name" style="font-weight:500"></span>
                                                        <span x-text="(c.type || '').replace(/_/g,' ')" class="mbr-ins-type-badge"></span>
                                                    </button>
                                                </template>
                                                <template x-if="insAutoQuery.length >= 1 && insAutoResults.length === 0">
                                                    <div class="mbr-ins-empty">No match found</div>
                                                </template>
                                                <button type="button" @mousedown.prevent="openInsQuickAdd()" class="mbr-ins-add-btn">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    Add new insurance company
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mbr-checkbox-row">
                                    <label class="mbr-checkbox-label" :class="settings.has_wage_loss ? 'checked' : ''">
                                        <input type="checkbox" x-model="settings.has_wage_loss" @change="saveSettings()"
                                            class="mbr-checkbox" :disabled="report?.status !== 'draft'">
                                        Wage Loss
                                    </label>
                                    <label class="mbr-checkbox-label" :class="settings.has_essential_service ? 'checked' : ''">
                                        <input type="checkbox" x-model="settings.has_essential_service" @change="saveSettings()"
                                            class="mbr-checkbox" :disabled="report?.status !== 'draft'">
                                        Essential Service
                                    </label>
                                    <label class="mbr-checkbox-label" :class="settings.has_health_subrogation ? 'checked' : ''">
                                        <input type="checkbox" x-model="settings.has_health_subrogation" @change="saveSettings()"
                                            class="mbr-checkbox" :disabled="report?.status !== 'draft'">
                                        Health Subrogation #1
                                    </label>
                                    <label class="mbr-checkbox-label" :class="settings.has_health_subrogation2 ? 'checked' : ''">
                                        <input type="checkbox" x-model="settings.has_health_subrogation2" @change="saveSettings()"
                                            class="mbr-checkbox" :disabled="report?.status !== 'draft'">
                                        Health Subrogation #2
                                    </label>
                                </div>
                            </div>

                            <!-- MBR Table -->
                            <div class="mbr-table-wrap">
                                <table class="mbr-table">
                                    <thead>
                                        <tr class="mbr-col-head">
                                            <th class="mbr-th-provider">Provider</th>
                                            <th class="mbr-th-r mbr-th-amount">Charges</th>
                                            <th class="mbr-th-r mbr-th-amount" x-show="settings.pip1_name" x-text="settings.pip1_name"></th>
                                            <th class="mbr-th-r mbr-th-amount" x-show="settings.pip2_name" x-text="settings.pip2_name"></th>
                                            <th class="mbr-th-r mbr-th-amount" x-show="settings.health1_name" x-text="settings.health1_name"></th>
                                            <th class="mbr-th-r mbr-th-amount" x-show="settings.health2_name" x-text="settings.health2_name"></th>
                                            <th class="mbr-th-r mbr-th-amount" x-show="settings.health3_name" x-text="settings.health3_name"></th>
                                            <th class="mbr-th-r mbr-th-amount">Discount</th>
                                            <th class="mbr-th-r mbr-th-amount">Office Paid</th>
                                            <th class="mbr-th-r mbr-th-amount">Client Paid</th>
                                            <th class="mbr-th-balance mbr-th-amount">Balance</th>
                                            <th class="mbr-th-c mbr-th-dates">Dates</th>
                                            <th class="mbr-th-c mbr-th-visits">Visits</th>
                                            <th class="mbr-th-note">Note</th>
                                            <th class="mbr-th-action" x-show="report?.status === 'draft'"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="row in displayRows" :key="row._key">
                                            <tr :class="row._type === 'header' ? 'mbr-sec-row' : 'mbr-data-row'">

                                                <!-- Category Header -->
                                                <td x-show="row._type === 'header'" :colspan="totalCols">
                                                    <span x-text="row.label"></span>
                                                </td>

                                                <!-- Provider Name -->
                                                <td x-show="row._type === 'line'">
                                                    <span class="mbr-provider-name" x-text="row.provider_name"></span>
                                                </td>

                                                <!-- Charges -->
                                                <td x-show="row._type === 'line'" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'charges', row.charges)"
                                                        @focus="startCellEdit($el, row._lineRef, 'charges')"
                                                        @blur="endCellEdit($el, row._lineRef, 'charges')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbr-cell-input"
                                                        :class="(Number(row.charges) || 0) === 0 ? 'mbr-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- PIP #1 -->
                                                <td x-show="row._type === 'line' && settings.pip1_name" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'pip1_amount', row.pip1_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'pip1_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'pip1_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbr-cell-input"
                                                        :class="(Number(row.pip1_amount) || 0) === 0 ? 'mbr-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>
                                                <!-- PIP #2 -->
                                                <td x-show="row._type === 'line' && settings.pip2_name" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'pip2_amount', row.pip2_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'pip2_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'pip2_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbr-cell-input"
                                                        :class="(Number(row.pip2_amount) || 0) === 0 ? 'mbr-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>
                                                <!-- Health #1 -->
                                                <td x-show="row._type === 'line' && settings.health1_name" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'health1_amount', row.health1_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'health1_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'health1_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbr-cell-input"
                                                        :class="(Number(row.health1_amount) || 0) === 0 ? 'mbr-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>
                                                <!-- Health #2 -->
                                                <td x-show="row._type === 'line' && settings.health2_name" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'health2_amount', row.health2_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'health2_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'health2_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbr-cell-input"
                                                        :class="(Number(row.health2_amount) || 0) === 0 ? 'mbr-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>
                                                <!-- Health #3 -->
                                                <td x-show="row._type === 'line' && settings.health3_name" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'health3_amount', row.health3_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'health3_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'health3_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbr-cell-input"
                                                        :class="(Number(row.health3_amount) || 0) === 0 ? 'mbr-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Discount -->
                                                <td x-show="row._type === 'line'" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'discount', row.discount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'discount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'discount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbr-cell-input"
                                                        :class="(Number(row.discount) || 0) === 0 ? 'mbr-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Office Paid -->
                                                <td x-show="row._type === 'line'" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'office_paid', row.office_paid)"
                                                        @focus="startCellEdit($el, row._lineRef, 'office_paid')"
                                                        @blur="endCellEdit($el, row._lineRef, 'office_paid')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbr-cell-input"
                                                        :class="(Number(row.office_paid) || 0) === 0 ? 'mbr-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Client Paid -->
                                                <td x-show="row._type === 'line'" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'client_paid', row.client_paid)"
                                                        @focus="startCellEdit($el, row._lineRef, 'client_paid')"
                                                        @blur="endCellEdit($el, row._lineRef, 'client_paid')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbr-cell-input"
                                                        :class="(Number(row.client_paid) || 0) === 0 ? 'mbr-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Balance -->
                                                <td x-show="row._type === 'line'" style="padding:6px;text-align:right">
                                                    <span class="mbr-balance"
                                                        :class="balanceColor(calcBalance(row))"
                                                        x-text="formatCurrency(calcBalance(row))">
                                                    </span>
                                                </td>

                                                <!-- Treatment Dates -->
                                                <td x-show="row._type === 'line'" style="padding:4px 1px">
                                                    <input type="text" :value="row.treatment_dates || ''"
                                                        @input="formatDateInput($event, row._lineRef)"
                                                        @change="saveLine(row._lineRef)"
                                                        placeholder="MM/DD/YY–MM/DD/YY"
                                                        class="mbr-date-input"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Visits -->
                                                <td x-show="row._type === 'line'" style="padding:4px 1px">
                                                    <input type="text" x-model="row._lineRef.visits"
                                                        @change="saveLine(row._lineRef)"
                                                        class="mbr-visits-input"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Note -->
                                                <td x-show="row._type === 'line'" style="padding:4px 3px">
                                                    <div @click="openNote($event, row.id)"
                                                        class="mbr-note-trigger"
                                                        :class="row.note ? 'has-note' : ''"
                                                        :title="row.note || ''"
                                                        x-text="row.note || '—'">
                                                    </div>
                                                </td>

                                                <!-- Actions: Delete -->
                                                <td x-show="row._type === 'line' && report?.status === 'draft'" class="mbr-td-action">
                                                    <button @click="deleteLine(row._lineRef)" class="mbr-delete-btn">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr class="mbr-total-row">
                                            <td class="mbr-total-label">TOTAL</td>
                                            <td class="mbr-total-r" x-text="formatCurrency(totals.charges)"></td>
                                            <td class="mbr-total-r" x-show="settings.pip1_name" x-text="formatCurrency(totals.pip1)"></td>
                                            <td class="mbr-total-r" x-show="settings.pip2_name" x-text="formatCurrency(totals.pip2)"></td>
                                            <td class="mbr-total-r" x-show="settings.health1_name" x-text="formatCurrency(totals.health1)"></td>
                                            <td class="mbr-total-r" x-show="settings.health2_name" x-text="formatCurrency(totals.health2)"></td>
                                            <td class="mbr-total-r" x-show="settings.health3_name" x-text="formatCurrency(totals.health3)"></td>
                                            <td class="mbr-total-r" x-text="formatCurrency(totals.discount)"></td>
                                            <td class="mbr-total-r" x-text="formatCurrency(totals.officePaid)"></td>
                                            <td class="mbr-total-r" x-text="formatCurrency(totals.clientPaid)"></td>
                                            <td class="mbr-total-r mbr-total-balance" x-text="formatCurrency(totals.balance)"></td>
                                            <td colspan="4" style="background:var(--navy)"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Bottom Bar — Notes + Actions -->
                            <div class="mbr-bottom-bar">
                                <div style="flex:1">
                                    <div class="mbr-notes-label">Report Notes</div>
                                    <textarea x-model="settings.notes" @change="saveSettings()"
                                        class="mbr-notes-textarea"
                                        placeholder="General notes about this report..."
                                        :disabled="report?.status !== 'draft'"></textarea>
                                </div>
                                <div style="display:flex;gap:8px;align-items:flex-end;padding-bottom:2px;position:relative">
                                    <template x-if="report?.status === 'draft'">
                                        <div style="display:flex;gap:8px">
                                            <button @click="addLine('rx')" class="mbr-btn-ghost">+ Add RX</button>
                                            <button @click="addLine('provider')" class="mbr-btn-ghost">+ Add Provider</button>
                                            <button @click="markComplete()" class="mbr-btn-gold">Mark Complete</button>
                                        </div>
                                    </template>

                                    <!-- Provider Search Dropdown -->
                                    <div x-show="showProviderSearch" @click.outside="showProviderSearch = false" @keydown.escape.window="showProviderSearch = false"
                                        class="mbr-provider-search" x-transition>
                                        <div class="mbr-ps-header">
                                            <input type="text" id="mbr-provider-search-input"
                                                x-model="providerSearchQuery"
                                                @input="searchProviders()"
                                                placeholder="Search provider name..."
                                                class="mbr-ps-input"
                                                @keydown.escape="showProviderSearch = false">
                                        </div>
                                        <div class="mbr-ps-results">
                                            <template x-if="providerSearchQuery.length > 0 && providerSearchResults.length === 0">
                                                <div class="mbr-ps-empty">No providers found</div>
                                            </template>
                                            <template x-for="p in providerSearchResults" :key="p.id">
                                                <button @click="selectProvider(p)" class="mbr-ps-item">
                                                    <span class="mbr-ps-name" x-text="p.name"></span>
                                                    <span class="mbr-ps-type" x-text="(p.type || '').replace(/_/g,' ')"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <template x-if="report?.status === 'completed'">
                                        <div style="display:flex;gap:8px">
                                            <button @click="reopenDraft()" class="mbr-btn-outline-red">Reopen as Draft</button>
                                            <button @click="approveReport()" class="mbr-btn-green">Approve & Close</button>
                                        </div>
                                    </template>
                                    <template x-if="report?.status === 'approved'">
                                        <span style="font-size:12px;color:var(--mbr-green);font-weight:500;padding:8px 0">
                                            Approved by <span x-text="report.approved_by_name"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Note popover -->
                            <template x-for="row in displayRows.filter(r => r._type === 'line')" :key="'mbr_notepop_' + row._key">
                                <div x-show="expandedNote === row.id"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    @click.outside="expandedNote = null"
                                    class="mbr-note-popover"
                                    :style="{ top: notePopoverPos.top, right: notePopoverPos.right }"
                                    @click.stop>
                                    <div class="mbr-note-popover-header">
                                        <span>Note</span>
                                        <button @click="expandedNote = null" style="background:none;border:none;cursor:pointer;color:var(--mbr-muted);padding:2px">
                                            <svg style="width:12px;height:12px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div style="padding:8px">
                                        <textarea x-model="row._lineRef.note"
                                            @input="debounceSaveLine(row._lineRef)"
                                            rows="4"
                                            placeholder="Add note..."
                                            :disabled="report?.status !== 'draft'"></textarea>
                                    </div>
                                </div>
                            </template>

                            <!-- Saving indicator -->
                            <div x-show="saving" x-transition class="mbr-saving">
                                <div class="mbr-saving-dot"></div>
                                Saving...
                            </div>
                        </div>
                    </template>

                    <!-- No report fallback -->
                    <template x-if="!loading && !report">
                        <div style="text-align:center;color:var(--mbr-muted);padding:32px 0;font-size:13px">Failed to load Medical Balance report</div>
                    </template>
                </div>

                <!-- Insurance Quick Add Modal (Full) -->
                <div x-show="showInsQuickAdd" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;"
                     @keydown.escape.window="showInsQuickAdd && (showInsQuickAdd = false)">
                    <div class="fixed inset-0" style="background:rgba(0,0,0,.45)" @click="showInsQuickAdd = false"></div>
                    <form @submit.prevent="saveInsQuickAdd()" class="icm relative z-10" @click.stop style="display:flex; flex-direction:column; max-height:90vh;">
                        <div class="icm-header" style="flex-shrink:0;">
                            <div>
                                <h3>New Insurance Company</h3>
                                <p class="icm-subtitle">Add a new insurance company</p>
                            </div>
                            <button type="button" class="icm-close" @click="showInsQuickAdd = false">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="icm-body" style="flex:1; min-height:0;">
                            <div class="icm-section"><span>Basic Info</span></div>
                            <div style="display:flex; gap:12px;">
                                <div style="flex:1;">
                                    <label class="icm-label">Company Name <span class="icm-req">*</span></label>
                                    <input type="text" x-model="insNewCompany.name" required class="icm-input">
                                </div>
                                <div style="flex:1;">
                                    <label class="icm-label">Type <span class="icm-req">*</span></label>
                                    <select x-model="insNewCompany.type" required class="icm-select">
                                        <option value="auto">Auto</option>
                                        <option value="health">Health</option>
                                        <option value="workers_comp">Worker's Comp</option>
                                        <option value="liability">Liability</option>
                                        <option value="um_uim">UM/UIM</option>
                                        <option value="government">Government</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="icm-section"><span>Contact</span></div>
                            <div style="display:flex; gap:12px;">
                                <div style="flex:1;">
                                    <label class="icm-label">Phone</label>
                                    <input type="text" x-model="insNewCompany.phone" class="icm-input">
                                </div>
                                <div style="flex:1;">
                                    <label class="icm-label">Fax</label>
                                    <input type="text" x-model="insNewCompany.fax" class="icm-input">
                                </div>
                                <div style="flex:1;">
                                    <label class="icm-label">Email</label>
                                    <input type="email" x-model="insNewCompany.email" class="icm-input">
                                </div>
                            </div>

                            <div class="icm-section"><span>Address</span></div>
                            <div>
                                <label class="icm-label">Street Address</label>
                                <input type="text" x-model="insNewCompany.address" class="icm-input">
                            </div>
                            <div style="display:flex; gap:12px;">
                                <div style="flex:3;">
                                    <label class="icm-label">City</label>
                                    <input type="text" x-model="insNewCompany.city" class="icm-input">
                                </div>
                                <div style="flex:1;">
                                    <label class="icm-label">State</label>
                                    <input type="text" x-model="insNewCompany.state" maxlength="2" class="icm-input" style="text-transform:uppercase;">
                                </div>
                                <div style="flex:1.5;">
                                    <label class="icm-label">ZIP</label>
                                    <input type="text" x-model="insNewCompany.zip" maxlength="10" class="icm-input">
                                </div>
                            </div>

                            <div class="icm-section"><span>Other</span></div>
                            <div>
                                <label class="icm-label">Website</label>
                                <input type="url" x-model="insNewCompany.website" class="icm-input" placeholder="https://...">
                            </div>
                            <div>
                                <label class="icm-label">Notes</label>
                                <textarea x-model="insNewCompany.notes" class="icm-textarea" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                        <div class="icm-footer" style="flex-shrink:0;">
                            <button type="button" @click="showInsQuickAdd = false" class="icm-btn-cancel">Cancel</button>
                            <button type="submit" :disabled="insQuickAddSaving" class="icm-btn-submit">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span x-text="insQuickAddSaving ? 'Saving...' : '+ Create'"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Insurance modal + autocomplete styles -->
                <style>
                    /* ICM (Insurance Company Modal) — reused from Database page */
                    .icm { width: 560px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
                    .icm-header { background: #0F1B2D; padding: 18px 24px; display: flex; align-items: flex-start; justify-content: space-between; }
                    .icm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; }
                    .icm-subtitle { font-size: 12px; font-weight: 500; color: var(--gold, #C9A84C); margin: 2px 0 0; }
                    .icm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
                    .icm-close:hover { color: rgba(255,255,255,.75); }
                    .icm-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; max-height: 70vh; overflow-y: auto; }
                    .icm-body::-webkit-scrollbar { width: 4px; }
                    .icm-body::-webkit-scrollbar-track { background: transparent; }
                    .icm-body::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
                    .icm-section { display: flex; align-items: center; gap: 10px; margin: 0; }
                    .icm-section::before, .icm-section::after { content: ''; flex: 1; height: 1px; background: var(--border, #d0cdc5); }
                    .icm-section span { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .1em; white-space: nowrap; }
                    .icm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
                    .icm-req { color: var(--gold, #C9A84C); }
                    .icm-input, .icm-select, .icm-textarea {
                        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
                        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
                    }
                    .icm-input:focus, .icm-select:focus, .icm-textarea:focus {
                        border-color: var(--gold, #C9A84C); background: #fff;
                        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
                    }
                    .icm-select {
                        appearance: none; cursor: pointer; padding-right: 30px;
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
                        background-repeat: no-repeat; background-position: right 10px center;
                    }
                    .icm-textarea { resize: vertical; min-height: 70px; line-height: 1.5; }
                    .icm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
                    .icm-btn-cancel {
                        background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
                        padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
                    }
                    .icm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
                    .icm-btn-submit {
                        background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px;
                        padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
                        box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s;
                    }
                    .icm-btn-submit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
                    .icm-btn-submit:disabled { opacity: .6; cursor: not-allowed; }

                    /* Autocomplete styles */
                    .mbr-ins-display { display:flex; align-items:center; justify-content:space-between; gap:6px; padding:7px 10px;
                        border:1.5px solid var(--border, #d0cdc5); border-radius:6px; font-size:13px; color:#1a2535;
                        background:#fff; cursor:pointer; min-height:36px; transition:border-color .15s; }
                    .mbr-ins-display:hover:not(.disabled) { border-color:#C9A84C; }
                    .mbr-ins-display.disabled { cursor:default; background:#f8f7f4; opacity:.7; }
                    .mbr-ins-clear { background:none; border:none; cursor:pointer; color:#b0afa8; padding:2px; line-height:0; transition:color .15s; flex-shrink:0; }
                    .mbr-ins-clear:hover { color:#d44; }
                    .mbr-ins-dropdown { position:absolute; top:100%; left:0; right:0; background:#fff; border:1.5px solid var(--border,#d0cdc5);
                        border-radius:0 0 7px 7px; box-shadow:0 8px 24px rgba(0,0,0,.12); z-index:20; max-height:220px; overflow-y:auto; }
                    .mbr-ins-item { display:flex; align-items:center; justify-content:space-between; gap:8px; width:100%;
                        padding:8px 12px; border:none; background:none; cursor:pointer; font-size:12.5px; color:#1a2535; text-align:left; transition:background .1s; }
                    .mbr-ins-item:hover { background:#f8f7f4; }
                    .mbr-ins-type-badge { font-size:10px; color:#8a8a82; text-transform:capitalize; flex-shrink:0; }
                    .mbr-ins-empty { padding:10px 12px; font-size:12px; color:#8a8a82; text-align:center; }
                    .mbr-ins-add-btn { display:flex; align-items:center; gap:6px; width:100%; padding:9px 12px;
                        border:none; border-top:1px solid #f0efe9; background:#fafaf8; cursor:pointer;
                        font-size:12px; font-weight:600; color:#C9A84C; transition:background .1s; }
                    .mbr-ins-add-btn:hover { background:#f5f3ec; }
                </style>

                <!-- MBR Import Preview Modal -->
                <style>
                    .mim-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.45); }
                    .mim-dialog { position:relative; width:800px; max-width:calc(100vw - 32px); border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.24); overflow:hidden; background:#fff; z-index:10; }
                    .mim-header { background:#0F1B2D; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; }
                    .mim-header h3 { font-size:15px; font-weight:700; color:#fff; margin:0; }
                    .mim-close { background:none; border:none; cursor:pointer; color:rgba(255,255,255,.35); transition:color .15s; padding:0; line-height:0; }
                    .mim-close:hover { color:rgba(255,255,255,.75); }
                    .mim-body { padding:24px; display:flex; flex-direction:column; gap:16px; }
                    .mim-summary { display:flex; gap:12px; }
                    .mim-summary-card { flex:1; background:#fafafa; border-radius:8px; padding:10px 16px; text-align:center; }
                    .mim-summary-card .mim-val { font-size:17px; font-weight:700; color:#1a1a1a; }
                    .mim-summary-card .mim-val-navy { font-size:17px; font-weight:700; color:#0F1B2D; }
                    .mim-summary-card .mim-lbl { font-size:10px; color:#8a8a82; text-transform:uppercase; letter-spacing:.04em; margin-top:2px; }
                    .mim-warning { background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:10px 16px; font-size:13px; color:#92400e; }
                    .mim-table-wrap { max-height:320px; overflow-y:auto; border:1.5px solid var(--border,#d0cdc5); border-radius:7px; }
                    .mim-table { width:100%; font-size:12px; border-collapse:collapse; }
                    .mim-table thead { position:sticky; top:0; background:#fff; }
                    .mim-table thead th { text-align:left; padding:8px 12px; font-size:9.5px; font-weight:700; color:var(--muted,#8a8a82); text-transform:uppercase; letter-spacing:.08em; border-bottom:1.5px solid var(--border,#d0cdc5); }
                    .mim-table thead th.r { text-align:right; }
                    .mim-table thead th.c { text-align:center; }
                    .mim-table tbody tr { border-bottom:1px solid #f3f1ed; }
                    .mim-table tbody tr:last-child { border-bottom:none; }
                    .mim-table tbody td { padding:6px 12px; font-size:12px; }
                    .mim-table tbody td.r { text-align:right; }
                    .mim-table tbody td.c { text-align:center; }
                    .mim-type-badge { display:inline-block; padding:2px 6px; border-radius:4px; font-size:10px; font-weight:600; }
                    .mim-type-provider { background:#dbeafe; color:#1d4ed8; }
                    .mim-type-rx { background:#f3e8ff; color:#7c3aed; }
                    .mim-footer { padding:14px 24px; border-top:1px solid var(--border,#d0cdc5); display:flex; justify-content:flex-end; gap:10px; }
                    .mim-btn-cancel { padding:8px 18px; font-size:13px; font-weight:600; border-radius:7px; border:1.5px solid var(--border,#d0cdc5); background:#fff; color:#555; cursor:pointer; transition:all .15s; }
                    .mim-btn-cancel:hover { background:#fafafa; border-color:#ccc; }
                    .mim-btn-submit { padding:8px 18px; font-size:13px; font-weight:700; border-radius:7px; border:none; background:var(--gold,#C9A84C); color:#fff; cursor:pointer; box-shadow:0 2px 8px rgba(201,168,76,.35); display:flex; align-items:center; gap:6px; transition:all .15s; }
                    .mim-btn-submit:hover { filter:brightness(1.05); }
                    .mim-btn-submit:disabled { opacity:.55; cursor:not-allowed; }
                </style>
                <div x-show="showMbrImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;"
                     @keydown.escape.window="showMbrImportModal = false">
                    <div class="mim-backdrop" @click="showMbrImportModal = false"></div>
                    <div class="mim-dialog" @click.stop>
                        <div class="mim-header">
                            <h3>Import Medical Balance Preview</h3>
                            <button type="button" class="mim-close" @click="showMbrImportModal = false">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="mim-body">
                            <div class="mim-summary">
                                <div class="mim-summary-card">
                                    <p class="mim-val" x-text="mbrImportSummary.count || 0"></p>
                                    <p class="mim-lbl">Lines</p>
                                </div>
                                <div class="mim-summary-card">
                                    <p class="mim-val" x-text="formatCurrency(mbrImportSummary.total_charges || 0)"></p>
                                    <p class="mim-lbl">Total Charges</p>
                                </div>
                                <div class="mim-summary-card">
                                    <p class="mim-val-navy" x-text="formatCurrency(mbrImportSummary.total_pip1 || 0)"></p>
                                    <p class="mim-lbl">Total PIP #1</p>
                                </div>
                                <div class="mim-summary-card">
                                    <p class="mim-val" x-text="formatCurrency(mbrImportSummary.total_balance || 0)"
                                        :style="(mbrImportSummary.total_balance || 0) > 0 ? 'color:#d97706' : 'color:#16a34a'"></p>
                                    <p class="mim-lbl">Total Balance</p>
                                </div>
                            </div>

                            <template x-if="lines.length > 0">
                                <div class="mim-warning">
                                    <strong>Warning:</strong> This will replace all <span x-text="lines.length"></span> existing Medical Balance lines with the imported data.
                                </div>
                            </template>

                            <div class="mim-table-wrap">
                                <table class="mim-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Provider</th>
                                            <th class="r">Charges</th>
                                            <th class="r">PIP #1</th>
                                            <th class="r">Discount</th>
                                            <th class="r">Balance</th>
                                            <th>Dates</th>
                                            <th class="c">Visits</th>
                                            <th class="c">Matched</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(row, idx) in mbrImportPreview" :key="idx">
                                            <tr>
                                                <td>
                                                    <span class="mim-type-badge"
                                                        :class="row.line_type === 'provider' ? 'mim-type-provider' : 'mim-type-rx'"
                                                        x-text="row.line_type.replace('_',' ').toUpperCase()"></span>
                                                </td>
                                                <td style="font-weight:500" x-text="row.provider_name"></td>
                                                <td class="r" x-text="formatCurrency(row.charges)"></td>
                                                <td class="r" x-text="formatCurrency(row.pip1_amount)"></td>
                                                <td class="r" x-text="formatCurrency(row.discount)"></td>
                                                <td class="r" style="font-weight:600"
                                                    :style="row.balance > 0 ? 'color:#d97706' : (row.balance < 0 ? 'color:#dc2626' : 'color:#16a34a')"
                                                    x-text="formatCurrency(row.balance)"></td>
                                                <td style="font-size:11px" x-text="row.treatment_dates || '-'"></td>
                                                <td class="c" x-text="row.visits || '-'"></td>
                                                <td class="c">
                                                    <span x-show="row.matched_provider" style="color:#16a34a">&#10003;</span>
                                                    <span x-show="!row.matched_provider && row.line_type === 'provider'" style="color:#aaa">-</span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mim-footer">
                            <button type="button" @click="showMbrImportModal = false" class="mim-btn-cancel">Cancel</button>
                            <button type="button" @click="confirmMbrImport()" :disabled="mbrImporting"
                                    class="mim-btn-submit">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <span x-text="mbrImporting ? 'Importing...' : 'Import ' + (mbrImportSummary.count || 0) + ' Lines'"></span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
