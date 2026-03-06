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

        <!-- Provider Assignments -->
        <template x-if="pendingProviderAssignments.length > 0">
            <div>
                <div style="padding:8px 16px; background:#fef3c7; border-bottom:1px solid #fcd34d; display:flex; align-items:center; gap:8px;">
                    <span style="font-size:13px; font-weight:700; color:#92400e;">Provider Assignments</span>
                    <span style="background:#f59e0b; color:#fff; font-size:10px; font-weight:700; padding:1px 7px; border-radius:999px;" x-text="pendingProviderAssignments.length"></span>
                </div>
                <template x-for="pa in pendingProviderAssignments" :key="'prov-' + pa.id">
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
                                <span style="font-size:11px; color:#9ca3af;" x-text="'Deadline: ' + (pa.deadline || '—')"></span>
                                <template x-if="pa.activated_by_name">
                                    <span style="font-size:11px; color:#9ca3af;" x-text="'From: ' + pa.activated_by_name"></span>
                                </template>
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
                            <button @click="acceptProviderAssignment(pa.id)" style="padding:4px 12px; font-size:12px; font-weight:600; color:#fff; background:#10b981; border:none; border-radius:6px; cursor:pointer;">Accept</button>
                            <button @click="declineProviderAssignment(pa.id)" style="padding:4px 12px; font-size:12px; font-weight:600; color:#fff; background:#ef4444; border:none; border-radius:6px; cursor:pointer;">Decline</button>
                        </div>
                    </div>
                </template>
            </div>
        </template>

    </div>
</template>
