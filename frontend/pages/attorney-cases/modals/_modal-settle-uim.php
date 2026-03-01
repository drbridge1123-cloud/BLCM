<!-- Settle UIM Modal -->
<div x-show="showSettleUimModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50" @click="showSettleUimModal = false"></div>
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 relative z-10 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <h3 class="text-lg font-bold mb-1">Settle UIM Case</h3>
            <p class="text-sm text-v2-text-light mb-3">
                <span x-text="settlingCase?.case_number" class="font-semibold"></span> -
                <span x-text="settlingCase?.client_name"></span>
            </p>

            <!-- Previous BI settlement info -->
            <div x-show="hasCommission" class="px-4 py-2.5 bg-purple-50 border border-purple-200 rounded-lg mb-4 text-sm">
                <span class="text-purple-700 font-semibold">BI Settled:</span>
                <span class="font-bold" x-text="'$' + (settlingCase?.settled || 0).toLocaleString()"></span>
                <span class="mx-2 text-v2-text-light">|</span>
                <span class="text-purple-700 font-semibold">BI Commission:</span>
                <span class="font-bold" x-text="'$' + (settlingCase?.commission || 0).toLocaleString()"></span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div :class="hasCommission ? '' : 'col-span-2'">
                    <label class="block text-sm font-medium text-v2-text-mid mb-1">UIM Settled ($) *</label>
                    <input type="number" x-model.number="settleUimForm.settled" step="0.01" min="0" required
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-navy-500 focus:border-navy-500">
                </div>
                <div x-show="hasCommission">
                    <label class="block text-sm font-medium text-v2-text-mid mb-1">Legal Fee (33.33%)</label>
                    <div class="px-3 py-2 bg-v2-bg border rounded-lg text-sm text-v2-text-mid"
                         x-text="'$' + ((settleUimForm.settled || 0) / 3).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                    </div>
                </div>
            </div>

            <template x-if="hasCommission">
                <div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-v2-text-mid mb-1">Disc. Legal Fee ($) *</label>
                            <input type="number" x-model.number="settleUimForm.discounted_legal_fee" step="0.01" min="0" required
                                   class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-navy-500 focus:border-navy-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-v2-text-mid mb-1">UIM Commission (5%)</label>
                            <div class="px-3 py-2 bg-green-50 border border-green-200 rounded-lg text-sm font-bold text-green-700"
                                 x-text="'$' + ((settleUimForm.discounted_legal_fee || 0) * 0.05).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                            </div>
                        </div>
                    </div>

                    <!-- Total commission display -->
                    <div class="mt-4 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-center">
                        <span class="text-sm text-green-700 font-medium">Total Commission (BI + UIM):</span>
                        <span class="text-lg font-bold text-green-800 ml-2"
                              x-text="'$' + ((settlingCase?.commission || 0) + (settleUimForm.discounted_legal_fee || 0) * 0.05).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                        </span>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-v2-text-mid mb-1">Month</label>
                        <input type="text" x-model="settleUimForm.month" placeholder="e.g. Feb. 2025"
                               class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-navy-500 focus:border-navy-500">
                    </div>
                </div>
            </template>

            <div class="mt-4">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" x-model="settleUimForm.check_received" class="rounded border-v2-card-border text-gold">
                    Check Received
                </label>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button @click="showSettleUimModal = false" class="px-4 py-2 text-sm text-v2-text-mid border rounded-lg hover:bg-v2-bg">Cancel</button>
                <button @click="settleUim()" class="px-4 py-2 text-sm text-white rounded-lg" style="background:#1a2a44;" onmouseover="this.style.background='#243650'" onmouseout="this.style.background='#1a2a44'">Settle UIM</button>
            </div>
        </div>
    </div>
</div>
