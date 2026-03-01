/**
 * Template Selector Component
 *
 * Reusable Alpine.js component for selecting letter templates in request forms.
 * Loads available templates by type and provides preview functionality.
 *
 * Usage:
 *   <div x-data="templateSelector('medical_records')" x-init="init()">
 *       <!-- Use component properties and methods -->
 *   </div>
 */

/**
 * Create template selector Alpine component
 * @param {string} templateType - Type of templates to load ('medical_records', 'health_ledger', etc.)
 * @param {object} requestData - Request data for preview rendering (optional)
 * @returns {object} Alpine.js component
 */
function templateSelector(templateType = 'medical_records', requestData = null) {
    return {
        // State
        templates: [],
        selectedTemplateId: null,
        selectedTemplate: null,
        loading: false,
        showPreview: false,
        previewHtml: '',

        // Request data for preview
        requestData: requestData,

        /**
         * Initialize component - load templates
         */
        async init() {
            await this.loadTemplates();

            // Auto-select default template if exists
            const defaultTemplate = this.templates.find(t => t.is_default === 1);
            if (defaultTemplate) {
                this.selectedTemplateId = defaultTemplate.id;
                await this.selectTemplate(defaultTemplate.id);
            }
        },

        /**
         * Load available templates from API
         */
        async loadTemplates() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    type: templateType,
                    active_only: 1
                });

                const response = await api.get(`templates?${params.toString()}`);
                if (response.success) {
                    this.templates = response.data || [];
                }
            } catch (error) {
                console.error('Failed to load templates:', error);
                showToast('Failed to load templates', 'error');
            } finally {
                this.loading = false;
            }
        },

        /**
         * Handle template selection
         * @param {number} templateId - Selected template ID
         */
        async selectTemplate(templateId) {
            if (!templateId) {
                this.selectedTemplate = null;
                this.selectedTemplateId = null;
                return;
            }

            this.loading = true;
            try {
                const response = await api.get(`templates/${templateId}`);
                if (response.success) {
                    this.selectedTemplate = response.data;
                    this.selectedTemplateId = templateId;

                    // Trigger custom event for parent component
                    this.$dispatch('template-selected', {
                        templateId: templateId,
                        template: this.selectedTemplate
                    });
                }
            } catch (error) {
                console.error('Failed to load template:', error);
                showToast('Failed to load template details', 'error');
            } finally {
                this.loading = false;
            }
        },

        /**
         * Preview selected template with request data
         */
        async previewSelectedTemplate() {
            if (!this.selectedTemplateId) {
                showToast('Please select a template first', 'warning');
                return;
            }

            this.loading = true;
            try {
                const payload = {
                    template_id: this.selectedTemplateId
                };

                // If request data provided, use it for preview
                if (this.requestData) {
                    payload.sample_data = this.requestData;
                }

                const response = await api.post('templates/preview', payload);
                if (response.success) {
                    this.previewHtml = response.data.html;
                    this.showPreview = true;
                }
            } catch (error) {
                console.error('Failed to preview template:', error);
                showToast('Failed to generate preview', 'error');
            } finally {
                this.loading = false;
            }
        },

        /**
         * Close preview modal
         */
        closePreview() {
            this.showPreview = false;
            this.previewHtml = '';
        },

        /**
         * Get template name by ID
         * @param {number} templateId
         * @returns {string}
         */
        getTemplateName(templateId) {
            const template = this.templates.find(t => t.id === templateId);
            return template ? template.name : 'Unknown Template';
        },

        /**
         * Check if template is default
         * @param {number} templateId
         * @returns {boolean}
         */
        isDefaultTemplate(templateId) {
            const template = this.templates.find(t => t.id === templateId);
            return template ? template.is_default === 1 : false;
        },

        /**
         * Update request data for preview
         * @param {object} data - New request data
         */
        updateRequestData(data) {
            this.requestData = data;
        },

        /**
         * Get available placeholders for current template type
         * @returns {array}
         */
        getAvailablePlaceholders() {
            const placeholdersByType = {
                'medical_records': [
                    { placeholder: '{{firm_name}}', description: 'Law firm name' },
                    { placeholder: '{{firm_address}}', description: 'Law firm address' },
                    { placeholder: '{{firm_phone}}', description: 'Law firm phone number' },
                    { placeholder: '{{firm_fax}}', description: 'Law firm fax number' },
                    { placeholder: '{{provider_name}}', description: 'Medical provider name' },
                    { placeholder: '{{provider_address}}', description: 'Provider full address' },
                    { placeholder: '{{client_name}}', description: 'Client full name' },
                    { placeholder: '{{client_dob}}', description: 'Client date of birth' },
                    { placeholder: '{{doi}}', description: 'Date of incident' },
                    { placeholder: '{{doi|date:m/d/Y}}', description: 'Date of incident (formatted)' },
                    { placeholder: '{{case_number}}', description: 'Case number' },
                    { placeholder: '{{attorney_name}}', description: 'Assigned attorney name' },
                    { placeholder: '{{request_date}}', description: 'Request date' },
                    { placeholder: '{{request_date|date:F j, Y}}', description: 'Request date (formatted)' },
                    { placeholder: '{{record_types_list}}', description: 'List of requested record types' },
                    { placeholder: '{{notes}}', description: 'Additional notes' },
                    { placeholder: '{{notes|default:No additional notes}}', description: 'Notes with default' },
                    { placeholder: '{{#if authorization_sent}}...{{else}}...{{/if}}', description: 'Conditional: if authorization sent' }
                ],
                'health_ledger': [
                    { placeholder: '{{firm_name}}', description: 'Law firm name' },
                    { placeholder: '{{firm_address}}', description: 'Law firm address' },
                    { placeholder: '{{firm_attorneys}}', description: 'Attorney list HTML' },
                    { placeholder: '{{insurance_carrier}}', description: 'Insurance carrier name' },
                    { placeholder: '{{claim_number}}', description: 'Insurance claim number' },
                    { placeholder: '{{member_id}}', description: 'Member ID (subrogation)' },
                    { placeholder: '{{client_name}}', description: 'Client full name' },
                    { placeholder: '{{client_dob|date:m/d/Y}}', description: 'Client DOB (formatted)' },
                    { placeholder: '{{doi|date:m/d/Y}}', description: 'Date of loss (formatted)' },
                    { placeholder: '{{case_number}}', description: 'Case number' },
                    { placeholder: '{{attorney_name}}', description: 'Attorney name' },
                    { placeholder: '{{request_date|date:F j, Y}}', description: 'Request date (formatted)' },
                    { placeholder: '{{request_method}}', description: 'Send method: Email or Fax' },
                    { placeholder: '{{recipient_contact}}', description: 'Carrier email or fax' },
                    { placeholder: '{{settlement_amount|currency}}', description: 'Settlement amount' },
                    { placeholder: '{{settlement_date|date:m/d/Y}}', description: 'Settlement date' },
                    { placeholder: '{{attorney_fees|currency}}', description: 'Attorney fees' },
                    { placeholder: '{{costs|currency}}', description: 'Costs' },
                    { placeholder: '{{treatment_end_date|date:m/d/Y}}', description: 'Last treatment date' }
                ],
                'balance_verification': [
                    { placeholder: '{{firm_name}}', description: 'Law firm name' },
                    { placeholder: '{{firm_attorneys}}', description: 'Attorney list HTML' },
                    { placeholder: '{{provider_name}}', description: 'Provider name' },
                    { placeholder: '{{provider_address}}', description: 'Provider address' },
                    { placeholder: '{{provider_fax}}', description: 'Provider fax' },
                    { placeholder: '{{client_name}}', description: 'Client full name' },
                    { placeholder: '{{client_dob|date:m/d/Y}}', description: 'Client DOB (formatted)' },
                    { placeholder: '{{doi|date:m/d/Y}}', description: 'Date of loss (formatted)' },
                    { placeholder: '{{case_number}}', description: 'Case number' },
                    { placeholder: '{{attorney_name}}', description: 'Attorney name' },
                    { placeholder: '{{request_date|date:F j, Y}}', description: 'Request date (formatted)' },
                    { placeholder: '{{request_method}}', description: 'Send method: Email or Fax' },
                    { placeholder: '{{recipient_contact}}', description: 'Provider email or fax' }
                ],
                'bulk_request': [
                    { placeholder: '{{firm_name}}', description: 'Law firm name' },
                    { placeholder: '{{firm_address}}', description: 'Law firm address' },
                    { placeholder: '{{provider_name}}', description: 'Medical provider name' },
                    { placeholder: '{{total_requests}}', description: 'Number of requests in bulk' },
                    { placeholder: '{{request_date}}', description: 'Request date' }
                ],
                'custom': [
                    { placeholder: '{{firm_name}}', description: 'Law firm name' },
                    { placeholder: '{{client_name}}', description: 'Client name' },
                    { placeholder: '{{case_number}}', description: 'Case number' },
                    { placeholder: '{{request_date}}', description: 'Request date' }
                ]
            };

            return placeholdersByType[templateType] || placeholdersByType['custom'];
        }
    };
}
