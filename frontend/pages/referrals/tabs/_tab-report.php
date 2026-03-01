<!-- Referral Report Tab -->
<div class="space-y-6">

    <!-- Total -->
    <div class="bg-white border rounded-lg px-4 py-3 text-center">
        <span class="text-sm text-v2-text-light">Total Referrals (<span x-text="reportYear"></span>):</span>
        <span class="text-xl font-bold text-v2-text ml-2" x-text="report.total_referrals"></span>
    </div>

    <div class="grid grid-cols-2 gap-6">
        <!-- By Personal Referrer -->
        <div class="bg-white border rounded-lg overflow-hidden">
            <div class="px-4 py-2 bg-v2-bg border-b">
                <h4 class="text-sm font-bold text-v2-text-mid">By Referral Source</h4>
            </div>
            <table class="w-full text-sm">
                <thead class="text-xs text-v2-text-light">
                    <tr>
                        <th class="px-3 py-1.5 text-left">Source</th>
                        <th class="px-3 py-1.5 text-right">Count</th>
                        <th class="px-3 py-1.5 text-right">Settled</th>
                        <th class="px-3 py-1.5 text-right">Commission</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-v2-card-border">
                    <template x-for="item in report.by_personal" :key="item.name">
                        <tr class="hover:bg-v2-bg">
                            <td class="px-3 py-1.5 font-medium" x-text="item.name"></td>
                            <td class="px-3 py-1.5 text-right" x-text="item.referral_count"></td>
                            <td class="px-3 py-1.5 text-right" x-text="item.settled_count || 0"></td>
                            <td class="px-3 py-1.5 text-right text-green-700" x-text="formatCurrency(item.total_commission)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- By Provider -->
        <div class="bg-white border rounded-lg overflow-hidden">
            <div class="px-4 py-2 bg-v2-bg border-b">
                <h4 class="text-sm font-bold text-v2-text-mid">By Provider</h4>
            </div>
            <table class="w-full text-sm">
                <thead class="text-xs text-v2-text-light">
                    <tr>
                        <th class="px-3 py-1.5 text-left">Provider</th>
                        <th class="px-3 py-1.5 text-right">Count</th>
                        <th class="px-3 py-1.5 text-right">Settled</th>
                        <th class="px-3 py-1.5 text-right">Commission</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-v2-card-border">
                    <template x-for="item in report.by_provider" :key="item.name">
                        <tr class="hover:bg-v2-bg">
                            <td class="px-3 py-1.5 font-medium" x-text="item.name"></td>
                            <td class="px-3 py-1.5 text-right" x-text="item.referral_count"></td>
                            <td class="px-3 py-1.5 text-right" x-text="item.settled_count || 0"></td>
                            <td class="px-3 py-1.5 text-right text-green-700" x-text="formatCurrency(item.total_commission)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">
        <!-- By Status -->
        <div class="bg-white border rounded-lg overflow-hidden">
            <div class="px-4 py-2 bg-v2-bg border-b">
                <h4 class="text-sm font-bold text-v2-text-mid">By Status</h4>
            </div>
            <div class="p-4">
                <div class="space-y-2">
                    <template x-for="item in report.by_status" :key="item.status">
                        <div class="flex items-center justify-between">
                            <span class="text-sm px-2 py-0.5 rounded" :class="getStatusClass(item.status)" x-text="item.status"></span>
                            <span class="text-sm font-bold" x-text="item.count"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- By Body Shop -->
        <div class="bg-white border rounded-lg overflow-hidden">
            <div class="px-4 py-2 bg-v2-bg border-b">
                <h4 class="text-sm font-bold text-v2-text-mid">By Body Shop</h4>
            </div>
            <table class="w-full text-sm">
                <thead class="text-xs text-v2-text-light">
                    <tr>
                        <th class="px-3 py-1.5 text-left">Body Shop</th>
                        <th class="px-3 py-1.5 text-right">Count</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-v2-card-border">
                    <template x-for="item in report.by_destination" :key="item.name">
                        <tr class="hover:bg-v2-bg">
                            <td class="px-3 py-1.5 font-medium" x-text="item.name"></td>
                            <td class="px-3 py-1.5 text-right" x-text="item.referral_count"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
