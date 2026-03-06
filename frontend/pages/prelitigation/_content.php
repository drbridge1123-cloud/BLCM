<!-- All sp- styles loaded from shared sp-design-system.css -->
<style>
.prelit-row-overdue { background-color: #fef2f2; border-left: 4px solid #ef4444; }
.prelit-row-overdue:hover { background-color: #fee2e2 !important; }
.prelit-row-due { background-color: #fffbeb; border-left: 4px solid #f59e0b; }
.prelit-row-due:hover { background-color: #fef3c7 !important; }
</style>

<div x-data="prelitTrackerPage()">

    <!-- Page header row -->
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <div>
            <div class="sp-eyebrow">Case Management</div>
            <h1 class="sp-title" style="font-size:16px;">Prelitigation Tracker</h1>
        </div>
    </div>

    <!-- Staff Tabs -->
    <?php include __DIR__ . '/../../components/_staff-tabs.php'; ?>

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">

        <!-- Gold bar -->
        <div class="sp-gold-bar"></div>

        <!-- Header -->
        <div class="sp-header" style="flex-wrap:wrap; gap:16px;">
            <div class="sp-stats">
                <div class="sp-stat" style="cursor:pointer;" @click="toggleFilter('')" :style="activeFilter === '' ? 'box-shadow:0 0 0 2px #C9A84C;' : ''">
                    <div class="sp-stat-num" style="color:#1a2535;" x-text="summary.total ?? '-'"></div>
                    <div class="sp-stat-label">Total</div>
                </div>
                <div class="sp-stat" style="cursor:pointer;" @click="toggleFilter('followup_due')" :style="activeFilter === 'followup_due' ? 'box-shadow:0 0 0 2px #D97706;' : ''">
                    <div class="sp-stat-num" style="color:#D97706;" x-text="summary.followup_due ?? '-'"></div>
                    <div class="sp-stat-label">Follow-up Due</div>
                </div>
                <div class="sp-stat" style="cursor:pointer;" @click="toggleFilter('no_contact')" :style="activeFilter === 'no_contact' ? 'box-shadow:0 0 0 2px #8a8a82;' : ''">
                    <div class="sp-stat-num" style="color:#8a8a82;" x-text="summary.no_contact ?? '-'"></div>
                    <div class="sp-stat-label">No Contact</div>
                </div>
                <div class="sp-stat" style="cursor:pointer;" @click="toggleFilter('treatment_complete')" :style="activeFilter === 'treatment_complete' ? 'box-shadow:0 0 0 2px #1a9e6a;' : ''">
                    <div class="sp-stat-num" style="color:#1a9e6a;" x-text="summary.treatment_complete ?? '-'"></div>
                    <div class="sp-stat-label">Treatment Complete</div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="sp-toolbar" style="gap:8px;">
            <input type="text" x-model="search" @input.debounce.300ms="loadData(1)" placeholder="Search case #, client name, or phone..."
                   class="sp-search" style="width:280px;">
            <select x-model="treatmentStatusFilter" @change="loadData(1)" class="sp-select">
                <option value="">All Treatment Status</option>
                <option value="in_treatment">In Treatment</option>
                <option value="treatment_done">Treatment Done</option>
            </select>
            <button @click="resetFilters()" class="sp-btn" x-show="search || activeFilter || treatmentStatusFilter">Reset</button>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="sp-loading">Loading...</div>

        <!-- Table -->
        <div x-show="!loading" x-cloak style="overflow-x:auto;">
            <table class="sp-table sp-table-compact">
                <thead>
                    <tr>
                        <th style="cursor:pointer;" @click="sort('case_number')">Case #</th>
                        <th style="cursor:pointer;" @click="sort('client_name')">Client</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th style="cursor:pointer;" @click="sort('doi')">DOI</th>
                        <th style="cursor:pointer;" @click="sort('treatment_status')">Treatment</th>
                        <th style="cursor:pointer;" @click="sort('last_followup_date')">Last Contact</th>
                        <th>Method</th>
                        <th style="cursor:pointer;" @click="sort('next_followup_date')">Next Follow-up</th>
                        <th class="center">Days</th>
                        <th class="center" style="cursor:pointer;" @click="sort('followup_count')">#</th>
                        <th style="cursor:pointer;" @click="sort('assigned_name')">Assigned</th>
                        <th class="center">Actions</th>
                    </tr>
                </thead>
                <tbody x-show="items.length === 0">
                    <tr style="cursor:default;"><td colspan="13" class="sp-empty">No prelitigation cases found</td></tr>
                </tbody>
                <template x-for="item in items" :key="item.id">
                    <tbody>
                    <tr style="cursor:pointer;"
                        :class="{
                            'prelit-row-overdue': item.is_overdue && expandedId !== item.id,
                            'prelit-row-due': !item.is_overdue && item.is_followup_due && expandedId !== item.id
                        }"
                        :style="expandedId === item.id ? 'box-shadow:inset 0 0 0 2px #C9A84C; background:#fff;' : ''"
                        @click="toggleExpand(item.id)">
                        <td><span class="sp-case-num" x-text="item.case_number"></span></td>
                        <td><span class="sp-client" style="font-size:12px; max-width:150px; display:inline-block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="item.client_name"></span></td>
                        <td><span style="font-size:11px; color:#1a2535;" x-text="formatPhoneNumber(item.client_phone) || '—'"></span></td>
                        <td><span style="font-size:11px; color:#1a2535; max-width:160px; display:inline-block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="item.client_email || '—'"></span></td>
                        <td><span class="sp-date-main" style="font-size:11px;" x-text="formatDate(item.doi)"></span></td>
                        <td>
                            <span class="sp-status"
                                  :class="item.treatment_status === 'treatment_done' ? 'sp-status-paid' : 'sp-status-in-progress'"
                                  x-text="item.treatment_status === 'treatment_done' ? 'Done' : 'In Treatment'"></span>
                        </td>
                        <td><span class="sp-date-main" style="font-size:11px;" x-text="item.last_followup_date ? formatDate(item.last_followup_date) : '—'"></span></td>
                        <td>
                            <span x-show="item.last_followup_type" class="sp-stage" style="font-size:9px; padding:2px 8px; background:rgba(138,138,130,.08); color:#8a8a82; border:1px solid rgba(138,138,130,.15); text-transform:capitalize;" x-text="item.last_followup_type"></span>
                            <span x-show="!item.last_followup_type" class="sp-dash">—</span>
                        </td>
                        <td>
                            <span x-show="item.next_followup_date"
                                  :style="item.is_overdue ? 'color:#e74c3c; font-weight:700;' : item.is_followup_due ? 'color:#D97706; font-weight:600;' : 'color:#8a8a82;'"
                                  style="font-family:'IBM Plex Mono',monospace; font-size:11px;"
                                  x-text="formatDate(item.next_followup_date)"></span>
                            <span x-show="!item.next_followup_date" class="sp-dash">—</span>
                        </td>
                        <td style="text-align:center;">
                            <span x-show="item.days_since_last_contact !== null" class="sp-days-badge"
                                  :class="item.days_since_last_contact > 21 ? 'sp-days-over' : item.days_since_last_contact > 14 ? 'sp-days-warn' : 'sp-days-ok'"
                                  x-text="item.days_since_last_contact + 'd'"></span>
                            <span x-show="item.days_since_last_contact === null" class="sp-dash">—</span>
                        </td>
                        <td style="text-align:center;"><span class="sp-mono" style="font-size:11px;" x-text="item.followup_count || '0'"></span></td>
                        <td>
                            <span style="font-size:12px; color:#8a8a82; max-width:100px; display:inline-block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="item.assigned_name || '—'"></span>
                            <template x-if="item.assignment_status === 'pending'">
                                <span style="background:rgba(245,158,11,.1); color:#D97706; border:1px solid rgba(245,158,11,.2); font-size:9px; padding:1px 5px; border-radius:4px; margin-left:4px;">Pending</span>
                            </template>
                        </td>
                        <td @click.stop>
                            <div class="sp-actions">
                                <button @click="openContacts(item)" class="sp-act" :class="item.client_id ? 'sp-act-green' : 'sp-act-navy'" :title="item.client_id ? 'View Contacts' : 'Add Contacts'">
                                    <span style="font-size:13px;">&#128100;</span>
                                    <span class="sp-tip" x-text="item.client_id ? 'Contacts' : 'Add Contacts'"></span>
                                </button>
                                <button @click="openFollowupModal(item)" class="sp-act sp-act-gold">
                                    <span>📞</span>
                                    <span class="sp-tip">Follow-up</span>
                                </button>
                                <button @click="openCompleteModal(item)" x-show="item.treatment_status === 'treatment_done'" class="sp-act sp-act-green">
                                    <span>✓</span>
                                    <span class="sp-tip">Send to Billing</span>
                                </button>
                                <button @click="goToCase(item.id)" class="sp-act sp-act-blue">
                                    <span>↗</span>
                                    <span class="sp-tip">Open Case</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Expanded Follow-up History -->
                    <tr x-show="expandedId === item.id">
                        <td colspan="13" style="padding:0 !important; border-top:none !important;">
                            <div style="background:#fafaf8; border-top:1px solid #f5f2ee; border-bottom:2px solid rgba(201,168,76,.3); padding:16px 24px;">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <span style="font-size:12px; font-weight:700; color:#1a2535; font-family:'IBM Plex Sans',sans-serif;">Follow-up History</span>
                                        <span style="font-size:11px; color:#8a8a82;" x-text="followupHistory.length + ' follow-up(s)'"></span>
                                    </div>
                                    <button @click="openFollowupModal(item)" class="sp-new-btn" style="padding:5px 12px; font-size:11px;">+ Log Follow-up</button>
                                </div>
                                <template x-if="followupHistory.length === 0">
                                    <p class="sp-empty" style="padding:16px 0;">No follow-ups yet.</p>
                                </template>
                                <template x-if="followupHistory.length > 0">
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <template x-for="fu in followupHistory" :key="fu.id">
                                            <div style="display:flex; align-items:center; justify-content:space-between; background:#fff; border-radius:8px; border:1px solid #e8e4dc; padding:8px 14px;">
                                                <div style="display:flex; align-items:center; gap:10px; font-size:12px;">
                                                    <span class="sp-date-main" style="font-size:11px;" x-text="formatDate(fu.followup_date)"></span>
                                                    <span class="sp-stage" style="font-size:9px; padding:2px 8px; background:rgba(138,138,130,.08); color:#8a8a82; border:1px solid rgba(138,138,130,.15); text-transform:capitalize;" x-text="fu.followup_type"></span>
                                                    <span class="sp-status"
                                                          :class="{
                                                              'sp-status-paid': fu.contact_result === 'reached',
                                                              'sp-status-unpaid': fu.contact_result === 'voicemail' || fu.contact_result === 'callback_scheduled',
                                                              'sp-status-rejected': fu.contact_result === 'no_answer',
                                                              'sp-status-in-progress': fu.contact_result === 'treatment_update'
                                                          }"
                                                          x-text="getContactResultLabel(fu.contact_result)"></span>
                                                    <template x-if="fu.treatment_status_update">
                                                        <span style="font-size:11px; color:#8a8a82;" x-text="'Treatment: ' + fu.treatment_status_update"></span>
                                                    </template>
                                                    <template x-if="fu.notes">
                                                        <span style="font-size:11px; color:#8a8a82; max-width:200px; display:inline-block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="fu.notes"></span>
                                                    </template>
                                                </div>
                                                <div style="display:flex; align-items:center; gap:8px; font-size:11px; color:#8a8a82;">
                                                    <template x-if="fu.next_followup_date">
                                                        <span x-text="'Next: ' + formatDate(fu.next_followup_date)"></span>
                                                    </template>
                                                    <template x-if="fu.created_by_name">
                                                        <span x-text="'by ' + fu.created_by_name"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </template>
            </table>
        </div>

    </div><!-- /sp-card -->

    <!-- ═══ Log Follow-up Modal ═══ -->
    <div x-show="showFollowupModal" x-cloak class="sp-modal-overlay" @click.self="showFollowupModal = false">
        <div class="sp-modal-box" style="max-width:520px;" @click.stop>
            <div class="sp-modal-header">
                <h3 class="sp-modal-title">Log Follow-up</h3>
                <button @click="showFollowupModal = false" class="sp-modal-close">&times;</button>
            </div>
            <div class="sp-modal-body">
                <div style="font-size:12px; color:#8a8a82;" x-text="followupForm._label"></div>
                <div class="sp-form-grid-2">
                    <div>
                        <label class="sp-form-label">Follow-up Date *</label>
                        <input type="date" x-model="followupForm.followup_date" class="sp-search" style="width:100%;">
                    </div>
                    <div>
                        <label class="sp-form-label">Method *</label>
                        <select x-model="followupForm.followup_type" class="sp-select" style="width:100%;">
                            <option value="">Select...</option>
                            <option value="phone">Phone</option>
                            <option value="email">Email</option>
                            <option value="text">Text</option>
                            <option value="in_person">In Person</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="sp-form-grid-2">
                    <div>
                        <label class="sp-form-label">Result *</label>
                        <select x-model="followupForm.contact_result" class="sp-select" style="width:100%;">
                            <option value="">Select...</option>
                            <option value="reached">Reached</option>
                            <option value="voicemail">Voicemail</option>
                            <option value="text">Text</option>
                            <option value="no_answer">No Answer</option>
                            <option value="callback_scheduled">Callback Scheduled</option>
                            <option value="treatment_update">Treatment Update</option>
                        </select>
                    </div>
                    <div>
                        <label class="sp-form-label">Treatment Status</label>
                        <select x-model="followupForm.treatment_status_update" class="sp-select" style="width:100%;">
                            <option value="">No Change</option>
                            <option value="in_treatment">Still In Treatment</option>
                            <option value="treatment_done">Treatment Completed</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="sp-form-label">Next Follow-up Date</label>
                    <input type="date" x-model="followupForm.next_followup_date" class="sp-search" style="width:100%;">
                    <p style="font-size:10px; color:#8a8a82; margin-top:4px;">Leave blank to auto-calculate (+21 days)</p>
                </div>
                <div>
                    <label class="sp-form-label">Notes</label>
                    <textarea x-model="followupForm.notes" rows="2" class="sp-search" style="width:100%; resize:none;" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="sp-modal-footer">
                <button @click="showFollowupModal = false" class="sp-btn">Cancel</button>
                <button @click="submitFollowup()" :disabled="saving" class="sp-new-btn-navy">
                    <span x-show="!saving">Save Follow-up</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ═══ Send to Billing Modal ═══ -->
    <div x-show="showCompleteModal" x-cloak class="sp-modal-overlay" @click.self="showCompleteModal = false">
        <div class="sp-modal-box" style="max-width:440px;" @click.stop>
            <div class="sp-modal-header">
                <h3 class="sp-modal-title">Send to Billing</h3>
                <button @click="showCompleteModal = false" class="sp-modal-close">&times;</button>
            </div>
            <div class="sp-modal-body">
                <div style="background:rgba(26,158,106,.04); border:1px solid rgba(26,158,106,.15); border-radius:8px; padding:12px 16px; font-size:12px; color:#1a9e6a;">
                    <strong x-text="completeForm._label"></strong> treatment is complete. This will move the case to <strong>Billing (Records Collection)</strong> status.
                </div>
                <div>
                    <label class="sp-form-label">Note (optional)</label>
                    <textarea x-model="completeForm.note" rows="2" class="sp-search" style="width:100%; resize:none;" placeholder="Optional note..."></textarea>
                </div>
            </div>
            <div class="sp-modal-footer">
                <button @click="showCompleteModal = false" class="sp-btn">Cancel</button>
                <button @click="submitComplete()" :disabled="saving" class="sp-new-btn-navy" style="background:#1a9e6a;">
                    <span x-show="!saving">Confirm & Send to Billing</span>
                    <span x-show="saving">Processing...</span>
                </button>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../components/_modal-contacts.php'; ?>

</div>
