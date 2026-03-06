<!-- All sp- styles loaded from shared sp-design-system.css -->

<div x-data="templatesPage()">

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">
        <div class="sp-gold-bar"></div>
        <div class="sp-header" style="flex-wrap:wrap; gap:16px;">
            <div style="flex:1;">
                <span class="sp-title" style="font-size:14px;">Letter Templates</span>
            </div>
            <button @click="openCreateModal()" class="sp-new-btn-navy">+ Create Template</button>
        </div>

        <div class="sp-toolbar">
            <div class="sp-toolbar-right" style="display:flex; gap:8px; width:100%; align-items:center;">
                <select x-model="filterType" @change="loadTemplates()" class="sp-select">
                    <option value="">All Types</option>
                    <option value="medical_records">Medical Records</option>
                    <option value="health_ledger">Health Ledger</option>
                    <option value="bulk_request">Bulk Request</option>
                    <option value="balance_verification">Balance Verification</option>
                    <option value="medical_discount">Medical Discount</option>
                    <option value="custom">Custom</option>
                </select>
                <label style="display:flex; align-items:center; gap:6px; font-size:12px; cursor:pointer;">
                    <input type="checkbox" x-model="activeOnly" @change="loadTemplates()" style="accent-color:#C9A84C;">
                    <span style="color:#6b7280;">Active only</span>
                </label>
            </div>
        </div>

        <table class="sp-table">
            <thead><tr>
                <th>Name</th><th>Type</th><th>Default</th><th style="text-align:center;">Status</th>
                <th>Created By</th><th>Updated</th><th style="text-align:center;">Actions</th>
            </tr></thead>
            <tbody>
                <template x-if="loading"><tr><td colspan="7" class="sp-empty">Loading...</td></tr></template>
                <template x-if="!loading && templates.length === 0"><tr><td colspan="7" class="sp-empty">No templates found</td></tr></template>
                <template x-for="template in templates" :key="template.id">
                    <tr>
                        <td>
                            <div style="font-weight:600;" x-text="template.name"></div>
                            <div style="font-size:11px; color:#9ca3af; margin-top:2px;" x-text="template.description || 'No description'"></div>
                        </td>
                        <td><span class="sp-stage" style="background:rgba(59,130,246,.1); color:#2563eb;" x-text="template.template_type.replace('_', ' ')"></span></td>
                        <td><span x-show="template.is_default" class="sp-stage" style="background:#C9A84C; color:#fff;">Default</span></td>
                        <td style="text-align:center;">
                            <span class="sp-stage" :style="template.is_active ? 'background:#dcfce7; color:#15803d;' : 'background:#fee2e2; color:#dc2626;'" x-text="template.is_active ? 'Active' : 'Inactive'"></span>
                        </td>
                        <td style="color:#6b7280; font-size:13px;" x-text="template.created_by_name || 'System'"></td>
                        <td class="sp-mono" style="font-size:12px;" x-text="formatDate(template.updated_at)"></td>
                        <td style="text-align:center;">
                            <div class="sp-actions" style="justify-content:center;">
                                <button @click="viewVersions(template)" class="sp-act sp-act-gold">
                                    <span class="sp-tip">History</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </button>
                                <button @click="previewTemplate(template)" class="sp-act sp-act-blue">
                                    <span class="sp-tip">Preview</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button @click="editTemplate(template)" class="sp-act sp-act-gold">
                                    <span class="sp-tip">Edit</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button @click="deleteTemplate(template)" class="sp-act sp-act-red">
                                    <span class="sp-tip">Delete</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Create/Edit Modal -->
    <div x-show="showModal" x-cloak class="sp-modal-overlay" @keydown.escape.window="showModal && closeModal()">
        <div class="sp-modal-box" style="max-width:1000px" @click.stop>
            <div class="sp-modal-header">
                <div>
                    <div class="sp-modal-title" x-text="editingTemplate ? 'Edit Template' : 'Create Template'"></div>
                    <div class="tpm-subtitle">HTML template with placeholder support</div>
                </div>
                <button type="button" class="sp-modal-close" @click="closeModal()">&times;</button>
            </div>
            <div class="sp-modal-body" style="flex:1; overflow-y:auto;">
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
                    <div style="display:flex; flex-direction:column; gap:16px;">
                        <div>
                            <label class="sp-form-label">Template Name <span class="tpm-req">*</span></label>
                            <input type="text" x-model="form.name" class="sp-search" placeholder="e.g., Medical Records Request - Standard">
                        </div>
                        <div>
                            <label class="sp-form-label">Description</label>
                            <textarea x-model="form.description" rows="2" class="sp-search" style="resize:vertical;min-height:70px;line-height:1.5" placeholder="Brief description of this template"></textarea>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; align-items:end;">
                            <div>
                                <label class="sp-form-label">Template Type <span class="tpm-req">*</span></label>
                                <select x-model="form.template_type" class="sp-select">
                                    <option value="medical_records">Medical Records</option>
                                    <option value="health_ledger">Health Ledger</option>
                                    <option value="bulk_request">Bulk Request</option>
                                    <option value="balance_verification">Balance Verification</option>
                                    <option value="medical_discount">Medical Discount</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                            <div style="padding-bottom:4px;">
                                <label style="display:flex; align-items:center; gap:6px; font-size:12px; cursor:pointer;">
                                    <input type="checkbox" x-model="form.is_default" style="accent-color:#C9A84C;">
                                    <span style="color:#6b7280;">Set as default template</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="sp-form-label">Subject Template</label>
                            <input type="text" x-model="form.subject_template" class="sp-search" placeholder="e.g., Medical Records Request - {{client_name}}">
                        </div>
                        <div>
                            <label class="sp-form-label">Body Template <span class="tpm-req">*</span> (HTML with placeholders)</label>
                            <textarea x-model="form.body_template" rows="20" class="sp-search" style="resize:vertical;min-height:70px;line-height:1.5;font-family:'IBM Plex Mono',monospace;font-size:12px;" placeholder="Enter HTML template with {{placeholders}}"></textarea>
                        </div>
                        <template x-if="editingTemplate">
                            <div>
                                <label class="sp-form-label">Change Notes</label>
                                <input type="text" x-model="form.change_notes" class="sp-search" placeholder="Describe what changed in this version">
                            </div>
                        </template>
                    </div>
                    <div>
                        <div style="position:sticky; top:0; background:#fafaf8; border-radius:10px; border:1px solid #e8e4dc; padding:16px;">
                            <h3 style="font-size:13px; font-weight:600; color:#1a2535; margin-bottom:12px;">Available Placeholders</h3>
                            <div style="display:flex; flex-direction:column; gap:8px; font-size:12px; max-height:600px; overflow-y:auto;">
                                <div><code style="background:#fff; padding:2px 8px; border-radius:4px; font-size:11px;">{{firm_name}}</code> - Firm name</div>
                                <div><code style="background:#fff; padding:2px 8px; border-radius:4px; font-size:11px;">{{firm_address}}</code> - Firm address</div>
                                <div><code style="background:#fff; padding:2px 8px; border-radius:4px; font-size:11px;">{{firm_phone}}</code> - Firm phone</div>
                                <div><code style="background:#fff; padding:2px 8px; border-radius:4px; font-size:11px;">{{client_name}}</code> - Client name</div>
                                <div><code style="background:#fff; padding:2px 8px; border-radius:4px; font-size:11px;">{{case_number}}</code> - Case number</div>
                                <div><code style="background:#fff; padding:2px 8px; border-radius:4px; font-size:11px;">{{doi|date:m/d/Y}}</code> - Date of injury</div>
                                <div><code style="background:#fff; padding:2px 8px; border-radius:4px; font-size:11px;">{{provider_name}}</code> - Provider</div>
                                <div><code style="background:#fff; padding:2px 8px; border-radius:4px; font-size:11px;">{{record_types_list}}</code> - Records list</div>
                                <div style="border-top:1px solid #e8e4dc; padding-top:8px;">
                                    <strong>Conditionals:</strong>
                                    <pre style="background:#fff; padding:8px; border-radius:4px; margin-top:4px; font-size:11px;">{{#if authorization_sent}}
Text if true
{{else}}
Text if false
{{/if}}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sp-modal-footer" style="justify-content:space-between;">
                <button type="button" @click="closeModal()" class="sp-btn">Cancel</button>
                <div style="display:flex; gap:10px;">
                    <button type="button" @click="previewCurrent()" class="sp-btn" style="border-color:#C9A84C; color:#C9A84C;">Preview</button>
                    <button type="button" @click="saveTemplate()" class="sp-new-btn">
                        <span x-text="editingTemplate ? 'Update Template' : 'Create Template'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div x-show="showPreviewModal" x-cloak class="sp-modal-overlay" @keydown.escape.window="showPreviewModal && closePreviewModal()">
        <div class="sp-modal-box sp-modal-box-lg" @click.stop>
            <div class="sp-modal-header">
                <div>
                    <div class="sp-modal-title">Template Preview</div>
                    <div class="tpm-subtitle">Rendered output preview</div>
                </div>
                <button type="button" class="sp-modal-close" @click="closePreviewModal()">&times;</button>
            </div>
            <div class="sp-modal-body" style="flex:1; overflow-y:auto;">
                <div x-html="previewHtml"></div>
            </div>
            <div class="sp-modal-footer">
                <button type="button" @click="closePreviewModal()" class="sp-btn">Close</button>
            </div>
        </div>
    </div>

    <!-- Version History Modal -->
    <div x-show="showVersionsModal" x-cloak class="sp-modal-overlay" @keydown.escape.window="showVersionsModal && closeVersionsModal()">
        <div class="sp-modal-box" style="max-width:680px" @click.stop>
            <div class="sp-modal-header">
                <div>
                    <div class="sp-modal-title">Version History</div>
                    <div class="tpm-subtitle">Template revision log</div>
                </div>
                <button type="button" class="sp-modal-close" @click="closeVersionsModal()">&times;</button>
            </div>
            <div class="sp-modal-body" style="flex:1; overflow-y:auto;">
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <template x-for="(version, idx) in versions" :key="version.id">
                        <div style="border:1px solid #e8e4dc; border-radius:10px; padding:14px 16px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-weight:600; color:#1a2535;">Version <span x-text="version.version_number"></span></span>
                                    <span x-show="idx === 0" class="sp-stage" style="background:#dcfce7; color:#15803d;">Current</span>
                                </div>
                                <span class="sp-mono" style="font-size:11px; color:#9ca3af;" x-text="formatDateTime(version.created_at)"></span>
                            </div>
                            <div style="font-size:13px; color:#6b7280; margin-bottom:4px;">
                                Changed by: <span x-text="version.changed_by_name || 'System'"></span>
                            </div>
                            <div style="display:flex; align-items:center; justify-content:space-between;">
                                <div style="font-size:12px; color:#9ca3af;" x-text="version.change_notes || 'No notes'"></div>
                                <button x-show="idx !== 0" @click="restoreVersion(version)" class="sp-act sp-act-gold">Restore</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="sp-modal-footer">
                <button type="button" @click="closeVersionsModal()" class="sp-btn">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.tpm-subtitle{font-size:12px;font-weight:500;color:#C9A84C;margin-top:2px}
.tpm-req{color:#C9A84C}
</style>
