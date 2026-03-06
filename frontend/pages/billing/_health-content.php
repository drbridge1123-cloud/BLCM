<!-- Summary Cards -->
<div style="display:grid; grid-template-columns:repeat(6,1fr); gap:10px; padding:0 24px 12px;">
    <div @click="toggleStatusFilter('')" class="sp-stat" style="cursor:pointer; border-radius:8px; padding:8px 12px; border:1.5px solid #e8e4dc;"
         :style="statusFilter === '' ? 'border-color:#C9A84C; box-shadow:0 0 0 2px rgba(201,168,76,.15);' : ''">
        <div class="sp-stat-label" style="font-size:10px;">Total</div>
        <div class="sp-stat-num" style="color:#1a2535;" x-text="summary.total ?? '-'"></div>
    </div>
    <div @click="toggleStatusFilter('not_started')" class="sp-stat" style="cursor:pointer; border-radius:8px; padding:8px 12px; border:1.5px solid #e8e4dc;"
         :style="statusFilter === 'not_started' ? 'border-color:#9ca3af; box-shadow:0 0 0 2px rgba(156,163,175,.15);' : ''">
        <div class="sp-stat-label" style="font-size:10px;">Not Started</div>
        <div class="sp-stat-num" style="color:#6b7280;" x-text="summary.not_started ?? '-'"></div>
    </div>
    <div @click="toggleStatusFilter('requesting')" class="sp-stat" style="cursor:pointer; border-radius:8px; padding:8px 12px; border:1.5px solid #e8e4dc;"
         :style="statusFilter === 'requesting' ? 'border-color:#3b82f6; box-shadow:0 0 0 2px rgba(59,130,246,.15);' : ''">
        <div class="sp-stat-label" style="font-size:10px;">Requesting</div>
        <div class="sp-stat-num" style="color:#2563eb;" x-text="summary.requesting ?? '-'"></div>
    </div>
    <div @click="toggleStatusFilter('follow_up')" class="sp-stat" style="cursor:pointer; border-radius:8px; padding:8px 12px; border:1.5px solid #e8e4dc;"
         :style="statusFilter === 'follow_up' ? 'border-color:#f59e0b; box-shadow:0 0 0 2px rgba(245,158,11,.15);' : ''">
        <div class="sp-stat-label" style="font-size:10px;">Follow Up</div>
        <div class="sp-stat-num" style="color:#d97706;" x-text="summary.follow_up ?? '-'"></div>
    </div>
    <div @click="toggleStatusFilter('received')" class="sp-stat" style="cursor:pointer; border-radius:8px; padding:8px 12px; border:1.5px solid #e8e4dc;"
         :style="statusFilter === 'received' ? 'border-color:#22c55e; box-shadow:0 0 0 2px rgba(34,197,94,.15);' : ''">
        <div class="sp-stat-label" style="font-size:10px;">Received</div>
        <div class="sp-stat-num" style="color:#16a34a;" x-text="summary.received ?? '-'"></div>
    </div>
    <div @click="toggleStatusFilter('done')" class="sp-stat" style="cursor:pointer; border-radius:8px; padding:8px 12px; border:1.5px solid #e8e4dc;"
         :style="statusFilter === 'done' ? 'border-color:#059669; box-shadow:0 0 0 2px rgba(5,150,105,.15);' : ''">
        <div class="sp-stat-label" style="font-size:10px;">Done</div>
        <div class="sp-stat-num" style="color:#059669;" x-text="summary.done ?? '-'"></div>
    </div>
</div>

<!-- Filters -->
<div style="padding:0 24px 12px;">
    <div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px;">
        <input type="text" x-model="search" @input.debounce.300ms="loadData(1)" placeholder="Search client, case #, or carrier..."
               class="sp-search" style="flex:1; min-width:200px;">
        <select x-model="statusFilter" @change="loadData(1)" class="sp-select">
            <option value="">All Statuses</option><option value="not_started">Not Started</option><option value="requesting">Requesting</option>
            <option value="follow_up">Follow Up</option><option value="received">Received</option><option value="done">Done</option>
        </select>
        <select x-model="assignedFilter" @change="loadData(1)" class="sp-select">
            <option value="">All Staff</option>
            <template x-for="s in staffList" :key="s.id"><option :value="s.id" x-text="s.display_name || s.full_name"></option></template>
        </select>
        <button @click="resetFilters()" class="sp-btn"
                x-show="search || statusFilter || tierFilter || assignedFilter">Reset</button>
    </div>
