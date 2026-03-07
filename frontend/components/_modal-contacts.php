<!-- Contacts Modal (Client + Dynamic Adjuster Tabs) -->
<div x-show="showContactsModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(0,0,0,.45);" @click.self="showContactsModal = false"
     @keydown.escape.window="if(showAddTabMenu){ showAddTabMenu=false } else { showContactsModal=false }">
    <div class="ct-modal">
        <!-- Header -->
        <div class="ct-header">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">Contacts</h3>
            <button @click="showContactsModal = false" style="background:none; border:none; color:rgba(255,255,255,.4); cursor:pointer; font-size:20px;">&times;</button>
        </div>

        <!-- Loading -->
        <div x-show="contactsLoading" style="padding:40px; text-align:center;">
            <span style="color:#8a8a82;">Loading...</span>
        </div>

        <!-- Tab Bar + Content -->
        <div x-show="!contactsLoading" style="display:flex; flex-direction:column; flex:1;">
            <!-- Tab bar: Client + Adjuster tabs + Add button -->
            <div style="display:flex; align-items:center; gap:0; border-bottom:1.5px solid #e8e4dc; padding:0 20px; flex-shrink:0; flex-wrap:wrap;">
                <!-- Client tab -->
                <button @click="contactsTab = 'client'"
                    class="ct-tab" :class="contactsTab === 'client' ? 'ct-tab-active' : ''">
                    Client
                    <span x-show="clientMatchStatus === 'matched'" style="color:#10b981; font-size:8px; margin-left:2px;">&#10003;</span>
                </button>

                <!-- Dynamic adjuster tabs -->
                <template x-for="tab in adjusterTabs" :key="tab.tabKey">
                    <button @click="switchAdjusterTab(tab.tabKey)"
                        class="ct-tab" :class="contactsTab === tab.tabKey ? 'ct-tab-active' : ''">
                        <span x-text="_getTabLabel(tab)"></span>
                        <span x-show="tab.matchStatus === 'matched'" style="color:#10b981; font-size:8px; margin-left:1px;">&#10003;</span>
                    </button>
                </template>

                <!-- + Add coverage button -->
                <div style="position:relative;" @click.outside="showAddTabMenu = false">
                    <button @click="showAddTabMenu = !showAddTabMenu"
                        style="background:none; border:none; cursor:pointer; font-size:15px; font-weight:600; color:#b0aca6; padding:6px 8px; margin-bottom:-1.5px; line-height:1; transition:color .15s;"
                        @mouseenter="$el.style.color='#C9A84C'" @mouseleave="$el.style.color='#b0aca6'">+</button>
                    <div x-show="showAddTabMenu" x-transition
                         style="position:absolute; left:0; top:100%; margin-top:4px; min-width:220px; background:#fff; border:1.5px solid #e8e4dc; border-radius:10px; box-shadow:0 8px 24px rgba(15,27,45,.10), 0 2px 6px rgba(15,27,45,.05); z-index:20; overflow:hidden;">
                        <template x-for="(type, idx) in allCoverageTypes" :key="type">
                            <button type="button" @click="addAdjusterTab(type); showAddTabMenu = false"
                                :style="'display:flex; align-items:center; justify-content:space-between; gap:8px; width:100%; text-align:left; padding:9px 14px; border:none; background:none; cursor:pointer; font-size:13px; font-weight:500; color:#1a2535; font-family:inherit; transition:background .12s;'
                                    + (idx < allCoverageTypes.length - 1 ? 'border-bottom:1px solid #f0ede8;' : '')"
                                @mouseenter="$el.style.background='#f0ede8'"
                                @mouseleave="$el.style.background='none'">
                                <span x-text="coverageLabels[type]"></span>
                                <span x-show="coverageTags[type]" x-text="coverageTags[type]"
                                    style="font-size:9px; font-weight:700; padding:2px 6px; border-radius:5px; background:#f0ede8; color:#b0aca6; letter-spacing:.05em;"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div style="padding:20px 24px; display:flex; flex-direction:column; gap:14px; overflow:visible; min-height:280px;">

                <!-- ═══ CLIENT TAB ═══ -->
                <template x-if="contactsTab === 'client'">
                    <div style="display:flex; flex-direction:column; gap:14px;">

                        <!-- IDLE: Search -->
                        <template x-if="clientMatchStatus === 'idle'">
                            <div>
                                <label class="ct-label">Client <span style="color:#C9A84C;">*</span></label>
                                <div class="ct-search-wrap">
                                    <span class="ct-search-icon">&#128269;</span>
                                    <input type="text" x-model="clientSearch" @input.debounce.300ms="searchClients()"
                                           placeholder="Search client by name..." class="ct-input" style="padding-left:34px;">
                                    <div x-show="showClientDropdown" @click.outside="showClientDropdown = false" class="ct-dropdown">
                                        <template x-for="cl in clientResults" :key="cl.id">
                                            <button type="button" @click="selectClient(cl)" class="ct-dropdown-item">
                                                <span x-text="cl.name" style="font-weight:600;"></span>
                                                <span x-text="formatDate(cl.dob)" style="font-size:11px; color:#8a8a82;"></span>
                                            </button>
                                        </template>
                                        <button type="button" @click="startAddClient()" class="ct-dropdown-create">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            Create "<span x-text="clientSearch"></span>"
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- MATCHED: read-only display -->
                        <template x-if="clientMatchStatus === 'matched'">
                            <div style="display:flex; flex-direction:column; gap:14px;">
                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                    <p class="ct-selected-badge">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span x-text="clientForm.name"></span>
                                    </p>
                                    <div style="display:flex; gap:6px;">
                                        <button @click="clientMatchStatus = 'adding'" class="ct-action-btn ct-btn-edit"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</button>
                                        <button @click="clearClientMatch()" class="ct-action-btn ct-btn-change"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6M23 20v-6h-6"/><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/></svg>Change</button>
                                    </div>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label class="ct-label">Date of Birth</label>
                                        <input type="text" :value="clientForm.dob ? clientForm.dob.replace(/^(\d{4})-(\d{2})-(\d{2})$/, '$2/$3/$1') : '-'" class="ct-input ct-readonly" disabled>
                                    </div>
                                    <div>
                                        <label class="ct-label">Phone</label>
                                        <input type="text" :value="formatPhoneNumber(clientForm.phone) || '-'" class="ct-input ct-readonly" disabled>
                                    </div>
                                </div>
                                <div>
                                    <label class="ct-label">Email</label>
                                    <input type="text" :value="clientForm.email || '-'" class="ct-input ct-readonly" disabled>
                                </div>
                                <div class="ct-section-divider">Address</div>
                                <div>
                                    <input type="text" :value="[clientForm.address_street, clientForm.address_city, clientForm.address_state, clientForm.address_zip].filter(Boolean).join(', ') || '-'" class="ct-input ct-readonly" disabled>
                                </div>
                            </div>
                        </template>

                        <!-- ADDING: editable form for new client -->
                        <template x-if="clientMatchStatus === 'adding'">
                            <div style="display:flex; flex-direction:column; gap:14px;">
                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                    <div class="ct-section-divider" style="flex:1;" x-text="clientForm.id ? 'Edit Client' : 'New Client'"></div>
                                    <button @click="clientForm.id ? (clientMatchStatus = 'matched') : clearClientMatch()" class="ct-btn-cancel" style="margin-left:8px;" x-text="clientForm.id ? 'Cancel' : 'Back to Search'"></button>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label class="ct-label">Name <span style="color:#e74c3c;">*</span></label>
                                        <input type="text" x-model="clientForm.name" class="ct-input">
                                    </div>
                                    <div>
                                        <label class="ct-label">Date of Birth</label>
                                        <input type="date" x-model="clientForm.dob" class="ct-input">
                                    </div>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label class="ct-label">Phone</label>
                                        <input type="text" x-model="clientForm.phone" @blur="autoFormatPhone($el)" class="ct-input" placeholder="(000) 000-0000">
                                    </div>
                                    <div>
                                        <label class="ct-label">Email</label>
                                        <input type="email" x-model="clientForm.email" class="ct-input">
                                    </div>
                                </div>
                                <div class="ct-section-divider">Address</div>
                                <div>
                                    <label class="ct-label">Street</label>
                                    <input type="text" x-model="clientForm.address_street" class="ct-input">
                                </div>
                                <div style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:12px;">
                                    <div>
                                        <label class="ct-label">City</label>
                                        <input type="text" x-model="clientForm.address_city" class="ct-input">
                                    </div>
                                    <div>
                                        <label class="ct-label">State</label>
                                        <input type="text" x-model="clientForm.address_state" class="ct-input" style="text-transform:uppercase;" maxlength="2">
                                    </div>
                                    <div>
                                        <label class="ct-label">Zip</label>
                                        <input type="text" x-model="clientForm.address_zip" class="ct-input" maxlength="10">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- ═══ DYNAMIC ADJUSTER TABS ═══ -->
                <template x-for="tab in adjusterTabs" :key="tab.tabKey">
                    <div x-show="contactsTab === tab.tabKey" style="display:flex; flex-direction:column; gap:14px;">

                        <!-- IDLE: Search -->
                        <div x-show="tab.matchStatus === 'idle'">
                            <label class="ct-label"><span x-text="_getTabLabel(tab)"></span> Adjuster</label>
                            <div class="ct-search-wrap">
                                <span class="ct-search-icon">&#128269;</span>
                                <input type="text" class="ct-input" style="padding-left:34px;" placeholder="Search adjuster by name..."
                                       x-model="adjusterSearchQuery"
                                       @input.debounce.300ms="searchAdjusters($event.target.value)">
                                <div x-show="showAdjusterDropdown" @click.outside="showAdjusterDropdown = false" class="ct-dropdown">
                                    <template x-for="adj in adjusterSearchResults" :key="adj.id">
                                        <button type="button" @click="selectAdjuster(adj)" class="ct-dropdown-item">
                                            <span x-text="adj.first_name + ' ' + adj.last_name" style="font-weight:600;"></span>
                                            <span x-text="adj.company_name || ''" style="font-size:11px; color:#8a8a82;"></span>
                                        </button>
                                    </template>
                                    <button type="button" @click="startAddAdjuster()" class="ct-dropdown-create">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        Create "<span x-text="adjusterSearchQuery"></span>"
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- MATCHED: read-only display -->
                        <div x-show="tab.matchStatus === 'matched'" style="display:flex; flex-direction:column; gap:14px;">
                            <div style="display:flex; align-items:center; justify-content:space-between;">
                                <p class="ct-selected-badge">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span x-text="tab.form.first_name + ' ' + tab.form.last_name"></span>
                                </p>
                                <div style="display:flex; gap:6px;">
                                    <button @click="tab.matchStatus = 'adding'; insuranceSearch = tab.form.company_name || ''" class="ct-action-btn ct-btn-edit"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</button>
                                    <button @click="clearAdjuster()" class="ct-action-btn ct-btn-change"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6M23 20v-6h-6"/><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/></svg>Change</button>
                                </div>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div>
                                    <label class="ct-label">Title</label>
                                    <input type="text" :value="tab.form.title || '-'" class="ct-input ct-readonly" disabled>
                                </div>
                                <div>
                                    <label class="ct-label">Insurance Company</label>
                                    <input type="text" :value="tab.form.company_name || '-'" class="ct-input ct-readonly" disabled>
                                </div>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div>
                                    <label class="ct-label">Phone</label>
                                    <input type="text" :value="formatPhoneNumber(tab.form.phone) || '-'" class="ct-input ct-readonly" disabled>
                                </div>
                                <div>
                                    <label class="ct-label">Fax</label>
                                    <input type="text" :value="formatPhoneNumber(tab.form.fax) || '-'" class="ct-input ct-readonly" disabled>
                                </div>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div>
                                    <label class="ct-label">Email</label>
                                    <input type="text" :value="tab.form.email || '-'" class="ct-input ct-readonly" disabled>
                                </div>
                                <div>
                                    <label class="ct-label">Claim #</label>
                                    <input type="text" :value="tab.form.claim_number || '-'" class="ct-input ct-readonly" disabled>
                                </div>
                            </div>
                            <!-- Negotiate toggle -->
                            <div x-show="tab.caseAdjusterId" style="display:flex; align-items:center; gap:8px; padding:8px 0 0;">
                                <label class="ct-toggle" @click="tab.negotiateChecked = !tab.negotiateChecked; toggleNegotiate(tab)">
                                    <span class="ct-toggle-track" :class="tab.negotiateChecked && 'ct-toggle-on'">
                                        <span class="ct-toggle-thumb" :class="tab.negotiateChecked && 'ct-toggle-thumb-on'"></span>
                                    </span>
                                    <span style="font-size:12px; font-weight:600; color:#1a2535;">Negotiate</span>
                                </label>
                                <span x-show="tab.negotiateChecked" style="font-size:10px; color:#10b981;">Active</span>
                            </div>
                        </div>

                        <!-- ADDING: editable form -->
                        <div x-show="tab.matchStatus === 'adding'" style="display:flex; flex-direction:column; gap:14px;">
                            <div style="display:flex; align-items:center; justify-content:space-between;">
                                <div class="ct-section-divider" style="flex:1;" x-text="tab.form.id ? 'Edit Adjuster' : 'New Adjuster'"></div>
                                <button @click="tab.form.id ? (tab.matchStatus = 'matched') : clearAdjuster()" class="ct-btn-cancel" style="margin-left:8px;" x-text="tab.form.id ? 'Cancel' : 'Back to Search'"></button>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div>
                                    <label class="ct-label">First Name <span style="color:#e74c3c;">*</span></label>
                                    <input type="text" x-model="tab.form.first_name" class="ct-input">
                                </div>
                                <div>
                                    <label class="ct-label">Last Name <span style="color:#e74c3c;">*</span></label>
                                    <input type="text" x-model="tab.form.last_name" class="ct-input">
                                </div>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div>
                                    <label class="ct-label">Title</label>
                                    <template x-if="!customTitle">
                                        <select x-model="tab.form.title" class="ct-input"
                                                @change="if($event.target.value === '__custom__'){ tab.form.title = ''; customTitle = true; }">
                                            <option value="">-- Select Title --</option>
                                            <template x-for="t in titleOptions" :key="t">
                                                <option :value="t" x-text="t"></option>
                                            </template>
                                            <option value="__custom__">+ Add Custom...</option>
                                        </select>
                                    </template>
                                    <template x-if="customTitle">
                                        <div style="position:relative;">
                                            <input type="text" x-model="tab.form.title" class="ct-input" placeholder="Enter custom title...">
                                            <button type="button" @click="customTitle = false; tab.form.title = '';"
                                                    style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:#8a8a82; cursor:pointer; font-size:14px;"
                                                    title="Back to list">&times;</button>
                                        </div>
                                    </template>
                                </div>
                                <div class="ct-search-wrap">
                                    <label class="ct-label">Insurance Company</label>
                                    <input type="text" class="ct-input" placeholder="Search company..."
                                           x-model="insuranceSearch"
                                           @input.debounce.300ms="searchInsuranceCompanies($event.target.value)"
                                           @focus="insuranceSearch.length >= 2 && searchInsuranceCompanies(insuranceSearch)">
                                    <div x-show="showInsuranceDropdown" @click.outside="showInsuranceDropdown = false" class="ct-dropdown">
                                        <template x-for="co in insuranceResults" :key="co.id">
                                            <button type="button" @click="selectInsuranceCompany(co)" class="ct-dropdown-item">
                                                <span x-text="co.name"></span>
                                            </button>
                                        </template>
                                        <button type="button" @click="createInsuranceCompany()" class="ct-dropdown-create">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            Create "<span x-text="insuranceSearch"></span>"
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div>
                                    <label class="ct-label">Phone</label>
                                    <input type="text" x-model="tab.form.phone" @blur="autoFormatPhone($el)" class="ct-input" placeholder="(000) 000-0000">
                                </div>
                                <div>
                                    <label class="ct-label">Fax</label>
                                    <input type="text" x-model="tab.form.fax" @blur="autoFormatPhone($el)" class="ct-input" placeholder="(000) 000-0000">
                                </div>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div>
                                    <label class="ct-label">Email</label>
                                    <input type="email" x-model="tab.form.email" class="ct-input">
                                </div>
                                <div>
                                    <label class="ct-label">Claim #</label>
                                    <input type="text" x-model="tab.form.claim_number" class="ct-input" placeholder="CLM-123456">
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty state when no adjuster tabs and on non-client tab -->
                <template x-if="contactsTab !== 'client' && !getActiveTab()">
                    <div style="text-align:center; padding:20px; color:#8a8a82;">
                        Click <strong>+</strong> to add an adjuster.
                    </div>
                </template>

            </div>

            <!-- Footer -->
            <div style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; align-items:center; flex-shrink:0;">
                <!-- Delete (only for adjuster tabs) -->
                <button x-show="getActiveTab()" @click="if(confirm('Remove this adjuster?')) removeAdjusterTab(contactsTab)"
                    class="ct-btn-delete">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    Delete
                </button>
                <div style="flex:1;"></div>
                <div style="display:flex; gap:10px;">
                    <button @click="showContactsModal = false" class="sp-btn">Cancel</button>
                    <button @click="saveContacts()" :disabled="contactsSaving || !canSave()"
                            x-show="canSave()"
                            class="sp-new-btn-navy">
                        <span x-text="contactsSaving ? 'Saving...' : 'Save'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ct-modal {
    background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22);
    width:100%; max-width:620px; overflow-y:auto;
    display:flex; flex-direction:column;
    max-height:90vh;
}
.ct-header {
    background:#0F1B2D; padding:18px 24px;
    display:flex; align-items:center; justify-content:space-between; flex-shrink:0;
}
.ct-label {
    display:block; font-size:9.5px; font-weight:700; color:#8a8a82;
    text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;
}
.ct-input {
    width:100%; padding:9px 12px; font-size:13px; font-family:inherit;
    border:1.5px solid #d0cdc5; border-radius:7px; background:#fafafa;
    color:#1a2535; outline:none; transition:all .15s;
}
.ct-input:focus {
    border-color:#C9A84C; background:#fff;
    box-shadow:0 0 0 3px rgba(201,168,76,.1);
}
.ct-input::placeholder { color:#c5c5c5; }
.ct-input:disabled { cursor:default; }
.ct-readonly {
    background:#f4f3f0 !important; color:#6b7280 !important;
    border-color:#e8e4dc !important;
}
.ct-search-wrap { position:relative; }
.ct-search-icon {
    position:absolute; left:11px; top:50%; transform:translateY(-50%);
    font-size:13px; color:#bbb; pointer-events:none; z-index:1;
}
.ct-dropdown {
    position:absolute; z-index:10; width:100%; margin-top:4px; background:#fff;
    border:1.5px solid #d0cdc5; border-radius:8px;
    box-shadow:0 8px 24px rgba(0,0,0,.12); max-height:220px; overflow-y:auto;
}
.ct-dropdown-item {
    width:100%; text-align:left; background:none; border:none; padding:9px 14px;
    font-size:13px; color:#1a2535; cursor:pointer; display:flex; justify-content:space-between; align-items:center;
    transition:background .1s; font-family:inherit;
}
.ct-dropdown-item:hover { background:rgba(201,168,76,.06); }
.ct-dropdown-create {
    width:100%; text-align:left; background:none; border:none; padding:9px 14px;
    font-size:13px; font-weight:600; color:#C9A84C; cursor:pointer;
    display:flex; align-items:center; gap:6px; border-top:1px solid #d0cdc5;
    transition:background .1s; font-family:inherit;
}
.ct-dropdown-create:hover { background:rgba(201,168,76,.06); }
.ct-selected-badge {
    font-size:12.5px; font-weight:500; color:#C9A84C; margin:0;
    display:flex; align-items:center; gap:5px;
}
.ct-action-btn {
    font-size:11px; font-weight:500; font-family:inherit; cursor:pointer;
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 10px; border-radius:6px; border:1px solid; transition:all .15s;
}
.ct-btn-edit { color:#92710a; background:#fdf8ec; border-color:#e8d8a0; }
.ct-btn-edit:hover { background:#f8efd4; border-color:#d4be6a; }
.ct-btn-change { color:#6b7280; background:#f9fafb; border-color:#e5e7eb; }
.ct-btn-change:hover { background:#f3f4f6; border-color:#d1d5db; }
.ct-btn-cancel { color:#6b7280; background:none; border:none; font-size:11px; font-weight:500; font-family:inherit; cursor:pointer; padding:3px 6px; }
.ct-btn-cancel:hover { color:#1a2535; }
.ct-section-divider {
    display:flex; align-items:center; gap:12px;
    font-size:9px; font-weight:700; color:#8a8a82;
    text-transform:uppercase; letter-spacing:.1em; margin:4px 0;
}
.ct-section-divider::before,
.ct-section-divider::after {
    content:''; flex:1; height:1px; background:#d0cdc5;
}
.ct-tab {
    padding:8px 10px; background:none; border:none;
    border-bottom:2px solid transparent;
    cursor:pointer; font-size:11.5px; font-weight:500;
    margin-bottom:-1.5px; font-family:inherit;
    display:inline-flex; align-items:center; gap:2px;
    color:#8a8a82; transition:color .15s; white-space:nowrap;
}
.ct-tab:hover { color:#1a2535; }
.ct-btn-delete {
    display:inline-flex; align-items:center; gap:5px;
    font-size:11.5px; font-weight:500; font-family:inherit; cursor:pointer;
    padding:5px 12px; border-radius:6px; border:1px solid #f0c8c8;
    background:#fef2f2; color:#dc2626; transition:all .15s;
}
.ct-btn-delete:hover { background:#fee2e2; border-color:#e8a0a0; }
.ct-toggle {
    display:inline-flex; align-items:center; gap:8px; cursor:pointer; user-select:none;
}
.ct-toggle-track {
    width:32px; height:18px; border-radius:9px; background:#d0cdc5;
    position:relative; transition:background .2s;
}
.ct-toggle-on { background:#C9A84C; }
.ct-toggle-thumb {
    position:absolute; top:2px; left:2px; width:14px; height:14px;
    border-radius:50%; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.15);
    transition:transform .2s;
}
.ct-toggle-thumb-on { transform:translateX(14px); }
.ct-tab-active {
    border-bottom-color:#C9A84C;
    color:#1a2535 !important; font-weight:700;
}
</style>
