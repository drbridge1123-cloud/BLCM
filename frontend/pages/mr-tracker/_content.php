<!-- All sp- styles loaded from shared sp-design-system.css -->

<style>
    .tracker-row-overdue { background-color: #fef2f2; border-left: 4px solid #ef4444; }
    .tracker-row-overdue:hover { background-color: #fee2e2 !important; }
    .tracker-row-followup { background-color: #fffbeb; border-left: 4px solid #f59e0b; }
    .tracker-row-followup:hover { background-color: #fef3c7 !important; }
    .escalation-badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; white-space: nowrap; }
    .escalation-admin { background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .escalation-manager { background-color: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
    .escalation-action { background-color: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .hl-row-followup { background-color: #fffbeb; border-left: 3px solid #f59e0b; }
</style>

<div x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') === 'health' ? 'health' : 'mr' }">

    <!-- Page header row -->
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <div>
            <div class="sp-eyebrow">Case Management</div>
            <h1 class="sp-title" style="font-size:16px;">Tracker</h1>
        </div>
        <div class="sp-tabs">
            <button class="sp-tab" :class="activeTab === 'mr' && 'on'" @click="activeTab = 'mr'">MR Tracker</button>
            <button class="sp-tab" :class="activeTab === 'health' && 'on'" @click="activeTab = 'health'">Health Tracker</button>
        </div>
    </div>

    <!-- ===================== MR TRACKER TAB ===================== -->
    <div x-show="activeTab === 'mr'" x-data="trackerPage()" x-init="init()">

        <!-- Staff Tabs -->
        <?php include __DIR__ . '/../../components/_staff-tabs.php'; ?>

        <div class="sp-card">

            <!-- Gold bar -->
            <div class="sp-gold-bar"></div>

            <!-- Case Filter Banner -->
            <div x-show="caseIdFilter" style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:8px 16px; margin:12px 24px; display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px; font-size:13px; color:#1e40af;">
                    <span style="font-weight:600;">Filtered by case</span>
                    <span style="color:#2563eb;" x-text="items.length ? '#' + items[0].case_number : ''"></span>
                    <span style="color:#3b82f6;" x-text="'(' + items.length + ' provider' + (items.length !== 1 ? 's' : '') + ')'"></span>
                </div>
                <button @click="resetFilters()" style="font-size:12px; color:#2563eb; text-decoration:underline; background:none; border:none; cursor:pointer;">Show All</button>
            </div>

            <!-- New Assignments Panel -->
            <template x-if="pendingAssignments.length > 0">
                <div style="background:#fffbeb; border-radius:8px; border:1px solid #fcd34d; margin:0 24px 12px; overflow:hidden;">
                    <div style="padding:8px 16px; background:#fef3c7; border-bottom:1px solid #fcd34d; display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:13px; font-weight:700; color:#92400e;">New Assignments</span>
                            <span style="background:#f59e0b; color:#fff; font-size:10px; font-weight:700; padding:1px 7px; border-radius:999px;" x-text="pendingAssignments.length"></span>
                        </div>
                    </div>
                    <template x-for="pa in pendingAssignments" :key="pa.id">
                        <div style="padding:8px 16px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid rgba(253,224,71,.3);">
                            <div style="min-width:0;">
                                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                    <span style="font-size:13px; font-weight:600; color:#1a2535;" x-text="pa.provider_name"></span>
                                    <span style="font-size:11px; color:#9ca3af;">|</span>
                                    <span style="font-size:12px; color:#6b7280;" x-text="'Case #' + pa.case_number"></span>
                                    <span style="font-size:11px; color:#9ca3af;">|</span>
                                    <span style="font-size:12px; color:#6b7280;" x-text="pa.client_name"></span>
                                </div>
                                <div style="display:flex; align-items:center; gap:10px; margin-top:2px;">
                                    <span style="font-size:11px; color:#9ca3af;" x-text="'Deadline: ' + formatDate(pa.deadline)"></span>
                                    <template x-if="pa.activated_by_name"><span style="font-size:11px; color:#9ca3af;" x-text="'From: ' + pa.activated_by_name"></span></template>
                                    <template x-if="pa.request_mr || pa.request_bill || pa.request_chart || pa.request_img || pa.request_op">
                                        <span style="font-size:11px; color:#9ca3af;">
                                            Records:
                                            <template x-if="pa.request_mr"><span style="font-weight:600;">MR </span></template>
                                            <template x-if="pa.request_bill"><span style="font-weight:600;">Bill </span></template>
                                            <template x-if="pa.request_chart"><span style="font-weight:600;">Chart </span></template>
                                            <template x-if="pa.request_img"><span style="font-weight:600;">Img </span></template>
                                            <template x-if="pa.request_op"><span style="font-weight:600;">OP </span></template>
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <div style="display:flex; gap:6px; flex-shrink:0;">
                                <button @click="acceptAssignment(pa.id)" style="padding:4px 12px; font-size:12px; font-weight:600; color:#fff; background:#10b981; border:none; border-radius:6px; cursor:pointer;">Accept</button>
                                <button @click="declineAssignment(pa.id)" style="padding:4px 12px; font-size:12px; font-weight:600; color:#fff; background:#ef4444; border:none; border-radius:6px; cursor:pointer;">Decline</button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Summary Cards -->
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; padding:12px 24px;">
                <div @click="toggleFilter('')" class="sp-stat" style="cursor:pointer; border-radius:8px; padding:10px 14px; border:1.5px solid #e8e4dc;"
                     :style="activeFilter === '' ? 'border-color:#C9A84C; box-shadow:0 0 0 2px rgba(201,168,76,.15);' : ''">
                    <div class="sp-stat-label" style="font-size:10px;">Total</div>
                    <div class="sp-stat-num" style="color:#1a2535;" x-text="summary.total ?? '-'"></div>
                </div>
                <div @click="toggleFilter('overdue')" class="sp-stat" style="cursor:pointer; border-radius:8px; padding:10px 14px; border:1.5px solid #e8e4dc;"
                     :style="activeFilter === 'overdue' ? 'border-color:#ef4444; box-shadow:0 0 0 2px rgba(239,68,68,.15);' : ''">
                    <div class="sp-stat-label" style="font-size:10px;">Overdue</div>
                    <div class="sp-stat-num" style="color:#dc2626;" x-text="summary.overdue ?? '-'"></div>
                </div>
                <div @click="toggleFilter('followup_due')" class="sp-stat" style="cursor:pointer; border-radius:8px; padding:10px 14px; border:1.5px solid #e8e4dc;"
                     :style="activeFilter === 'followup_due' ? 'border-color:#f59e0b; box-shadow:0 0 0 2px rgba(245,158,11,.15);' : ''">
                    <div class="sp-stat-label" style="font-size:10px;">Follow-up Due</div>
                    <div class="sp-stat-num" style="color:#d97706;" x-text="summary.followup_due ?? '-'"></div>
                </div>
                <div @click="toggleFilter('no_request')" class="sp-stat" style="cursor:pointer; border-radius:8px; padding:10px 14px; border:1.5px solid #e8e4dc;"
                     :style="activeFilter === 'no_request' ? 'border-color:#9ca3af; box-shadow:0 0 0 2px rgba(156,163,175,.15);' : ''">
                    <div class="sp-stat-label" style="font-size:10px;">Not Started</div>
                    <div class="sp-stat-num" style="color:#6b7280;" x-text="summary.not_started ?? '-'"></div>
                </div>
            </div>

            <!-- Filters -->
            <div style="padding:0 24px 12px;">
                <div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px;">
                    <input type="text" x-model="search" @input.debounce.300ms="loadData(1)" placeholder="Search case #, client, or provider..."
                           class="sp-search" style="flex:1; min-width:200px;">
                    <select x-model="statusFilter" @change="loadData(1)" class="sp-select">
                        <option value="">All Statuses</option>
                        <option value="not_started">Not Started</option><option value="requesting">Requesting</option>
                        <option value="follow_up">Follow Up</option><option value="action_needed">Action Needed</option>
                        <option value="received_partial">Partial</option><option value="on_hold">On Hold</option>
                        <option value="received_complete">Complete</option><option value="verified">Verified</option>
                    </select>
                    <select x-model="tierFilter" @change="loadData(1)" class="sp-select">
                        <option value="">All Tiers</option>
                        <option value="admin">Admin Escalation (deadline+14d)</option>
                        <option value="action">Action Needed (past deadline)</option>
                    </select>
                    <select x-model="assignedFilter" @change="loadData(1)" class="sp-select">
                        <option value="">All Staff</option>
                        <template x-for="staff in staffList" :key="staff.id">
                            <option :value="staff.id" x-text="staff.full_name"></option>
                        </template>
                    </select>
                    <button @click="resetFilters()" class="sp-btn"
                            x-show="search || statusFilter || activeFilter || tierFilter || assignedFilter">Reset</button>
                </div>
            </div>

            <!-- Loading -->
            <template x-if="loading">
                <div class="sp-loading"><div class="spinner"></div></div>
            </template>

            <!-- Table -->
            <template x-if="!loading">
                <div style="overflow-x:auto;" x-init="initScrollContainer($el)">
                    <table class="sp-table sp-table-compact">
                        <thead><tr>
                            <th style="width:40px;"><input type="checkbox" @change="toggleSelectAll()" :checked="allSelected" style="cursor:pointer;"></th>
                            <th class="cursor-pointer select-none" @click="sort('case_number')">Case #</th>
                            <th class="cursor-pointer select-none" @click="sort('client_name')">Client</th>
                            <th class="cursor-pointer select-none" @click="sort('provider_name')">Provider</th>
                            <th class="cursor-pointer select-none" @click="sort('overall_status')">Status</th>
                            <th class="cursor-pointer select-none" @click="sort('last_request_date')">Last Request</th>
                            <th class="cursor-pointer select-none" @click="sort('next_followup_date')">Follow-up</th>
                            <th class="cursor-pointer select-none" @click="sort('request_count')">#</th>
                            <th class="cursor-pointer select-none" @click="sort('deadline')">Deadline</th>
                            <th class="cursor-pointer select-none" @click="sort('days_since_request')">Days</th>
                            <th>Tier</th>
                            <th>Assigned</th>
                            <th>Actions</th>
                        </tr></thead>
                        <tbody x-show="items.length === 0">
                            <tr><td colspan="13" class="sp-empty">No records found</td></tr>
                        </tbody>
                        <template x-for="item in items" :key="item.id">
                            <tbody>
                            <tr class="cursor-pointer"
                                :class="{ 'tracker-row-overdue': item.is_overdue && !selectedItems.includes(item.id) && expandedId !== item.id, 'tracker-row-followup': !item.is_overdue && item.is_followup_due && !selectedItems.includes(item.id) && expandedId !== item.id }"
                                :style="expandedId === item.id ? 'background:rgba(201,168,76,.04); box-shadow:inset 0 0 0 2px rgba(201,168,76,.3);' : selectedItems.includes(item.id) ? 'background:#eff6ff;' : ''"
                                @click="toggleExpand(item.id)">
                                <td @click.stop><input type="checkbox" :checked="selectedItems.includes(item.id)" @click="toggleSelect(item.id, $event)" style="cursor:pointer;"></td>
                                <td style="font-weight:600; color:#C9A84C; white-space:nowrap;" x-text="item.case_number"></td>
                                <td style="max-width:150px;" class="truncate" x-text="item.client_name"></td>
                                <td style="max-width:180px;" class="truncate" x-text="item.provider_name"></td>
                                <td><span class="status-badge" :class="'status-' + item.overall_status" x-text="getStatusLabel(item.overall_status)"></span></td>
                                <td style="white-space:nowrap;">
                                    <template x-if="item.last_request_date"><div style="display:flex; align-items:center; gap:6px;"><span class="sp-mono" x-text="formatDate(item.last_request_date)"></span><span class="sp-stage" style="background:rgba(201,168,76,.1); color:#7a6520; font-size:10px; padding:1px 6px;" x-text="getMethodLabel(item.last_request_method)"></span></div></template>
                                    <template x-if="!item.last_request_date"><span style="color:#d1d5db;">-</span></template>
                                </td>
                                <td style="white-space:nowrap;">
                                    <template x-if="item.next_followup_date"><span class="sp-mono" :style="item.is_followup_due ? 'color:#d97706; font-weight:600;' : ''" x-text="formatDate(item.next_followup_date)"></span></template>
                                    <template x-if="!item.next_followup_date"><span style="color:#d1d5db;">-</span></template>
                                </td>
                                <td style="text-align:center;" x-text="item.request_count || '-'"></td>
                                <td style="white-space:nowrap;">
                                    <template x-if="item.deadline"><span class="sp-mono" :style="{ color: item.days_until_deadline < 0 ? '#dc2626' : item.days_until_deadline <= 7 ? '#d97706' : '#6b7280', fontWeight: item.days_until_deadline < 0 ? '700' : item.days_until_deadline <= 7 ? '600' : '400' }" x-text="formatDate(item.deadline)"></span></template>
                                    <template x-if="!item.deadline"><span style="color:#d1d5db;">-</span></template>
                                </td>
                                <td style="text-align:center;">
                                    <template x-if="item.days_since_request !== null"><span class="sp-mono" :style="item.days_since_request > 30 ? 'color:#ef4444; font-weight:600;' : item.days_since_request > 14 ? 'color:#d97706;' : ''" x-text="item.days_since_request + 'd'"></span></template>
                                    <template x-if="item.days_since_request === null"><span style="color:#d1d5db;">-</span></template>
                                </td>
                                <td>
                                    <template x-if="item.escalation_tier !== 'normal'"><span class="escalation-badge" :class="item.escalation_css" x-text="item.escalation_label"></span></template>
                                    <template x-if="item.escalation_tier === 'normal'"><span style="font-size:12px; color:#9ca3af;">&mdash;</span></template>
                                </td>
                                <td style="max-width:100px;" class="truncate" x-text="item.assigned_name || '-'"></td>
                                <td @click.stop>
                                    <div class="sp-actions">
                                        <button @click="openRequestModal(item)" class="sp-act sp-act-gold sp-act-label" title="New Request">Req</button>
                                        <button @click="openReceiptModal(item)" class="sp-act sp-act-green" title="Log Receipt" x-show="item.overall_status !== 'received_complete' && item.overall_status !== 'verified'">✓</button>
                                        <button @click="goToCase(item.case_id, item.id)" class="sp-act sp-act-blue" title="Open Case">↗</button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Expanded Request History -->
                            <tr x-show="expandedId === item.id">
                                <td colspan="13" style="padding:0 !important; border-top:none !important;">
                                    <div style="background:#fafaf8; border-top:1px solid #e8e4dc; border-bottom:2px solid rgba(201,168,76,.3); padding:16px 24px;">
                                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                                            <div style="display:flex; align-items:center; gap:10px;">
                                                <span style="font-size:13px; font-weight:700; color:#1a2535;">Request History</span>
                                                <span style="font-size:12px; color:#9ca3af;" x-text="requestHistory.length + ' request(s)'"></span>
                                            </div>
                                            <button @click="openRequestModal(item)" class="sp-new-btn-navy" style="padding:5px 12px; font-size:12px;">+ New Request</button>
                                        </div>
                                        <template x-if="requestHistory.length === 0">
                                            <p style="text-align:center; color:#9ca3af; padding:16px 0; font-size:12px;">No requests yet. Click "New Request" to create one.</p>
                                        </template>
                                        <template x-if="requestHistory.length > 0">
                                            <div style="display:flex; flex-direction:column; gap:6px;">
                                                <template x-for="req in requestHistory" :key="req.id">
                                                    <div style="display:flex; align-items:center; justify-content:space-between; background:#fff; border-radius:8px; border:1px solid #e8e4dc; padding:8px 14px;">
                                                        <div style="display:flex; align-items:center; gap:12px; font-size:13px;">
                                                            <span class="sp-mono" style="font-weight:600;" x-text="formatDate(req.request_date)"></span>
                                                            <span class="sp-stage" style="background:rgba(201,168,76,.1); color:#7a6520; font-size:10px; padding:1px 6px;" x-text="getMethodLabel(req.request_method)"></span>
                                                            <span style="font-size:11px; color:#9ca3af; text-transform:capitalize;" x-text="(req.request_type || '').replace('_', ' ')"></span>
                                                            <span class="sp-stage" :style="{ background: req.send_status === 'draft' ? '#f3f4f6' : req.send_status === 'sent' ? '#dcfce7' : req.send_status === 'failed' ? '#fee2e2' : '#dbeafe', color: req.send_status === 'draft' ? '#6b7280' : req.send_status === 'sent' ? '#15803d' : req.send_status === 'failed' ? '#dc2626' : '#2563eb' }" style="font-size:10px; padding:1px 6px;" x-text="getSendStatusLabel(req.send_status)"></span>
                                                            <template x-if="req.sent_to"><span style="font-size:11px; color:#9ca3af;" x-text="'→ ' + req.sent_to"></span></template>
                                                        </div>
                                                        <div style="display:flex; align-items:center; gap:4px;" @click.stop>
                                                            <template x-if="req.request_method === 'email' || req.request_method === 'fax'">
                                                                <button @click="openPreviewModal(req)" class="sp-act sp-act-gold sp-act-label" x-text="req.send_status === 'draft' ? 'Preview & Send' : req.send_status === 'failed' ? 'Retry' : 'Preview'"></button>
                                                            </template>
                                                            <button @click="deleteRequest(req)" class="sp-act sp-act-red" title="Delete" style="font-size:11px; padding:3px 6px;">✕</button>
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
            </template>

            <!-- Bulk Action Bar -->
            <div x-show="selectedItems.length > 0" x-transition
                 style="position:fixed; bottom:0; left:0; right:0; background:#fff; border-top:2px solid #C9A84C; box-shadow:0 -4px 16px rgba(0,0,0,.1); z-index:50; padding:12px 24px; display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <span style="font-size:13px; font-weight:600; color:#1a2535;"><span x-text="selectedItems.length"></span> item(s) selected</span>
                    <button @click="clearSelections()" style="font-size:13px; color:#6b7280; text-decoration:underline; background:none; border:none; cursor:pointer;">Clear Selection</button>
                </div>
                <button @click="openBulkRequestModal()" class="sp-new-btn-navy">Bulk Request</button>
            </div>

            <?php include __DIR__ . '/_mr-modals.php'; ?>

        </div><!-- /sp-card -->

    </div><!-- /MR Tracker Tab -->

    <!-- ===================== HEALTH TRACKER TAB ===================== -->
    <div x-show="activeTab === 'health'" x-cloak x-data="healthLedgerPage()" x-init="init()">

        <div class="sp-card">
            <div class="sp-gold-bar"></div>
            <div style="display:flex; align-items:center; justify-content:flex-end; padding:12px 24px 0;">
                <button @click="openAddModal()" class="sp-new-btn-navy">+ Add Item</button>
            </div>
            <?php include __DIR__ . '/_health-content.php'; ?>
        </div>

    </div><!-- /Health Tracker Tab -->

</div>
