function trackerPage() {
    return {
        ...listPageBase('billing/list', {
            defaultSort: 'deadline',
            defaultDir: 'asc',
            perPage: 99999,
            filtersToParams() {
                return {
                    status: this.statusFilter,
                    filter: this.activeFilter,
                    tier: this.tierFilter,
                    assigned_to: this.staffFilter || this.assignedFilter,
                    case_id: this.caseIdFilter,
                };
            }
        }),

        // Page-specific state
        summary: { total: 0, overdue: 0, followup_due: 0, not_started: 0 },
        statusFilter: '',
        activeFilter: '',
        tierFilter: '',
        assignedFilter: '',
        staffFilter: '',
        caseIdFilter: '',
        staffList: [],

        // Expand / request history
        expandedId: null,
        requestHistory: [],

        // Request modal
        showRequestModal: false,
        saving: false,
        reqForm: {},
        hlTemplates: [],

        // Receipt modal
        showReceiptModal: false,
        receiptForm: {},

        // Preview & Send modal (full letter editing)
        showPreviewModal: false,
        sending: false,
        previewData: { method: '', recipient: '', provider_name: '', client_name: '', send_status: '', subject: '', letter_html: '', request_id: null },
        isEditingLetter: false,
        originalLetterHtml: '',
        originalSubject: '',

        // Bulk selection
        selectedItems: [],
        allSelected: false,
        lastClickedIndex: null,

        // Bulk request modal
        showBulkRequestModal: false,
        bulkRequestForm: {
            request_date: new Date().toISOString().split('T')[0],
            request_method: 'email',
            request_type: 'follow_up',
            followup_date: '',
            notes: '',
            template_id: ''
        },
        bulkRequestCases: [],
        bulkRequestProviderName: '',
        bulkRequestError: '',
        bulkTemplates: [],

        // Bulk preview modal
        showBulkPreviewModal: false,
        bulkPreviewHtml: '',
        bulkPreviewProviderName: '',
        bulkPreviewCaseCount: 0,

        _resetPageFilters() {
            this.statusFilter = '';
            this.activeFilter = '';
            this.tierFilter = '';
            this.assignedFilter = '';
            this.staffFilter = '';
            this.caseIdFilter = '';
            // Clean URL param
            const url = new URL(window.location);
            url.searchParams.delete('case_id');
            window.history.replaceState({}, '', url);
        },

        _hasPageFilters() {
            return !!(this.statusFilter || this.activeFilter || this.tierFilter || this.assignedFilter || this.staffFilter || this.caseIdFilter);
        },

        async init() {
            // Check for case_id URL param (from "Manage in Tracker")
            const urlParams = new URLSearchParams(window.location.search);
            this.caseIdFilter = urlParams.get('case_id') || '';

            this.loadStaff();
            this.loadTemplates();
            await this.loadData(1);

            this.selectedItems = [];
            this.allSelected = false;
        },

        async loadStaff() {
            try {
                const all = await Alpine.store('staff').getList();
                const allowed = ['ella', 'miki', 'jimi'];
                this.staffList = all.filter(u => {
                    const name = ((u.display_name || u.full_name) || '').toLowerCase();
                    return allowed.some(a => name.includes(a));
                });
            } catch(e) { this.staffList = []; }
        },

        async loadTemplates() {
            try {
                const r = await api.get('templates?active_only=1');
                const all = r.data || [];
                this.hlTemplates = all;
                this.bulkTemplates = all.filter(t => t.type === 'bulk_request');
                const def = this.bulkTemplates.find(t => t.is_default);
                if (def) this.bulkRequestForm.template_id = def.id;
            } catch(e) { this.hlTemplates = []; this.bulkTemplates = []; }
        },

        toggleFilter(filter) {
            this.activeFilter = this.activeFilter === filter ? '' : filter;
            this.loadData(1);
        },

        goToCase(caseId, cpId) {
            let url = '/blcm/frontend/pages/bl-cases/detail.php?id=' + caseId;
            if (cpId) url += '&cp=' + cpId;
            window.location.href = url;
        },

        getMethodLabel(method) {
            const labels = { email: 'Email', fax: 'Fax', portal: 'Portal', phone: 'Phone', mail: 'Mail' };
            return labels[method] || method || '';
        },

        // Bulk selection methods
        toggleSelect(id, event) {
            const currentIndex = this.items.findIndex(item => item.id === id);

            // Shift-click range selection
            if (event && event.shiftKey && this.lastClickedIndex !== null) {
                event.preventDefault();

                const start = Math.min(this.lastClickedIndex, currentIndex);
                const end = Math.max(this.lastClickedIndex, currentIndex);

                for (let i = start; i <= end; i++) {
                    const itemId = this.items[i].id;
                    if (!this.selectedItems.includes(itemId)) {
                        this.selectedItems.push(itemId);
                    }
                }
            } else {
                const index = this.selectedItems.indexOf(id);
                if (index > -1) {
                    this.selectedItems.splice(index, 1);
                } else {
                    this.selectedItems.push(id);
                }
            }

            this.lastClickedIndex = currentIndex;
            this.updateAllSelected();
        },

        toggleSelectAll() {
            if (this.allSelected) {
                this.selectedItems = [];
            } else {
                this.selectedItems = this.items.map(item => item.id);
            }
            this.updateAllSelected();
        },

        updateAllSelected() {
            this.allSelected = this.items.length > 0 && this.selectedItems.length === this.items.length;
        },

        clearSelections() {
            this.selectedItems = [];
            this.allSelected = false;
        },

        // Bulk request modal methods
        async openBulkRequestModal() {
            if (this.selectedItems.length === 0) {
                showToast('Please select at least one case', 'error');
                return;
            }

            // Reset form
            this.bulkRequestForm.request_date = new Date().toISOString().split('T')[0];
            const nextWeek = new Date();
            nextWeek.setDate(nextWeek.getDate() + 7);
            this.bulkRequestForm.followup_date = nextWeek.toISOString().split('T')[0];
            this.bulkRequestForm.notes = '';
            this.bulkRequestError = '';

            // Get selected items details
            const selectedCases = this.items.filter(item => this.selectedItems.includes(item.id));

            // Validate same provider
            const providers = [...new Set(selectedCases.map(c => c.provider_name))];
            if (providers.length > 1) {
                this.bulkRequestError = 'Selected cases must be from the same provider. Found: ' + providers.join(', ');
                this.showBulkRequestModal = true;
                return;
            }

            this.bulkRequestProviderName = providers[0];

            // Populate cases with default recipients
            this.bulkRequestCases = selectedCases.map(c => ({
                id: c.id,
                case_number: c.case_number,
                client_name: c.client_name,
                provider_name: c.provider_name,
                recipient: ''
            }));

            this.showBulkRequestModal = true;
        },

        closeBulkRequestModal() {
            this.showBulkRequestModal = false;
            this.bulkRequestCases = [];
            this.bulkRequestProviderName = '';
            this.bulkRequestError = '';
        },

        removeFromBulk(index) {
            this.bulkRequestCases.splice(index, 1);
            if (this.bulkRequestCases.length === 0) {
                this.closeBulkRequestModal();
            }
        },

        async previewBulkRequests() {
            if (this.bulkRequestCases.length === 0) {
                showToast('No cases to preview', 'error');
                return;
            }

            try {
                const payload = {
                    requests: this.bulkRequestCases.map(c => ({
                        case_provider_id: c.id,
                        recipient: c.recipient || undefined
                    })),
                    request_date: this.bulkRequestForm.request_date,
                    request_method: this.bulkRequestForm.request_method,
                    request_type: this.bulkRequestForm.request_type,
                    next_followup_date: this.bulkRequestForm.followup_date,
                    notes: this.bulkRequestForm.notes,
                    template_id: this.bulkRequestForm.template_id || undefined
                };

                const res = await api.post('requests/preview-bulk', payload);
                this.bulkPreviewHtml = res.data.letter_html || '';
                this.bulkPreviewProviderName = res.data.provider_name || '';
                this.bulkPreviewCaseCount = res.data.case_count || 0;
                this.showBulkPreviewModal = true;
            } catch (e) {
                showToast('Failed to generate preview: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        closeBulkPreviewModal() {
            this.showBulkPreviewModal = false;
            this.bulkPreviewHtml = '';
            this.bulkPreviewProviderName = '';
            this.bulkPreviewCaseCount = 0;
        },

        async createAndSendBulkRequests() {
            if (this.bulkRequestCases.length === 0) {
                showToast('No cases to process', 'error');
                return;
            }

            if (!confirm(`Create and send ${this.bulkRequestCases.length} request(s) to ${this.bulkRequestProviderName}?`)) {
                return;
            }

            try {
                const payload = {
                    requests: this.bulkRequestCases.map(c => ({
                        case_provider_id: c.id,
                        recipient: c.recipient || undefined
                    })),
                    request_date: this.bulkRequestForm.request_date,
                    request_method: this.bulkRequestForm.request_method,
                    request_type: this.bulkRequestForm.request_type,
                    next_followup_date: this.bulkRequestForm.followup_date,
                    notes: this.bulkRequestForm.notes,
                    template_id: this.bulkRequestForm.template_id || undefined,
                    auto_send: true
                };

                const res = await api.post('requests/bulk-create', payload);
                showToast(res.message || 'Bulk requests created and sent successfully', 'success');

                this.closeBulkRequestModal();
                this.clearSelections();
                await this.loadData(this.pagination?.page || 1);
            } catch (e) {
                showToast('Failed to create bulk requests: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        async confirmAndSendBulk() {
            this.closeBulkPreviewModal();
            await this.createAndSendBulkRequests();
        },

        // --- Expand / Request History ---

        async toggleExpand(id) {
            if (this.expandedId === id) { this.expandedId = null; return; }
            this.expandedId = id;
            this.requestHistory = [];
            try {
                const r = await api.get('requests?case_provider_id=' + id);
                this.requestHistory = r.data || [];
            } catch(e) { this.requestHistory = []; }
        },

        async loadRequestHistory(cpId) {
            try {
                const r = await api.get('requests?case_provider_id=' + cpId);
                this.requestHistory = r.data || [];
            } catch(e) { this.requestHistory = []; }
        },

        getSendStatusLabel(status) {
            const labels = { draft: 'Draft', sending: 'Sending...', sent: 'Sent', failed: 'Failed' };
            return labels[status] || status;
        },

        // --- Request Modal ---

        openRequestModal(item) {
            const defaultTpl = this.hlTemplates.find(t => t.is_default == 1);
            this.reqForm = {
                _cpId: item.id,
                _caseId: item.case_id,
                request_date: new Date().toISOString().split('T')[0],
                request_method: '',
                request_type: item.request_count > 0 ? 'follow_up' : 'initial',
                sent_to: '',
                notes: '',
                template_id: defaultTpl ? defaultTpl.id : (this.hlTemplates[0]?.id || ''),
                template_data: {},
                document_ids: [],
                _showSettlement: false,
                _carrierLabel: item.client_name + ' — ' + item.provider_name,
                _email: item.provider_email || '',
                _fax: item.provider_fax || ''
            };
            this.showRequestModal = true;
        },

        openReceiptModal(item) {
            const needed = (item.record_types_needed || '').split(',').filter(Boolean);
            this.receiptForm = {
                _cpId: item.id,
                _label: item.client_name + ' — ' + item.provider_name,
                received_date: new Date().toISOString().split('T')[0],
                received_method: '',
                has_medical_records: false,
                has_billing: false,
                has_chart: false,
                has_imaging: false,
                has_op_report: false,
                is_complete: false,
                incomplete_reason: '',
                notes: '',
                // Show checkboxes based on what was requested or needed
                _needsMr: !!item.request_mr || needed.includes('medical_records'),
                _needsBill: !!item.request_bill || needed.includes('billing'),
                _needsChart: !!item.request_chart || needed.includes('chart'),
                _needsImg: !!item.request_img || needed.includes('imaging'),
                _needsOp: !!item.request_op || needed.includes('op_report'),
            };
            // If no specific types were set, show all checkboxes
            if (!this.receiptForm._needsMr && !this.receiptForm._needsBill && !this.receiptForm._needsChart && !this.receiptForm._needsImg && !this.receiptForm._needsOp) {
                this.receiptForm._needsMr = true;
                this.receiptForm._needsBill = true;
                this.receiptForm._needsChart = true;
                this.receiptForm._needsImg = true;
                this.receiptForm._needsOp = true;
            }
            this.showReceiptModal = true;
        },

        async submitReceipt() {
            if (!this.receiptForm.received_date || !this.receiptForm.received_method) {
                showToast('Date and method are required', 'error');
                return;
            }
            this.saving = true;
            try {
                const payload = {
                    case_provider_id: this.receiptForm._cpId,
                    received_date: this.receiptForm.received_date,
                    received_method: this.receiptForm.received_method,
                    has_medical_records: this.receiptForm.has_medical_records ? 1 : 0,
                    has_billing: this.receiptForm.has_billing ? 1 : 0,
                    has_chart: this.receiptForm.has_chart ? 1 : 0,
                    has_imaging: this.receiptForm.has_imaging ? 1 : 0,
                    has_op_report: this.receiptForm.has_op_report ? 1 : 0,
                    is_complete: this.receiptForm.is_complete ? 1 : 0,
                    notes: this.receiptForm.notes,
                };
                if (!this.receiptForm.is_complete && this.receiptForm.incomplete_reason) {
                    payload.incomplete_reason = this.receiptForm.incomplete_reason;
                }
                await api.post('receipts', payload);
                showToast(this.receiptForm.is_complete ? 'Marked as received (complete)' : 'Receipt logged (partial)');
                this.showReceiptModal = false;
                this.loadData(this.pagination?.page || 1);
            } catch(e) {
                showToast(e.data?.message || 'Failed to log receipt', 'error');
            }
            this.saving = false;
        },

        async updateItemStatus(id, status) {
            try {
                await api.put('case-providers/' + id, { overall_status: status });
                showToast('Status updated');
                this.loadData(this.pagination?.page || 1);
            } catch(e) { showToast(e.data?.message || 'Update failed', 'error'); }
        },

        updateRecipient() {
            if (this.reqForm.request_method === 'email') this.reqForm.sent_to = this.reqForm._email;
            else if (this.reqForm.request_method === 'fax') this.reqForm.sent_to = this.reqForm._fax;
            else this.reqForm.sent_to = '';
        },

        async submitRequest() {
            if (!this.reqForm.request_date || !this.reqForm.request_method) { showToast('Date and method required', 'error'); return; }
            this.saving = true;
            try {
                const payload = {
                    case_provider_id: this.reqForm._cpId,
                    request_date: this.reqForm.request_date,
                    request_method: this.reqForm.request_method,
                    request_type: this.reqForm.request_type,
                    sent_to: this.reqForm.sent_to,
                    notes: this.reqForm.notes,
                };
                if (this.reqForm.template_id) payload.template_id = this.reqForm.template_id;
                if (this.reqForm.template_data && Object.keys(this.reqForm.template_data).length > 0) {
                    payload.template_data = this.reqForm.template_data;
                }
                const res = await api.post('requests', payload);
                const createdRequest = res.data;
                const method = this.reqForm.request_method;
                const cpId = this.reqForm._cpId;
                const docIds = this.reqForm.document_ids || [];

                // Attach documents if any selected
                if (createdRequest && createdRequest.id && docIds.length > 0) {
                    for (const docId of docIds) {
                        try {
                            await api.post('requests/' + createdRequest.id + '/attach', { document_id: docId });
                        } catch(e) { console.error('Failed to attach document:', e); }
                    }
                }

                showToast('Request created' + (docIds.length > 0 ? ' with ' + docIds.length + ' attachment(s)' : ''));
                this.showRequestModal = false;

                // Auto-expand the row and refresh history
                this.expandedId = cpId;
                await this.loadRequestHistory(cpId);
                this.loadData(this.pagination?.page || 1);

                // Auto-open preview for email/fax methods
                if ((method === 'email' || method === 'fax') && createdRequest && createdRequest.id) {
                    this.$nextTick(() => this.openPreviewModal(createdRequest));
                }
            } catch(e) { showToast(e.data?.message || 'Error', 'error'); }
            this.saving = false;
        },

        // --- Preview & Send (full letter editing, same as case-detail) ---

        async openPreviewModal(req) {
            try {
                const res = await api.get('requests/' + req.id + '/preview');
                this.previewData = res.data;
                this.previewData.request_id = req.id;
                this.isEditingLetter = false;
                this.originalLetterHtml = '';
                this.originalSubject = '';
                this.showPreviewModal = true;
            } catch(e) {
                showToast(e.data?.message || 'Failed to load preview', 'error');
            }
        },

        toggleLetterEdit() {
            const iframe = this.$refs.letterIframe;
            if (!iframe) return;
            if (this.isEditingLetter) {
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    this.previewData.letter_html = '<!DOCTYPE html>' + iframeDoc.documentElement.outerHTML;
                    iframeDoc.designMode = 'off';
                } catch(e) {}
                this.isEditingLetter = false;
            } else {
                if (!this.originalLetterHtml) {
                    this.originalLetterHtml = this.previewData.letter_html;
                    this.originalSubject = this.previewData.subject;
                }
                this.isEditingLetter = true;
                const enableEdit = () => {
                    try {
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        if (iframeDoc && iframeDoc.body) { iframeDoc.designMode = 'on'; }
                        else { setTimeout(enableEdit, 50); }
                    } catch(e) { setTimeout(enableEdit, 50); }
                };
                this.$nextTick(() => enableEdit());
            }
        },

        resetLetterToOriginal() {
            if (this.originalLetterHtml) {
                this.previewData.letter_html = this.originalLetterHtml;
                this.previewData.subject = this.originalSubject;
                if (this.isEditingLetter) {
                    const iframe = this.$refs.letterIframe;
                    const enableEdit = () => {
                        try {
                            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                            if (iframeDoc && iframeDoc.body && iframeDoc.readyState === 'complete') { iframeDoc.designMode = 'on'; }
                            else { setTimeout(enableEdit, 50); }
                        } catch(e) { setTimeout(enableEdit, 50); }
                    };
                    iframe.addEventListener('load', enableEdit, { once: true });
                }
                showToast('Letter reset to original');
            }
        },

        closePreviewModal() {
            if (this.isEditingLetter) {
                try {
                    const iframe = this.$refs.letterIframe;
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    iframeDoc.designMode = 'off';
                } catch(e) {}
            }
            this.isEditingLetter = false;
            this.originalLetterHtml = '';
            this.originalSubject = '';
            this.showPreviewModal = false;
        },

        getEditedLetterHtml() {
            try {
                const iframe = this.$refs.letterIframe;
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                return '<!DOCTYPE html>' + iframeDoc.documentElement.outerHTML;
            } catch(e) { return this.previewData.letter_html; }
        },

        async confirmAndSend() {
            if (!this.previewData.recipient) { showToast('Please enter a recipient', 'error'); return; }
            if (!await confirmAction('Send this request via ' + (this.previewData.method === 'email' ? 'email' : 'fax') + ' to ' + this.previewData.recipient + '?')) return;
            this.sending = true;
            try {
                const payload = { recipient: this.previewData.recipient };
                if (this.originalLetterHtml) {
                    const currentHtml = this.isEditingLetter ? this.getEditedLetterHtml() : this.previewData.letter_html;
                    if (currentHtml !== this.originalLetterHtml) payload.letter_html = currentHtml;
                    if (this.previewData.subject !== this.originalSubject) payload.subject = this.previewData.subject;
                }
                const res = await api.post('requests/' + this.previewData.request_id + '/send', payload);
                showToast(res.message || 'Sent successfully!');
                this.closePreviewModal();
                if (this.expandedId) await this.loadRequestHistory(this.expandedId);
                this.loadData(this.pagination?.page || 1);
            } catch(e) { showToast(e.data?.message || 'Send failed', 'error'); }
            this.sending = false;
        },

        async deleteRequest(req) {
            if (!confirm('Delete this ' + req.send_status + ' ' + (req.request_type || '') + ' request (' + req.request_date + ')?')) return;
            try {
                await api.delete('requests/' + req.id);
                showToast('Request deleted');
                if (this.expandedId) await this.loadRequestHistory(this.expandedId);
                this.loadData(this.pagination?.page || 1);
            } catch(e) { showToast(e.data?.message || 'Delete failed', 'error'); }
        }
    };
}
