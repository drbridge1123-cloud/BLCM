function templatesPage() {
    return {
        templates: [],
        loading: false,
        filterType: '',
        activeOnly: true,

        showModal: false,
        editingTemplate: null,
        form: {
            name: '',
            description: '',
            template_type: 'medical_records',
            subject_template: '',
            body_template: '',
            is_default: false,
            change_notes: ''
        },

        showPreviewModal: false,
        previewHtml: '',

        showVersionsModal: false,
        versionsTemplateId: null,
        versions: [],

        async init() {
            await this.loadTemplates();
        },

        async loadTemplates() {
            this.loading = true;
            try {
                let params = [];
                if (this.filterType) params.push(`type=${this.filterType}`);
                if (this.activeOnly) params.push('active_only=1');

                const query = params.length > 0 ? '?' + params.join('&') : '';
                const res = await api.get('templates' + query);
                this.templates = res.data || [];
            } catch (e) {
                showToast('Failed to load templates: ' + (e.response?.data?.error || e.message), 'error');
            }
            this.loading = false;
        },

        openCreateModal() {
            this.editingTemplate = null;
            this.form = {
                name: '',
                description: '',
                template_type: 'medical_records',
                subject_template: '',
                body_template: '',
                is_default: false,
                change_notes: ''
            };
            this.showModal = true;
        },

        async editTemplate(template) {
            try {
                const full = await api.get(`templates/${template.id}`);
                const t = full.data;
                this.editingTemplate = t;
                this.form = {
                    name: t.name,
                    description: t.description || '',
                    template_type: t.template_type,
                    subject_template: t.subject_template || '',
                    body_template: t.body_template || '',
                    is_default: t.is_default == 1,
                    change_notes: ''
                };
                this.showModal = true;
            } catch (e) {
                showToast('Failed to load template: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        closeModal() {
            this.showModal = false;
            this.editingTemplate = null;
        },

        async saveTemplate() {
            if (!this.form.name || !this.form.body_template) {
                showToast('Name and body template are required', 'error');
                return;
            }

            // Confirmation for edits
            if (this.editingTemplate) {
                const notes = this.form.change_notes ? ` (${this.form.change_notes})` : '';
                if (!confirm(`Save changes to "${this.form.name}"?${notes}\n\nA new version will be created. You can restore previous versions from Version History.`)) {
                    return;
                }
            }

            try {
                if (this.editingTemplate) {
                    await api.put(`templates/${this.editingTemplate.id}`, this.form);
                    showToast('Template updated successfully', 'success');
                } else {
                    await api.post('templates', this.form);
                    showToast('Template created successfully', 'success');
                }
                this.closeModal();
                await this.loadTemplates();
            } catch (e) {
                showToast('Failed to save template: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        async deleteTemplate(template) {
            if (!confirm(`Delete template "${template.name}"?`)) return;

            try {
                await api.delete(`templates/${template.id}`);
                showToast('Template deleted successfully', 'success');
                await this.loadTemplates();
            } catch (e) {
                showToast('Failed to delete template: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        async previewTemplate(template) {
            try {
                const fullTemplate = await api.get(`templates/${template.id}`);
                const res = await api.post('templates/preview', {
                    body_template: fullTemplate.data.body_template
                });
                this.previewHtml = res.data.html;
                this.showPreviewModal = true;
            } catch (e) {
                showToast('Failed to preview: ' + (e.data?.message || e.message), 'error');
            }
        },

        async previewCurrent() {
            if (!this.form.body_template) {
                showToast('Body template is required for preview', 'error');
                return;
            }

            try {
                const res = await api.post('templates/preview', {
                    body_template: this.form.body_template
                });
                this.previewHtml = res.data.html;
                this.showPreviewModal = true;
            } catch (e) {
                showToast('Failed to preview: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        closePreviewModal() {
            this.showPreviewModal = false;
        },

        async viewVersions(template) {
            try {
                this.versionsTemplateId = template.id;
                const res = await api.get(`templates/${template.id}/versions`);
                this.versions = res.data.versions || [];
                this.showVersionsModal = true;
            } catch (e) {
                showToast('Failed to load versions: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        async restoreVersion(version) {
            if (!confirm(`Restore to version ${version.version_number}?\n\nThe current template will be replaced with this version's content. A new version entry will be created for the restore.`)) {
                return;
            }

            try {
                await api.post(`templates/${this.versionsTemplateId}/restore`, {
                    version_id: version.id
                });
                showToast(`Restored to version ${version.version_number}`, 'success');

                // Refresh versions list
                const res = await api.get(`templates/${this.versionsTemplateId}/versions`);
                this.versions = res.data.versions || [];

                await this.loadTemplates();
            } catch (e) {
                showToast('Failed to restore: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        closeVersionsModal() {
            this.showVersionsModal = false;
            this.versionsTemplateId = null;
            this.versions = [];
        }
    };
}
