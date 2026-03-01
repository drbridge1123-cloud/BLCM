<!-- Send to Accounting Modal -->
<div x-show="showSendAcctModal" x-cloak
     class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
     @click.self="showSendAcctModal = false">
    <div class="sp-card" style="width:100%; max-width:480px; margin:16px;" @click.stop>
        <div class="sp-gold-bar"></div>

        <!-- Header -->
        <div style="background:#0F1B2D; padding:16px 20px; display:flex; align-items:center; justify-content:space-between;">
            <h3 style="color:#fff; font-size:15px; font-weight:700; font-family:'IBM Plex Sans',sans-serif; margin:0;">Send to Accounting</h3>
            <button @click="showSendAcctModal = false" style="color:rgba(255,255,255,.5); font-size:18px; cursor:pointer; background:none; border:none;">&times;</button>
        </div>

        <div style="padding:16px 20px; display:flex; flex-direction:column; gap:14px;">

            <!-- Case Info -->
            <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:8px; padding:12px 16px;">
                <div style="font-size:11px; color:#8a8a82; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Attorney Case</div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="sp-case-num" x-text="sendAcctForm._caseNumber"></span>
                    <span style="font-size:13px; font-weight:600; color:#1a2535;" x-text="sendAcctForm._clientName"></span>
                </div>
            </div>

            <!-- Link to Main Case -->
            <div>
                <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Link to Main Case (optional)</label>

                <!-- Auto-matched case -->
                <template x-if="sendAcctForm._matchedCase">
                    <div style="background:rgba(26,158,106,.06); border:1px solid rgba(26,158,106,.2); border-radius:8px; padding:10px 14px; display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="color:#1a9e6a; font-size:12px; font-weight:700;">&#10003;</span>
                            <span class="sp-case-num" x-text="sendAcctForm._matchedCase.case_number"></span>
                            <span style="font-size:12px; color:#1a2535;" x-text="sendAcctForm._matchedCase.client_name"></span>
                            <span class="sp-status sp-status-in-progress" style="font-size:9px;" x-text="sendAcctForm._matchedCase.status"></span>
                        </div>
                        <button @click="sendAcctForm._matchedCase = null; sendAcctForm.linked_case_id = null"
                                style="font-size:11px; color:#e74c3c; cursor:pointer; background:none; border:none;">Change</button>
                    </div>
                </template>

                <!-- Search input -->
                <template x-if="!sendAcctForm._matchedCase">
                    <div style="position:relative;">
                        <input type="text" x-model="sendAcctForm._searchQuery"
                               @input.debounce.300ms="searchLinkedCase()"
                               class="sp-search" style="width:100%; padding:8px 12px;"
                               placeholder="Search by case number or client name...">

                        <!-- Loading -->
                        <div x-show="sendAcctForm._searching" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:11px; color:#8a8a82;">
                            Searching...
                        </div>

                        <!-- Search Results Dropdown -->
                        <div x-show="sendAcctForm._searchResults.length > 0 && !sendAcctForm._matchedCase"
                             style="position:absolute; top:100%; left:0; right:0; z-index:10; background:#fff; border:1px solid #e8e4dc; border-radius:0 0 8px 8px; box-shadow:0 4px 12px rgba(0,0,0,.1); max-height:200px; overflow-y:auto;">
                            <template x-for="r in sendAcctForm._searchResults" :key="r.id">
                                <div @click="selectLinkedCase(r)"
                                     style="padding:8px 14px; cursor:pointer; border-bottom:1px solid #f5f2ee; font-size:12px; display:flex; align-items:center; gap:8px;"
                                     class="hover:bg-gray-50">
                                    <span class="sp-case-num" x-text="r.case_number"></span>
                                    <span style="color:#1a2535;" x-text="r.client_name"></span>
                                    <span class="sp-status sp-status-in-progress" style="font-size:9px;" x-text="r.status"></span>
                                </div>
                            </template>
                        </div>

                        <!-- No results -->
                        <div x-show="sendAcctForm._noResults" style="font-size:11px; color:#e74c3c; margin-top:4px;">
                            No matching cases found.
                        </div>
                    </div>
                </template>
            </div>

            <!-- Assign To -->
            <div>
                <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Assign To *</label>
                <select x-model="sendAcctForm.assigned_to" class="sp-select" style="width:100%; padding:8px 12px;">
                    <template x-for="u in accountingStaff" :key="u.id">
                        <option :value="u.id" x-text="u.full_name"></option>
                    </template>
                </select>
            </div>

            <!-- Info box -->
            <div style="background:rgba(37,99,235,.04); border:1px solid rgba(37,99,235,.15); border-radius:8px; padding:10px 14px; font-size:11px; color:#2563eb; line-height:1.5;">
                <template x-if="sendAcctForm._matchedCase">
                    <span>Disbursement items will be auto-generated from the linked case's settlement data.</span>
                </template>
                <template x-if="!sendAcctForm._matchedCase">
                    <span>No main case linked. You can add disbursement items manually in the Accounting Tracker.</span>
                </template>
            </div>

            <!-- Note -->
            <div>
                <label style="font-size:11px; font-weight:600; color:#8a8a82; display:block; margin-bottom:4px; font-family:'IBM Plex Sans',sans-serif;">Note (optional)</label>
                <textarea x-model="sendAcctForm.note" rows="2" class="sp-search" style="width:100%; padding:8px 12px; resize:none;" placeholder="Optional note..."></textarea>
            </div>
        </div>

        <!-- Footer -->
        <div style="display:flex; justify-content:flex-end; gap:8px; padding:12px 20px; background:#fafaf8; border-top:1px solid #f5f2ee;">
            <button @click="showSendAcctModal = false" class="sp-btn">Cancel</button>
            <button @click="submitSendToAccounting()" :disabled="saving" class="sp-new-btn">
                <span x-show="!saving">Send to Accounting</span>
                <span x-show="saving">Sending...</span>
            </button>
        </div>
    </div>
</div>
