<!-- Create Referral Modal -->
<div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45);" @click.self="showCreateModal = false">
    <div style="background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22); width:100%; max-width:600px; max-height:90vh; overflow:hidden; display:flex; flex-direction:column;" @click.stop>
        <div style="background:#0F1B2D; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">New Referral</h3>
            <button @click="showCreateModal = false" style="background:none; border:none; color:rgba(255,255,255,.4); cursor:pointer; font-size:20px;">&times;</button>
        </div>
        <div style="padding:24px; overflow-y:auto; display:flex; flex-direction:column; gap:16px;">
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Client Name *</label>
                    <input type="text" x-model="createForm.client_name" required class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Date of Birth</label>
                    <input type="date" x-model="createForm.client_dob" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Signed Date</label>
                    <input type="date" x-model="createForm.signed_date" class="sp-search" style="width:100%;">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">File #</label>
                    <input type="text" x-model="createForm.file_number" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Status</label>
                    <select x-model="createForm.status" class="sp-select" style="width:100%;">
                        <option value="">-- Select --</option>
                        <option value="INI">INI</option><option value="REC">REC</option><option value="NEG">NEG</option><option value="FILE">FILE</option><option value="LIT">LIT</option><option value="SETTLE">SETTLE</option><option value="RFD">RFD</option><option value="HEALTH">HEALTH</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Date of Loss</label>
                    <input type="date" x-model="createForm.date_of_loss" class="sp-search" style="width:100%;">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Referred By</label>
                    <input type="text" x-model="createForm.referred_by" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Referral Type</label>
                    <select x-model="createForm.referral_type" class="sp-select" style="width:100%;">
                        <option value="">-- Select --</option>
                        <option value="Marketing">Marketing</option><option value="Friend">Friend</option><option value="Client Referral">Client Referral</option><option value="Provider Referral">Provider Referral</option><option value="Friend's Referral">Friend's Referral</option><option value="Relatives">Relatives</option><option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div style="position:relative;">
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Referred to Provider</label>
                    <input type="text" x-model="createForm.referred_to_provider" class="sp-search" style="width:100%;"
                           @input.debounce.300ms="searchProviders(createForm.referred_to_provider)"
                           @focus="if(createForm.referred_to_provider.length >= 2) searchProviders(createForm.referred_to_provider)"
                           autocomplete="off">
                    <div x-show="showProviderDropdown" @click.outside="showProviderDropdown = false"
                         style="position:absolute; left:0; right:0; z-index:10; background:#fff; border:1px solid #e2ddd6; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.12); max-height:180px; overflow-y:auto; margin-top:2px;">
                        <template x-for="p in providerSearchResults" :key="p.id">
                            <div @click="selectProvider(p, 'createForm')"
                                 style="padding:8px 12px; cursor:pointer; font-size:12px; border-bottom:1px solid #f4f2ee; display:flex; justify-content:space-between; align-items:center;"
                                 onmouseover="this.style.background='#f9f8f6'" onmouseout="this.style.background='#fff'">
                                <span x-text="p.name" style="font-weight:600; color:#1a2535;"></span>
                                <span x-text="p.type || ''" style="color:#8a8a82; font-size:10px;"></span>
                            </div>
                        </template>
                    </div>
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Referred to Body Shop</label>
                    <input type="text" x-model="createForm.referred_to_body_shop" class="sp-search" style="width:100%;">
                </div>
            </div>
            <template x-if="isAdmin">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Lead</label>
                        <select x-model="createForm.lead_id" class="sp-select" style="width:100%;">
                            <option value="">-- Select --</option>
                            <template x-for="u in users" :key="u.id"><option :value="u.id" x-text="u.display_name || u.full_name"></option></template>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Case Manager</label>
                        <select x-model="createForm.case_manager_id" class="sp-select" style="width:100%;">
                            <option value="">-- Select --</option>
                            <template x-for="u in users" :key="u.id"><option :value="u.id" x-text="u.display_name || u.full_name"></option></template>
                        </select>
                    </div>
                </div>
            </template>
            <div>
                <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Remark</label>
                <textarea x-model="createForm.remark" rows="2" class="sp-search" style="width:100%; resize:none;"></textarea>
            </div>
        </div>
        <div style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
            <button @click="showCreateModal = false" class="sp-btn">Cancel</button>
            <button @click="createReferral()" :disabled="saving" class="sp-new-btn-navy">
                <span x-text="saving ? 'Saving...' : 'Add Referral'"></span>
            </button>
        </div>
    </div>
</div>
