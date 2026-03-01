<div x-data="healthTrackerPage()">

    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-v2-text">Health Tracker</h1>
            <p class="text-sm text-v2-text-light">Track health insurance ledger items and requests</p>
        </div>
        <div class="flex items-center gap-3 text-sm">
            <div class="bg-white border border-v2-card-border rounded-lg px-3 py-1.5 flex items-center gap-2">
                <span class="text-v2-text-light">Total:</span>
                <span class="font-semibold text-v2-text" x-text="pagination ? pagination.total : 0"></span>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg px-3 py-1.5 flex items-center gap-2">
                <span class="text-blue-600">Requesting:</span>
                <span class="font-semibold text-blue-700" x-text="stats.requesting"></span>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-1.5 flex items-center gap-2">
                <span class="text-yellow-600">Follow Up:</span>
                <span class="font-semibold text-yellow-700" x-text="stats.follow_up"></span>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-4 mb-4">
        <div class="flex items-center gap-3 flex-wrap">
            <input type="text" x-model="search" @input.debounce.300ms="loadData(1)" placeholder="Search client, case #, carrier..."
                   class="px-3 py-2 border border-v2-card-border rounded-lg text-sm w-64 focus:ring-2 focus:ring-gold outline-none">
            <select x-model="statusFilter" @change="loadData(1)" class="px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                <option value="">All Statuses</option>
                <option value="not_started">Not Started</option>
                <option value="requesting">Requesting</option>
                <option value="follow_up">Follow Up</option>
                <option value="received">Received</option>
                <option value="done">Done</option>
            </select>
            <select x-model="assignedFilter" @change="loadData(1)" class="px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                <option value="">All Assigned</option>
                <template x-for="u in users" :key="u.id">
                    <option :value="u.id" x-text="u.full_name"></option>
                </template>
            </select>
            <button @click="resetFilters()" class="text-xs text-gold hover:underline"
                    x-show="search || statusFilter || assignedFilter">Reset Filters</button>
            <div class="ml-auto flex items-center gap-2">
                <button @click="showImportModal = true" class="px-3 py-2 text-sm border border-v2-card-border rounded-lg hover:bg-v2-bg text-v2-text-mid">
                    Import CSV
                </button>
                <button @click="openAddModal()" class="px-4 py-2 bg-gold text-navy font-semibold text-sm font-medium rounded-lg hover:bg-gold/90">
                    + New Item
                </button>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white rounded-xl shadow-sm border border-v2-card-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-v2-bg border-b">
                    <tr>
                        <th class="px-3 py-3 text-left font-medium text-v2-text-mid">Case #</th>
                        <th class="px-3 py-3 text-left font-medium text-v2-text-mid">Client</th>
                        <th class="px-3 py-3 text-left font-medium text-v2-text-mid">Insurance Carrier</th>
                        <th class="px-3 py-3 text-left font-medium text-v2-text-mid">Claim #</th>
                        <th class="px-3 py-3 text-center font-medium text-v2-text-mid">Status</th>
                        <th class="px-3 py-3 text-center font-medium text-v2-text-mid">Last Request</th>
                        <th class="px-3 py-3 text-center font-medium text-v2-text-mid">Next Follow-up</th>
                        <th class="px-3 py-3 text-left font-medium text-v2-text-mid">Assigned</th>
                        <th class="px-3 py-3 text-center font-medium text-v2-text-mid">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr><td colspan="9" class="px-4 py-12 text-center text-v2-text-light">Loading health ledger data...</td></tr>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <tr><td colspan="9" class="px-4 py-12 text-center text-v2-text-light">No items found</td></tr>
                    </template>
                    <template x-for="item in items" :key="item.id">
                        <tr class="border-b hover:bg-v2-bg">
                            <td class="px-3 py-2.5 font-medium text-v2-text" x-text="item.case_number || '-'"></td>
                            <td class="px-3 py-2.5" x-text="item.client_name"></td>
                            <td class="px-3 py-2.5" x-text="item.insurance_carrier"></td>
                            <td class="px-3 py-2.5 text-v2-text-light" x-text="item.claim_number || '-'"></td>
                            <td class="px-3 py-2.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                      :class="getHealthStatusColor(item.overall_status)"
                                      x-text="formatHealthStatus(item.overall_status)"></span>
                            </td>
                            <td class="px-3 py-2.5 text-center text-v2-text-light" x-text="item.last_request_date ? formatDate(item.last_request_date) : '-'"></td>
                            <td class="px-3 py-2.5 text-center text-v2-text-light" x-text="item.last_next_followup ? formatDate(item.last_next_followup) : '-'"></td>
                            <td class="px-3 py-2.5 text-v2-text-mid" x-text="item.assigned_name || '-'"></td>
                            <td class="px-3 py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="openEditModal(item)" class="px-2 py-1 text-xs bg-v2-bg hover:bg-v2-bg rounded">Edit</button>
                                    <button @click="viewRequests(item)" class="px-2 py-1 text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 rounded">Requests</button>
                                    <button @click="deleteItem(item)" class="px-2 py-1 text-xs bg-red-50 text-red-600 hover:bg-red-100 rounded">Del</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Add Item Modal -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showAddModal=false" @keydown.escape.window="showAddModal=false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold">New Health Ledger Item</h3>
                <button @click="showAddModal=false" class="text-v2-text-light hover:text-v2-text-mid">&times;</button>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Client Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="addForm.client_name" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Insurance Carrier <span class="text-red-500">*</span></label>
                        <input type="text" x-model="addForm.insurance_carrier" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Case Number</label>
                        <input type="text" x-model="addForm.case_number" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Claim Number</label>
                        <input type="text" x-model="addForm.claim_number" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Member ID</label>
                        <input type="text" x-model="addForm.member_id" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Assigned To</label>
                        <select x-model="addForm.assigned_to" class="w-full px-3 py-2 border rounded-lg text-sm">
                            <option value="">Unassigned</option>
                            <template x-for="u in users" :key="u.id">
                                <option :value="u.id" x-text="u.full_name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Carrier Contact Email</label>
                        <input type="email" x-model="addForm.carrier_contact_email" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Carrier Contact Fax</label>
                        <input type="text" x-model="addForm.carrier_contact_fax" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-v2-text-mid mb-1">Note</label>
                    <textarea x-model="addForm.note" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t flex justify-end gap-2">
                <button @click="showAddModal=false" class="px-4 py-2 text-sm text-v2-text-mid hover:bg-v2-bg rounded-lg">Cancel</button>
                <button @click="createItem()" :disabled="saving" class="px-4 py-2 text-sm bg-gold text-navy font-semibold rounded-lg hover:bg-gold/90 disabled:opacity-50">
                    <span x-text="saving ? 'Saving...' : 'Create Item'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showEditModal=false" @keydown.escape.window="showEditModal=false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold">Edit Health Ledger Item</h3>
                <button @click="showEditModal=false" class="text-v2-text-light hover:text-v2-text-mid">&times;</button>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Client Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="editForm.client_name" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Insurance Carrier <span class="text-red-500">*</span></label>
                        <input type="text" x-model="editForm.insurance_carrier" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Case Number</label>
                        <input type="text" x-model="editForm.case_number" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Claim Number</label>
                        <input type="text" x-model="editForm.claim_number" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Member ID</label>
                        <input type="text" x-model="editForm.member_id" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Status</label>
                        <select x-model="editForm.overall_status" class="w-full px-3 py-2 border rounded-lg text-sm">
                            <option value="not_started">Not Started</option>
                            <option value="requesting">Requesting</option>
                            <option value="follow_up">Follow Up</option>
                            <option value="received">Received</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Assigned To</label>
                        <select x-model="editForm.assigned_to" class="w-full px-3 py-2 border rounded-lg text-sm">
                            <option value="">Unassigned</option>
                            <template x-for="u in users" :key="u.id">
                                <option :value="u.id" x-text="u.full_name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Carrier Contact Email</label>
                        <input type="email" x-model="editForm.carrier_contact_email" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-v2-text-mid mb-1">Carrier Contact Fax</label>
                    <input type="text" x-model="editForm.carrier_contact_fax" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-v2-text-mid mb-1">Note</label>
                    <textarea x-model="editForm.note" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t flex justify-end gap-2">
                <button @click="showEditModal=false" class="px-4 py-2 text-sm text-v2-text-mid hover:bg-v2-bg rounded-lg">Cancel</button>
                <button @click="updateItem()" :disabled="saving" class="px-4 py-2 text-sm bg-gold text-navy font-semibold rounded-lg hover:bg-gold/90 disabled:opacity-50">
                    <span x-text="saving ? 'Saving...' : 'Update Item'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- View Requests Slide-out Panel -->
    <div x-show="showRequests" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 z-50 w-full max-w-lg bg-white shadow-2xl border-l border-v2-card-border flex flex-col">

        <!-- Panel Header -->
        <div class="px-6 py-4 border-b flex items-center justify-between bg-v2-bg">
            <div>
                <h3 class="text-lg font-semibold">Request History</h3>
                <p class="text-xs text-v2-text-light" x-text="requestsFor ? requestsFor.client_name + ' - ' + requestsFor.insurance_carrier : ''"></p>
            </div>
            <button @click="showRequests=false" class="text-v2-text-light hover:text-v2-text-mid">&times;</button>
        </div>

        <!-- New Request Form -->
        <div class="px-6 py-4 border-b bg-blue-50/50">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold text-v2-text-mid">New Request</h4>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-v2-text-mid mb-1">Type <span class="text-red-500">*</span></label>
                    <select x-model="requestForm.request_type" class="w-full px-2 py-1.5 border rounded-lg text-sm">
                        <option value="">Select...</option>
                        <option value="initial">Initial</option>
                        <option value="follow_up">Follow Up</option>
                        <option value="re_request">Re-Request</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-v2-text-mid mb-1">Method <span class="text-red-500">*</span></label>
                    <select x-model="requestForm.request_method" class="w-full px-2 py-1.5 border rounded-lg text-sm">
                        <option value="">Select...</option>
                        <option value="fax">Fax</option>
                        <option value="email">Email</option>
                        <option value="portal">Portal</option>
                        <option value="phone">Phone</option>
                        <option value="mail">Mail</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-v2-text-mid mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" x-model="requestForm.request_date" class="w-full px-2 py-1.5 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-v2-text-mid mb-1">Sent To</label>
                    <input type="text" x-model="requestForm.sent_to" class="w-full px-2 py-1.5 border rounded-lg text-sm" placeholder="Recipient">
                </div>
                <div>
                    <label class="block text-xs font-medium text-v2-text-mid mb-1">Next Follow-up</label>
                    <input type="date" x-model="requestForm.next_followup_date" class="w-full px-2 py-1.5 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-v2-text-mid mb-1">Notes</label>
                    <input type="text" x-model="requestForm.notes" class="w-full px-2 py-1.5 border rounded-lg text-sm" placeholder="Optional notes">
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <button @click="createRequest()" :disabled="savingRequest" class="px-4 py-1.5 text-sm bg-gold text-navy font-semibold rounded-lg hover:bg-gold/90 disabled:opacity-50">
                    <span x-text="savingRequest ? 'Saving...' : 'Add Request'"></span>
                </button>
            </div>
        </div>

        <!-- Request List -->
        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-3">
            <template x-if="loadingRequests">
                <div class="text-center py-8 text-v2-text-light">Loading requests...</div>
            </template>
            <template x-if="!loadingRequests && selectedItemRequests.length === 0">
                <div class="text-center py-8 text-v2-text-light">No requests yet for this item</div>
            </template>
            <template x-for="req in selectedItemRequests" :key="req.id">
                <div class="border border-v2-card-border rounded-lg p-3 bg-v2-bg">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                  :class="{
                                      'bg-green-100 text-green-700': req.request_type === 'initial',
                                      'bg-yellow-100 text-yellow-700': req.request_type === 'follow_up',
                                      'bg-orange-100 text-orange-700': req.request_type === 're_request'
                                  }"
                                  x-text="req.request_type?.replace(/_/g, ' ') || '-'"></span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700"
                                  x-text="req.request_method || '-'"></span>
                        </div>
                        <span class="text-xs text-v2-text-light" x-text="formatDate(req.request_date)"></span>
                    </div>
                    <div class="text-xs text-v2-text-light space-y-0.5">
                        <div x-show="req.sent_to"><span class="font-medium">Sent to:</span> <span x-text="req.sent_to"></span></div>
                        <div x-show="req.send_status"><span class="font-medium">Status:</span> <span x-text="req.send_status"></span></div>
                        <div x-show="req.next_followup_date"><span class="font-medium">Follow-up:</span> <span x-text="formatDate(req.next_followup_date)"></span></div>
                        <div x-show="req.notes"><span class="font-medium">Notes:</span> <span x-text="req.notes"></span></div>
                        <div x-show="req.created_by_name" class="text-v2-text-light">By <span x-text="req.created_by_name"></span></div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Requests panel backdrop -->
    <div x-show="showRequests" x-cloak class="fixed inset-0 z-40 bg-black/20" @click="showRequests=false"></div>

    <!-- Import CSV Modal -->
    <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showImportModal=false" @keydown.escape.window="showImportModal=false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold">Import CSV</h3>
                <button @click="showImportModal=false" class="text-v2-text-light hover:text-v2-text-mid">&times;</button>
            </div>
            <div class="px-6 py-4 space-y-4">
                <p class="text-sm text-v2-text-light">Upload a CSV file with columns: <code class="text-xs bg-v2-bg px-1 py-0.5 rounded">case_number, client_name, insurance_carrier, claim_number, member_id, carrier_contact_email, carrier_contact_fax</code></p>
                <div class="text-sm text-v2-text-light">Required columns: <strong>client_name</strong>, <strong>insurance_carrier</strong></div>
                <input type="file" x-ref="csvFile" accept=".csv" class="w-full text-sm text-v2-text-light file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gold/10 file:text-gold hover:file:bg-gold/20">
                <div x-show="importResult" class="text-sm p-3 rounded-lg" :class="importResult?.inserted > 0 ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700'">
                    <span x-text="importResult?.message"></span>
                </div>
            </div>
            <div class="px-6 py-4 border-t flex justify-end gap-2">
                <button @click="showImportModal=false" class="px-4 py-2 text-sm text-v2-text-mid hover:bg-v2-bg rounded-lg">Cancel</button>
                <button @click="importCSV()" :disabled="importing" class="px-4 py-2 text-sm bg-gold text-navy font-semibold rounded-lg hover:bg-gold/90 disabled:opacity-50">
                    <span x-text="importing ? 'Importing...' : 'Import'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
