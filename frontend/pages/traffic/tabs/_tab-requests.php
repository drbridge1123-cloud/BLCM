<!-- Traffic Requests Tab -->
<div class="flex items-center justify-between mb-3">
    <div class="flex items-center gap-1">
        <button @click="requestStatusFilter = ''; loadRequests()" class="px-3 py-1.5 text-xs rounded-full border"
                :class="requestStatusFilter === '' ? 'bg-gold text-white' : 'hover:bg-v2-bg'">All</button>
        <button @click="requestStatusFilter = 'pending'; loadRequests()" class="px-3 py-1.5 text-xs rounded-full border"
                :class="requestStatusFilter === 'pending' ? 'bg-gold text-white' : 'hover:bg-v2-bg'">Pending</button>
        <button @click="requestStatusFilter = 'accepted'; loadRequests()" class="px-3 py-1.5 text-xs rounded-full border"
                :class="requestStatusFilter === 'accepted' ? 'bg-gold text-white' : 'hover:bg-v2-bg'">Accepted</button>
        <button @click="requestStatusFilter = 'denied'; loadRequests()" class="px-3 py-1.5 text-xs rounded-full border"
                :class="requestStatusFilter === 'denied' ? 'bg-gold text-white' : 'hover:bg-v2-bg'">Denied</button>
    </div>
    <button @click="openRequestModal()" x-show="isAdmin"
            class="px-3 py-2 text-sm bg-gold text-white rounded-lg hover:bg-gold/90">+ New Request</button>
</div>

<div class="bg-white border rounded-lg overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-v2-bg text-xs text-v2-text-light uppercase">
            <tr>
                <th class="px-3 py-2 text-left">Client</th>
                <th class="px-3 py-2 text-left">Court</th>
                <th class="px-3 py-2 text-left">Court Date</th>
                <th class="px-3 py-2 text-left">Charge</th>
                <th class="px-3 py-2 text-left">Requested By</th>
                <th class="px-3 py-2 text-left">Assigned To</th>
                <th class="px-3 py-2 text-center">Status</th>
                <th class="px-3 py-2 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-v2-card-border">
            <template x-if="requests.length === 0">
                <tr><td colspan="8" class="px-3 py-8 text-center text-v2-text-light">No requests found</td></tr>
            </template>
            <template x-for="r in requests" :key="r.id">
                <tr class="hover:bg-v2-bg">
                    <td class="px-3 py-2 font-medium" x-text="r.client_name"></td>
                    <td class="px-3 py-2 text-xs" x-text="r.court || '—'"></td>
                    <td class="px-3 py-2 text-xs whitespace-nowrap" x-text="formatDate(r.court_date)"></td>
                    <td class="px-3 py-2 text-xs" x-text="r.charge || '—'"></td>
                    <td class="px-3 py-2 text-xs" x-text="r.requested_by_name"></td>
                    <td class="px-3 py-2 text-xs" x-text="r.assigned_to_name"></td>
                    <td class="px-3 py-2 text-center">
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold"
                              :class="{
                                  'bg-amber-100 text-amber-700': r.status === 'pending',
                                  'bg-green-100 text-green-700': r.status === 'accepted',
                                  'bg-red-100 text-red-600': r.status === 'denied'
                              }"
                              x-text="r.status.toUpperCase()"></span>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <template x-if="r.status === 'pending'">
                            <div class="flex items-center justify-center gap-1">
                                <button @click="respondRequest(r.id, 'accept')" class="text-green-600 hover:text-green-800 text-xs font-medium">Accept</button>
                                <button @click="respondRequest(r.id, 'deny')" class="text-red-500 hover:text-red-700 text-xs">Deny</button>
                            </div>
                        </template>
                        <template x-if="r.status === 'denied'">
                            <span class="text-xs text-v2-text-light" x-text="r.deny_reason" :title="r.deny_reason">Denied</span>
                        </template>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>