</div>

<!-- Loading -->
<template x-if="loading"><div class="sp-loading"><div class="spinner"></div></div></template>

<!-- Table -->
<template x-if="!loading">
    <div style="overflow-x:auto;" x-init="initScrollContainer($el)">
        <table class="sp-table sp-table-compact">
            <thead><tr>
                <th style="width:32px;"></th>
                <th class="cursor-pointer select-none" @click="sort('client_name')">Client</th>
                <th class="cursor-pointer select-none" @click="sort('case_number')">Case #</th>
                <th class="cursor-pointer select-none" @click="sort('insurance_carrier')">Carrier</th>
                <th class="cursor-pointer select-none" @click="sort('overall_status')">Status</th>
                <th class="cursor-pointer select-none" @click="sort('last_request_date')">Last Request</th>
                <th class="cursor-pointer select-none" @click="sort('request_count')">#</th>
                <th>Follow-up</th><th>Days</th><th>Assigned</th><th>Actions</th>
            </tr></thead>
            <tbody>
                <template x-if="items.length === 0"><tr><td colspan="11" class="sp-empty">No records found</td></tr></template>
                <template x-for="item in items" :key="item.id">
                    <tr><td colspan="11" style="padding:0 !important;">
                        <div :style="expandedId === item.id ? 'border:2px solid rgba(201,168,76,.4); border-radius:8px; background:#fff;' : ''" style="transition:all .15s;">
                            <div style="display:flex; align-items:center; cursor:pointer; transition:background .1s;" :class="{ 'hl-row-followup': item.is_followup_due && expandedId !== item.id }" @click="toggleExpand(item.id)"
                                 onmouseover="this.style.background='rgba(201,168,76,.03)'" onmouseout="this.style.background=''">
                                <div style="width:36px; padding:10px 10px 10px 14px; flex-shrink:0;">
                                    <svg style="width:14px; height:14px; color:#9ca3af; transition:transform .15s;" :style="expandedId === item.id ? 'transform:rotate(90deg);' : ''" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 4.707a1 1 0 011.414 0L14.414 10l-5.707 5.707a1 1 0 01-1.414-1.414L11.586 10 7.293 5.707a1 1 0 010-1z"/></svg>
                                </div>
                                <div style="flex:1; display:grid; grid-template-columns:repeat(10,1fr); gap:8px; padding:10px 14px 10px 0; align-items:center; font-size:13px;">
                                    <div style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-weight:600;" x-text="item.client_name"></div>
                                    <div>
                                        <template x-if="item.case_id"><a :href="'/blcm/frontend/pages/bl-cases/detail.php?id=' + item.case_id" style="color:#C9A84C; text-decoration:none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" x-text="item.case_number" @click.stop></a></template>
                                        <template x-if="!item.case_id"><span style="color:#9ca3af;" x-text="item.case_number || '-'"></span></template>
                                    </div>
                                    <div style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="item.insurance_carrier"></div>
                                    <div><span class="status-badge" :class="'status-' + item.overall_status" x-text="getStatusLabel(item.overall_status)"></span></div>
                                    <div style="white-space:nowrap;">
                                        <template x-if="item.last_request_date"><div style="display:flex; align-items:center; gap:4px;"><span class="sp-mono" x-text="formatDate(item.last_request_date)"></span><span class="sp-stage" style="background:rgba(201,168,76,.1); color:#7a6520; font-size:10px; padding:1px 5px;" x-text="item.last_request_method || ''"></span></div></template>
                                        <template x-if="!item.last_request_date"><span style="color:#d1d5db;">-</span></template>
                                    </div>
                                    <div style="text-align:center;" x-text="item.request_count || '-'"></div>
                                    <div style="white-space:nowrap;">
                                        <template x-if="item.next_followup_date"><span class="sp-mono" :style="item.is_followup_due ? 'color:#d97706; font-weight:600;' : ''" x-text="formatDate(item.next_followup_date)"></span></template>
                                        <template x-if="!item.next_followup_date"><span style="color:#d1d5db;">-</span></template>
                                    </div>
                                    <div style="text-align:center;">
                                        <template x-if="item.days_since_request !== null"><span class="sp-mono" :style="item.days_since_request > 30 ? 'color:#ef4444; font-weight:600;' : item.days_since_request > 14 ? 'color:#d97706;' : ''" x-text="item.days_since_request + 'd'"></span></template>
                                        <template x-if="item.days_since_request === null"><span style="color:#d1d5db;">-</span></template>
                                    </div>
                                    <div style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#6b7280;" x-text="item.assigned_name || '-'"></div>
                                    <div style="display:flex; gap:4px;" @click.stop>
                                        <button @click="openRequestModal(item)" class="sp-act sp-act-gold sp-act-label" title="New Request">Req</button>
                                        <button @click="updateStatus(item.id, 'received')" class="sp-act sp-act-green" title="Mark Received" x-show="item.overall_status !== 'received' && item.overall_status !== 'done'">✓</button>
                                        <button @click="openEditModal(item)" class="sp-act sp-act-gold" title="Edit">✎</button>
                                        <button @click="deleteItem(item.id)" class="sp-act sp-act-red" title="Delete">✕</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Expanded: Request History -->
                            <template x-if="expandedId === item.id">
                                <div style="background:#fafaf8; border-top:1px solid #e8e4dc; padding:16px 24px;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                                        <span style="font-size:13px; font-weight:700; color:#1a2535;">Request History</span>
                                        <button @click="openRequestModal(item)" class="sp-new-btn-navy" style="padding:5px 12px; font-size:12px;">+ New Request</button>
                                    </div>
                                    <template x-if="requestHistory.length === 0"><p style="text-align:center; color:#9ca3af; padding:16px 0; font-size:12px;">No requests yet</p></template>
                                    <template x-if="requestHistory.length > 0">
                                        <div style="display:flex; flex-direction:column; gap:6px;">
                                            <template x-for="req in requestHistory" :key="req.id">
                                                <div style="display:flex; align-items:center; gap:12px; background:#fff; border-radius:8px; padding:8px 14px; border:1px solid #e8e4dc; font-size:13px;">
                                                    <span class="sp-mono" style="font-weight:600; white-space:nowrap;" x-text="formatDate(req.request_date)"></span>
                                                    <span class="sp-stage" :style="{ background: req.request_method === 'email' ? '#ccfbf1' : req.request_method === 'fax' ? '#ede9fe' : req.request_method === 'portal' ? '#dbeafe' : req.request_method === 'phone' ? '#fef3c7' : '#f3f4f6', color: req.request_method === 'email' ? '#0f766e' : req.request_method === 'fax' ? '#7c3aed' : req.request_method === 'portal' ? '#2563eb' : req.request_method === 'phone' ? '#b45309' : '#6b7280' }" style="font-size:10px; padding:1px 6px;" x-text="req.request_method"></span>
                                                    <span style="font-size:11px; color:#9ca3af; text-transform:capitalize;" x-text="(req.request_type || '').replace('_',' ')"></span>
                                                    <span style="font-size:11px; color:#6b7280;" x-text="req.sent_to ? 'To: ' + req.sent_to : ''"></span>
                                                    <div style="flex:1;"></div>
                                                    <span class="sp-stage" :style="{ background: req.send_status === 'draft' ? '#f3f4f6' : req.send_status === 'sent' ? '#dcfce7' : req.send_status === 'failed' ? '#fee2e2' : '#fef3c7', color: req.send_status === 'draft' ? '#6b7280' : req.send_status === 'sent' ? '#15803d' : req.send_status === 'failed' ? '#dc2626' : '#b45309' }" style="font-size:10px; padding:1px 6px; border-radius:999px;" x-text="req.send_status"></span>
                                                    <template x-if="req.send_status === 'draft' && (req.request_method === 'email' || req.request_method === 'fax')"><button @click="openSendModal(req)" class="sp-act sp-act-blue" style="font-size:11px; padding:3px 8px;">Preview & Send</button></template>
                                                    <template x-if="req.send_status === 'failed' && (req.request_method === 'email' || req.request_method === 'fax')"><button @click="openSendModal(req)" class="sp-act sp-act-red" style="font-size:11px; padding:3px 8px;">Retry</button></template>
                                                    <span style="font-size:11px; color:#9ca3af;" x-text="req.created_by_name || ''"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </td></tr>
                </template>
            </tbody>
        </table>
    </div>
</template>

<?php include __DIR__ . '/_health-modals.php'; ?>
