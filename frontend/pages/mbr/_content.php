<!-- All sp- styles loaded from shared sp-design-system.css -->

<div class="max-w-full mx-auto" x-data="mbrPage()">

    <!-- ═══════════════════════════════════════════════ -->
    <!--  LIST VIEW                                      -->
    <!-- ═══════════════════════════════════════════════ -->
    <div x-show="view === 'list'" x-cloak>

        <!-- ═══ Unified Card ═══ -->
        <div class="sp-card">

            <!-- Gold bar -->
            <div class="sp-gold-bar"></div>

            <!-- Header -->
            <div class="sp-header">
                <div>
                    <div class="sp-eyebrow">Medical Records</div>
                    <h1 class="sp-title">Medical Bills Data Summary</h1>
                </div>
                <button @click="openCreateModal()" class="sp-new-btn">+ Create Report</button>
            </div>

            <!-- Toolbar -->
            <div class="sp-toolbar" style="gap:8px;">
                <div class="sp-tabs">
                    <button class="sp-tab" :class="statusFilter === '' && 'on'" @click="statusFilter = ''; loadData(1)">All
                        <span class="sp-tab-count" style="background:rgba(37,99,235,.1); color:#2563eb;" x-text="summary.total || 0"></span>
                    </button>
                    <button class="sp-tab" :class="statusFilter === 'draft' && 'on'" @click="statusFilter = 'draft'; loadData(1)">Draft
                        <span class="sp-tab-count" style="background:rgba(217,119,6,.1); color:#d97706;" x-text="summary.draft || 0"></span>
                    </button>
                    <button class="sp-tab" :class="statusFilter === 'completed' && 'on'" @click="statusFilter = 'completed'; loadData(1)">Completed
                        <span class="sp-tab-count" style="background:rgba(37,99,235,.1); color:#2563eb;" x-text="summary.completed || 0"></span>
                    </button>
                    <button class="sp-tab" :class="statusFilter === 'approved' && 'on'" @click="statusFilter = 'approved'; loadData(1)">Approved
                        <span class="sp-tab-count" style="background:rgba(16,185,129,.1); color:#059669;" x-text="summary.approved || 0"></span>
                    </button>
                </div>
                <input type="text" x-model="search" @input.debounce.300ms="loadData(1)" placeholder="Search case # or client..."
                       class="sp-search" style="width:240px;">
                <button @click="resetFilters()" class="sp-btn" x-show="search || statusFilter">Reset</button>
            </div>

            <!-- Reports Table -->
            <table class="sp-table">
                <thead>
                    <tr>
                        <th>Case #</th>
                        <th>Client</th>
                        <th class="center">Status</th>
                        <th>Created</th>
                        <th>Completed By</th>
                        <th>Approved By</th>
                        <th class="center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr style="cursor:default;"><td colspan="7" class="sp-loading">Loading reports...</td></tr>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <tr style="cursor:default;"><td colspan="7" class="sp-empty">No MBR reports found</td></tr>
                    </template>
                    <template x-for="item in items" :key="item.id">
                        <tr style="cursor:default;">
                            <td><span class="sp-case-num" x-text="item.case_number"></span></td>
                            <td><span class="sp-client" style="font-size:12px;" x-text="item.client_name"></span></td>
                            <td style="text-align:center;">
                                <span class="sp-status"
                                      :class="{
                                          'sp-status-in-progress': item.status === 'draft',
                                          'sp-status-unpaid': item.status === 'completed',
                                          'sp-status-paid': item.status === 'approved'
                                      }"
                                      x-text="item.status"></span>
                            </td>
                            <td><span class="sp-date-main" style="font-size:11px;" x-text="formatDate(item.created_at?.split(' ')[0])"></span></td>
                            <td><span style="font-size:12px; color:#8a8a82;" x-text="item.completed_by_name || '—'"></span></td>
                            <td><span style="font-size:12px; color:#8a8a82;" x-text="item.approved_by_name || '—'"></span></td>
                            <td style="text-align:center;">
                                <button @click="viewReport(item.id)" class="sp-act sp-act-gold" style="width:auto; padding:0 10px; font-size:10px; height:24px;">View / Edit</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>


        </div><!-- /sp-card -->
    </div>

    <!-- ═══════════════════════════════════════════════ -->
    <!--  DETAIL / EDIT VIEW                             -->
    <!-- ═══════════════════════════════════════════════ -->
    <div x-show="view === 'detail'" x-cloak>

        <!-- Back + Header -->
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
            <button @click="backToList()" class="sp-btn">&larr; Back</button>
            <div style="flex:1;">
                <div class="sp-eyebrow">MBR Report</div>
                <h1 class="sp-title" style="font-size:16px;">
                    <span x-text="currentReport.case_number"></span> — <span x-text="currentReport.client_name"></span>
                </h1>
                <div style="font-size:11px; color:#8a8a82; margin-top:2px;">
                    DOI: <span x-text="currentReport.doi ? formatDate(currentReport.doi) : 'N/A'"></span>
                    <template x-if="currentReport.attorney_name">
                        <span> | Attorney: <span x-text="currentReport.attorney_name"></span></span>
                    </template>
                </div>
            </div>
            <span class="sp-status"
                  :class="{
                      'sp-status-in-progress': currentReport.status === 'draft',
                      'sp-status-unpaid': currentReport.status === 'completed',
                      'sp-status-paid': currentReport.status === 'approved'
                  }"
                  x-text="currentReport.status"></span>
        </div>

        <!-- Insurance Carriers & Flags -->
        <div class="sp-card" style="margin-bottom:12px;">
            <div class="sp-gold-bar"></div>
            <div style="padding:16px 20px;">
                <div style="font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#8a8a82; font-family:'IBM Plex Sans',sans-serif; margin-bottom:10px;">Insurance Carriers</div>
                <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:10px; margin-bottom:14px;">
                    <div>
                        <label style="font-size:10px; font-weight:600; color:#8a8a82; display:block; margin-bottom:3px; font-family:'IBM Plex Sans',sans-serif;">PIP-1</label>
                        <input type="text" x-model="headerForm.pip1_name" class="sp-search" style="width:100%; padding:6px 10px; font-size:12px;">
                    </div>
                    <div>
                        <label style="font-size:10px; font-weight:600; color:#8a8a82; display:block; margin-bottom:3px; font-family:'IBM Plex Sans',sans-serif;">PIP-2</label>
                        <input type="text" x-model="headerForm.pip2_name" class="sp-search" style="width:100%; padding:6px 10px; font-size:12px;">
                    </div>
                    <div>
                        <label style="font-size:10px; font-weight:600; color:#8a8a82; display:block; margin-bottom:3px; font-family:'IBM Plex Sans',sans-serif;">Health-1</label>
                        <input type="text" x-model="headerForm.health1_name" class="sp-search" style="width:100%; padding:6px 10px; font-size:12px;">
                    </div>
                    <div>
                        <label style="font-size:10px; font-weight:600; color:#8a8a82; display:block; margin-bottom:3px; font-family:'IBM Plex Sans',sans-serif;">Health-2</label>
                        <input type="text" x-model="headerForm.health2_name" class="sp-search" style="width:100%; padding:6px 10px; font-size:12px;">
                    </div>
                    <div>
                        <label style="font-size:10px; font-weight:600; color:#8a8a82; display:block; margin-bottom:3px; font-family:'IBM Plex Sans',sans-serif;">Health-3</label>
                        <input type="text" x-model="headerForm.health3_name" class="sp-search" style="width:100%; padding:6px 10px; font-size:12px;">
                    </div>
                </div>

                <div style="font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#8a8a82; font-family:'IBM Plex Sans',sans-serif; margin-bottom:8px;">Flags</div>
                <div style="display:flex; align-items:center; gap:20px; margin-bottom:14px;">
                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; color:#1a2535; cursor:pointer;">
                        <input type="checkbox" x-model="headerForm.has_wage_loss" style="accent-color:#C9A84C;"> Wage Loss
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; color:#1a2535; cursor:pointer;">
                        <input type="checkbox" x-model="headerForm.has_essential_service" style="accent-color:#C9A84C;"> Essential Service
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; color:#1a2535; cursor:pointer;">
                        <input type="checkbox" x-model="headerForm.has_health_subrogation" style="accent-color:#C9A84C;"> Health Subrogation
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; color:#1a2535; cursor:pointer;">
                        <input type="checkbox" x-model="headerForm.has_health_subrogation2" style="accent-color:#C9A84C;"> Health Subrogation 2
                    </label>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-size:10px; font-weight:600; color:#8a8a82; display:block; margin-bottom:3px; font-family:'IBM Plex Sans',sans-serif;">Notes</label>
                    <textarea x-model="headerForm.notes" rows="2" class="sp-search" style="width:100%; padding:6px 10px; font-size:12px; resize:none;"></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button @click="saveHeader()" :disabled="savingHeader" class="sp-new-btn" style="padding:6px 14px; font-size:11px;">
                        <span x-text="savingHeader ? 'Saving...' : 'Save Header'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Lines Table -->
        <div class="sp-card" style="margin-bottom:12px;">
            <div style="padding:10px 20px; border-bottom:1px solid #f5f2ee; display:flex; align-items:center; justify-content:space-between; background:#fafaf8;">
                <span style="font-size:12px; font-weight:700; color:#1a2535; font-family:'IBM Plex Sans',sans-serif;">Line Items</span>
                <div style="display:flex; align-items:center; gap:8px;" x-show="currentReport.status === 'draft'">
                    <select x-model="newLineType" class="sp-select" style="padding:4px 10px; font-size:11px;">
                        <option value="provider">Provider</option>
                        <option value="bridge_law">Bridge Law</option>
                        <option value="wage_loss">Wage Loss</option>
                        <option value="essential_service">Essential Service</option>
                        <option value="health_subrogation">Health Subrogation</option>
                        <option value="health_subrogation2">Health Subrogation 2</option>
                        <option value="rx">Rx</option>
                    </select>
                    <button @click="addLine()" class="sp-new-btn" style="padding:4px 10px; font-size:11px;">+ Add Line</button>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="sp-table sp-table-compact" style="font-size:11px;">
                    <thead>
                        <tr>
                            <th class="center" style="width:30px;">#</th>
                            <th style="width:70px;">Type</th>
                            <th style="min-width:140px;">Provider</th>
                            <th class="right" style="width:80px;">Charges</th>
                            <th class="right" style="width:70px;">PIP-1</th>
                            <th class="right" style="width:70px;">PIP-2</th>
                            <th class="right" style="width:70px;">Health-1</th>
                            <th class="right" style="width:70px;">Health-2</th>
                            <th class="right" style="width:70px;">Health-3</th>
                            <th class="right" style="width:70px;">Discount</th>
                            <th class="right" style="width:70px;">Office Pd</th>
                            <th class="right" style="width:70px;">Client Pd</th>
                            <th class="right" style="width:80px;">Balance</th>
                            <th class="center" style="width:50px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="detailLoading">
                            <tr style="cursor:default;"><td colspan="14" class="sp-loading">Loading lines...</td></tr>
                        </template>
                        <template x-if="!detailLoading && lines.length === 0">
                            <tr style="cursor:default;"><td colspan="14" class="sp-empty">No line items yet. Add a line or activate providers.</td></tr>
                        </template>
                        <template x-for="(line, idx) in lines" :key="line.id">
                            <tr style="cursor:default;">
                                <td style="text-align:center;"><span style="font-family:'IBM Plex Mono',monospace; font-size:10px; color:#8a8a82;" x-text="idx + 1"></span></td>
                                <td>
                                    <span class="sp-stage" style="font-size:8px; padding:2px 6px;"
                                          :style="line.line_type === 'provider' ? 'background:rgba(37,99,235,.08); color:#2563eb; border:1px solid rgba(37,99,235,.15);' : 'background:rgba(124,92,191,.08); color:#7C5CBF; border:1px solid rgba(124,92,191,.15);'"
                                          x-text="formatLineType(line.line_type)"></span>
                                </td>
                                <td><input type="text" x-model="line.provider_name" @change="saveLine(line)" class="sp-search" style="width:100%; padding:3px 6px; font-size:11px;"></td>
                                <td><input type="number" step="0.01" x-model.number="line.charges" @change="saveLine(line)" class="sp-search" style="width:100%; padding:3px 6px; font-size:11px; text-align:right;"></td>
                                <td><input type="number" step="0.01" x-model.number="line.pip1_amount" @change="saveLine(line)" class="sp-search" style="width:100%; padding:3px 6px; font-size:11px; text-align:right;"></td>
                                <td><input type="number" step="0.01" x-model.number="line.pip2_amount" @change="saveLine(line)" class="sp-search" style="width:100%; padding:3px 6px; font-size:11px; text-align:right;"></td>
                                <td><input type="number" step="0.01" x-model.number="line.health1_amount" @change="saveLine(line)" class="sp-search" style="width:100%; padding:3px 6px; font-size:11px; text-align:right;"></td>
                                <td><input type="number" step="0.01" x-model.number="line.health2_amount" @change="saveLine(line)" class="sp-search" style="width:100%; padding:3px 6px; font-size:11px; text-align:right;"></td>
                                <td><input type="number" step="0.01" x-model.number="line.health3_amount" @change="saveLine(line)" class="sp-search" style="width:100%; padding:3px 6px; font-size:11px; text-align:right;"></td>
                                <td><input type="number" step="0.01" x-model.number="line.discount" @change="saveLine(line)" class="sp-search" style="width:100%; padding:3px 6px; font-size:11px; text-align:right;"></td>
                                <td><input type="number" step="0.01" x-model.number="line.office_paid" @change="saveLine(line)" class="sp-search" style="width:100%; padding:3px 6px; font-size:11px; text-align:right;"></td>
                                <td><input type="number" step="0.01" x-model.number="line.client_paid" @change="saveLine(line)" class="sp-search" style="width:100%; padding:3px 6px; font-size:11px; text-align:right;"></td>
                                <td style="text-align:right;"><span class="sp-mono" :style="calcBalance(line) < 0 ? 'color:#e74c3c; font-weight:700;' : 'font-weight:600;'" x-text="formatCurrency(calcBalance(line))"></span></td>
                                <td style="text-align:center;">
                                    <button @click="deleteLine(line.id)" x-show="currentReport.status === 'draft'" class="sp-act sp-act-red" style="width:22px; height:22px; font-size:10px;">✕</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <!-- Totals Footer -->
                    <tfoot x-show="lines.length > 0" style="background:rgba(201,168,76,.08); border-top:2px solid rgba(201,168,76,.3);">
                        <tr style="cursor:default;">
                            <td colspan="3" style="text-align:right; font-weight:700; font-size:10px; color:#7a6520; text-transform:uppercase; letter-spacing:.1em;">Totals</td>
                            <td style="text-align:right;"><span class="sp-mono" style="font-weight:700; font-size:11px;" x-text="formatCurrency(totals.charges)"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" style="font-size:11px;" x-text="formatCurrency(totals.pip1)"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" style="font-size:11px;" x-text="formatCurrency(totals.pip2)"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" style="font-size:11px;" x-text="formatCurrency(totals.health1)"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" style="font-size:11px;" x-text="formatCurrency(totals.health2)"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" style="font-size:11px;" x-text="formatCurrency(totals.health3)"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" style="font-size:11px;" x-text="formatCurrency(totals.discount)"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" style="font-size:11px;" x-text="formatCurrency(totals.office_paid)"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" style="font-size:11px;" x-text="formatCurrency(totals.client_paid)"></span></td>
                            <td style="text-align:right;"><span class="sp-mono" style="font-weight:700; font-size:11px;" :style="totals.balance < 0 ? 'color:#e74c3c;' : ''" x-text="formatCurrency(totals.balance)"></span></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display:flex; align-items:center; gap:8px;">
            <template x-if="currentReport.status === 'draft'">
                <button @click="activateProviders()" class="sp-btn">Activate Providers</button>
            </template>
            <template x-if="currentReport.status === 'draft'">
                <button @click="completeReport()" class="sp-new-btn" style="background:#D97706;">Mark Complete</button>
            </template>
            <template x-if="currentReport.status === 'completed'">
                <button @click="approveReport()" class="sp-new-btn" style="background:#1a9e6a;">Approve</button>
            </template>
            <template x-if="currentReport.status === 'completed'">
                <button @click="reopenReport()" class="sp-btn">Reopen as Draft</button>
            </template>
        </div>
    </div>

    <!-- ═══ Create Modal ═══ -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showCreateModal=false">
        <div class="sp-card" style="width:100%; max-width:520px; margin:16px; max-height:90vh; overflow-y:auto;" @click.stop>
            <div class="sp-gold-bar"></div>
            <div class="sp-header" style="padding:16px 20px;">
                <h3 class="sp-title" style="font-size:15px; flex:1;">Create MBR Report</h3>
                <button @click="showCreateModal=false" style="color:#8a8a82; font-size:18px; cursor:pointer; background:none; border:none;">&times;</button>
            </div>
            <div style="padding:16px 20px; display:flex; flex-direction:column; gap:14px;">
                <!-- Case Search -->
                <div style="position:relative;">
                    <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Case Number *</label>
                    <input type="text" x-model="createForm.caseSearch" @input.debounce.300ms="searchCases()"
                           placeholder="Search by case # or client name..." class="sp-search" style="width:100%; padding:8px 12px;">
                    <div x-show="caseResults.length > 0" style="position:absolute; z-index:10; margin-top:4px; width:100%; background:#fff; border:1px solid #e8e4dc; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,.1); max-height:192px; overflow-y:auto;">
                        <template x-for="c in caseResults" :key="c.id">
                            <button @click="selectCase(c)" style="width:100%; text-align:left; padding:8px 12px; border-bottom:1px solid #f5f2ee; font-size:12px; cursor:pointer; background:none; border-left:none; border-right:none; border-top:none; font-family:'IBM Plex Sans',sans-serif;"
                                    onmouseover="this.style.background='#fdfcf9'" onmouseout="this.style.background='none'">
                                <span class="sp-case-num" x-text="c.case_number"></span>
                                <span style="color:#8a8a82; margin-left:8px;" x-text="c.client_name"></span>
                            </button>
                        </template>
                    </div>
                    <div x-show="createForm.selectedCase" style="margin-top:4px; font-size:11px; color:#1a9e6a;">
                        Selected: <span x-text="createForm.selectedCase?.case_number"></span> - <span x-text="createForm.selectedCase?.client_name"></span>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">PIP-1 Name</label>
                        <input type="text" x-model="createForm.pip1_name" class="sp-search" style="width:100%; padding:8px 12px;">
                    </div>
                    <div>
                        <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">PIP-2 Name</label>
                        <input type="text" x-model="createForm.pip2_name" class="sp-search" style="width:100%; padding:8px 12px;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Health-1 Name</label>
                        <input type="text" x-model="createForm.health1_name" class="sp-search" style="width:100%; padding:8px 12px;">
                    </div>
                    <div>
                        <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Health-2 Name</label>
                        <input type="text" x-model="createForm.health2_name" class="sp-search" style="width:100%; padding:8px 12px;">
                    </div>
                    <div>
                        <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Health-3 Name</label>
                        <input type="text" x-model="createForm.health3_name" class="sp-search" style="width:100%; padding:8px 12px;">
                    </div>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; padding:12px 20px; background:#fafaf8; border-top:1px solid #f5f2ee;">
                <button @click="showCreateModal=false" class="sp-btn">Cancel</button>
                <button @click="createReport()" :disabled="creatingReport" class="sp-new-btn">
                    <span x-text="creatingReport ? 'Creating...' : 'Create Report'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
