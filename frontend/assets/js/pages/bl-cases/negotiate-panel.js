function negotiatePanel(caseId) {
    return {
        open: false,
        loading: true,
        tabs: [],
        activeTabKey: null,
        bestOffersByType: { '3rd_party': 0, um: 0, uim: 0, dv: 0 },
        providerNegotiations: [],

        roundForm: {
            demand_date: new Date().toISOString().split('T')[0],
            demand_amount: 0,
            offer_date: '',
            offer_amount: 0,
            notes: '',
            status: 'pending',
        },

        viewingNote: null,
        _saveTimer: null,
        _adjSaveTimer: null,

        getActiveTab() {
            return this.tabs.find(t => t.key === this.activeTabKey) || null;
        },

        formatPhone(field) {
            const tab = this.getActiveTab();
            if (!tab) return;
            let val = tab.adjuster_info[field] || '';
            let digits = val.replace(/\D/g, '');
            if (digits.length === 0) return;
            if (digits.length > 10 && digits[0] === '1') digits = digits.substring(1);
            if (digits.length === 10) {
                tab.adjuster_info[field] = '(' + digits.substring(0,3) + ') ' + digits.substring(3,6) + '-' + digits.substring(6);
            }
            this.saveAdjusterInfo();
        },

        formatEmail() {
            const tab = this.getActiveTab();
            if (!tab) return;
            let val = tab.adjuster_info.adjuster_email || '';
            tab.adjuster_info.adjuster_email = val.trim().toLowerCase();
            this.saveAdjusterInfo();
        },

        async init() {
            await Promise.all([this.loadNegotiations(), this.loadProviderNegotiations()]);
            this.loading = false;

            // Auto-fill adjuster fields when contacts modal saves
            document.addEventListener('adjuster-saved', (e) => {
                const d = e.detail;
                const matchKey = d.coverage_type + '_' + (d.coverage_index || 1);
                const found = this.tabs.find(t => t.key === matchKey);
                if (found) {
                    found.adjuster_info.insurance_company = d.insurance_company;
                    found.adjuster_info.party = d.party;
                    found.adjuster_info.adjuster_phone = d.adjuster_phone;
                    found.adjuster_info.adjuster_fax = d.adjuster_fax;
                    found.adjuster_info.adjuster_email = d.adjuster_email;
                    found.adjuster_info.claim_number = d.claim_number;
                    this.saveAdjusterInfo();
                }
            });

            // Contacts negotiate toggle ON → create local tab with adjuster info
            document.addEventListener('negotiate-add-tab', (e) => {
                const d = e.detail;
                const key = d.coverage_type + '_' + d.coverage_index;
                if (this.tabs.find(t => t.key === key)) return; // already exists
                const baseLabel = this.getCoverageLabel(d.coverage_type);
                const sameType = this.tabs.filter(t => t.coverage_type === d.coverage_type);
                const totalOfType = sameType.length + 1;
                const label = totalOfType > 1 ? `${baseLabel} (${d.coverage_index})` : baseLabel;
                if (totalOfType === 2 && sameType[0]) {
                    sameType[0].label = `${baseLabel} (${sameType[0].coverage_index})`;
                }
                this.tabs.push({
                    coverage_type: d.coverage_type,
                    coverage_index: d.coverage_index,
                    key,
                    label,
                    rounds: [],
                    best_offer: 0,
                    adjuster_info: {
                        insurance_company: d.insurance_company || '',
                        party: d.party || '',
                        adjuster_phone: d.adjuster_phone || '',
                        adjuster_fax: d.adjuster_fax || '',
                        adjuster_email: d.adjuster_email || '',
                        claim_number: d.claim_number || '',
                    },
                    _isLocal: true,
                });
                this.activeTabKey = key;
            });

            // Contacts negotiate toggle OFF → remove tab
            document.addEventListener('negotiate-remove-tab', (e) => {
                const key = e.detail.coverage_type + '_' + e.detail.coverage_index;
                this.tabs = this.tabs.filter(t => t.key !== key);
                // Fix labels after removal
                const type = e.detail.coverage_type;
                const remaining = this.tabs.filter(t => t.coverage_type === type);
                const baseLabel = this.getCoverageLabel(type);
                if (remaining.length === 1) {
                    remaining[0].label = baseLabel;
                }
                if (this.activeTabKey === key) {
                    this.activeTabKey = this.tabs.length > 0 ? this.tabs[0].key : null;
                }
            });

            // Clear adjuster info when contacts tab removed
            document.addEventListener('adjuster-removed', (e) => {
                const matchKey = e.detail.coverage_type + '_' + (e.detail.coverage_index || 1);
                for (const tab of this.tabs) {
                    if (tab.key === matchKey) {
                        tab.adjuster_info = { insurance_company: '', party: '', adjuster_phone: '', adjuster_fax: '', adjuster_email: '', claim_number: '' };
                    }
                }
            });
        },

        async loadNegotiations() {
            try {
                const res = await api.get(`negotiations/${caseId}`);
                if (res.success) {
                    this.tabs = res.tabs;
                    this.bestOffersByType = res.best_offers;
                    // Maintain active tab or select first
                    if (this.tabs.length > 0 && !this.tabs.find(t => t.key === this.activeTabKey)) {
                        this.activeTabKey = this.tabs[0].key;
                    }
                    if (this.tabs.length === 0) {
                        this.activeTabKey = null;
                    }
                    // Notify disbursement panel
                    window.dispatchEvent(new CustomEvent('negotiation-updated', {
                        detail: { bestOffers: res.best_offers, activeCoverages: res.active_coverages }
                    }));
                }
            } catch (e) {
                console.error('Failed to load negotiations:', e);
            }
        },

        async loadProviderNegotiations() {
            try {
                const res = await api.get(`provider-negotiations/${caseId}`);
                if (res.success) {
                    this.providerNegotiations = res.negotiations;
                }
            } catch (e) {
                console.error('Failed to load provider negotiations:', e);
            }
        },

        getCoverageLabel(type) {
            const labels = { '3rd_party': '3rd Party', 'um': 'UM', 'uim': 'UIM', 'pip': 'PIP', 'pd': 'PD', 'dv': 'DV', 'bi': 'BI' };
            return labels[type] || type;
        },

        getTotalBestOffer() {
            return Object.values(this.bestOffersByType).reduce((sum, v) => sum + (v || 0), 0);
        },

        addCoverage(type) {
            const existing = this.tabs.filter(t => t.coverage_type === type);
            const nextIndex = existing.length > 0 ? Math.max(...existing.map(t => t.coverage_index)) + 1 : 1;
            const baseLabel = this.getCoverageLabel(type);
            // Check total count of this type (existing + this new one)
            const totalOfType = existing.length + 1;
            const label = totalOfType > 1 ? `${baseLabel} (${nextIndex})` : baseLabel;
            // If adding a second of same type, relabel the first one
            if (totalOfType === 2) {
                const first = existing[0];
                if (first) first.label = `${baseLabel} (${first.coverage_index})`;
            }
            const key = `${type}_${nextIndex}`;
            this.tabs.push({
                coverage_type: type,
                coverage_index: nextIndex,
                key,
                label,
                rounds: [],
                best_offer: 0,
                adjuster_info: { insurance_company: '', party: '', adjuster_phone: '', adjuster_fax: '', adjuster_email: '', claim_number: '' },
                _isLocal: true,
            });
            this.activeTabKey = key;
        },

        async removeCoverage(tabKey) {
            if (!confirm('이 커버리지와 모든 라운드를 삭제하시겠습니까?')) return;
            const tab = this.tabs.find(t => t.key === tabKey);
            if (!tab) return;
            if (tab._isLocal) {
                // Local tab (not yet saved) — just remove from array
                this.tabs = this.tabs.filter(t => t.key !== tabKey);
                if (this.activeTabKey === tabKey) {
                    this.activeTabKey = this.tabs.length > 0 ? this.tabs[0].key : null;
                }
                return;
            }
            try {
                await api.delete(`negotiations/group/${caseId}/${tab.coverage_type}/${tab.coverage_index}`);
                showToast('Coverage removed', 'success');
                await this.loadNegotiations();
            } catch (e) {
                showToast('Failed to remove coverage', 'error');
            }
        },

        resetRoundForm() {
            this.roundForm = {
                demand_date: new Date().toISOString().split('T')[0],
                demand_amount: 0,
                offer_date: '',
                offer_amount: 0,
                notes: '',
                status: 'pending',
            };
        },

        autoFillDate(round) {
            const today = new Date().toISOString().split('T')[0];
            if (round.demand_amount && !round.demand_date) round.demand_date = today;
            if (round.offer_amount && !round.offer_date) round.offer_date = today;
        },

        inlineSaveRound(round) {
            this.autoFillDate(round);
            clearTimeout(this._inlineSaveTimers?.[round.id]);
            if (!this._inlineSaveTimers) this._inlineSaveTimers = {};
            const tab = this.getActiveTab();
            if (!tab) return;
            this._inlineSaveTimers[round.id] = setTimeout(async () => {
                try {
                    const adj = tab.adjuster_info;
                    await api.post(`negotiations/${caseId}`, {
                        coverage_type: tab.coverage_type,
                        coverage_index: tab.coverage_index,
                        round: {
                            id: round.id,
                            round_number: round.round_number,
                            demand_date: round.demand_date || null,
                            demand_amount: round.demand_amount || 0,
                            offer_date: round.offer_date || null,
                            offer_amount: round.offer_amount || 0,
                            insurance_company: adj?.insurance_company || null,
                            party: adj?.party || null,
                            adjuster_phone: adj?.adjuster_phone || null,
                            adjuster_fax: adj?.adjuster_fax || null,
                            adjuster_email: adj?.adjuster_email || null,
                            claim_number: adj?.claim_number || null,
                            status: round.status,
                            notes: round.notes || null,
                        },
                    });
                    await this.loadNegotiations();
                } catch (e) {
                    showToast('Failed to save round', 'error');
                }
            }, 500);
        },

        async saveAdjusterInfo() {
            const tab = this.getActiveTab();
            if (!tab) return;
            clearTimeout(this._adjSaveTimer);
            this._adjSaveTimer = setTimeout(async () => {
                try {
                    const adj = tab.adjuster_info;
                    await api.post(`negotiations/${caseId}`, {
                        coverage_type: tab.coverage_type,
                        coverage_index: tab.coverage_index,
                        adjuster_info: {
                            insurance_company: adj.insurance_company || null,
                            party: adj.party || null,
                            adjuster_phone: adj.adjuster_phone || null,
                            adjuster_fax: adj.adjuster_fax || null,
                            adjuster_email: adj.adjuster_email || null,
                            claim_number: adj.claim_number || null,
                        },
                    });
                } catch (e) {
                    console.error('Failed to save adjuster info:', e);
                }
            }, 500);
        },

        async saveRound() {
            this.autoFillDate(this.roundForm);
            if (!this.roundForm.demand_amount && !this.roundForm.offer_amount && !this.roundForm.notes) return;
            const tab = this.getActiveTab();
            if (!tab) return;
            try {
                const adj = tab.adjuster_info;
                const roundData = {
                    id: null,
                    round_number: null,
                    demand_date: this.roundForm.demand_date || null,
                    demand_amount: this.roundForm.demand_amount || 0,
                    offer_date: this.roundForm.offer_date || null,
                    offer_amount: this.roundForm.offer_amount || 0,
                    insurance_company: adj.insurance_company || null,
                    party: adj.party || null,
                    adjuster_phone: adj.adjuster_phone || null,
                    adjuster_fax: adj.adjuster_fax || null,
                    adjuster_email: adj.adjuster_email || null,
                    claim_number: adj.claim_number || null,
                    status: this.roundForm.status,
                    notes: this.roundForm.notes || null,
                };

                const res = await api.post(`negotiations/${caseId}`, {
                    coverage_type: tab.coverage_type,
                    coverage_index: tab.coverage_index,
                    round: roundData,
                });

                if (res.success) {
                    showToast('Round added', 'success');
                    this.resetRoundForm();
                    await this.loadNegotiations();
                }
            } catch (e) {
                showToast('Failed to save round', 'error');
                console.error(e);
            }
        },

        async deleteRound(round) {
            if (!confirm('Delete this negotiation round?')) return;
            try {
                const res = await api.delete(`negotiations/${round.id}`);
                if (res.success) {
                    showToast('Round deleted', 'success');
                    await this.loadNegotiations();
                }
            } catch (e) {
                showToast('Failed to delete round', 'error');
            }
        },

        // Provider negotiations
        async autoPopulateProviders() {
            try {
                const res = await api.post(`provider-negotiations/${caseId}/populate`, {});
                if (res.success) {
                    showToast(res.message, 'success');
                    await this.loadProviderNegotiations();
                }
            } catch (e) {
                showToast(e.data?.error || 'Failed to auto-populate', 'error');
            }
        },

        async saveProviderNeg(pn) {
            clearTimeout(this._saveTimer);
            this._saveTimer = setTimeout(async () => {
                try {
                    await api.post(`provider-negotiations/${caseId}`, {
                        id: pn.id,
                        case_provider_id: pn.case_provider_id,
                        mbr_line_id: pn.mbr_line_id,
                        provider_name: pn.provider_name,
                        original_balance: pn.original_balance,
                        requested_reduction: pn.requested_reduction,
                        accepted_amount: pn.accepted_amount,
                        reduction_percent: pn.reduction_percent,
                        status: pn.status,
                        contact_name: pn.contact_name,
                        contact_info: pn.contact_info,
                        notes: pn.notes,
                    });
                } catch (e) {
                    console.error('Failed to save provider negotiation:', e);
                }
            }, 500);
        },

        updateReductionPercent(pn, val) {
            pn.reduction_percent = parseFloat(val) || 0;
            pn.accepted_amount = Math.round(pn.original_balance * (1 - pn.reduction_percent / 100) * 100) / 100;
            pn.requested_reduction = Math.round((pn.original_balance - pn.accepted_amount) * 100) / 100;
            this.saveProviderNeg(pn);
        },

        updateAcceptedAmount(pn, val) {
            pn.accepted_amount = parseFloat(val) || 0;
            pn.requested_reduction = Math.round((pn.original_balance - pn.accepted_amount) * 100) / 100;
            pn.reduction_percent = pn.original_balance > 0
                ? Math.round((1 - pn.accepted_amount / pn.original_balance) * 10000) / 100
                : 0;
            this.saveProviderNeg(pn);
        },

        updateProviderStatus(pn, val) {
            pn.status = val;
            if (val === 'waived') {
                pn.accepted_amount = 0;
                pn.reduction_percent = 100;
                pn.requested_reduction = pn.original_balance;
            }
            this.saveProviderNeg(pn);
        },

        getNegotiateStatus() {
            const allRounds = this.tabs.flatMap(t => t.rounds || []);
            if (allRounds.length === 0) return null;
            if (allRounds.some(r => r.status === 'accepted')) return 'accepted';
            if (allRounds.some(r => r.status === 'countered')) return 'countered';
            if (allRounds.some(r => r.status === 'pending')) return 'pending';
            return 'rejected';
        },

        getNegotiateStatusLabel(status) {
            const labels = { pending: 'Pending', countered: 'Countered', accepted: 'Accepted', rejected: 'Rejected' };
            return labels[status] || '';
        },

        async deleteProviderNeg(pn) {
            if (!confirm('Remove this provider negotiation?')) return;
            try {
                const res = await api.delete(`provider-negotiations/${pn.id}`);
                if (res.success) {
                    this.providerNegotiations = this.providerNegotiations.filter(p => p.id !== pn.id);
                    showToast('Removed', 'success');
                }
            } catch (e) {
                showToast('Failed to delete', 'error');
            }
        },
    };
}
