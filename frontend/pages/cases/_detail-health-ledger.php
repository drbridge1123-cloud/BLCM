<!-- Health Insurance Ledger Panel -->
<div class="c1-section" data-panel
     x-data="healthLedgerPanel(caseId, caseData?.case_number)" x-init="init()">

    <!-- Header -->
    <div class="c1-section-header" :class="open && 'is-open'"
         @click="open = !open">
        <div class="flex items-center gap-2.5">
            <span class="c1-num c1-num-gold">06</span>
            <h3 class="panel-title">Health Insurance Ledger</h3>
            <span class="panel-count" x-text="items.length" x-show="!loading"></span>
            <template x-if="!loading && receivedCount > 0">
                <span style="font-size:11px; color:#16a34a; font-weight:600; background:#f0fdf4; padding:2px 8px; border-radius:10px;"
                      x-text="receivedCount + ' received'"></span>
            </template>
        </div>
        <button @click.stop="goToHealthTracker()" class="panel-btn">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Manage in Tracker
        </button>
    </div>

    <!-- Collapsible Body -->
    <div x-show="open" x-collapse>

        <!-- Loading -->
        <template x-if="loading">
            <div class="flex justify-center py-8">
                <div class="spinner"></div>
            </div>
        </template>

        <template x-if="!loading">
            <div>
                <!-- Empty state -->
                <template x-if="items.length === 0">
                    <div class="text-center py-8 text-sm" style="color:var(--text-light)">
                        No health ledger requests linked to this case.
                        <br>
                        <button @click="goToHealthTracker()" class="mt-1 text-xs underline" style="color:var(--gold)">
                            Add in Health Tracker
                        </button>
                    </div>
                </template>

                <!-- Items table -->
                <template x-if="items.length > 0">
                    <div style="overflow-x:auto">
                        <table class="data-table" style="width:100%">
                            <thead>
                                <tr style="background:#b5a070">
                                    <th style="text-align:left; color:#0F1B2D; font-size:10px; font-weight:700; text-transform:uppercase; padding:8px 12px">Carrier</th>
                                    <th style="text-align:left; color:#0F1B2D; font-size:10px; font-weight:700; text-transform:uppercase; padding:8px 12px">Claim #</th>
                                    <th style="text-align:left; color:#0F1B2D; font-size:10px; font-weight:700; text-transform:uppercase; padding:8px 12px">Member ID</th>
                                    <th style="text-align:left; color:#0F1B2D; font-size:10px; font-weight:700; text-transform:uppercase; padding:8px 12px">Status</th>
                                    <th style="text-align:left; color:#0F1B2D; font-size:10px; font-weight:700; text-transform:uppercase; padding:8px 12px">Last Request</th>
                                    <th style="text-align:center; color:#0F1B2D; font-size:10px; font-weight:700; text-transform:uppercase; padding:8px 12px">#</th>
                                    <th style="text-align:left; color:#0F1B2D; font-size:10px; font-weight:700; text-transform:uppercase; padding:8px 12px">Assigned</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in items" :key="item.id">
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="px-3 py-2.5 text-sm font-medium" x-text="item.insurance_carrier"></td>
                                        <td class="px-3 py-2.5 text-xs" style="color:var(--text-light)" x-text="item.claim_number || '-'"></td>
                                        <td class="px-3 py-2.5 text-xs" style="color:var(--text-light)" x-text="item.member_id || '-'"></td>
                                        <td class="px-3 py-2.5">
                                            <span class="status-badge"
                                                  :class="'status-' + item.overall_status"
                                                  x-text="getStatusLabel(item.overall_status)"></span>
                                            <template x-if="item.is_followup_due">
                                                <span style="margin-left:4px; font-size:10px; color:#d97706; font-weight:600">⚠ Due</span>
                                            </template>
                                        </td>
                                        <td class="px-3 py-2.5 text-xs">
                                            <span x-text="item.last_request_date ? formatDate(item.last_request_date) : '-'"></span>
                                            <template x-if="item.last_request_method">
                                                <span class="ml-1 text-xs px-1.5 py-0.5 rounded"
                                                      style="background:#f0f2f5; color:var(--text-light)"
                                                      x-text="item.last_request_method"></span>
                                            </template>
                                        </td>
                                        <td class="px-3 py-2.5 text-xs text-center" style="color:var(--text-light)" x-text="item.request_count"></td>
                                        <td class="px-3 py-2.5 text-xs" style="color:var(--text-light)" x-text="item.assigned_name || '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
