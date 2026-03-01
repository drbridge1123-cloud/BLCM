            <!-- MBDS Report Section -->
            <div class="mbds-panel c1-section" data-panel x-data="mbdsPanel(caseId)" x-init="init()">

                <!-- Report Header Bar -->
                <div class="mbds-header c1-section-header" :class="mbdsOpen && 'is-open'" @click="mbdsOpen = !mbdsOpen; if(mbdsOpen) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
                    <div class="mbds-header-left">
                        <span class="c1-num c1-num-gold">05</span>
                        <span class="mbds-title">Medical Balance Report</span>
                        <template x-if="report">
                            <span class="mbds-badge"
                                :class="'mbds-badge-' + report.status"
                                x-text="getMbdsStatusLabel(report.status)"></span>
                        </template>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <template x-if="report">
                            <button @click.stop="printMbds()" class="panel-btn-navy">
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
                <div x-show="mbdsOpen" x-collapse>

                    <!-- Loading -->
                    <template x-if="loading">
                        <div class="mbds-loading">
                            <div class="spinner"></div>
                        </div>
                    </template>

                    <template x-if="!loading && report">
                        <div>

                            <!-- Insurance Settings -->
                            <div class="mbds-section-label">
                                <svg fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 0L12 4L8 8L4 4Z" transform="translate(0,4)"/>
                                </svg>
                                <span>Insurance Settings</span>
                            </div>
                            <div class="mbds-insurance-body">
                                <div class="mbds-insurance-grid">
                                    <div>
                                        <label class="mbds-field-label">PIP #1</label>
                                        <input type="text" x-model="settings.pip1_name" @change="saveSettings()"
                                            placeholder="Auto insurance carrier..."
                                            class="mbds-field-input"
                                            :disabled="report?.status !== 'draft'">
                                    </div>
                                    <div>
                                        <label class="mbds-field-label">PIP #2</label>
                                        <input type="text" x-model="settings.pip2_name" @change="saveSettings()"
                                            placeholder="Optional..."
                                            class="mbds-field-input"
                                            :disabled="report?.status !== 'draft'">
                                    </div>
                                    <div>
                                        <label class="mbds-field-label">Health #1</label>
                                        <input type="text" x-model="settings.health1_name" @change="saveSettings()"
                                            placeholder="Health insurance carrier..."
                                            class="mbds-field-input"
                                            :disabled="report?.status !== 'draft'">
                                    </div>
                                    <div>
                                        <label class="mbds-field-label">Health #2</label>
                                        <input type="text" x-model="settings.health2_name" @change="saveSettings()"
                                            placeholder="Optional..."
                                            class="mbds-field-input"
                                            :disabled="report?.status !== 'draft'">
                                    </div>
                                    <div>
                                        <label class="mbds-field-label">Health #3</label>
                                        <input type="text" x-model="settings.health3_name" @change="saveSettings()"
                                            placeholder="Optional..."
                                            class="mbds-field-input"
                                            :disabled="report?.status !== 'draft'">
                                    </div>
                                </div>
                                <div class="mbds-checkbox-row">
                                    <label class="mbds-checkbox-label" :class="settings.has_wage_loss ? 'checked' : ''">
                                        <input type="checkbox" x-model="settings.has_wage_loss" @change="saveSettings()"
                                            class="mbds-checkbox" :disabled="report?.status !== 'draft'">
                                        Wage Loss
                                    </label>
                                    <label class="mbds-checkbox-label" :class="settings.has_essential_service ? 'checked' : ''">
                                        <input type="checkbox" x-model="settings.has_essential_service" @change="saveSettings()"
                                            class="mbds-checkbox" :disabled="report?.status !== 'draft'">
                                        Essential Service
                                    </label>
                                    <label class="mbds-checkbox-label" :class="settings.has_health_subrogation ? 'checked' : ''">
                                        <input type="checkbox" x-model="settings.has_health_subrogation" @change="saveSettings()"
                                            class="mbds-checkbox" :disabled="report?.status !== 'draft'">
                                        Health Subrogation #1
                                    </label>
                                    <label class="mbds-checkbox-label" :class="settings.has_health_subrogation2 ? 'checked' : ''">
                                        <input type="checkbox" x-model="settings.has_health_subrogation2" @change="saveSettings()"
                                            class="mbds-checkbox" :disabled="report?.status !== 'draft'">
                                        Health Subrogation #2
                                    </label>
                                </div>
                            </div>

                            <!-- MBDS Table -->
                            <div class="mbds-table-wrap">
                                <table class="mbds-table">
                                    <thead>
                                        <tr class="mbds-col-head">
                                            <th class="mbds-th-provider">Provider</th>
                                            <th class="mbds-th-r mbds-th-amount">Charges</th>
                                            <th class="mbds-th-r mbds-th-amount" x-show="settings.pip1_name">PIP #1</th>
                                            <th class="mbds-th-r mbds-th-amount" x-show="settings.pip2_name">PIP #2</th>
                                            <th class="mbds-th-r mbds-th-amount" x-show="settings.health1_name">Health #1</th>
                                            <th class="mbds-th-r mbds-th-amount" x-show="settings.health2_name">Health #2</th>
                                            <th class="mbds-th-r mbds-th-amount" x-show="settings.health3_name">Health #3</th>
                                            <th class="mbds-th-r mbds-th-amount">Discount</th>
                                            <th class="mbds-th-r mbds-th-amount">Office Paid</th>
                                            <th class="mbds-th-r mbds-th-amount">Client Paid</th>
                                            <th class="mbds-th-balance mbds-th-amount">Balance</th>
                                            <th class="mbds-th-c mbds-th-dates">Dates</th>
                                            <th class="mbds-th-c mbds-th-visits">Visits</th>
                                            <th class="mbds-th-note">Note</th>
                                            <th class="mbds-th-action" x-show="report?.status === 'draft'"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="row in displayRows" :key="row._key">
                                            <tr :class="row._type === 'header' ? 'mbds-sec-row' : 'mbds-data-row'">

                                                <!-- Category Header -->
                                                <td x-show="row._type === 'header'" :colspan="totalCols">
                                                    <span x-text="row.label"></span>
                                                </td>

                                                <!-- Provider Name -->
                                                <td x-show="row._type === 'line'">
                                                    <span class="mbds-provider-name" x-text="row.provider_name"></span>
                                                </td>

                                                <!-- Charges -->
                                                <td x-show="row._type === 'line'" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'charges', row.charges)"
                                                        @focus="startCellEdit($el, row._lineRef, 'charges')"
                                                        @blur="endCellEdit($el, row._lineRef, 'charges')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.charges) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- PIP #1 -->
                                                <td x-show="row._type === 'line' && settings.pip1_name" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'pip1_amount', row.pip1_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'pip1_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'pip1_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.pip1_amount) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>
                                                <!-- PIP #2 -->
                                                <td x-show="row._type === 'line' && settings.pip2_name" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'pip2_amount', row.pip2_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'pip2_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'pip2_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.pip2_amount) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>
                                                <!-- Health #1 -->
                                                <td x-show="row._type === 'line' && settings.health1_name" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'health1_amount', row.health1_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'health1_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'health1_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.health1_amount) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>
                                                <!-- Health #2 -->
                                                <td x-show="row._type === 'line' && settings.health2_name" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'health2_amount', row.health2_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'health2_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'health2_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.health2_amount) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>
                                                <!-- Health #3 -->
                                                <td x-show="row._type === 'line' && settings.health3_name" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'health3_amount', row.health3_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'health3_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'health3_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.health3_amount) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Discount -->
                                                <td x-show="row._type === 'line'" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'discount', row.discount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'discount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'discount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.discount) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Office Paid -->
                                                <td x-show="row._type === 'line'" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'office_paid', row.office_paid)"
                                                        @focus="startCellEdit($el, row._lineRef, 'office_paid')"
                                                        @blur="endCellEdit($el, row._lineRef, 'office_paid')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.office_paid) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Client Paid -->
                                                <td x-show="row._type === 'line'" style="padding:4px 3px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'client_paid', row.client_paid)"
                                                        @focus="startCellEdit($el, row._lineRef, 'client_paid')"
                                                        @blur="endCellEdit($el, row._lineRef, 'client_paid')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.client_paid) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Balance -->
                                                <td x-show="row._type === 'line'" style="padding:6px;text-align:right">
                                                    <span class="mbds-balance"
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
                                                        class="mbds-date-input"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Visits -->
                                                <td x-show="row._type === 'line'" style="padding:4px 1px">
                                                    <input type="text" x-model="row._lineRef.visits"
                                                        @change="saveLine(row._lineRef)"
                                                        class="mbds-visits-input"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Note -->
                                                <td x-show="row._type === 'line'" style="padding:4px 3px">
                                                    <div @click="openNote($event, row.id)"
                                                        class="mbds-note-trigger"
                                                        :class="row.note ? 'has-note' : ''"
                                                        :title="row.note || ''"
                                                        x-text="row.note || '—'">
                                                    </div>
                                                </td>

                                                <!-- Actions: Delete -->
                                                <td x-show="row._type === 'line' && report?.status === 'draft'" class="mbds-td-action">
                                                    <button @click="deleteLine(row._lineRef)" class="mbds-delete-btn">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr class="mbds-total-row">
                                            <td class="mbds-total-label">TOTAL</td>
                                            <td class="mbds-total-r" x-text="formatCurrency(totals.charges)"></td>
                                            <td class="mbds-total-r" x-show="settings.pip1_name" x-text="formatCurrency(totals.pip1)"></td>
                                            <td class="mbds-total-r" x-show="settings.pip2_name" x-text="formatCurrency(totals.pip2)"></td>
                                            <td class="mbds-total-r" x-show="settings.health1_name" x-text="formatCurrency(totals.health1)"></td>
                                            <td class="mbds-total-r" x-show="settings.health2_name" x-text="formatCurrency(totals.health2)"></td>
                                            <td class="mbds-total-r" x-show="settings.health3_name" x-text="formatCurrency(totals.health3)"></td>
                                            <td class="mbds-total-r" x-text="formatCurrency(totals.discount)"></td>
                                            <td class="mbds-total-r" x-text="formatCurrency(totals.officePaid)"></td>
                                            <td class="mbds-total-r" x-text="formatCurrency(totals.clientPaid)"></td>
                                            <td class="mbds-total-r mbds-total-balance" x-text="formatCurrency(totals.balance)"></td>
                                            <td colspan="4" style="background:var(--navy)"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Bottom Bar — Notes + Actions -->
                            <div class="mbds-bottom-bar">
                                <div style="flex:1">
                                    <div class="mbds-notes-label">Report Notes</div>
                                    <textarea x-model="settings.notes" @change="saveSettings()"
                                        class="mbds-notes-textarea"
                                        placeholder="General notes about this report..."
                                        :disabled="report?.status !== 'draft'"></textarea>
                                </div>
                                <div style="display:flex;gap:8px;align-items:flex-end;padding-bottom:2px;position:relative">
                                    <template x-if="report?.status === 'draft'">
                                        <div style="display:flex;gap:8px">
                                            <button @click="addLine('rx')" class="mbds-btn-ghost">+ Add RX</button>
                                            <button @click="addLine('provider')" class="mbds-btn-ghost">+ Add Provider</button>
                                            <button @click="markComplete()" class="mbds-btn-gold">Mark Complete</button>
                                        </div>
                                    </template>

                                    <!-- Provider Search Dropdown -->
                                    <div x-show="showProviderSearch" @click.outside="showProviderSearch = false" @keydown.escape.window="showProviderSearch = false"
                                        class="mbds-provider-search" x-transition>
                                        <div class="mbds-ps-header">
                                            <input type="text" id="mbds-provider-search-input"
                                                x-model="providerSearchQuery"
                                                @input="searchProviders()"
                                                placeholder="Search provider name..."
                                                class="mbds-ps-input"
                                                @keydown.escape="showProviderSearch = false">
                                        </div>
                                        <div class="mbds-ps-results">
                                            <template x-if="providerSearchQuery.length > 0 && providerSearchResults.length === 0">
                                                <div class="mbds-ps-empty">No providers found</div>
                                            </template>
                                            <template x-for="p in providerSearchResults" :key="p.id">
                                                <button @click="selectProvider(p)" class="mbds-ps-item">
                                                    <span class="mbds-ps-name" x-text="p.name"></span>
                                                    <span class="mbds-ps-type" x-text="(p.type || '').replace(/_/g,' ')"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <template x-if="report?.status === 'completed'">
                                        <div style="display:flex;gap:8px">
                                            <button @click="reopenDraft()" class="mbds-btn-outline-red">Reopen as Draft</button>
                                            <button @click="approveReport()" class="mbds-btn-green">Approve & Close</button>
                                        </div>
                                    </template>
                                    <template x-if="report?.status === 'approved'">
                                        <span style="font-size:12px;color:var(--mbds-green);font-weight:500;padding:8px 0">
                                            Approved by <span x-text="report.approved_by_name"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Note popover -->
                            <template x-for="row in displayRows.filter(r => r._type === 'line')" :key="'mbds_notepop_' + row._key">
                                <div x-show="expandedNote === row.id"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    @click.outside="expandedNote = null"
                                    class="mbds-note-popover"
                                    :style="{ top: notePopoverPos.top, right: notePopoverPos.right }"
                                    @click.stop>
                                    <div class="mbds-note-popover-header">
                                        <span>Note</span>
                                        <button @click="expandedNote = null" style="background:none;border:none;cursor:pointer;color:var(--mbds-muted);padding:2px">
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
                            <div x-show="saving" x-transition class="mbds-saving">
                                <div class="mbds-saving-dot"></div>
                                Saving...
                            </div>
                        </div>
                    </template>

                    <!-- No report fallback -->
                    <template x-if="!loading && !report">
                        <div style="text-align:center;color:var(--mbds-muted);padding:32px 0;font-size:13px">Failed to load Medical Balance report</div>
                    </template>
                </div>

                <!-- MBDS Import Preview Modal -->
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
                <div x-show="showMbdsImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;"
                     @keydown.escape.window="showMbdsImportModal = false">
                    <div class="mim-backdrop" @click="showMbdsImportModal = false"></div>
                    <div class="mim-dialog" @click.stop>
                        <div class="mim-header">
                            <h3>Import Medical Balance Preview</h3>
                            <button type="button" class="mim-close" @click="showMbdsImportModal = false">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="mim-body">
                            <div class="mim-summary">
                                <div class="mim-summary-card">
                                    <p class="mim-val" x-text="mbdsImportSummary.count || 0"></p>
                                    <p class="mim-lbl">Lines</p>
                                </div>
                                <div class="mim-summary-card">
                                    <p class="mim-val" x-text="formatCurrency(mbdsImportSummary.total_charges || 0)"></p>
                                    <p class="mim-lbl">Total Charges</p>
                                </div>
                                <div class="mim-summary-card">
                                    <p class="mim-val-navy" x-text="formatCurrency(mbdsImportSummary.total_pip1 || 0)"></p>
                                    <p class="mim-lbl">Total PIP #1</p>
                                </div>
                                <div class="mim-summary-card">
                                    <p class="mim-val" x-text="formatCurrency(mbdsImportSummary.total_balance || 0)"
                                        :style="(mbdsImportSummary.total_balance || 0) > 0 ? 'color:#d97706' : 'color:#16a34a'"></p>
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
                                        <template x-for="(row, idx) in mbdsImportPreview" :key="idx">
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
                            <button type="button" @click="showMbdsImportModal = false" class="mim-btn-cancel">Cancel</button>
                            <button type="button" @click="confirmMbdsImport()" :disabled="mbdsImporting"
                                    class="mim-btn-submit">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <span x-text="mbdsImporting ? 'Importing...' : 'Import ' + (mbdsImportSummary.count || 0) + ' Lines'"></span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
