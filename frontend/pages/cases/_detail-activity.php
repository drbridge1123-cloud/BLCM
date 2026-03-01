            <!-- Activity Log section -->
            <div class="act-panel c1-section c1-group-start" data-panel x-data="{logOpen: false}">
                <div class="c1-section-header" :class="logOpen && 'is-open'" @click="logOpen = !logOpen; if(logOpen) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
                    <div class="flex items-center gap-2.5">
                        <span class="c1-num c1-num-olive">02</span>
                        <h3 class="panel-title">Activity Log</h3>
                        <span class="panel-count" x-text="notes.length"></span>
                    </div>
                    <select x-model="noteFilterProvider" @change="loadNotes()" @click.stop
                        class="act-form-input" style="width:auto; border:1px solid #d0cdc5; padding:4px 8px; font-size:13px;">
                        <option value="">All Providers</option>
                        <template x-for="prov in providers" :key="prov.id">
                            <option :value="prov.id" x-text="prov.provider_name"></option>
                        </template>
                    </select>
                </div>
                <div x-show="logOpen" x-collapse>
                    <div class="act-body">
                        <!-- Add note form -->
                        <form @submit.prevent="addNote()">
                            <div class="act-form-card">
                                <div class="act-form-labels">
                                    <div class="act-form-label">Type</div>
                                    <div class="act-form-label">Provider</div>
                                    <div class="act-form-label">Contact</div>
                                    <div class="act-form-label">Date</div>
                                </div>
                                <div class="act-form-inputs">
                                    <select x-model="newNote.note_type" class="act-form-input">
                                        <option value="general">General</option>
                                        <option value="follow_up">Follow-Up</option>
                                        <option value="issue">Issue</option>
                                        <option value="handoff">Handoff</option>
                                    </select>
                                    <select x-model="newNote.case_provider_id" class="act-form-input">
                                        <option value="">No Provider</option>
                                        <template x-for="prov in providers" :key="prov.id">
                                            <option :value="prov.id" x-text="prov.provider_name"></option>
                                        </template>
                                    </select>
                                    <select x-model="newNote.contact_method" class="act-form-input">
                                        <option value="">No Contact</option>
                                        <option value="phone">Phone</option>
                                        <option value="fax">Fax</option>
                                        <option value="email">Email</option>
                                        <option value="portal">Portal</option>
                                        <option value="mail">Mail</option>
                                        <option value="in_person">In Person</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <input type="datetime-local" x-model="newNote.contact_date" class="act-form-input">
                                </div>
                                <div class="act-form-note-row">
                                    <input type="text" x-model="newNote.content" placeholder="Add a note..." class="act-form-note-input">
                                    <button type="submit" :disabled="!newNote.content.trim()" class="act-form-btn">Add</button>
                                </div>
                            </div>
                        </form>

                        <!-- Notes list -->
                        <div class="activity-list">
                            <template x-for="(note, idx) in notes" :key="note.id">
                                <div class="activity-entry group">
                                    <!-- Avatar -->
                                    <div class="activity-avatar" :class="idx % 2 === 0 ? 'activity-avatar-gold' : 'activity-avatar-navy'"
                                        x-text="(note.author_name || '?').split(' ').map(w => w[0]).join('').toUpperCase().slice(0,2)"></div>
                                    <!-- Type chip -->
                                    <span class="activity-chip activity-chip-type" x-text="note.note_type"></span>
                                    <!-- Provider chip -->
                                    <template x-if="note.provider_name">
                                        <span class="activity-chip activity-chip-provider" x-text="note.provider_name"></span>
                                    </template>
                                    <!-- Contact method chip -->
                                    <template x-if="note.contact_method">
                                        <span class="activity-chip activity-chip-contact" x-text="getContactMethodLabel(note.contact_method)"></span>
                                    </template>
                                    <!-- Note text -->
                                    <span class="activity-text" x-text="note.content"></span>
                                    <!-- Date -->
                                    <span class="activity-date-text" x-text="note.contact_date ? formatDate(note.contact_date) : formatDate(note.created_at)"></span>
                                    <!-- Delete -->
                                    <button @click="deleteNote(note.id)"
                                        class="icon-btn icon-btn-danger icon-btn-sm opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0"
                                        title="Delete note">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </template>
                            <template x-if="notes.length === 0">
                                <p style="text-align:center; color:#8a8a82; padding:24px 0; font-size:13px;">No notes yet</p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
