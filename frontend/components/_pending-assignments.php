<!-- Pending Assignments Banner (Case + Provider) -->
<template x-if="pendingCaseAssignments.length > 0 || pendingProviderAssignments.length > 0">
    <div style="background:#fffbeb; border-radius:8px; border:1px solid #fcd34d; margin:0 24px 12px; overflow:hidden;">

        <!-- Case Assignments -->
        <template x-if="pendingCaseAssignments.length > 0">
            <div>
                <div style="padding:8px 16px; background:#fef3c7; border-bottom:1px solid #fcd34d; display:flex; align-items:center; gap:8px;">
                    <span style="font-size:13px; font-weight:700; color:#92400e;">Case Assignments</span>
                    <span style="background:#f59e0b; color:#fff; font-size:10px; font-weight:700; padding:1px 7px; border-radius:999px;" x-text="pendingCaseAssignments.length"></span>
                </div>
                <template x-for="pa in pendingCaseAssignments" :key="'case-' + pa.id">
                    <div style="padding:8px 16px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid rgba(253,224,71,.3);">
                        <div style="min-width:0;">
                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <span style="font-size:13px; font-weight:600; color:#1a2535;" x-text="'#' + pa.case_number"></span>
                                <span style="font-size:11px; color:#9ca3af;">|</span>
                                <span style="font-size:12px; color:#6b7280;" x-text="pa.client_name"></span>
                                <template x-if="pa.status">
                                    <span style="font-size:10px; font-weight:600; color:#6366f1; background:#eef2ff; padding:1px 6px; border-radius:4px; text-transform:uppercase;" x-text="pa.status"></span>
                                </template>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px; margin-top:2px;">
                                <span style="font-size:11px; color:#9ca3af;" x-text="'DOI: ' + formatDate(pa.doi)"></span>
                                <template x-if="pa.assigned_by_name">
                                    <span style="font-size:11px; color:#9ca3af;" x-text="'From: ' + pa.assigned_by_name"></span>
                                </template>
                            </div>
                        </div>
                        <div style="display:flex; gap:6px; flex-shrink:0;">
                            <button @click="acceptCaseAssignment(pa.id)" style="padding:4px 12px; font-size:12px; font-weight:600; color:#fff; background:#10b981; border:none; border-radius:6px; cursor:pointer;">Accept</button>
                            <button @click="declineCaseAssignment(pa.id)" style="padding:4px 12px; font-size:12px; font-weight:600; color:#fff; background:#ef4444; border:none; border-radius:6px; cursor:pointer;">Decline</button>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- Provider Assignments (grouped by case) -->
        <template x-if="pendingProviderAssignments.length > 0">
            <div>
                <div style="padding:8px 16px; background:#fef3c7; border-bottom:1px solid #fcd34d; display:flex; align-items:center; gap:8px;">
                    <span style="font-size:13px; font-weight:700; color:#92400e;">Provider Assignments</span>
                    <span style="background:#f59e0b; color:#fff; font-size:10px; font-weight:700; padding:1px 7px; border-radius:999px;" x-text="pendingProviderAssignments.length"></span>
                </div>

                <template x-for="group in getGroupedProviderAssignments()" :key="'grp-' + group.case_id">
                    <div style="padding:10px 16px; border-bottom:1px solid rgba(253,224,71,.3);">
                        <!-- Case header row -->
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                            <div style="min-width:0; flex:1;">
                                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                    <span style="font-size:13px; font-weight:700; color:#1a2535;" x-text="'Case #' + group.case_number"></span>
                                    <span style="font-size:11px; color:#9ca3af;">|</span>
                                    <span style="font-size:12.5px; color:#6b7280;" x-text="group.client_name"></span>
                                    <span style="font-size:10px; font-weight:600; color:#92400e; background:rgba(245,158,11,.15); padding:1px 7px; border-radius:4px;" x-text="group.providers.length + ' provider' + (group.providers.length > 1 ? 's' : '')"></span>
                                </div>
                                <div style="display:flex; align-items:center; gap:10px; margin-top:3px;">
                                    <span style="font-size:11px; color:#9ca3af;" x-text="'Deadline: ' + (group.deadline || '—')"></span>
                                    <template x-if="group.activated_by_name">
                                        <span style="font-size:11px; color:#9ca3af;" x-text="'From: ' + group.activated_by_name"></span>
                                    </template>
                                    <template x-if="getRecordLabels(group)">
                                        <span style="font-size:11px; color:#9ca3af;">Records: <strong x-text="getRecordLabels(group)"></strong></span>
                                    </template>
                                </div>
                            </div>
                            <div style="display:flex; gap:6px; flex-shrink:0;">
                                <button @click="acceptAllProviders(group)" style="padding:4px 12px; font-size:12px; font-weight:600; color:#fff; background:#10b981; border:none; border-radius:6px; cursor:pointer; white-space:nowrap;">Accept All</button>
                                <button @click="declineAllProviders(group)" style="padding:4px 12px; font-size:12px; font-weight:600; color:#fff; background:#ef4444; border:none; border-radius:6px; cursor:pointer; white-space:nowrap;">Decline All</button>
                            </div>
                        </div>

                        <!-- Provider list (compact) -->
                        <div style="margin-top:6px; padding-left:4px;">
                            <template x-for="(p, idx) in group.providers" :key="p.id">
                                <template x-if="idx < 3 || isGroupExpanded(group.case_id)">
                                    <div style="font-size:12px; color:#4b5563; padding:1.5px 0; display:flex; align-items:center; gap:5px;">
                                        <span style="color:#d1d5db;">&#8226;</span>
                                        <span x-text="p.provider_name"></span>
                                        <span style="font-size:10.5px; color:#9ca3af;" x-text="getProviderTypeLabel ? getProviderTypeLabel(p.provider_type) : (p.provider_type || '')"></span>
                                    </div>
                                </template>
                            </template>
                            <template x-if="group.providers.length > 3 && !isGroupExpanded(group.case_id)">
                                <button @click="toggleGroupExpand(group.case_id)"
                                    style="font-size:11.5px; color:#b45309; font-weight:600; background:none; border:none; cursor:pointer; padding:2px 0; margin-top:1px;">
                                    ... + <span x-text="group.providers.length - 3"></span> more
                                </button>
                            </template>
                            <template x-if="group.providers.length > 3 && isGroupExpanded(group.case_id)">
                                <button @click="toggleGroupExpand(group.case_id)"
                                    style="font-size:11.5px; color:#b45309; font-weight:600; background:none; border:none; cursor:pointer; padding:2px 0; margin-top:1px;">
                                    Show less
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>

    </div>
</template>
