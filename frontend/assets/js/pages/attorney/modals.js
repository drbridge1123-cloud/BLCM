/**
 * Attorney Cases — Modal Handlers Mixin
 * CRUD operations, settlement workflows, transfer & accounting.
 * Merged into the main controller via spread: ...attorneyModalsMixin()
 */
function attorneyModalsMixin() {
    return {
        // -------------------------------------------------------
        //  Commission / fee helpers that use `this`
        // -------------------------------------------------------

        /** Get the commission rate for the current litigation settlement. */
        getLitCommRate() {
            const resType = this.settleLitForm.resolution_type;
            if (isVariableType(resType)) {
                return parseFloat(this.settleLitForm.manual_commission_rate) || 0;
            }
            const feeRate = getLitFeeRate(resType);
            if (feeRate === 40) return 10;
            return 5;
        },

        /**
         * Compute the "difference" used for litigation settlement fee calculation.
         * For 40% types the full settled amount is used; otherwise settled - presuit offer.
         */
        getLitDifference() {
            const settled  = parseFloat(this.settleLitForm.settled) || 0;
            const presuit  = parseFloat(this.settleLitForm.presuit_offer) || 0;
            const feeRate  = getLitFeeRate(this.settleLitForm.resolution_type);
            if (feeRate === 40) return settled; // no deduction for 40% types
            return Math.max(0, settled - presuit);
        },

        // -------------------------------------------------------
        //  CRUD -- Create
        // -------------------------------------------------------

        openCreateModal() {
            this.resetCreateForm();
            this.showCreateModal = true;
        },

        async createCase() {
            if (!this.createForm.case_number || !this.createForm.client_name) {
                showToast('Case number and client name are required', 'error');
                return;
            }
            this.saving = true;
            try {
                await api.post('attorney', this.createForm);
                showToast('Case created', 'success');
                this.showCreateModal = false;
                this.resetCreateForm();
                await this.loadStats();
                await this.loadTab(this.activeTab);
            } catch (e) {
                showToast(e.data?.message || e.message || 'An error occurred', 'error');
            }
            this.saving = false;
        },

        resetCreateForm() {
            this.createForm = {
                case_number: '',
                client_name: '',
                case_type: 'Auto',
                assigned_date: new Date().toISOString().split('T')[0],
                phase: 'demand',
                attorney_user_id: '',
                month: '',
                note: ''
            };
        },

        // -------------------------------------------------------
        //  CRUD -- Edit
        // -------------------------------------------------------

        openEdit(c) {
            this.editingCase = c;
            this.editForm = {
                id:             c.id,
                case_number:    c.case_number,
                client_name:    c.client_name,
                case_type:      c.case_type || 'Auto',
                assigned_date:  c.assigned_date || '',
                month:          c.month || '',
                note:           c.note || '',
                stage:          c.stage || '',
                check_received: !!c.check_received,
                is_marketing:   !!c.is_marketing,
                demand_out_date:  c.demand_out_date || '',
                negotiate_date:   c.negotiate_date || '',
                demand_deadline:  c.demand_deadline || '',
                top_offer_date:   c.top_offer_date || '',
                _attorney_name: c.attorney_name || '—'
            };
            this.showEditModal = true;
            this.loadTransferHistory(c.id);
        },

        async updateCase() {
            this.saving = true;
            try {
                await api.put('attorney/' + this.editForm.id, this.editForm);
                showToast('Case updated', 'success');
                this.showEditModal = false;
                await this.loadTab(this.activeTab);
            } catch (e) {
                showToast(e.data?.message || e.message || 'An error occurred', 'error');
            }
            this.saving = false;
        },

        // -------------------------------------------------------
        //  CRUD -- Delete
        // -------------------------------------------------------

        async deleteCase(id) {
            if (!confirm('Delete this case?')) return;
            try {
                await api.delete('attorney/' + id);
                showToast('Case deleted', 'success');
                await this.loadStats();
                await this.loadTab(this.activeTab);
            } catch (e) {
                showToast(e.data?.message || e.message || 'An error occurred', 'error');
            }
        },

        // -------------------------------------------------------
        //  Toggle Date (demand_out_date, negotiate_date, etc.)
        // -------------------------------------------------------

        formatDateShort(dateStr) {
            if (!dateStr) return '';
            return formatDate(dateStr);
        },

        async toggleDate(caseId, field, currentValue) {
            const label = field.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
            if (currentValue) {
                if (!confirm(`Clear "${label}" date (${this.formatDateShort(currentValue)})?`)) return;
            } else {
                const today = formatDate(new Date().toISOString().split('T')[0]);
                if (!confirm(`Set "${label}" to today (${today})?`)) return;
            }
            const date = currentValue ? null : new Date().toISOString().split('T')[0];
            try {
                await api.post('attorney/toggle-date', { case_id: caseId, field: field, date: date });
                await this.loadTab(this.activeTab);
            } catch (e) {
                showToast(e.data?.message || e.message || 'An error occurred', 'error');
            }
        },

        // -------------------------------------------------------
        //  Top Offer
        // -------------------------------------------------------

        openTopOffer(c) {
            this.settlingCase = c;
            this.topOfferForm = {
                case_id:          c.id,
                top_offer_amount: '',
                assignee_id:      '',
                note:             ''
            };
            this.showTopOfferModal = true;
        },

        async submitTopOffer() {
            if (!this.topOfferForm.top_offer_amount || !this.topOfferForm.assignee_id) {
                showToast('Amount and assignee are required', 'error');
                return;
            }
            this.saving = true;
            try {
                await api.post('attorney/top-offer', this.topOfferForm);
                showToast('Top offer submitted', 'success');
                this.showTopOfferModal = false;
                await this.loadTab('demand');
            } catch (e) {
                showToast(e.data?.message || e.message || 'An error occurred', 'error');
            }
            this.saving = false;
        },

        // -------------------------------------------------------
        //  Settle Demand
        // -------------------------------------------------------

        openSettleDemand(c) {
            this.settlingCase = c;
            const amt = c.top_offer_amount ? parseFloat(c.top_offer_amount) : '';
            this.settleForm = {
                case_id:              c.id,
                settled:              amt,
                discounted_legal_fee: '',
                month:                '',
                check_received:       false,
                is_policy_limit:      false
            };
            this.showSettleDemandModal = true;
        },

        async settleDemand() {
            if (!this.settleForm.settled) {
                showToast('Settled amount is required', 'error');
                return;
            }
            if (this.hasCommission && !this.settleForm.discounted_legal_fee) {
                showToast('Discounted legal fee is required', 'error');
                return;
            }
            this.saving = true;
            try {
                const res = await api.post('attorney/settle-demand', this.settleForm);
                const msg = this.settleForm.is_policy_limit
                    ? 'Settled \u2192 moved to UIM'
                    : 'Demand settled';
                const commMsg = res.data?.commission ? ' (Commission: $' + fmt(res.data.commission) + ')' : '';
                showToast(msg + commMsg, 'success');
                this.showSettleDemandModal = false;
                await this.loadStats();
                await this.loadTab(this.activeTab);
            } catch (e) {
                showToast(e.data?.message || e.message || 'An error occurred', 'error');
            }
            this.saving = false;
        },

        // -------------------------------------------------------
        //  To Litigation
        // -------------------------------------------------------

        openToLit(c) {
            this.settlingCase = c;
            this.toLitForm = {
                case_id:               c.id,
                litigation_start_date: new Date().toISOString().split('T')[0],
                presuit_offer:         0,
                note:                  ''
            };
            this.showToLitModal = true;
        },

        async toLitigation() {
            this.saving = true;
            try {
                await api.post('attorney/to-litigation', this.toLitForm);
                showToast('Case moved to Litigation', 'success');
                this.showToLitModal = false;
                await this.loadStats();
                await this.loadTab(this.activeTab);
            } catch (e) {
                showToast(e.data?.message || e.message || 'An error occurred', 'error');
            }
            this.saving = false;
        },

        // -------------------------------------------------------
        //  To UIM
        // -------------------------------------------------------

        openToUim(c) {
            this.settlingCase = c;
            this.toUimForm = {
                case_id: c.id,
                note:    ''
            };
            this.showToUimModal = true;
        },

        async toUim() {
            this.saving = true;
            try {
                await api.post('attorney/to-uim', this.toUimForm);
                showToast('Case moved to UIM', 'success');
                this.showToUimModal = false;
                await this.loadStats();
                await this.loadTab(this.activeTab);
            } catch (e) {
                showToast(e.data?.message || e.message || 'An error occurred', 'error');
            }
            this.saving = false;
        },

        // -------------------------------------------------------
        //  Settle Litigation
        // -------------------------------------------------------

        openSettleLit(c) {
            this.settlingCase = c;
            this.settleLitForm = {
                case_id:                c.id,
                resolution_type:        '',
                settled:                '',
                discounted_legal_fee:   '',
                presuit_offer:          parseFloat(c.presuit_offer) || 0,
                month:                  '',
                check_received:         false,
                is_policy_limit:        false,
                manual_fee_rate:        '',
                manual_commission_rate: '',
                fee_rate_override:      false,
                note:                   ''
            };
            this.showSettleLitModal = true;
        },

        async settleLitigation() {
            if (!this.settleLitForm.settled) {
                showToast('Settled amount is required', 'error');
                return;
            }
            if (this.hasCommission && !this.settleLitForm.resolution_type) {
                showToast('Resolution type is required', 'error');
                return;
            }
            if (this.hasCommission && !this.settleLitForm.discounted_legal_fee) {
                showToast('Discounted legal fee is required', 'error');
                return;
            }
            this.saving = true;
            try {
                const res = await api.post('attorney/settle-litigation', this.settleLitForm);
                const msg = this.settleLitForm.is_policy_limit
                    ? 'Settled \u2192 moved to UIM'
                    : 'Litigation settled';
                const commMsg = res.data?.commission ? ' (Commission: $' + fmt(res.data.commission) + ')' : '';
                showToast(msg + commMsg, 'success');
                this.showSettleLitModal = false;
                await this.loadStats();
                await this.loadTab(this.activeTab);
            } catch (e) {
                showToast(e.data?.message || e.message || 'An error occurred', 'error');
            }
            this.saving = false;
        },

        // -------------------------------------------------------
        //  Settle UIM
        // -------------------------------------------------------

        openSettleUim(c) {
            this.settlingCase = c;
            this.settleUimForm = {
                case_id:              c.id,
                settled:              '',
                discounted_legal_fee: '',
                month:                '',
                check_received:       false
            };
            this.showSettleUimModal = true;
        },

        async settleUim() {
            if (!this.settleUimForm.settled) {
                showToast('Settled amount is required', 'error');
                return;
            }
            if (this.hasCommission && !this.settleUimForm.discounted_legal_fee) {
                showToast('Discounted legal fee is required', 'error');
                return;
            }
            this.saving = true;
            try {
                const res = await api.post('attorney/settle-uim', this.settleUimForm);
                const commMsg = res.data?.total_commission ? ' (Total Commission: $' + fmt(res.data.total_commission) + ')' : '';
                showToast('UIM settled' + commMsg, 'success');
                this.showSettleUimModal = false;
                await this.loadStats();
                await this.loadTab(this.activeTab);
            } catch (e) {
                showToast(e.data?.message || e.message || 'An error occurred', 'error');
            }
            this.saving = false;
        },

        // -------------------------------------------------------
        //  Check Received toggle (settled tab)
        // -------------------------------------------------------

        async toggleCheck(c) {
            const action = c.check_received ? 'uncheck' : 'check';
            if (!confirm(`Mark "${c.case_number} - ${c.client_name}" as ${action === 'check' ? 'Check Received' : 'Check Not Received'}?`)) return;
            try {
                await api.put('attorney/' + c.id, { check_received: c.check_received ? 0 : 1 });
                await this.loadTab('settled');
            } catch (e) {
                showToast(e.data?.message || e.message || 'An error occurred', 'error');
            }
        },

        // -------------------------------------------------------
        //  Navigate to Case Tracker
        // -------------------------------------------------------

        async goToCaseTracker(caseNumber) {
            // Try to find the main case by case number and go directly to case detail
            try {
                const res = await api.get('bl-cases?search=' + encodeURIComponent(caseNumber) + '&per_page=1');
                if (res.data && res.data.length > 0) {
                    window.location.href = '/blcm/frontend/pages/bl-cases/detail.php?id=' + res.data[0].id;
                    return;
                }
            } catch (e) {}
            // Fallback: go to cases list with search
            window.location.href = '/blcm/frontend/pages/bl-cases/index.php?search=' + encodeURIComponent(caseNumber) + '&from=attorney';
        },

        // -------------------------------------------------------
        //  Transfer Case
        // -------------------------------------------------------

        async loadTransferHistory(caseId) {
            try {
                const res = await api.get(`attorney/transfer-history?case_id=${caseId}`);
                this.transferHistory = res.data || [];
            } catch (e) {
                this.transferHistory = [];
            }
        },

        openTransferFromEdit() {
            const c = this.editingCase;
            if (!c) return;
            this.showEditModal = false;
            this.openTransferModal(c);
        },

        async openTransferModal(c) {
            // Build attorney list excluding current attorney
            this.transferAttorneyList = this.staffList.filter(
                u => u.id.toString() !== c.attorney_user_id?.toString()
            );

            this.transferForm = {
                case_id: c.id,
                to_attorney_id: this.transferAttorneyList.length === 1
                    ? this.transferAttorneyList[0].id.toString() : '',
                note: '',
                _caseNumber: c.case_number,
                _clientName: c.client_name,
                _currentAttorney: c.attorney_name || '—',
                _currentAttorneyId: c.attorney_user_id
            };

            this.showTransferModal = true;
        },

        async submitTransfer() {
            if (!this.transferForm.to_attorney_id) {
                showToast('Select an attorney to transfer to', 'error');
                return;
            }
            if (!this.transferForm.note.trim()) {
                showToast('Transfer note is required', 'error');
                return;
            }
            this.saving = true;
            try {
                const res = await api.post('attorney/transfer', {
                    case_id: this.transferForm.case_id,
                    to_attorney_id: parseInt(this.transferForm.to_attorney_id),
                    note: this.transferForm.note.trim()
                });
                if (res.success) {
                    showToast(`Case transferred: ${res.data.from} → ${res.data.to}`, 'success');
                    this.showTransferModal = false;
                    this.loadData();
                } else {
                    showToast(res.message || 'Error', 'error');
                }
            } catch (e) {
                showToast('Failed to transfer case', 'error');
            } finally {
                this.saving = false;
            }
        },

        // -------------------------------------------------------
        //  Send to Billing Final
        // -------------------------------------------------------

        async openSendBillingModal(c) {
            this.sendBillingForm = {
                case_id: c.id,
                assigned_to: '',
                note: '',
                _caseNumber: c.case_number,
                _clientName: c.client_name
            };

            // Load billing staff if not loaded
            if (this.billingStaff.length === 0) {
                try {
                    const all = await Alpine.store('staff').getList();
                    const billingNames = ['ella', 'jimi'];
                    this.billingStaff = all.filter(u => {
                        const name = (u.display_name || u.full_name || '').toLowerCase();
                        return billingNames.some(n => name.includes(n));
                    });
                } catch (e) {
                    this.billingStaff = [];
                }
            }

            if (this.billingStaff.length > 0) {
                this.sendBillingForm.assigned_to = this.billingStaff[0].id.toString();
            }

            this.showSendBillingModal = true;
        },

        async submitSendToBilling() {
            this.saving = true;
            try {
                const payload = {
                    case_id: this.sendBillingForm.case_id,
                    assigned_to: parseInt(this.sendBillingForm.assigned_to),
                    note: this.sendBillingForm.note
                };
                const res = await api.post('attorney/send-to-billing-final', payload);
                if (res.success) {
                    showToast('Sent to billing for final balance checkup', 'success');
                    this.showSendBillingModal = false;
                    this.loadData();
                } else {
                    showToast(res.message || 'Error', 'error');
                }
            } catch (e) {
                showToast('Failed to send to billing', 'error');
            } finally {
                this.saving = false;
            }
        },

        // -------------------------------------------------------
        //  Send to Accounting
        // -------------------------------------------------------

        async openSendAcctModal(c) {
            this.sendAcctForm = {
                case_id: c.id,
                linked_case_id: null,
                assigned_to: '6',
                note: '',
                _caseNumber: c.case_number,
                _clientName: c.client_name,
                _matchedCase: null,
                _searching: false,
                _searchQuery: c.case_number,
                _searchResults: [],
                _noResults: false
            };

            // Load accounting staff if not loaded (same filter as accounting tracker)
            if (this.accountingStaff.length === 0) {
                try {
                    const all = await Alpine.store('staff').getList();
                    const acctNames = ['chloe', 'daniel'];
                    this.accountingStaff = all.filter(u => {
                        const name = (u.display_name || u.full_name || '').toLowerCase();
                        return acctNames.some(n => name.includes(n));
                    });
                    if (this.accountingStaff.length > 0 && !this.accountingStaff.find(u => u.id == this.sendAcctForm.assigned_to)) {
                        this.sendAcctForm.assigned_to = this.accountingStaff[0].id.toString();
                    }
                } catch (e) {
                    this.accountingStaff = [];
                }
            }

            this.showSendAcctModal = true;

            // Auto-search for matching main case by case_number
            this.searchLinkedCase();
        },

        async searchLinkedCase() {
            const query = this.sendAcctForm._searchQuery?.trim();
            if (!query || query.length < 2) {
                this.sendAcctForm._searchResults = [];
                this.sendAcctForm._noResults = false;
                return;
            }

            this.sendAcctForm._searching = true;
            this.sendAcctForm._noResults = false;

            try {
                const res = await api.get(`cases?search=${encodeURIComponent(query)}&per_page=5`);
                if (res.success) {
                    const results = res.data || [];
                    this.sendAcctForm._searchResults = results;
                    this.sendAcctForm._noResults = results.length === 0;

                    // Auto-select if exact case_number match
                    const exact = results.find(r => r.case_number === this.sendAcctForm._caseNumber);
                    if (exact) {
                        this.selectLinkedCase(exact);
                    }
                }
            } catch (e) {
                console.error('Error searching cases:', e);
            } finally {
                this.sendAcctForm._searching = false;
            }
        },

        selectLinkedCase(c) {
            this.sendAcctForm._matchedCase = {
                id: c.id,
                case_number: c.case_number,
                client_name: c.client_name,
                status: c.status
            };
            this.sendAcctForm.linked_case_id = c.id;
            this.sendAcctForm._searchResults = [];
            this.sendAcctForm._noResults = false;
        },

        async submitSendToAccounting() {
            this.saving = true;
            try {
                const payload = {
                    case_id: this.sendAcctForm.case_id,
                    assigned_to: parseInt(this.sendAcctForm.assigned_to),
                    note: this.sendAcctForm.note
                };
                if (this.sendAcctForm.linked_case_id) {
                    payload.linked_case_id = this.sendAcctForm.linked_case_id;
                }
                const res = await api.post('attorney/send-to-accounting', payload);
                if (res.success) {
                    const count = res.data?.disbursement_count || 0;
                    const msg = count > 0
                        ? `Sent to accounting (${count} disbursement items created)`
                        : 'Sent to accounting';
                    showToast(msg, 'success');
                    this.showSendAcctModal = false;
                    this.loadData();
                } else {
                    showToast(res.message || 'Error', 'error');
                }
            } catch (e) {
                showToast('Failed to send to accounting', 'error');
            } finally {
                this.saving = false;
            }
        }
    };
}
