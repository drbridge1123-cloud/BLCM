<!-- Contacts Modal (Client + 3rd Adjuster + UM Adjuster) — Add Provider style -->
<div x-show="showContactsModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(0,0,0,.45);" @click.self="showContactsModal = false" @keydown.escape.window="showContactsModal = false">
    <div class="ct-modal" @click.stop>
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
            <!-- Tab buttons -->
            <div style="display:flex; gap:0; border-bottom:1.5px solid #e8e4dc; padding:0 24px; flex-shrink:0;">
                <button @click="contactsTab = 'client'"
                    class="ct-tab" :class="contactsTab === 'client' ? 'ct-tab-active' : ''">
                    Client
                    <span x-show="clientMatchStatus === 'matched'" style="color:#10b981; font-size:9px; margin-left:2px;">&#10003;</span>
                </button>
                <button @click="contactsTab = '3rd'"
                    class="ct-tab" :class="contactsTab === '3rd' ? 'ct-tab-active' : ''">
                    3rd Adjuster
                    <span x-show="adjuster3rdMatchStatus === 'matched'" style="color:#10b981; font-size:9px; margin-left:2px;">&#10003;</span>
                </button>
                <button @click="contactsTab = 'um'"
                    class="ct-tab" :class="contactsTab === 'um' ? 'ct-tab-active' : ''">
                    UM Adjuster
                    <span x-show="adjusterUmMatchStatus === 'matched'" style="color:#10b981; font-size:9px; margin-left:2px;">&#10003;</span>
                </button>
            </div>

            <!-- Tab Content -->
            <div style="padding:20px 24px; display:flex; flex-direction:column; gap:14px;">

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
                                    <!-- Dropdown -->
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

                <!-- ═══ 3RD ADJUSTER TAB ═══ -->
                <template x-if="contactsTab === '3rd'">
                    <div style="display:flex; flex-direction:column; gap:14px;">

                        <!-- IDLE: Search -->
                        <template x-if="adjuster3rdMatchStatus === 'idle'">
                            <div>
                                <label class="ct-label">3rd Party Adjuster</label>
                                <div class="ct-search-wrap">
                                    <span class="ct-search-icon">&#128269;</span>
                                    <input type="text" class="ct-input" style="padding-left:34px;" placeholder="Search adjuster by name..."
                                           x-model="adjusterSearchQuery"
                                           @input.debounce.300ms="searchAdjusters($event.target.value)">
                                    <!-- Dropdown -->
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
                        </template>

                        <!-- MATCHED: read-only display -->
                        <template x-if="adjuster3rdMatchStatus === 'matched'">
                            <div style="display:flex; flex-direction:column; gap:14px;">
                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                    <p class="ct-selected-badge">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span x-text="adjuster3rdForm.first_name + ' ' + adjuster3rdForm.last_name"></span>
                                    </p>
                                    <div style="display:flex; gap:6px;">
                                        <button @click="adjuster3rdMatchStatus = 'adding'; insuranceSearch = adjuster3rdForm.company_name || ''" class="ct-action-btn ct-btn-edit"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</button>
                                        <button @click="clearAdjuster()" class="ct-action-btn ct-btn-change"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6M23 20v-6h-6"/><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/></svg>Change</button>
                                    </div>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label class="ct-label">Title</label>
                                        <input type="text" :value="adjuster3rdForm.title || '-'" class="ct-input ct-readonly" disabled>
                                    </div>
                                    <div>
                                        <label class="ct-label">Insurance Company</label>
                                        <input type="text" :value="adjuster3rdForm.company_name || '-'" class="ct-input ct-readonly" disabled>
                                    </div>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label class="ct-label">Phone</label>
                                        <input type="text" :value="formatPhoneNumber(adjuster3rdForm.phone) || '-'" class="ct-input ct-readonly" disabled>
                                    </div>
                                    <div>
                                        <label class="ct-label">Fax</label>
                                        <input type="text" :value="formatPhoneNumber(adjuster3rdForm.fax) || '-'" class="ct-input ct-readonly" disabled>
                                    </div>
                                </div>
                                <div>
                                    <label class="ct-label">Email</label>
                                    <input type="text" :value="adjuster3rdForm.email || '-'" class="ct-input ct-readonly" disabled>
                                </div>
                            </div>
                        </template>

                        <!-- ADDING: editable form for new adjuster -->
                        <template x-if="adjuster3rdMatchStatus === 'adding'">
                            <div style="display:flex; flex-direction:column; gap:14px;">
                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                    <div class="ct-section-divider" style="flex:1;" x-text="adjuster3rdForm.id ? 'Edit Adjuster' : 'New Adjuster'"></div>
                                    <button @click="adjuster3rdForm.id ? (adjuster3rdMatchStatus = 'matched') : clearAdjuster()" class="ct-btn-cancel" style="margin-left:8px;" x-text="adjuster3rdForm.id ? 'Cancel' : 'Back to Search'"></button>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label class="ct-label">First Name <span style="color:#e74c3c;">*</span></label>
                                        <input type="text" x-model="adjuster3rdForm.first_name" class="ct-input">
                                    </div>
                                    <div>
                                        <label class="ct-label">Last Name <span style="color:#e74c3c;">*</span></label>
                                        <input type="text" x-model="adjuster3rdForm.last_name" class="ct-input">
                                    </div>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label class="ct-label">Title</label>
                                        <template x-if="!customTitle">
                                            <select x-model="adjuster3rdForm.title" class="ct-input"
                                                    @change="if($event.target.value === '__custom__'){ adjuster3rdForm.title = ''; customTitle = true; }">
                                                <option value="">-- Select Title --</option>
                                                <template x-for="t in titleOptions" :key="t">
                                                    <option :value="t" x-text="t"></option>
                                                </template>
                                                <option value="__custom__">+ Add Custom...</option>
                                            </select>
                                        </template>
                                        <template x-if="customTitle">
                                            <div style="position:relative;">
                                                <input type="text" x-model="adjuster3rdForm.title" class="ct-input" placeholder="Enter custom title...">
                                                <button type="button" @click="customTitle = false; adjuster3rdForm.title = '';"
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
                                        <input type="text" x-model="adjuster3rdForm.phone" @blur="autoFormatPhone($el)" class="ct-input" placeholder="(000) 000-0000">
                                    </div>
                                    <div>
                                        <label class="ct-label">Fax</label>
                                        <input type="text" x-model="adjuster3rdForm.fax" @blur="autoFormatPhone($el)" class="ct-input" placeholder="(000) 000-0000">
                                    </div>
                                </div>
                                <div>
                                    <label class="ct-label">Email</label>
                                    <input type="email" x-model="adjuster3rdForm.email" class="ct-input">
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- ═══ UM ADJUSTER TAB ═══ -->
                <template x-if="contactsTab === 'um'">
                    <div style="display:flex; flex-direction:column; gap:14px;">

                        <!-- IDLE: Search -->
                        <template x-if="adjusterUmMatchStatus === 'idle'">
                            <div>
                                <label class="ct-label">UM Adjuster</label>
                                <div class="ct-search-wrap">
                                    <span class="ct-search-icon">&#128269;</span>
                                    <input type="text" class="ct-input" style="padding-left:34px;" placeholder="Search adjuster by name..."
                                           x-model="adjusterSearchQuery"
                                           @input.debounce.300ms="searchAdjusters($event.target.value)">
                                    <!-- Dropdown -->
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
                        </template>

                        <!-- MATCHED: read-only display -->
                        <template x-if="adjusterUmMatchStatus === 'matched'">
                            <div style="display:flex; flex-direction:column; gap:14px;">
                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                    <p class="ct-selected-badge">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span x-text="adjusterUmForm.first_name + ' ' + adjusterUmForm.last_name"></span>
                                    </p>
                                    <div style="display:flex; gap:6px;">
                                        <button @click="adjusterUmMatchStatus = 'adding'; insuranceSearch = adjusterUmForm.company_name || ''" class="ct-action-btn ct-btn-edit"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</button>
                                        <button @click="clearAdjuster()" class="ct-action-btn ct-btn-change"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6M23 20v-6h-6"/><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/></svg>Change</button>
                                    </div>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label class="ct-label">Title</label>
                                        <input type="text" :value="adjusterUmForm.title || '-'" class="ct-input ct-readonly" disabled>
                                    </div>
                                    <div>
                                        <label class="ct-label">Insurance Company</label>
                                        <input type="text" :value="adjusterUmForm.company_name || '-'" class="ct-input ct-readonly" disabled>
                                    </div>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label class="ct-label">Phone</label>
                                        <input type="text" :value="formatPhoneNumber(adjusterUmForm.phone) || '-'" class="ct-input ct-readonly" disabled>
                                    </div>
                                    <div>
                                        <label class="ct-label">Fax</label>
                                        <input type="text" :value="formatPhoneNumber(adjusterUmForm.fax) || '-'" class="ct-input ct-readonly" disabled>
                                    </div>
                                </div>
                                <div>
                                    <label class="ct-label">Email</label>
                                    <input type="text" :value="adjusterUmForm.email || '-'" class="ct-input ct-readonly" disabled>
                                </div>
                            </div>
                        </template>

                        <!-- ADDING: editable form for new adjuster -->
                        <template x-if="adjusterUmMatchStatus === 'adding'">
                            <div style="display:flex; flex-direction:column; gap:14px;">
                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                    <div class="ct-section-divider" style="flex:1;" x-text="adjusterUmForm.id ? 'Edit Adjuster' : 'New Adjuster'"></div>
                                    <button @click="adjusterUmForm.id ? (adjusterUmMatchStatus = 'matched') : clearAdjuster()" class="ct-btn-cancel" style="margin-left:8px;" x-text="adjusterUmForm.id ? 'Cancel' : 'Back to Search'"></button>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label class="ct-label">First Name <span style="color:#e74c3c;">*</span></label>
                                        <input type="text" x-model="adjusterUmForm.first_name" class="ct-input">
                                    </div>
                                    <div>
                                        <label class="ct-label">Last Name <span style="color:#e74c3c;">*</span></label>
                                        <input type="text" x-model="adjusterUmForm.last_name" class="ct-input">
                                    </div>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div>
                                        <label class="ct-label">Title</label>
                                        <template x-if="!customTitle">
                                            <select x-model="adjusterUmForm.title" class="ct-input"
                                                    @change="if($event.target.value === '__custom__'){ adjusterUmForm.title = ''; customTitle = true; }">
                                                <option value="">-- Select Title --</option>
                                                <template x-for="t in titleOptions" :key="t">
                                                    <option :value="t" x-text="t"></option>
                                                </template>
                                                <option value="__custom__">+ Add Custom...</option>
                                            </select>
                                        </template>
                                        <template x-if="customTitle">
                                            <div style="position:relative;">
                                                <input type="text" x-model="adjusterUmForm.title" class="ct-input" placeholder="Enter custom title...">
                                                <button type="button" @click="customTitle = false; adjusterUmForm.title = '';"
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
                                        <input type="text" x-model="adjusterUmForm.phone" @blur="autoFormatPhone($el)" class="ct-input" placeholder="(000) 000-0000">
                                    </div>
                                    <div>
                                        <label class="ct-label">Fax</label>
                                        <input type="text" x-model="adjusterUmForm.fax" @blur="autoFormatPhone($el)" class="ct-input" placeholder="(000) 000-0000">
                                    </div>
                                </div>
                                <div>
                                    <label class="ct-label">Email</label>
                                    <input type="email" x-model="adjusterUmForm.email" class="ct-input">
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

            </div>

            <!-- Footer -->
            <div style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
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

<style>
.ct-modal {
    background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22);
    width:100%; max-width:620px; overflow:hidden;
    display:flex; flex-direction:column;
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
    padding:11px 18px; background:none; border:none;
    border-bottom:2px solid transparent;
    cursor:pointer; font-size:13px; font-weight:500;
    margin-bottom:-1.5px; font-family:inherit;
    display:inline-flex; align-items:center;
    color:#8a8a82; transition:color .15s; white-space:nowrap;
}
.ct-tab:hover { color:#1a2535; }
.ct-tab-active {
    border-bottom-color:#C9A84C;
    color:#1a2535 !important; font-weight:700;
}
</style>
