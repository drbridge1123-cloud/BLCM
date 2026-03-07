<div x-data="casesListPage()">

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">

        <!-- Gold bar -->
        <div class="sp-gold-bar"></div>

        <!-- Header -->
        <div class="sp-header" style="padding:20px 24px 16px;">
            <div style="display:flex; align-items:center; width:100%;">
                <div style="flex:1">
                    <a x-show="fromAttorneyCases" x-cloak href="/blcm/frontend/pages/attorney/index.php"
                       style="display:inline-flex; align-items:center; gap:4px; font-size:11px; color:#8a8a82; text-decoration:none; margin-bottom:4px;">&larr; Attorney Cases</a>
                    <div class="sp-eyebrow">Case Management</div>
                    <h1 class="sp-title">Cases</h1>
                </div>
                <button @click="showCreateModal = true" class="sp-new-btn">+ New Case</button>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="sp-toolbar" style="flex-wrap:wrap;">
            <div class="sp-tabs" style="flex-wrap:wrap;">
                <button class="sp-tab" :class="statusFilter === 'ini,rec,verification,rfd,neg,lit,fbc,accounting' && 'on'"
                        @click="statusFilter = statusFilter === 'ini,rec,verification,rfd,neg,lit,fbc,accounting' ? '' : 'ini,rec,verification,rfd,neg,lit,fbc,accounting'; loadData(1)">Active
                    <span class="sp-tab-count" style="background:rgba(37,99,235,.1); color:#2563eb;" x-text="summary.active ?? 0"></span>
                </button>
                <button class="sp-tab" :class="statusFilter === 'ini' && 'on'"
                        @click="statusFilter = statusFilter === 'ini' ? '' : 'ini'; loadData(1)">Treatment
                    <span class="sp-tab-count" style="background:rgba(139,92,246,.1); color:#8b5cf6;" x-text="summary.ini ?? 0"></span>
                </button>
                <button class="sp-tab" :class="statusFilter === 'rec,verification' && 'on'"
                        @click="statusFilter = statusFilter === 'rec,verification' ? '' : 'rec,verification'; loadData(1)">Records
                    <span class="sp-tab-count" style="background:rgba(234,88,12,.1); color:#ea580c;" x-text="(parseInt(summary.rec || 0) + parseInt(summary.verification || 0)) || 0"></span>
                </button>
                <button class="sp-tab" :class="statusFilter === 'rfd,neg,lit' && 'on'"
                        @click="statusFilter = statusFilter === 'rfd,neg,lit' ? '' : 'rfd,neg,lit'; loadData(1)">Attorney
                    <span class="sp-tab-count" style="background:rgba(201,168,76,.12); color:#b8960c;" x-text="(parseInt(summary.rfd || 0) + parseInt(summary.neg || 0) + parseInt(summary.lit || 0)) || 0"></span>
                </button>
                <button class="sp-tab" :class="statusFilter === 'fbc' && 'on'"
                        @click="statusFilter = statusFilter === 'fbc' ? '' : 'fbc'; loadData(1)">Final Review
                    <span class="sp-tab-count" style="background:rgba(6,182,212,.1); color:#0891b2;" x-text="summary.fbc ?? 0"></span>
                </button>
                <button class="sp-tab" :class="statusFilter === 'accounting' && 'on'"
                        @click="statusFilter = statusFilter === 'accounting' ? '' : 'accounting'; loadData(1)">Settlement
                    <span class="sp-tab-count" style="background:rgba(16,185,129,.1); color:#059669;" x-text="summary.accounting ?? 0"></span>
                </button>
                <button class="sp-tab" :class="statusFilter === 'closed' && 'on'"
                        @click="statusFilter = statusFilter === 'closed' ? '' : 'closed'; loadData(1)">Closed
                    <span class="sp-tab-count" x-text="summary.closed ?? 0"></span>
                </button>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="sp-toolbar" style="padding:12px 24px;">
            <input type="text" class="sp-search" style="width:320px" placeholder="Search cases..."
                   x-model="search" @input.debounce.300ms="loadData(1)">

            <select class="sp-select" x-model="assignedFilter" @change="loadData(1)">
                <option value="">All Staff</option>
                <template x-for="u in staffList" :key="u.id">
                    <option :value="u.id" x-text="u.display_name || u.full_name"></option>
                </template>
            </select>

            <div class="sp-toolbar-right"></div>
        </div>

        <!-- Table -->
        <div style="overflow-x:auto;">
            <table class="sp-table">
                <thead>
                    <tr>
                        <th class="cursor-pointer select-none" @click="sort('case_number')">Case #</th>
                        <th class="cursor-pointer select-none" @click="sort('client_name')">Client Name</th>
                        <th class="cursor-pointer select-none" @click="sort('client_dob')">DOB</th>
                        <th class="cursor-pointer select-none" @click="sort('doi')">DOI</th>
                        <th class="cursor-pointer select-none" @click="sort('attorney_name')">Attorney</th>
                        <th class="cursor-pointer select-none" @click="sort('assigned_name')">Assigned</th>
                        <th class="cursor-pointer select-none" @click="sort('status')">Status</th>
                        <th>Progress</th>
                        <th class="center">Issues</th>
                        <th class="cursor-pointer select-none" @click="sort('created_at')">Created</th>
                        <th class="center">Tracker</th>
                        <th x-show="$store.auth.isAdmin" class="center" style="width:40px"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr><td colspan="12" class="sp-loading">Loading cases...</td></tr>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <tr><td colspan="12" class="sp-empty">No cases found</td></tr>
                    </template>
                    <template x-for="c in items" :key="c.id">
                        <tr @click="window.location.href='/blcm/frontend/pages/bl-cases/detail.php?id='+c.id"
                            :style="$store.auth.isStaff && !c.assigned_name ? 'opacity:.5' : ''">

                            <td><span class="sp-case-num" x-text="c.case_number"></span></td>
                            <td><span class="sp-client" x-text="c.client_name"></span></td>
                            <td><span class="sp-mono" x-text="formatDate(c.client_dob)"></span></td>
                            <td><span class="sp-mono" x-text="formatDate(c.doi)"></span></td>
                            <td><span class="sp-month" x-text="c.attorney_name || '—'"></span></td>
                            <td style="white-space:nowrap;">
                                <span class="sp-month" style="vertical-align:middle;" x-text="c.assigned_name || '—'"></span>
                                <template x-if="c.assignment_status === 'pending'">
                                    <span style="display:inline-block; background:rgba(245,158,11,.1); color:#D97706; border:1px solid rgba(245,158,11,.2); font-size:9px; padding:0 5px; border-radius:4px; margin-left:5px; vertical-align:middle; line-height:18px;">Pending</span>
                                </template>
                                <template x-if="c.assignment_status === 'declined'">
                                    <span style="display:inline-block; background:rgba(239,68,68,.1); color:#ef4444; border:1px solid rgba(239,68,68,.2); font-size:9px; padding:0 5px; border-radius:4px; margin-left:5px; vertical-align:middle; line-height:18px;">Declined</span>
                                </template>
                            </td>
                            <td>
                                <span class="sp-stage" :class="caseStatusClass(c.status)" x-text="getStatusLabel(c.status)"></span>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <div style="width:56px; height:5px; background:#e8e4dc; border-radius:3px; overflow:hidden;">
                                        <div style="height:100%; background:#1a9e6a; border-radius:3px;"
                                             :style="'width:' + (c.provider_total > 0 ? Math.round(c.provider_done/c.provider_total*100) : 0) + '%'"></div>
                                    </div>
                                    <span class="sp-month" x-text="c.provider_done + '/' + c.provider_total"></span>
                                </div>
                            </td>
                            <td style="text-align:center">
                                <template x-if="c.provider_overdue > 0">
                                    <span class="sp-status" style="background:rgba(231,76,60,.08); color:#e74c3c; border:1px solid rgba(231,76,60,.15); font-size:9px;" x-text="c.provider_overdue"></span>
                                </template>
                                <template x-if="c.provider_followup > 0 && c.provider_overdue == 0">
                                    <span class="sp-status" style="background:rgba(217,119,6,.08); color:#D97706; border:1px solid rgba(217,119,6,.15); font-size:9px;" x-text="c.provider_followup"></span>
                                </template>
                                <template x-if="c.provider_overdue == 0 && c.provider_followup == 0">
                                    <span style="color:#1a9e6a; font-size:11px;">&#10003;</span>
                                </template>
                            </td>
                            <td><span class="sp-mono" x-text="formatDate(c.created_at)"></span></td>
                            <td style="text-align:center" @click.stop>
                                <button class="sp-act sp-act-gold sp-act-label" style="font-size:10px; padding:2px 8px; white-space:nowrap;"
                                        @click="goToTracker(c)" x-text="getTrackerLabel(c.status)"></button>
                            </td>
                            <td x-show="$store.auth.isAdmin" style="text-align:center" @click.stop>
                                <div style="display:flex; gap:4px; justify-content:center;">
                                    <template x-if="c.assignment_status === 'declined'">
                                        <button class="sp-act" style="color:#D97706; border-color:rgba(217,119,6,.3); background:rgba(217,119,6,.06); width:auto; padding:0 8px; font-size:10px; height:22px;" @click="openReassignModal(c)" title="Reassign">
                                            Reassign
                                        </button>
                                    </template>
                                    <button class="sp-act sp-act-red" @click="deleteCase(c.id, c.case_number, c.client_name)">
                                        <span class="sp-tip">Delete</span>
                                        &#128465;
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>


    </div><!-- /sp-card -->

    <!-- Create Case Modal -->
    <style>
    .ncm { width: 540px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
    .ncm-header { background: #0F1B2D; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; }
    .ncm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; }
    .ncm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
    .ncm-close:hover { color: rgba(255,255,255,.75); }
    .ncm-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
    .ncm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
    .ncm-req { color: var(--gold, #C9A84C); }
    .ncm-input, .ncm-select {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
    }
    .ncm-input:focus, .ncm-select:focus { border-color: var(--gold, #C9A84C); background: #fff; box-shadow: 0 0 0 3px rgba(201,168,76,.1); }
    .ncm-input::placeholder { color: #c5c5c5; }
    .ncm-input.ncm-mono { font-family: 'IBM Plex Mono', monospace; font-weight: 600; }
    .ncm-input.ncm-date { font-family: 'IBM Plex Mono', monospace; font-size: 12.5px; }
    .ncm-select { appearance: none; cursor: pointer; padding-right: 30px; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; }
    .ncm-textarea { width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px; padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit; resize: vertical; min-height: 70px; line-height: 1.5; }
    .ncm-textarea:focus { border-color: var(--gold, #C9A84C); background: #fff; box-shadow: 0 0 0 3px rgba(201,168,76,.1); }
    .ncm-textarea::placeholder { color: #c5c5c5; }
    .ncm-section { display: flex; align-items: center; gap: 10px; margin: 0; }
    .ncm-section::before, .ncm-section::after { content: ''; flex: 1; height: 1px; background: var(--border, #d0cdc5); }
    .ncm-section span { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .1em; white-space: nowrap; }
    .ncm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
    .ncm-btn-cancel { background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px; padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s; }
    .ncm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
    .ncm-btn-submit { background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px; padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s; }
    .ncm-btn-submit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
    .ncm-btn-submit:disabled { opacity: .55; cursor: not-allowed; }

    </style>

    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @keydown.escape.window="showCreateModal && (showCreateModal = false)">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showCreateModal = false"></div>
        <form @submit.prevent="createCase()" class="ncm relative z-10" @click.stop>
            <div class="ncm-header">
                <h3>New Case</h3>
                <button type="button" class="ncm-close" @click="showCreateModal = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="ncm-body">
                <div class="ncm-section"><span>Case Info</span></div>
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="ncm-label">Case Number <span class="ncm-req">*</span></label>
                        <input type="text" x-model="newCase.case_number" required class="ncm-input ncm-mono">
                    </div>
                    <div style="flex:1;">
                        <label class="ncm-label">Client Name <span class="ncm-req">*</span></label>
                        <input type="text" x-model="newCase.client_name" required class="ncm-input">
                    </div>
                </div>
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="ncm-label">Date of Birth <span class="ncm-req">*</span></label>
                        <input type="date" x-model="newCase.client_dob" required class="ncm-input ncm-date">
                    </div>
                    <div style="flex:1;">
                        <label class="ncm-label">Date of Injury <span class="ncm-req">*</span></label>
                        <input type="date" x-model="newCase.doi" required class="ncm-input ncm-date">
                    </div>
                </div>
                <div class="ncm-section"><span>Assignment</span></div>
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="ncm-label">Attorney</label>
                        <input type="text" x-model="newCase.attorney_name" class="ncm-input" placeholder="Attorney name...">
                    </div>
                    <div style="flex:1;">
                        <label class="ncm-label">Assigned To <span class="ncm-req">*</span></label>
                        <select x-model="newCase.assigned_to" required class="ncm-select">
                            <option value="">Select...</option>
                            <template x-for="u in staffList" :key="u.id">
                                <option :value="u.id" x-text="u.display_name || u.full_name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="ncm-section"><span>Notes</span></div>
                <div>
                    <textarea x-model="newCase.notes" class="ncm-textarea" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="ncm-footer">
                <button type="button" @click="showCreateModal = false" class="ncm-btn-cancel">Cancel</button>
                <button type="submit" :disabled="saving" class="ncm-btn-submit">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span x-text="saving ? 'Creating...' : 'Create Case'"></span>
                </button>
            </div>
        </form>
    </div>

<!-- Reassign Case Modal -->
<div x-show="showReassignModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45);" @click.self="showReassignModal = false">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:400px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="background:#1a2535; padding:16px 20px; display:flex; align-items:center; justify-content:space-between;">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">Reassign Case</h3>
            <button @click="showReassignModal = false" style="background:none; border:none; color:rgba(255,255,255,.4); cursor:pointer; font-size:20px;">&times;</button>
        </div>
        <div style="padding:20px;">
            <p style="font-size:13px; color:#6b7280; margin-bottom:16px;">
                Reassign <strong x-text="'#' + reassignForm.caseNumber + ' - ' + reassignForm.clientName"></strong> to a new staff member.
            </p>
            <label style="display:block; font-size:11px; font-weight:600; color:#8a8a82; text-transform:uppercase; margin-bottom:5px;">Assign To</label>
            <select x-model="reassignForm.assigned_to" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px;">
                <option value="">-- Select Staff --</option>
                <template x-for="u in staffList" :key="u.id">
                    <option :value="u.id" x-text="u.display_name || u.full_name"></option>
                </template>
            </select>
        </div>
        <div style="padding:12px 20px; border-top:1px solid #e5e7eb; display:flex; justify-content:flex-end; gap:8px;">
            <button @click="showReassignModal = false" class="sp-btn">Cancel</button>
            <button @click="submitReassign()" :disabled="saving" class="sp-new-btn-navy" x-text="saving ? 'Assigning...' : 'Assign'"></button>
        </div>
    </div>
</div>

</div>
