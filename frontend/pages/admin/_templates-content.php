<!-- All sp- styles loaded from shared sp-design-system.css -->

<div x-data="templatesPage()" x-init="init()">

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">
        <div class="sp-gold-bar"></div>
        <div class="sp-header" style="flex-wrap:wrap; gap:16px;">
            <div style="flex:1;">
                <div class="sp-eyebrow">Admin</div>
                <h1 class="sp-title">Letter Templates</h1>
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
                        <td class="sp-mono" style="font-size:12px;" x-text="new Date(template.updated_at).toLocaleDateString()"></td>
                        <td style="text-align:center;">
                            <div class="sp-actions" style="justify-content:center;">
                                <button @click="viewVersions(template)" class="sp-act sp-act-gold" title="Version History">Hist</button>
                                <button @click="previewTemplate(template)" class="sp-act sp-act-blue" title="Preview">View</button>
                                <button @click="editTemplate(template)" class="sp-act sp-act-gold" title="Edit">Edit</button>
                                <button @click="deleteTemplate(template)" class="sp-act sp-act-red" title="Delete">Del</button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Create/Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45); padding:16px;" @keydown.escape.window="showModal && closeModal()">
        <div class="tpm-modal" style="width:1000px; max-height:90vh; display:flex; flex-direction:column;" @click.stop>
            <div class="tpm-header">
                <div>
                    <div class="tpm-title" x-text="editingTemplate ? 'Edit Template' : 'Create Template'"></div>
                    <div class="tpm-subtitle">HTML template with placeholder support</div>
                </div>
                <button type="button" class="tpm-close" @click="closeModal()">&times;</button>
            </div>
            <div class="tpm-body" style="flex:1; overflow-y:auto;">
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
                    <div style="display:flex; flex-direction:column; gap:16px;">
                        <div>
                            <label class="tpm-label">Template Name <span class="tpm-req">*</span></label>
                            <input type="text" x-model="form.name" class="tpm-input" placeholder="e.g., Medical Records Request - Standard">
                        </div>
                        <div>
                            <label class="tpm-label">Description</label>
                            <textarea x-model="form.description" rows="2" class="tpm-textarea" placeholder="Brief description of this template"></textarea>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; align-items:end;">
                            <div>
                                <label class="tpm-label">Template Type <span class="tpm-req">*</span></label>
                                <select x-model="form.template_type" class="tpm-select">
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
                            <label class="tpm-label">Subject Template</label>
                            <input type="text" x-model="form.subject_template" class="tpm-input" placeholder="e.g., Medical Records Request - {{client_name}}">
                        </div>
                        <div>
                            <label class="tpm-label">Body Template <span class="tpm-req">*</span> (HTML with placeholders)</label>
                            <textarea x-model="form.body_template" rows="20" class="tpm-textarea" style="font-family:'IBM Plex Mono',monospace; font-size:12px;" placeholder="Enter HTML template with {{placeholders}}"></textarea>
                        </div>
                        <template x-if="editingTemplate">
                            <div>
                                <label class="tpm-label">Change Notes</label>
                                <input type="text" x-model="form.change_notes" class="tpm-input" placeholder="Describe what changed in this version">
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
            <div class="tpm-footer" style="justify-content:space-between;">
                <button type="button" @click="closeModal()" class="tpm-btn-cancel">Cancel</button>
                <div style="display:flex; gap:10px;">
                    <button type="button" @click="previewCurrent()" class="tpm-btn-cancel" style="border-color:#C9A84C; color:#C9A84C;">Preview</button>
                    <button type="button" @click="saveTemplate()" class="tpm-btn-submit">
                        <span x-text="editingTemplate ? 'Update Template' : 'Create Template'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div x-show="showPreviewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45); padding:16px;" @keydown.escape.window="showPreviewModal && closePreviewModal()">
        <div class="tpm-modal" style="width:800px; max-height:90vh; display:flex; flex-direction:column;" @click.stop>
            <div class="tpm-header">
                <div>
                    <div class="tpm-title">Template Preview</div>
                    <div class="tpm-subtitle">Rendered output preview</div>
                </div>
                <button type="button" class="tpm-close" @click="closePreviewModal()">&times;</button>
            </div>
            <div class="tpm-body" style="flex:1; overflow-y:auto;">
                <div x-html="previewHtml"></div>
            </div>
            <div class="tpm-footer">
                <button type="button" @click="closePreviewModal()" class="tpm-btn-cancel">Close</button>
            </div>
        </div>
    </div>

    <!-- Version History Modal -->
    <div x-show="showVersionsModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45); padding:16px;" @keydown.escape.window="showVersionsModal && closeVersionsModal()">
        <div class="tpm-modal" style="width:680px; max-height:90vh; display:flex; flex-direction:column;" @click.stop>
            <div class="tpm-header">
                <div>
                    <div class="tpm-title">Version History</div>
                    <div class="tpm-subtitle">Template revision log</div>
                </div>
                <button type="button" class="tpm-close" @click="closeVersionsModal()">&times;</button>
            </div>
            <div class="tpm-body" style="flex:1; overflow-y:auto;">
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <template x-for="(version, idx) in versions" :key="version.id">
                        <div style="border:1px solid #e8e4dc; border-radius:10px; padding:14px 16px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-weight:600; color:#1a2535;">Version <span x-text="version.version_number"></span></span>
                                    <span x-show="idx === 0" class="sp-stage" style="background:#dcfce7; color:#15803d;">Current</span>
                                </div>
                                <span class="sp-mono" style="font-size:11px; color:#9ca3af;" x-text="new Date(version.created_at).toLocaleString()"></span>
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
            <div class="tpm-footer">
                <button type="button" @click="closeVersionsModal()" class="tpm-btn-cancel">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.tpm-modal{border-radius:12px;box-shadow:0 24px 64px rgba(0,0,0,.24);overflow:hidden;background:#fff}
.tpm-header{background:#0F1B2D;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.tpm-title{font-size:15px;font-weight:700;color:#fff}
.tpm-subtitle{font-size:12px;font-weight:500;color:#C9A84C;margin-top:2px}
.tpm-close{width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;background:none;border:none;color:rgba(255,255,255,.35);cursor:pointer;font-size:20px;transition:color .15s}
.tpm-close:hover{color:rgba(255,255,255,.75)}
.tpm-body{padding:24px;display:flex;flex-direction:column;gap:16px}
.tpm-label{display:block;font-size:9.5px;font-weight:700;color:#8a8a82;text-transform:uppercase;letter-spacing:.08em;margin-bottom:5px}
.tpm-req{color:#C9A84C}
.tpm-input{width:100%;background:#fafafa;border:1.5px solid #e8e4dc;border-radius:7px;padding:9px 12px;font-size:13px;outline:none;transition:border-color .15s,background .15s,box-shadow .15s}
.tpm-input:focus{border-color:#C9A84C;background:#fff;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.tpm-textarea{width:100%;background:#fafafa;border:1.5px solid #e8e4dc;border-radius:7px;padding:9px 12px;font-size:13px;outline:none;resize:vertical;min-height:70px;line-height:1.5;transition:border-color .15s,background .15s,box-shadow .15s}
.tpm-textarea:focus{border-color:#C9A84C;background:#fff;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.tpm-select{width:100%;background:#fafafa;border:1.5px solid #e8e4dc;border-radius:7px;padding:9px 12px;font-size:13px;outline:none;appearance:none;padding-right:30px;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;transition:border-color .15s,background .15s,box-shadow .15s}
.tpm-select:focus{border-color:#C9A84C;background:#fff;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.tpm-footer{padding:14px 24px;border-top:1px solid #e8e4dc;display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-shrink:0}
.tpm-btn-cancel{background:#fff;border:1.5px solid #e8e4dc;border-radius:7px;padding:9px 18px;font-size:13px;font-weight:500;color:#5A6B82;cursor:pointer;transition:border-color .15s,color .15s}
.tpm-btn-cancel:hover{border-color:#94a3b8;color:#374151}
.tpm-btn-submit{background:#C9A84C;color:#fff;border:none;border-radius:7px;padding:9px 22px;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(201,168,76,.35);display:flex;align-items:center;gap:6px;transition:opacity .15s}
.tpm-btn-submit:hover{opacity:.92}
</style>
