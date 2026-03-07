<!-- Traffic Cases Tab -->
<div style="padding:12px 24px 0;">
    <div style="display:flex; align-items:center; gap:6px; margin-bottom:0;">
        <div class="sp-tabs">
            <button class="sp-tab" :class="statusFilter === '' && 'on'"
                    @click="statusFilter = ''; loadCases()">All
                <span class="sp-tab-count" style="background:rgba(37,99,235,.1); color:#2563eb;" x-text="(summary.active_count||0) + (summary.resolved_count||0)"></span>
            </button>
            <button class="sp-tab" :class="statusFilter === 'active' && 'on'"
                    @click="statusFilter = 'active'; loadCases()">Active
                <span class="sp-tab-count" style="background:rgba(37,99,235,.1); color:#2563eb;" x-text="summary.active_count || 0"></span>
            </button>
            <button class="sp-tab" :class="statusFilter === 'resolved' && 'on'"
                    @click="statusFilter = 'resolved'; loadCases()">Resolved
                <span class="sp-tab-count" style="background:rgba(16,185,129,.1); color:#059669;" x-text="summary.resolved_count || 0"></span>
            </button>
            <button class="sp-tab" :class="statusFilter === 'unpaid' && 'on'"
                    @click="statusFilter = 'unpaid'; loadCases()">Unpaid
                <span class="sp-tab-count" style="background:rgba(217,119,6,.1); color:#d97706;" x-text="summary.unpaid_count || 0"></span>
            </button>
        </div>
        <input type="text" x-model="search" @input="handleSearch()" placeholder="Search client, case #..."
               class="sp-search" style="margin-left:auto;">
    </div>
</div>

<div style="overflow-x:auto;">
    <table class="sp-table">
        <thead>
            <tr>
                <th>Client</th>
                <th>Court</th>
                <th>Court Date</th>
                <th>Charge</th>
                <th>Case #</th>
                <th class="center">Disposition</th>
                <th class="right">Commission</th>
                <th class="center">Status</th>
                <th class="center">Paid</th>
                <th class="center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <template x-if="loading">
                <tr style="cursor:default;"><td colspan="10" class="sp-loading">Loading...</td></tr>
            </template>
            <template x-if="!loading && cases.length === 0">
                <tr style="cursor:default;"><td colspan="10" class="sp-empty">No traffic cases found</td></tr>
            </template>
            <template x-for="c in cases" :key="c.id">
                <tr style="cursor:default;" :style="c.paid == 1 ? 'background:rgba(26,158,106,.03);' : ''">
                    <td>
                        <div class="sp-client" style="font-size:12px;" x-text="c.client_name"></div>
                        <div x-show="c.client_phone" style="font-size:10px; color:#8a8a82; margin-top:1px;" x-text="formatPhoneNumber(c.client_phone)"></div>
                    </td>
                    <td><span style="font-size:12px; color:#1a2535;" x-text="c.court || '—'"></span></td>
                    <td><span class="sp-date-main" style="font-size:11px;" x-text="formatDate(c.court_date)"></span></td>
                    <td><span style="font-size:12px; color:#1a2535;" x-text="c.charge || '—'"></span></td>
                    <td><span class="sp-case-num" style="font-size:11px;" x-text="c.case_number || '—'"></span></td>
                    <td style="text-align:center;">
                        <span class="sp-stage" style="font-size:9px; padding:2px 8px;"
                              :style="({
                                  'pending': 'background:rgba(138,138,130,.08); color:#8a8a82; border:1px solid rgba(138,138,130,.15);',
                                  'dismissed': 'background:rgba(26,158,106,.08); color:#1a9e6a; border:1px solid rgba(26,158,106,.15);',
                                  'amended': 'background:rgba(37,99,235,.08); color:#2563eb; border:1px solid rgba(37,99,235,.15);',
                                  'other': 'background:rgba(124,92,191,.08); color:#7C5CBF; border:1px solid rgba(124,92,191,.15);'
                              })[c.disposition] || ''"
                              x-text="(c.disposition || 'pending').toUpperCase()"></span>
                    </td>
                    <td style="text-align:right;">
                        <span :class="c.commission > 0 ? 'sp-comm' : 'sp-comm-zero'"
                              x-text="'$' + parseFloat(c.commission || 0).toFixed(2)"></span>
                    </td>
                    <td style="text-align:center;">
                        <span class="sp-status"
                              :class="c.status === 'active' ? 'sp-status-in-progress' : 'sp-status-paid'"
                              x-text="c.status.toUpperCase()"></span>
                    </td>
                    <td style="text-align:center;">
                        <button x-show="isAdmin" @click="togglePaid(c)"
                                class="sp-check" :class="c.paid == 1 && 'checked'"
                                x-text="c.paid == 1 ? '✓' : ''"></button>
                        <span x-show="!isAdmin" style="font-size:10px; font-weight:600;"
                              :style="c.paid == 1 ? 'color:#1a9e6a;' : 'color:#8a8a82;'"
                              x-text="c.paid == 1 ? 'Paid' : 'Unpaid'"></span>
                    </td>
                    <td>
                        <div class="sp-actions">
                            <button @click="openEditModal(c)" class="sp-act sp-act-gold">
                                <span>✎</span>
                                <span class="sp-tip">Edit</span>
                            </button>
                            <button @click="deleteCase(c.id)" class="sp-act sp-act-red">
                                <span>✕</span>
                                <span class="sp-tip">Delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>
