function negotiatePanel(caseId) {
    return {
        open: false,
        loading: true,
        activeCoverage: '3rd_party',
        activeCoverages: [],
        coverageNegotiations: { '3rd_party': [], um: [], uim: [], dv: [] },
        bestOffers: { '3rd_party': 0, um: 0, uim: 0, dv: 0 },
        adjusterInfo: {
            '3rd_party': { insurance_company: '', party: '', adjuster_phone: '', adjuster_fax: '', adjuster_email: '', claim_number: '' },
            um: { insurance_company: '', party: '', adjuster_phone: '', adjuster_fax: '', adjuster_email: '', claim_number: '' },
            uim: { insurance_company: '', party: '', adjuster_phone: '', adjuster_fax: '', adjuster_email: '', claim_number: '' },
            dv: { insurance_company: '', party: '', adjuster_phone: '', adjuster_fax: '', adjuster_email: '', claim_number: '' },
        },
        providerNegotiations: [],

        showRoundForm: false,
        editingRound: null,
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

        formatPhone(field) {
            let val = this.adjusterInfo[this.activeCoverage][field] || '';
            let digits = val.replace(/\D/g, '');
            if (digits.length === 0) return;
            if (digits.length > 10 && digits[0] === '1') digits = digits.substring(1);
            if (digits.length === 10) {
                this.adjusterInfo[this.activeCoverage][field] = '(' + digits.substring(0,3) + ') ' + digits.substring(3,6) + '-' + digits.substring(6);
            }
            this.saveAdjusterInfo();
        },

        formatEmail() {
            let val = this.adjusterInfo[this.activeCoverage].adjuster_email || '';
            this.adjusterInfo[this.activeCoverage].adjuster_email = val.trim().toLowerCase();
            this.saveAdjusterInfo();
        },

        async init() {
            await this.loadNegotiations();
            await this.loadProviderNegotiations();
            this.loading = false;
        },

        async loadNegotiations() {
            try {
                const res = await api.get(`negotiations/${caseId}`);
                if (res.success) {
                    this.coverageNegotiations = res.grouped;
                    this.bestOffers = res.best_offers;
                    this.activeCoverages = res.active_coverages;
                    if (res.adjuster_info) {
                        for (const type of ['3rd_party', 'um', 'uim', 'dv']) {
                            if (res.adjuster_info[type]) {
                                this.adjusterInfo[type] = { ...res.adjuster_info[type] };
                            }
                        }
                    }
                    if (this.activeCoverages.length > 0 && !this.activeCoverages.includes(this.activeCoverage)) {
                        this.activeCoverage = this.activeCoverages[0];
                    }
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
            const labels = {
                '3rd_party': '3rd Party',
                'um': 'UM',
                'uim': 'UIM',
                'dv': 'DV',
            };
            return labels[type] || type;
        },

        getTotalBestOffer() {
            return Object.values(this.bestOffers).reduce((sum, v) => sum + (v || 0), 0);
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
            this._inlineSaveTimers[round.id] = setTimeout(async () => {
                try {
                    const adj = this.adjusterInfo[this.activeCoverage];
                    await api.post(`negotiations/${caseId}`, {
                        coverage_type: this.activeCoverage,
                        round: {
                            id: round.id,
                            round_number: round.round_number,
                            demand_date: round.demand_date || null,
                            demand_amount: round.demand_amount || 0,
                            offer_date: round.offer_date || null,
                            offer_amount: round.offer_amount || 0,
                            insurance_company: adj.insurance_company || null,
                            party: adj.party || null,
                            adjuster_phone: adj.adjuster_phone || null,
                            adjuster_fax: adj.adjuster_fax || null,
                            adjuster_email: adj.adjuster_email || null,
                            claim_number: adj.claim_number || null,
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
            clearTimeout(this._adjSaveTimer);
            this._adjSaveTimer = setTimeout(async () => {
                try {
                    const adj = this.adjusterInfo[this.activeCoverage];
                    await api.post(`negotiations/${caseId}`, {
                        coverage_type: this.activeCoverage,
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
            // Only save if at least one field has data
            if (!this.roundForm.demand_amount && !this.roundForm.offer_amount && !this.roundForm.notes) return;
            try {
                const adj = this.adjusterInfo[this.activeCoverage];
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
                    coverage_type: this.activeCoverage,
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
                        mbds_line_id: pn.mbds_line_id,
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
