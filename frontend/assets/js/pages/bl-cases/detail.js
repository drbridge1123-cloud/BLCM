function caseDetailPage() {
    return {
        ...contactsMixin(),
        ...checklistMixin(),
        caseId: getQueryParam('id'),
        caseData: null,
        providers: [],
        notes: [],
        loading: true,
        saving: false,

        showEditModal: false,
        showAddProviderModal: false,
        showStatusDropdown: false,
        showStatusChangeModal: false,
        statusChangeForm: { target_status: '', note: '', direction: '', from_label: '', to_label: '', assign_to: '' },
        showRequestModal: false,
        showReceiptModal: false,
        showPreviewModal: false,
        showDeadlineModal: false,

        editData: {},
        currentProvider: null,
        showProviders: true,
        provSortBy: '',
        provSortDir: 'asc',
        previewData: { method: '', recipient: '', provider_name: '', client_name: '', send_status: '', subject: '', letter_html: '', request_id: null },
        isEditingLetter: false,
        originalLetterHtml: '',
        originalSubject: '',
        sending: false,
        deadlineProvider: null,
        deadlineForm: { deadline: '', reason: '' },
        deadlineHistory: [],

        providerSearch: '',
        providerResults: [],
        selectedProvider: null,
        newProvider: { record_types: [], deadline: '' },
        newRequest: { request_date: new Date().toISOString().split('T')[0], request_method: 'email', request_type: 'initial', sent_to: '', authorization_sent: true, notes: '', template_id: null, document_ids: [] },
        newReceipt: { received_date: new Date().toISOString().split('T')[0], received_method: 'fax', has_medical_records: false, has_billing: false, has_chart: false, has_imaging: false, is_complete: false, incomplete_reason: '', file_location: '', no_records: false, no_records_reason: '', no_records_detail: '' },
        newNote: { note_type: 'general', content: '', case_provider_id: '', contact_method: '', contact_date: '' },
        noteFilterProvider: '',

        // Payment state
        showPaymentModal: false,
        payments: [],
        paymentTotal: 0,
        staffList: [],
        paymentForm: { id: null, case_provider_id: null, provider_name: '', description: 'Record Fee', expense_category: 'mr_cost', billed_amount: 0, paid_amount: 0, payment_type: 'check', check_number: '', payment_date: new Date().toISOString().split('T')[0], paid_date: '', paid_by: '', receipt_document_id: null, receipt_file_name: '', notes: '' },
        paymentProviderSearch: '',
        paymentProviderResults: [],
        showPaymentProviderDropdown: false,
        showProviderDropdown: false,

        // Quick-add provider state
        showQuickAddProvider: false,
        quickAddFor: '', // 'provider' or 'payment'
        quickAddForm: { name: '', type: 'hospital', phone: '', fax: '', email: '', address: '', city: '', state: '', zip: '' },
        quickAddSaving: false,

        // Cost split state
        relatedCases: [],
        splitEnabled: false,
        splitSelectedCaseIds: [],
        loadingRelatedCases: false,
        showSplitPanel: false,

        // Workflow stepper
        workflowSteps: [
            { key: 'treatment', label: 'TREATMENT', statuses: ['ini'] },
            { key: 'collection', label: 'RECORDS', statuses: ['rec'] },
            { key: 'verification', label: 'VERIFICATION', statuses: ['verification'] },
            { key: 'demand', label: 'DEMAND', statuses: ['rfd'] },
            { key: 'negotiate', label: 'NEGOTIATE', statuses: ['neg'] },
            { key: 'litigation', label: 'LITIGATION', statuses: ['lit'] },
            { key: 'final_review', label: 'FINAL REVIEW', statuses: ['fbc'] },
            { key: 'settlement', label: 'SETTLEMENT', statuses: ['accounting'] },
        ],

        // INI activation state
        showIniStaffModal: false,
        iniSelectedStaff: '',
        iniActivating: false,
        iniProviderIds: [],  // specific provider IDs to activate (empty = all)
        iniRecordTypes: { request_mr: true, request_bill: true, request_chart: false, request_img: false, request_op: false },
        iniNotes: '',
        iniChangeStatus: '',  // optional status change on activation

        // Treatment Complete modal state
        showTxCompleteModal: false,
        txCompleteProvider: null,
        txCompleteChecked: false,
        txCompleteDate: '',
        txCompleteSaving: false,

        // Cost Ledger state
        allCosts: [],
        allCostsTotal: { billed: 0, paid: 0 },
        showCostLedger: false,
        showCostImportModal: false,
        costImportPreview: [],
        costImportSummary: {},
        costImporting: false,

        async init() {
            if (!this.caseId) {
                window.location.href = '/blcm/frontend/pages/bl-cases/index.php';
                return;
            }
            // Auto-collapse sidebar on case detail page
            Alpine.store('sidebar').collapsed = true;
            // Set default deadline (2 weeks from today)
            this.newProvider.deadline = this.getDefaultDeadline();
            await Promise.all([this.loadCase(), this.loadProviders(), this.loadNotes(), this.loadStaffList(), this.loadAllCosts(), this.clLoadBadge()]);

            // Auto-collapse providers if all are complete
            if (this.providers.length > 0 && this.providers.every(p => ['received_complete', 'no_records', 'verified'].includes(p.overall_status))) {
                this.showProviders = false;
            }

            // If cp param is present (from tracker), redirect to tracker
            const cpId = getQueryParam('cp');
            if (cpId) {
                this.loadPayments(parseInt(cpId));
            }

            this.loading = false;

            // Listen for MBR-triggered provider/cost changes
            window.addEventListener('providers-changed', () => this.loadProviders());
            window.addEventListener('costs-changed', () => this.loadAllCosts());

            // Scroll to expanded provider and flash
            if (cpId) {
                this.$nextTick(() => {
                    const el = document.getElementById('history-' + parseInt(cpId));
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.querySelector('td').classList.add('history-flash');
                    }
                });
            }
        },

        async loadCase() {
            try {
                const res = await api.get('bl-cases/' + this.caseId);
                this.caseData = res.data;
                this.editData = { ...res.data };
                // Convert to string for <select> compatibility
                if (this.editData.assigned_to) this.editData.assigned_to = String(this.editData.assigned_to);
            } catch (e) {
                showToast('Failed to load case', 'error');
            }
        },

        async loadProviders() {
            try {
                let url = 'case-providers?case_id=' + this.caseId;
                if (this.provSortBy) url += '&sort_by=' + this.provSortBy + '&sort_dir=' + this.provSortDir;
                const res = await api.get(url);
                this.providers = res.data || [];
            } catch (e) { }
        },

        sortProviders(column) {
            if (this.provSortBy === column) {
                this.provSortDir = this.provSortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.provSortBy = column;
                this.provSortDir = 'asc';
            }
            this.loadProviders();
        },

        async loadNotes() {
            try {
                let url = 'notes?case_id=' + this.caseId;
                if (this.noteFilterProvider) url += '&case_provider_id=' + this.noteFilterProvider;
                const res = await api.get(url);
                this.notes = res.data || [];
            } catch (e) { }
        },

        async updateCase() {
            // Block Treating Completed if treating/treatment_complete providers remain
            const wasCompleted = this.caseData.ini_completed;
            const nowCompleted = this.editData.ini_completed;
            if (!wasCompleted && nowCompleted) {
                const treatingProviders = this.providers.filter(p => p.overall_status === 'treating');
                if (treatingProviders.length > 0) {
                    showToast(`Cannot mark Treating Completed — ${treatingProviders.length} provider(s) still treating`, 'error');
                    this.editData.ini_completed = 0;
                    return;
                }
                const missingDate = this.providers.filter(p => p.overall_status === 'treatment_complete' && !p.treatment_end_date);
                if (missingDate.length > 0) {
                    showToast(`Missing treatment end date: ${missingDate.map(p => p.provider_name).join(', ')}`, 'error');
                    this.editData.ini_completed = 0;
                    return;
                }
                const readyProviders = this.providers.filter(p => p.overall_status === 'treatment_complete');
                if (readyProviders.length > 0) {
                    // Close edit modal, open INI staff modal for bulk activation
                    this.showEditModal = false;
                    this.completeIni();
                    return;
                }
            }

            this.saving = true;
            try {
                const payload = { ...this.editData };
                payload.assigned_to = payload.assigned_to ? parseInt(payload.assigned_to) : null;
                await api.put('bl-cases/' + this.caseId, payload);
                showToast('Case updated');
                this.showEditModal = false;
                await this.loadCase();
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Update failed', 'error');
            }
            this.saving = false;
        },

        activateProvider(p) {
            // Lightning bolt on treating → open Treatment Complete modal
            this.txCompleteProvider = p;
            this.txCompleteChecked = false;
            this.txCompleteDate = p.treatment_end_date || '';
            this.showTxCompleteModal = true;
        },

        async saveTreatmentComplete() {
            if (!this.txCompleteChecked || !this.txCompleteDate) return;
            this.txCompleteSaving = true;
            try {
                await api.put('case-providers/' + this.txCompleteProvider.id + '/complete-treatment', {
                    treatment_end_date: this.txCompleteDate
                });
                showToast('Treatment marked complete');
                this.showTxCompleteModal = false;
                await this.loadProviders();
                window.dispatchEvent(new CustomEvent('providers-changed'));
            } catch (e) {
                showToast(e.data?.message || 'Failed to update', 'error');
            }
            this.txCompleteSaving = false;
        },

        rushActivateProvider(p) {
            // Lightning bolt on treatment_complete → rush activate for billing (opens modal)
            this.iniProviderIds = [p.id];
            this.iniSelectedStaff = '';
            this.iniRecordTypes = { request_mr: true, request_bill: true, request_chart: false, request_img: false, request_op: false };
            this.iniNotes = '';
            this.iniChangeStatus = '';
            this.showIniStaffModal = true;
        },

        toggleIniCompleted() {
            if (this.caseData.ini_completed) {
                // Yes → confirm to undo
                if (!confirm('Are you sure you want to undo Treating Completed? Activated providers will revert to Tx Complete.')) return;
                api.put('bl-cases/' + this.caseId, { ini_completed: 0 })
                    .then(() => {
                        showToast('Treating Completed undone');
                        this.loadCase();
                        this.loadProviders();
                    })
                    .catch(e => showToast(e.data?.message || 'Failed to update', 'error'));
            } else {
                // No → validate all providers are treatment_complete with end dates
                const treatingProviders = this.providers.filter(p => p.overall_status === 'treating');
                if (treatingProviders.length > 0) {
                    showToast(`Cannot mark Treating Completed — ${treatingProviders.length} provider(s) still treating`, 'error');
                    return;
                }
                const missingDate = this.providers.filter(p => p.overall_status === 'treatment_complete' && !p.treatment_end_date);
                if (missingDate.length > 0) {
                    showToast(`Missing treatment end date: ${missingDate.map(p => p.provider_name).join(', ')}`, 'error');
                    return;
                }
                // All treatments done with dates → open INI staff modal for bulk activation
                this.completeIni();
            }
        },

        completeIni() {
            const readyProviders = this.providers.filter(p => p.overall_status === 'treatment_complete');
            if (readyProviders.length === 0) {
                showToast('No providers ready for activation', 'info');
                return;
            }
            this.iniProviderIds = readyProviders.map(p => p.id);
            this.iniSelectedStaff = '';
            this.iniRecordTypes = { request_mr: true, request_bill: true, request_chart: false, request_img: false, request_op: false };
            this.iniNotes = '';
            this.iniChangeStatus = (FORWARD_TRANSITIONS[this.caseData?.status] || [])[0] || '';
            this.showIniStaffModal = true;
        },

        async confirmIniActivation() {
            if (!this.iniSelectedStaff) {
                showToast('Please select a staff member', 'error');
                return;
            }
            this.iniActivating = true;
            try {
                const payload = {
                    assigned_to: parseInt(this.iniSelectedStaff),
                    ...this.iniRecordTypes,
                    notes: this.iniNotes || undefined
                };
                if (this.iniProviderIds.length > 0) {
                    payload.provider_ids = this.iniProviderIds;
                }
                const res = await api.post('bl-cases/' + this.caseId + '/activate-providers', payload);

                // Optionally change case status
                if (this.iniChangeStatus && this.iniChangeStatus !== this.caseData?.status) {
                    try {
                        await api.post('bl-cases/' + this.caseId + '/change-status', {
                            new_status: this.iniChangeStatus,
                            note: 'Status changed during provider activation',
                            assign_to: parseInt(this.iniSelectedStaff)
                        });
                    } catch (e) {
                        showToast(e.data?.message || 'Providers activated but status change failed', 'error');
                    }
                }

                showToast(res.message || 'Providers activated');
                this.showIniStaffModal = false;
                this.iniProviderIds = [];
                await this.loadProviders();
                await this.loadAllCosts();
                await this.loadCase();
                window.dispatchEvent(new CustomEvent('providers-changed'));
            } catch (e) {
                showToast(e.data?.message || 'Failed to activate', 'error');
            } finally {
                this.iniActivating = false;
            }
        },

        // ---- Status Dropdown Methods ----

        getPipelineLabel() {
            if (!this.caseData) return 'Status';
            const map = {
                ini: '1. Treatment',
                rec: '2. Records',
                verification: '3. Verification',
                rfd: '4. Demand',
                neg: '5. Negotiate',
                lit: '6. Litigation',
                fbc: '7. Final Review',
                accounting: '8. Settlement',
                closed: 'Closed',
            };
            return map[this.caseData.status] || 'Status';
        },

        getCurrentPipelineStep() {
            if (!this.caseData) return null;
            const map = {
                ini: 'treatment',
                rec: 'collection',
                verification: 'verification',
                rfd: 'demand',
                neg: 'negotiate',
                lit: 'litigation',
                fbc: 'final_review',
                accounting: 'settlement',
                closed: 'settlement',
            };
            return map[this.caseData.status] || null;
        },

        selectPipelineStep(stepKey) {
            this.showStatusDropdown = false;

            const stepToStatus = {
                treatment: 'ini',
                collection: 'rec',
                verification: 'verification',
                demand: 'rfd',
                negotiate: 'neg',
                litigation: 'lit',
                final_review: 'fbc',
                settlement: 'accounting',
            };

            const targetStatus = stepToStatus[stepKey];
            if (!targetStatus) return;

            const statusOrder = {
                ini: 1, rec: 2, verification: 3,
                rfd: 4, neg: 5, lit: 6, fbc: 7, accounting: 8, closed: 9
            };

            const currentOrder = statusOrder[this.caseData.status] ?? 0;
            const targetOrder = statusOrder[targetStatus] ?? 0;

            let direction;
            if (targetOrder > currentOrder) direction = 'forward';
            else if (targetOrder < currentOrder) direction = 'backward';
            else direction = 'reassign';

            const step = this.workflowSteps.find(s => s.key === stepKey);
            const toLabel = (this.workflowSteps.indexOf(step) + 1) + '. ' + step.label;

            // Pre-select default owner for backward/reassign
            const defaultOwners = { ini:'2', rec:'1', verification:'4', rfd:'4', neg:'1', lit:'4', fbc:'4', accounting:'6' };
            const defaultAssign = direction === 'forward' ? '' : (defaultOwners[targetStatus] || '');

            this.statusChangeForm = {
                target_status: targetStatus,
                note: '',
                direction: direction,
                from_label: this.getPipelineLabel(),
                to_label: direction === 'reassign' ? this.getPipelineLabel() : toLabel,
                assign_to: defaultAssign
            };
            this.showStatusChangeModal = true;
        },

        async submitStatusChange() {
            if (this.statusChangeForm.note.trim().length < 5) {
                showToast('Please enter a note (min 5 characters)', 'error');
                return;
            }
            if (!this.statusChangeForm.assign_to) {
                showToast('Please select a staff member', 'error');
                return;
            }
            this.saving = true;
            try {
                const dir = this.statusChangeForm.direction;
                const endpoint = dir === 'forward'
                    ? 'bl-cases/' + this.caseId + '/change-status'
                    : dir === 'backward'
                        ? 'bl-cases/' + this.caseId + '/send-back'
                        : 'bl-cases/' + this.caseId + '/assign';

                const payload = {
                    new_status: this.statusChangeForm.target_status,
                    note: this.statusChangeForm.note.trim(),
                    assign_to: parseInt(this.statusChangeForm.assign_to)
                };
                if (dir === 'reassign') {
                    payload.assigned_to = payload.assign_to;
                }

                await api.post(endpoint, payload);
                showToast(dir === 'reassign' ? 'Case reassigned' : 'Status updated');
                this.showStatusChangeModal = false;
                await this.loadCase();
            } catch (e) {
                showToast(e.data?.message || 'Failed to update status', 'error');
            } finally {
                this.saving = false;
            }
        },

        async searchProviders() {
            if (this.providerSearch.length < 2) { this.providerResults = []; this.showProviderDropdown = false; return; }
            try {
                const res = await api.get('providers/search?q=' + encodeURIComponent(this.providerSearch));
                this.providerResults = res.data || [];
                this.showProviderDropdown = true;
            } catch (e) { this.showProviderDropdown = true; }
        },

        selectProvider(p) {
            this.selectedProvider = p;
            this.providerSearch = p.name;
            this.providerResults = [];
            this.showProviderDropdown = false;
        },

        getDefaultDeadline() {
            const date = new Date();
            date.setDate(date.getDate() + 14); // 2 weeks from today
            return date.toISOString().split('T')[0];
        },

        async addProvider() {
            if (!this.selectedProvider) return;
            this.saving = true;
            try {
                await api.post('case-providers', {
                    case_id: parseInt(this.caseId),
                    provider_id: this.selectedProvider.id,
                });
                showToast('Provider added');
                this.showAddProviderModal = false;
                this.selectedProvider = null;
                this.providerSearch = '';
                await this.loadProviders();
                await this.loadAllCosts();
                window.dispatchEvent(new CustomEvent('providers-changed'));
            } catch (e) {
                showToast(e.data?.message || 'Failed to add provider', 'error');
            }
            this.saving = false;
        },

        openRequestModal(p) {
            this.currentProvider = p;

            // Default next follow-up: +7 days
            const nextFollowup = new Date();
            nextFollowup.setDate(nextFollowup.getDate() + 7);
            const nextFollowupStr = nextFollowup.toISOString().split('T')[0];

            this.newRequest = {
                request_date: new Date().toISOString().split('T')[0],
                request_method: 'email',
                request_type: p.overall_status === 'not_started' ? 'initial' : 'follow_up',
                sent_to: '',
                department: '',
                contact_id: '',
                authorization_sent: true,
                notes: '',
                next_followup_date: nextFollowupStr,
                template_id: null,
                document_ids: []
            };

            // Auto-select primary contact if available
            if (p.contacts && p.contacts.length > 0) {
                const primary = p.contacts.find(c => c.is_primary == 1) || p.contacts[0];
                this.newRequest.contact_id = primary.id;
                this.selectContact(primary.id);
            } else {
                this.updateSentToByMethod();
            }
            this.showRequestModal = true;
        },

        selectContact(contactId) {
            const p = this.currentProvider;
            if (!contactId || !p || !p.contacts) {
                this.updateSentToByMethod();
                return;
            }
            const contact = p.contacts.find(c => c.id == contactId);
            if (contact) {
                this.newRequest.request_method = contact.contact_type;
                this.newRequest.sent_to = contact.contact_value;
                this.newRequest.department = contact.department || '';
            } else {
                this.newRequest.department = '';
                this.updateSentToByMethod();
            }
        },

        updateSentToByMethod() {
            const p = this.currentProvider;
            if (!p) return;
            const method = this.newRequest.request_method;
            if (method === 'email') this.newRequest.sent_to = p.provider_email || '';
            else if (method === 'fax') this.newRequest.sent_to = p.provider_fax || '';
            else if (method === 'phone') this.newRequest.sent_to = p.provider_phone || '';
            else this.newRequest.sent_to = '';
        },

        async submitRequest() {
            this.saving = true;
            try {
                const endpoint = this.newRequest.request_type === 'follow_up' ? 'requests/followup' : 'requests';
                const response = await api.post(endpoint, {
                    case_provider_id: this.currentProvider.id,
                    ...this.newRequest,
                    authorization_sent: this.newRequest.authorization_sent ? 1 : 0
                });

                const requestId = response.data.id;

                // Attach selected documents if any
                if (this.newRequest.document_ids && this.newRequest.document_ids.length > 0) {
                    for (const documentId of this.newRequest.document_ids) {
                        if (!documentId || documentId === 0 || documentId === '0') {
                            continue;
                        }
                        try {
                            await api.post(`requests/${requestId}/attach`, {
                                document_id: documentId
                            });
                        } catch (attachError) {
                            console.error('Failed to attach document:', attachError);
                        }
                    }
                }

                showToast('Request logged');
                this.showRequestModal = false;
                const cpId = this.currentProvider.id;
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Failed to log request', 'error');
            }
            this.saving = false;
        },

        openReceiptModal(p) {
            this.currentProvider = p;
            const alreadyComplete = p.overall_status === 'received_complete';
            this.newReceipt = { received_date: new Date().toISOString().split('T')[0], received_method: 'fax', has_medical_records: false, has_billing: false, has_chart: false, has_imaging: false, is_complete: alreadyComplete, incomplete_reason: '', file_location: '', no_records: false, no_records_reason: '', no_records_detail: '' };
            this.showReceiptModal = true;
        },

        async submitReceipt() {
            this.saving = true;
            try {
                if (this.newReceipt.no_records) {
                    // No records — update status directly instead of creating a receipt
                    await api.put('case-providers/' + this.currentProvider.id + '/status', {
                        overall_status: 'no_records',
                        no_records_reason: this.newReceipt.no_records_reason || 'other',
                        no_records_detail: this.newReceipt.no_records_detail || '',
                    });
                    showToast('Marked as No Records');
                } else {
                    await api.post('receipts', {
                        case_provider_id: this.currentProvider.id,
                        ...this.newReceipt,
                        has_medical_records: this.newReceipt.has_medical_records ? 1 : 0,
                        has_billing: this.newReceipt.has_billing ? 1 : 0,
                        has_chart: this.newReceipt.has_chart ? 1 : 0,
                        has_imaging: this.newReceipt.has_imaging ? 1 : 0,
                        is_complete: this.newReceipt.is_complete ? 1 : 0,
                    });
                    showToast('Receipt logged');
                }
                this.showReceiptModal = false;
                await this.loadProviders();
                await this.loadCase();
            } catch (e) {
                showToast(e.data?.message || 'Failed to log receipt', 'error');
            }
            this.saving = false;
        },

        async setProviderOnHold() {
            if (!this.currentProvider) return;
            this.saving = true;
            try {
                await api.put('case-providers/' + this.currentProvider.id + '/status', { overall_status: 'on_hold' });
                showToast('Provider set to On Hold');
                this.showReceiptModal = false;
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Failed to update status', 'error');
            }
            this.saving = false;
        },

        async deleteProvider(id) {
            if (!await confirmAction('Remove this provider from the case?')) return;
            try {
                await api.delete('case-providers/' + id);
                showToast('Provider removed');
                await this.loadProviders();
                await this.loadAllCosts();
                window.dispatchEvent(new CustomEvent('providers-changed'));
            } catch (e) {
                showToast('Failed to remove provider', 'error');
            }
        },

        async updateProviderStatus(cp, newStatus) {
            try {
                await api.put('case-providers/' + cp.id + '/status', { overall_status: newStatus });
                showToast('Status updated', 'success');
                await this.loadProviders();
                await this.loadCase();
            } catch (e) {
                showToast('Failed to update status', 'error');
            }
        },

        async markComplete(cp) {
            if (!confirm('Mark this provider as records received complete?')) return;
            try {
                await api.put('case-providers/' + cp.id + '/status', { overall_status: 'received_complete' });
                showToast('Provider marked as complete', 'success');
                await this.loadProviders();
            } catch (e) {
                showToast('Failed to mark complete', 'error');
            }
        },

        async addNote() {
            if (!this.newNote.content.trim()) return;
            try {
                const payload = {
                    case_id: parseInt(this.caseId),
                    note_type: this.newNote.note_type,
                    content: this.newNote.content
                };
                if (this.newNote.case_provider_id) payload.case_provider_id = parseInt(this.newNote.case_provider_id);
                if (this.newNote.contact_method) payload.contact_method = this.newNote.contact_method;
                if (this.newNote.contact_date) payload.contact_date = this.newNote.contact_date;
                await api.post('notes', payload);
                this.newNote = { note_type: 'general', content: '', case_provider_id: '', contact_method: '', contact_date: '' };
                await this.loadNotes();
            } catch (e) {
                showToast('Failed to add note', 'error');
            }
        },

        async deleteNote(noteId) {
            if (!await confirmAction('Delete this note?')) return;
            try {
                await api.delete('notes/' + noteId);
                await this.loadNotes();
                showToast('Note deleted', 'success');
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete note', 'error');
            }
        },

        openDeadlineModal(p) {
            this.deadlineProvider = p;
            this.deadlineForm = { deadline: p.deadline || '', reason: '' };
            this.deadlineHistory = [];
            this.showDeadlineModal = true;
            this.loadDeadlineHistory(p.id);
        },

        async loadDeadlineHistory(cpId) {
            try {
                const res = await api.get('case-providers/' + cpId + '/deadline-history');
                this.deadlineHistory = res.data || [];
            } catch (e) { }
        },

        async submitDeadlineChange() {
            if (!this.deadlineForm.deadline || this.deadlineForm.reason.length < 5) return;
            this.saving = true;
            try {
                await api.put('case-providers/' + this.deadlineProvider.id + '/deadline', this.deadlineForm);
                showToast('Deadline updated');
                this.showDeadlineModal = false;
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Failed to update deadline', 'error');
            }
            this.saving = false;
        },

        async deleteRequest(req) {
            if (!confirm(`Delete this ${req.send_status} ${req.request_type} request (${req.request_date})?`)) {
                return;
            }

            try {
                await api.delete('requests/' + req.id);
                showToast('Request deleted successfully', 'success');
                await this.loadProviders();
                await this.loadCase();
            } catch (e) {
                showToast(e.response?.data?.error || 'Failed to delete request', 'error');
            }
        },

        async openPreviewModal(req) {
            try {
                const res = await api.get('requests/' + req.id + '/preview');
                this.previewData = res.data;
                this.isEditingLetter = false;
                this.originalLetterHtml = '';
                this.originalSubject = '';
                this.showPreviewModal = true;
            } catch (e) {
                showToast(e.data?.message || 'Failed to load preview', 'error');
            }
        },

        toggleLetterEdit() {
            const iframe = this.$refs.letterIframe;
            if (!iframe) return;

            if (this.isEditingLetter) {
                // Switching OFF edit mode - save edits from iframe
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    this.previewData.letter_html = '<!DOCTYPE html>' + iframeDoc.documentElement.outerHTML;
                    iframeDoc.designMode = 'off';
                } catch (e) { /* ignore */ }
                this.isEditingLetter = false;
            } else {
                // Switching ON edit mode - store originals on first edit
                if (!this.originalLetterHtml) {
                    this.originalLetterHtml = this.previewData.letter_html;
                    this.originalSubject = this.previewData.subject;
                }
                this.isEditingLetter = true;
                const enableEdit = () => {
                    try {
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        if (iframeDoc && iframeDoc.body) {
                            iframeDoc.designMode = 'on';
                        } else {
                            setTimeout(enableEdit, 50);
                        }
                    } catch (e) {
                        setTimeout(enableEdit, 50);
                    }
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
                            if (iframeDoc && iframeDoc.body && iframeDoc.readyState === 'complete') {
                                iframeDoc.designMode = 'on';
                            } else {
                                setTimeout(enableEdit, 50);
                            }
                        } catch (e) {
                            setTimeout(enableEdit, 50);
                        }
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
                } catch (e) { /* ignore */ }
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
            } catch (e) {
                return this.previewData.letter_html;
            }
        },

        async confirmAndSend() {
            if (!this.previewData.recipient) {
                showToast('Please enter a recipient', 'error');
                return;
            }
            if (!await confirmAction(
                'Send this request via ' + (this.previewData.method === 'email' ? 'email' : 'fax') + ' to ' + this.previewData.recipient + '?'
            )) return;

            this.sending = true;
            try {
                const payload = {
                    recipient: this.previewData.recipient
                };

                if (this.originalLetterHtml) {
                    const currentHtml = this.isEditingLetter ? this.getEditedLetterHtml() : this.previewData.letter_html;
                    if (currentHtml !== this.originalLetterHtml) {
                        payload.letter_html = currentHtml;
                    }
                    if (this.previewData.subject !== this.originalSubject) {
                        payload.subject = this.previewData.subject;
                    }
                }

                const res = await api.post('requests/' + this.previewData.request_id + '/send', payload);
                showToast(res.message || 'Sent successfully!');
                this.closePreviewModal();
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Send failed', 'error');
            }
            this.sending = false;
        },

        getSendStatusLabel(status) {
            const labels = { draft: 'Draft', sending: 'Sending...', sent: 'Sent', failed: 'Failed' };
            return labels[status] || status;
        },

        getRequestMethodLabel(method) {
            return REQUEST_METHODS[method] || method;
        },

        getRequestTypeLabel(type) {
            return REQUEST_TYPES[type] || type;
        },

        getContactMethodLabel(method) {
            const labels = { phone: 'Phone', fax: 'Fax', email: 'Email', portal: 'Portal', mail: 'Mail', in_person: 'In Person', other: 'Other' };
            return labels[method] || method;
        },

        formatDateTime(dateStr) {
            return window.formatDateTime(dateStr);
        },

        // ---- Panel scroll helper ----
        scrollToPanel(el) {
            this.$nextTick(() => {
                const panel = el.closest('[data-panel]') || el.parentElement;
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },

        // ---- Workflow Stepper ----

        getStepState(stepKey) {
            const statusOrder = ['ini', 'rec', 'verification', 'rfd', 'neg', 'lit', 'fbc', 'accounting', 'closed'];
            const status = this.caseData.status;
            const currentIdx = statusOrder.indexOf(status);
            const step = this.workflowSteps.find(s => s.key === stepKey);
            const stepStatuses = step.statuses.map(s => statusOrder.indexOf(s));
            const maxIdx = Math.max(...stepStatuses);
            const minIdx = Math.min(...stepStatuses);

            if (currentIdx > maxIdx) return 'completed';
            if (currentIdx >= minIdx && currentIdx <= maxIdx) return 'active';
            return 'pending';
        },

        getStepNumber(stepKey) {
            return this.workflowSteps.findIndex(s => s.key === stepKey) + 1;
        },

        // ---- Tracker Navigation from Pipeline ----

        getTrackerUrl(stepKey) {
            const caseNum = encodeURIComponent(this.caseData.case_number);
            const caseId = this.caseData.id;
            const trackerMap = {
                treatment:    '/blcm/frontend/pages/prelitigation/index.php',
                collection:   '/blcm/frontend/pages/billing/index.php?case_id=' + caseId,
                verification: '/blcm/frontend/pages/billing/index.php?case_id=' + caseId,
                demand:       '/blcm/frontend/pages/attorney/index.php?search=' + caseNum + '&from=case-detail&case_id=' + caseId,
                negotiate:    '/blcm/frontend/pages/attorney/index.php?search=' + caseNum + '&from=case-detail&case_id=' + caseId,
                litigation:   '/blcm/frontend/pages/attorney/index.php?search=' + caseNum + '&from=case-detail&case_id=' + caseId,
                settlement:   '/blcm/frontend/pages/accounting/index.php?search=' + caseNum + '&case_id=' + caseId,
                accounting:   '/blcm/frontend/pages/accounting/index.php?search=' + caseNum + '&case_id=' + caseId,
            };
            return trackerMap[stepKey] || '#';
        },

        getTrackerTooltip(stepKey) {
            const tooltipMap = {
                treatment:    'Go to Prelitigation Tracker',
                collection:   'Go to Billing Tracker',
                verification: 'Go to Billing Tracker',
                demand:       'Go to Attorney Cases',
                negotiate:    'Go to Attorney Cases',
                litigation:   'Go to Attorney Cases',
                settlement:   'Go to Accounting Tracker',
                accounting:   'Go to Accounting Tracker',
            };
            return tooltipMap[stepKey] || '';
        },

        goToTracker(stepKey) {
            const url = this.getTrackerUrl(stepKey);
            if (url !== '#') window.location.href = url;
        },

        // Dynamic button: go to the tracker that matches current status
        goToCurrentTracker() {
            const statusTrackerMap = {
                ini:                '/blcm/frontend/pages/prelitigation/index.php',
                rec:                '/blcm/frontend/pages/billing/index.php?case_id=' + this.caseData.id,
                verification:       '/blcm/frontend/pages/billing/index.php?case_id=' + this.caseData.id,
                rfd:                '/blcm/frontend/pages/attorney/index.php?search=' + encodeURIComponent(this.caseData.case_number) + '&from=case-detail&case_id=' + this.caseData.id,
                neg:                '/blcm/frontend/pages/attorney/index.php?search=' + encodeURIComponent(this.caseData.case_number) + '&from=case-detail&case_id=' + this.caseData.id,
                lit:                '/blcm/frontend/pages/attorney/index.php?search=' + encodeURIComponent(this.caseData.case_number) + '&from=case-detail&case_id=' + this.caseData.id,
                fbc: '/blcm/frontend/pages/accounting/index.php?search=' + encodeURIComponent(this.caseData.case_number) + '&case_id=' + this.caseData.id,
                accounting:         '/blcm/frontend/pages/accounting/index.php?search=' + encodeURIComponent(this.caseData.case_number) + '&case_id=' + this.caseData.id,
                closed:             '/blcm/frontend/pages/accounting/index.php?search=' + encodeURIComponent(this.caseData.case_number) + '&case_id=' + this.caseData.id,
            };
            const url = statusTrackerMap[this.caseData.status];
            if (url) window.location.href = url;
        },

        getCurrentTrackerLabel() {
            if (!this.caseData) return 'Tracker';
            const labelMap = {
                ini:                'Prelitigation',
                rec:                'Billing Tracker',
                verification:       'Billing Tracker',
                rfd:                'Attorney Cases',
                neg:                'Attorney Cases',
                lit:                'Attorney Cases',
                fbc: 'Accounting',
                accounting:         'Accounting',
                closed:             'Accounting',
            };
            return labelMap[this.caseData.status] || 'Tracker';
        },

        // ---- Cost Ledger Methods ----

        async loadAllCosts() {
            try {
                const res = await api.get('mr-fee-payments?case_id=' + this.caseId);
                const catOrder = { mr_cost: 0, litigation: 1, other: 2 };
                this.allCosts = (res.data.payments || []).sort((a, b) => {
                    const ca = catOrder[a.expense_category] ?? 9;
                    const cb = catOrder[b.expense_category] ?? 9;
                    if (ca !== cb) return ca - cb;
                    return (b.payment_date || '').localeCompare(a.payment_date || '');
                });
                this.allCostsTotal = {
                    billed: res.data.total_billed || 0,
                    paid: res.data.total_paid || 0
                };
            } catch (e) {
                this.allCosts = [];
                this.allCostsTotal = { billed: 0, paid: 0 };
            }
        },

        openCostModal() {
            this.currentProvider = null;
            this.paymentForm = {
                id: null,
                case_provider_id: null,
                provider_name: '',
                description: '',
                expense_category: 'other',
                billed_amount: 0,
                paid_amount: 0,
                payment_type: 'check',
                check_number: '',
                payment_date: new Date().toISOString().split('T')[0],
                paid_date: '',
                paid_by: '',
                receipt_document_id: null,
                receipt_file_name: '',
                notes: ''
            };
            this.paymentProviderSearch = '';
            this.paymentProviderResults = [];
            this.resetSplitState();
            this.showPaymentModal = true;
        },

        editCostEntry(pmt) {
            this.currentProvider = pmt.case_provider_id ? { id: pmt.case_provider_id } : null;
            this.resetSplitState();
            this.editPayment(pmt);
        },

        async deleteCostEntry(pmt) {
            if (!await confirmAction('Delete this cost entry of $' + parseFloat(pmt.paid_amount).toFixed(2) + '?')) return;
            try {
                await api.delete('mr-fee-payments/' + pmt.id);
                showToast('Cost entry deleted');
                await this.loadAllCosts();
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete cost entry', 'error');
            }
        },

        getCategoryLabel(cat) {
            const labels = { mr_cost: 'Records Fee', litigation: 'Litigation', other: 'Other' };
            return labels[cat] || cat;
        },

        getCostGroups() {
            const order = ['mr_cost', 'litigation', 'other'];
            const labels = { mr_cost: 'Costs', litigation: 'Litigation', other: 'Other Expenses' };
            return order.map(cat => {
                const costs = this.allCosts.filter(c => c.expense_category === cat).sort((a, b) => {
                    const aB = (a.provider_name || a.linked_provider_name || '').toUpperCase().includes('BRIDGE LAW') ? 0 : 1;
                    const bB = (b.provider_name || b.linked_provider_name || '').toUpperCase().includes('BRIDGE LAW') ? 0 : 1;
                    if (aB !== bB) return aB - bB;
                    return (b.payment_date || '').localeCompare(a.payment_date || '');
                });
                return {
                    category: cat,
                    label: labels[cat],
                    costs: costs,
                    totalBilled: costs.reduce((s, c) => s + parseFloat(c.billed_amount || 0), 0),
                    totalPaid: costs.reduce((s, c) => s + parseFloat(c.paid_amount || 0), 0),
                };
            }).filter(g => g.costs.length > 0);
        },

        printCostLedger() {
            const caseName = this.caseData?.client_name || '';
            const caseNum = this.caseData?.case_number || '';
            let rows = this.allCosts.map(c =>
                `<tr>
                    <td>${formatDate(c.payment_date)}</td>
                    <td>${c.description || '-'}</td>
                    <td>${c.linked_provider_name || '-'}</td>
                    <td>${this.getCategoryLabel(c.expense_category)}</td>
                    <td>$${parseFloat(c.billed_amount || 0).toFixed(2)}</td>
                    <td>$${parseFloat(c.paid_amount || 0).toFixed(2)}</td>
                </tr>`
            ).join('');
            rows += `<tr style="border-top:2px solid #333;font-weight:bold;">
                <td colspan="4" style="text-align:right;">TOTAL</td>
                <td>$${this.allCostsTotal.billed.toFixed(2)}</td>
                <td>$${this.allCostsTotal.paid.toFixed(2)}</td>
            </tr>`;
            const html = `<!DOCTYPE html><html><head><title>Cost Ledger - ${caseNum}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 30px; }
                    h2 { margin-bottom: 4px; }
                    .sub { color: #666; margin-bottom: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 13px; }
                    th { background: #f5f5f5; font-weight: 600; text-transform: uppercase; font-size: 11px; }
                </style>
            </head><body>
                <h2>Cost Ledger</h2>
                <div class="sub">${caseName} &mdash; ${caseNum}</div>
                <table>
                    <thead><tr><th>Date</th><th>Description</th><th>Provider</th><th>Category</th><th>Billed</th><th>Paid</th></tr></thead>
                    <tbody>${rows}</tbody>
                </table>
            </body></html>`;
            const w = window.open('', '_blank');
            w.document.write(html);
            w.document.close();
            w.onload = () => { w.print(); };
        },

        async previewCostImport(fileInput) {
            const file = fileInput.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);
            formData.append('case_id', this.caseId);
            formData.append('preview', '1');

            try {
                const res = await fetch('/blcm/backend/api/mr-fee-payments/import', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + (localStorage.getItem('auth_token') || '') },
                    body: formData
                });
                const data = await res.json();
                if (!data.success) {
                    showToast(data.error || 'Failed to parse CSV', 'error');
                    fileInput.value = '';
                    return;
                }
                this.costImportPreview = data.preview || [];
                this.costImportSummary = { count: data.count, total_billed: data.total_billed, total_paid: data.total_paid };
                this.showCostImportModal = true;
            } catch (e) {
                showToast('Failed to parse CSV file', 'error');
            }
            fileInput.value = '';
        },

        async confirmCostImport(fileInput) {
            // Re-upload without preview flag to actually import
            const file = this._costImportFile;
            if (!file) {
                showToast('No file selected', 'error');
                return;
            }

            this.costImporting = true;
            const formData = new FormData();
            formData.append('file', file);
            formData.append('case_id', this.caseId);

            try {
                const res = await fetch('/blcm/backend/api/mr-fee-payments/import', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + (localStorage.getItem('auth_token') || '') },
                    body: formData
                });
                const data = await res.json();
                if (!data.success) {
                    showToast(data.error || 'Import failed', 'error');
                    this.costImporting = false;
                    return;
                }
                showToast(`Imported ${data.imported} cost entries`, 'success');
                this.showCostImportModal = false;
                this.costImportPreview = [];
                this._costImportFile = null;
                await this.loadAllCosts();
            } catch (e) {
                showToast('Import failed', 'error');
            }
            this.costImporting = false;
        },

        handleCostImportFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            this._costImportFile = file;
            this.previewCostImport(event.target);
        },

        getCategoryClass(cat) {
            const classes = {
                mr_cost: 'bg-blue-100 text-blue-700',
                litigation: 'bg-purple-100 text-purple-700',
                other: 'bg-gray-100 text-gray-700'
            };
            return classes[cat] || 'bg-gray-100 text-gray-700';
        },

        // ---- Payment Methods ----

        async loadStaffList() {
            try {
                this.staffList = await Alpine.store('staff').getList();
            } catch (e) {
                this.staffList = [];
            }
        },

        async loadPayments(cpId) {
            try {
                const res = await api.get('mr-fee-payments?case_id=' + this.caseId + '&case_provider_id=' + cpId);
                this.payments = res.data.payments || [];
                this.paymentTotal = res.data.total_paid || 0;
            } catch (e) {
                this.payments = [];
                this.paymentTotal = 0;
            }
        },

        openPaymentModal(provider) {
            this.currentProvider = provider;
            this.paymentForm = {
                id: null,
                case_provider_id: provider?.id || null,
                provider_name: provider?.provider_name || '',
                description: 'Record Fee',
                expense_category: 'mr_cost',
                billed_amount: 0,
                paid_amount: 0,
                payment_type: 'check',
                check_number: '',
                payment_date: new Date().toISOString().split('T')[0],
                paid_date: '',
                paid_by: '',
                receipt_document_id: null,
                receipt_file_name: '',
                notes: '',
                _noRecordFee: provider?.charges_record_fee == 0
            };
            this.paymentProviderSearch = provider?.provider_name || '';
            this.paymentProviderResults = [];
            this.showPaymentModal = true;
        },

        editPayment(pmt) {
            const linkedProvider = pmt.case_provider_id
                ? this.providers.find(p => p.id == pmt.case_provider_id)
                : null;
            this.paymentForm = {
                id: pmt.id,
                case_provider_id: pmt.case_provider_id || null,
                provider_name: pmt.provider_name || pmt.linked_provider_name || '',
                description: pmt.description || '',
                expense_category: pmt.expense_category || 'mr_cost',
                billed_amount: parseFloat(pmt.billed_amount) || 0,
                paid_amount: parseFloat(pmt.paid_amount) || 0,
                payment_type: pmt.payment_type || '',
                check_number: pmt.check_number || '',
                payment_date: pmt.payment_date || '',
                paid_date: pmt.paid_date || '',
                paid_by: pmt.paid_by || '',
                receipt_document_id: pmt.receipt_document_id || null,
                receipt_file_name: pmt.receipt_file_name || '',
                notes: pmt.notes || '',
                _noRecordFee: linkedProvider ? linkedProvider.charges_record_fee == 0 : false
            };
            this.paymentProviderSearch = pmt.provider_name || pmt.linked_provider_name || '';
            this.paymentProviderResults = [];
            this.showPaymentModal = true;
        },

        async submitPayment() {
            this.saving = true;
            try {
                const payload = {
                    case_id: parseInt(this.caseId),
                    case_provider_id: this.paymentForm.case_provider_id || null,
                    provider_name: this.paymentForm.provider_name || null,
                    description: this.paymentForm.description,
                    expense_category: this.paymentForm.expense_category,
                    billed_amount: this.paymentForm.billed_amount || 0,
                    paid_amount: this.paymentForm.paid_amount || 0,
                    payment_type: this.paymentForm.payment_type || null,
                    check_number: this.paymentForm.check_number || null,
                    payment_date: this.paymentForm.payment_date || null,
                    paid_date: this.paymentForm.paid_date || null,
                    paid_by: this.paymentForm.paid_by || null,
                    receipt_document_id: this.paymentForm.receipt_document_id || null,
                    notes: this.paymentForm.notes || null
                };

                // Add split data if enabled
                if (this.splitEnabled && this.splitSelectedCaseIds.length > 1
                    && this.paymentForm.expense_category === 'litigation' && !this.paymentForm.id) {
                    payload.split_case_ids = this.splitSelectedCaseIds;
                }

                if (this.paymentForm.id) {
                    await api.put('mr-fee-payments/' + this.paymentForm.id, payload);
                    showToast('Payment updated');
                } else if (payload.split_case_ids) {
                    const res = await api.post('mr-fee-payments', payload);
                    showToast('Cost split across ' + payload.split_case_ids.length + ' cases ($' + this.splitPerPersonAmount().toFixed(2) + ' each)');
                } else {
                    await api.post('mr-fee-payments', payload);
                    showToast('Payment logged');
                }

                this.showPaymentModal = false;
                await this.loadAllCosts();
            } catch (e) {
                showToast(e.data?.message || 'Failed to save payment', 'error');
            }
            this.saving = false;
        },

        async searchPaymentProviders() {
            if (this.paymentProviderSearch.length < 2) {
                this.paymentProviderResults = [];
                this.showPaymentProviderDropdown = false;
                return;
            }
            try {
                const res = await api.get('providers/search?q=' + encodeURIComponent(this.paymentProviderSearch));
                this.paymentProviderResults = res.data || [];
                this.showPaymentProviderDropdown = true;
            } catch (e) {
                this.paymentProviderResults = [];
                this.showPaymentProviderDropdown = true;
            }
        },

        selectPaymentProvider(pr) {
            this.paymentProviderSearch = pr.name;
            this.paymentProviderResults = [];
            this.showPaymentProviderDropdown = false;
            this.paymentForm.provider_name = pr.name;
            this.paymentForm._noRecordFee = pr.charges_record_fee == 0;
            if (this.paymentForm._noRecordFee) {
                this.paymentForm.paid_amount = 0;
                this.paymentForm.payment_type = '';
                this.paymentForm.check_number = '';
                this.paymentForm.paid_by = '';
                this.paymentForm.paid_date = '';
            }
            // Provider-specific defaults (auto-fill but editable)
            const providerDefaults = {
                'Bridge Law & Associates, PLLC': { description: 'Cost', expense_category: 'other', billed_amount: 100, payment_type: 'other' },
                'WSP(Washington State Patrol)': { description: 'Police Report', expense_category: 'mr_cost', billed_amount: 10.50, payment_type: 'card' },
            };
            const defaults = providerDefaults[pr.name];
            if (defaults && !this.paymentForm.id) {
                Object.assign(this.paymentForm, defaults);
            }

            const matched = this.providers.find(p => p.provider_id == pr.id);
            if (matched) {
                this.paymentForm.case_provider_id = matched.id;
                this.currentProvider = matched;
            } else {
                this.paymentForm.case_provider_id = null;
                this.currentProvider = null;
            }
        },

        clearPaymentProvider() {
            this.paymentProviderSearch = '';
            this.paymentProviderResults = [];
            this.showPaymentProviderDropdown = false;
            this.paymentForm.provider_name = '';
            this.paymentForm.case_provider_id = null;
            this.paymentForm._noRecordFee = false;
            this.currentProvider = null;
        },

        autoFillCardNumber() {
            if (this.paymentForm.payment_type === 'card' && this.paymentForm.paid_by) {
                const staff = this.staffList.find(u => u.id == this.paymentForm.paid_by);
                if (staff && staff.card_last4) {
                    this.paymentForm.check_number = staff.card_last4;
                } else {
                    this.paymentForm.check_number = '';
                }
            } else if (this.paymentForm.payment_type !== 'check') {
                this.paymentForm.check_number = '';
            }
        },

        // ── Cost Split Functions ──
        async fetchRelatedCases() {
            this.loadingRelatedCases = true;
            try {
                const res = await api.get('bl-cases/related?case_id=' + this.caseId);
                this.relatedCases = res.data?.related_cases || [];
                if (this.relatedCases.length > 0) {
                    this.splitSelectedCaseIds = [
                        parseInt(this.caseId),
                        ...this.relatedCases.map(c => c.id)
                    ];
                    this.splitEnabled = true;
                    this.showSplitPanel = true;
                } else {
                    this.splitSelectedCaseIds = [];
                    this.splitEnabled = false;
                    this.showSplitPanel = false;
                }
            } catch (e) {
                this.relatedCases = [];
                this.splitSelectedCaseIds = [];
                this.showSplitPanel = false;
            }
            this.loadingRelatedCases = false;
        },

        toggleSplitCase(caseId) {
            if (caseId === parseInt(this.caseId)) return;
            const idx = this.splitSelectedCaseIds.indexOf(caseId);
            if (idx === -1) {
                this.splitSelectedCaseIds.push(caseId);
            } else {
                this.splitSelectedCaseIds.splice(idx, 1);
            }
        },

        splitPerPersonAmount() {
            const count = this.splitSelectedCaseIds.length;
            if (count <= 1) return this.paymentForm.billed_amount || 0;
            return Math.round(((this.paymentForm.billed_amount || 0) / count) * 100) / 100;
        },

        splitPerPersonPaid() {
            const count = this.splitSelectedCaseIds.length;
            if (count <= 1) return this.paymentForm.paid_amount || 0;
            return Math.round(((this.paymentForm.paid_amount || 0) / count) * 100) / 100;
        },

        resetSplitState() {
            this.relatedCases = [];
            this.splitEnabled = false;
            this.splitSelectedCaseIds = [];
            this.loadingRelatedCases = false;
            this.showSplitPanel = false;
        },

        async deletePayment(pmt) {
            if (!await confirmAction('Delete this payment of $' + parseFloat(pmt.paid_amount).toFixed(2) + '?')) return;
            try {
                await api.delete('mr-fee-payments/' + pmt.id);
                showToast('Payment deleted');
                await this.loadAllCosts();
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete payment', 'error');
            }
        },

        async uploadReceipt(event) {
            const file = event.target.files[0];
            if (!file) return;

            const uploading = true;
            try {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('case_id', this.caseId);
                formData.append('case_provider_id', this.currentProvider?.id || '');
                formData.append('document_type', 'other');
                formData.append('notes', 'MR Fee Receipt');

                const res = await api.upload('documents/upload', formData);
                this.paymentForm.receipt_document_id = res.data.id;
                this.paymentForm.receipt_file_name = res.data.original_file_name;
                showToast('Receipt uploaded');
            } catch (e) {
                showToast(e.data?.message || 'Upload failed', 'error');
            }
            event.target.value = '';
        },

        getPaymentTypeLabel(type) {
            const labels = { check: 'Check', card: 'Card', cash: 'Cash', wire: 'Wire', other: 'Other' };
            return labels[type] || type;
        },

        // ── Quick-Add Provider ──
        openQuickAddProvider(context) {
            this.quickAddFor = context; // 'provider' or 'payment'
            const prefill = context === 'payment' ? this.paymentProviderSearch : this.providerSearch;
            this.quickAddForm = { name: prefill || '', type: 'hospital', phone: '', fax: '', email: '', address: '', city: '', state: '', zip: '' };
            this.showQuickAddProvider = true;
            // Close autocomplete dropdowns
            this.providerResults = [];
            this.paymentProviderResults = [];
            this.showProviderDropdown = false;
            this.showPaymentProviderDropdown = false;
        },

        closeQuickAddProvider() {
            this.showQuickAddProvider = false;
            this.quickAddForm = { name: '', type: 'hospital', phone: '', fax: '', email: '', address: '', city: '', state: '', zip: '' };
        },

        async submitQuickAddProvider() {
            if (!this.quickAddForm.name.trim()) {
                showToast('Provider name is required', 'error');
                return;
            }
            this.quickAddSaving = true;
            try {
                const data = { ...this.quickAddForm };
                data.name = data.name.trim();
                const res = await api.post('providers', data);
                const newProvider = res.data;
                showToast('Provider "' + newProvider.name + '" created');
                this.closeQuickAddProvider();

                // Auto-select in the appropriate context
                if (this.quickAddFor === 'provider') {
                    this.selectProvider({ id: newProvider.id, name: newProvider.name, type: newProvider.type });
                } else if (this.quickAddFor === 'payment') {
                    this.selectPaymentProvider({
                        id: newProvider.id,
                        name: newProvider.name,
                        type: newProvider.type,
                        charges_record_fee: newProvider.charges_record_fee ?? 1
                    });
                }
            } catch (e) {
                showToast(e.data?.message || 'Failed to create provider', 'error');
            }
            this.quickAddSaving = false;
        }
    };
}
