function disbursementPanel(caseId) {
    return {
        open: false,
        loading: true,
        settlementData: null,
        pipInfo: null,

        settings: {
            settlement_amount: 0,
            attorney_fee_percent: 1/3,
            coverage_3rd_party: false,
            coverage_um: false,
            coverage_uim: false,
            policy_limit: false,
            um_uim_limit: false,
            pip_subrogation_amount: 0,
            pip_insurance_company: '',
            settlement_method: null,
        },

        bestOffers: { '3rd_party': 0, um: 0, uim: 0, dv: 0 },
        medicalBills: { total_charges: 0, total_balance: 0, providers: [] },
        medicalBalance: 0,
        healthSubrogation: 0,
        expenses: { reimbursable: 0, litigation: 0, other: 0, total: 0 },

        // Calculated results
        calculated: null,
        mahlerCalc: { gross: 0, fee: 0, recordsFee: 0, litigationFee: 0, costs: 0, otherExpenses: 0, afe: 0, medicalBalance: 0, subrogation: 0, clientNet: 0 },
        hammCalc: { gross: 0, thirdParty: 0, umOffer: 0, fee: 0, recordsFee: 0, litigationFee: 0, costs: 0, otherExpenses: 0, afe: 0, pipRatio: 0, pip: 0, clientCredit: 0, medicalBalance: 0, subrogation: 0, clientNet: 0 },
        disbursementLines: [],

        _debounceSave: null,

        async init() {
            this._debounceSave = createDebouncedSave(() => this.saveSettings(), 500);
            await this.loadSettlementData();
            this.loading = false;

            // Auto-reload when MBR data changes
            window.addEventListener('mbr-updated', () => {
                this.loadSettlementData();
            });

            // Auto-reload when negotiations change (accepted offers, etc.)
            window.addEventListener('negotiation-updated', () => {
                this.loadSettlementData();
            });
        },

        async loadSettlementData() {
            try {
                const res = await api.get(`settlement/${caseId}`);
                if (res.success) {
                    this.settlementData = res;
                    this.settings = { ...this.settings, ...res.settings };
                    // Map DB decimal to precise fraction
                    const fp = parseFloat(this.settings.attorney_fee_percent);
                    if (fp > 0.33 && fp < 0.34) this.settings.attorney_fee_percent = 1/3;
                    else if (fp === 0.4) this.settings.attorney_fee_percent = 0.4;
                    this.bestOffers = res.best_offers;
                    this.medicalBills = res.medical_bills;
                    this.medicalBalance = res.medical_balance;
                    this.healthSubrogation = res.health_subrogation;
                    this.expenses = res.expenses;
                    this.pipInfo = res.pip_info;

                    // Auto-sync PIP: amount from MBR, company from Contacts
                    if (this.pipInfo?.pip1_total > 0) {
                        this.settings.pip_subrogation_amount = this.pipInfo.pip1_total;
                    }
                    if (this.pipInfo?.contact_company) {
                        this.settings.pip_insurance_company = this.pipInfo.contact_company;
                    } else if (this.pipInfo?.pip1_name) {
                        this.settings.pip_insurance_company = this.pipInfo.pip1_name;
                    }

                    // Auto-sync coverage checkboxes from active negotiations
                    if (res.active_coverages) {
                        const ac = res.active_coverages;
                        const has3rd = ac.includes('3rd_party');
                        const hasUm = ac.includes('um');
                        const hasUim = ac.includes('uim');

                        this.settings.coverage_3rd_party = has3rd;
                        this.settings.coverage_um = hasUm;
                        this.settings.coverage_uim = hasUim;

                        // Auto-check 3rd party limit only when both 3rd_party + UIM exist
                        if (has3rd && hasUim) {
                            this.settings.policy_limit = true;
                        } else if (!hasUim) {
                            // If UIM removed, don't force policy_limit (keep manual)
                        }
                        this._debounceSave();
                    }

                    this.calculate();
                }
            } catch (e) {
                console.error('Failed to load settlement data:', e);
            }
        },

        setFeePercent(pct) {
            this.settings.attorney_fee_percent = pct;
            this.onSettingsChange();
        },

        selectMethod(method) {
            this.settings.settlement_method = method;
            this.onSettingsChange();
        },

        showMahler() {
            // Mahler only when 3rd party + PIP, but NO UM/UIM coverage
            return this.settings.pip_subrogation_amount > 0
                && this.settings.coverage_3rd_party
                && !this.settings.coverage_um
                && !this.settings.coverage_uim;
        },

        showHamm() {
            // Hamm when UM or UIM is checked (with PIP), hidden when UM/UIM limit reached
            return this.settings.pip_subrogation_amount > 0
                && (this.settings.coverage_um || this.settings.coverage_uim)
                && !this.settings.um_uim_limit;
        },

        onSettingsChange() {
            this.calculate();
            this.debouncedSave();
        },

        debouncedSave() {
            this._debounceSave();
        },

        async saveSettings() {
            try {
                await api.put(`settlement/${caseId}`, this.settings);
            } catch (e) {
                console.error('Failed to save settlement settings:', e);
            }
        },

        calculate() {
            // Calculate all methods
            this.mahlerCalc = this.getMahlerCalc();
            this.hammCalc = this.getHammCalc();

            const canMahler = this.showMahler();
            const canHamm = this.showHamm();

            // Clear method if conditions no longer met
            let method = this.settings.settlement_method;
            if (method === 'mahler' && !canMahler) method = null;
            if (method === 'hamm' && !canHamm) method = null;

            // Determine which calculation to use
            if (method === 'mahler') {
                this.calculated = this.mahlerCalc;
            } else if (method === 'hamm') {
                this.calculated = this.hammCalc;
            } else if (canMahler && !canHamm) {
                // Only Mahler available → auto-select
                this.calculated = this.mahlerCalc;
                this.settings.settlement_method = 'mahler';
            } else if (canHamm && !canMahler) {
                // Only Hamm available → auto-select
                this.calculated = this.hammCalc;
                this.settings.settlement_method = 'hamm';
            } else {
                // Standard: no PIP method or both available (user must pick)
                this.calculated = this.getStandardCalc();
                if (!canMahler && !canHamm) this.settings.settlement_method = null;
            }

            this.buildDisbursementLines();
        },

        getMahlerCalc() {
            // Mahler: Gross = 3rd Party only (PIP is separate, not included in gross)
            const gross = this.bestOffers['3rd_party'] || this.settings.settlement_amount || 0;
            const feePercent = this.settings.attorney_fee_percent || 1/3;
            const fee = Math.round(gross * feePercent * 100) / 100;
            const recordsFee = this.expenses.reimbursable || 0;
            const litigationFee = this.expenses.litigation || 0;
            const costs = recordsFee + litigationFee; // Records + Litigation only
            const otherExpenses = this.expenses.other || 0;
            const legalFeeAndExpenses = fee + costs; // excludes Other Expenses
            const pip = this.settings.pip_subrogation_amount || 0;
            const pipPercent = gross > 0 ? pip / gross : 0;
            const clientCredit = this.settings.policy_limit
                ? legalFeeAndExpenses
                : Math.round(pipPercent * legalFeeAndExpenses * 100) / 100;
            const medBal = this.medicalBalance || 0;
            const subrogation = this.healthSubrogation || 0;
            const totalDeductions = fee + costs + otherExpenses + pip + medBal + subrogation - clientCredit;
            const clientNet = Math.round((gross - totalDeductions) * 100) / 100;

            const subrogationLien = Math.round((pip - clientCredit) * 100) / 100;

            return {
                method: 'mahler',
                gross, fee, recordsFee, litigationFee, costs, otherExpenses,
                afe: legalFeeAndExpenses,
                pip, pipPercent, clientCredit, subrogationLien,
                medicalBalance: medBal, subrogation, totalDeductions, clientNet,
            };
        },

        getHammCalc() {
            // Hamm/Winters/Matsyuk: Gross = ALL sources (3rd Party + UM + UIM + PIP)
            const thirdParty = this.bestOffers['3rd_party'] || 0;
            const umOffer = (this.bestOffers['um'] || 0) + (this.bestOffers['uim'] || 0);
            const pip = this.settings.pip_subrogation_amount || 0;
            const gross = thirdParty + umOffer + pip;
            const feePercent = this.settings.attorney_fee_percent || 1/3;
            const fee = Math.round(gross * feePercent * 100) / 100;
            const recordsFee = this.expenses.reimbursable || 0;
            const litigationFee = this.expenses.litigation || 0;
            const costs = recordsFee + litigationFee; // Records + Litigation only
            const otherExpenses = this.expenses.other || 0;
            const legalFeeAndExpenses = fee + costs; // excludes Other Expenses

            // PIP ratio & Client Credit (Hamm Fee)
            const pipRatio = gross > 0 ? pip / gross : 0;
            const clientCredit = Math.round(pipRatio * legalFeeAndExpenses * 100) / 100;

            const medBal = this.medicalBalance || 0;
            const subrogation = this.healthSubrogation || 0;

            const totalDeductions = fee + costs + otherExpenses + pip + medBal + subrogation - clientCredit;
            const clientNet = Math.round((gross - totalDeductions) * 100) / 100;

            return {
                method: 'hamm',
                gross, thirdParty, umOffer, fee, recordsFee, litigationFee, costs, otherExpenses,
                afe: legalFeeAndExpenses, pipRatio, pip, clientCredit,
                medicalBalance: medBal, subrogation, totalDeductions, clientNet,
            };
        },

        getStandardCalc() {
            // Gross = manual settlement amount, or sum of best offers from selected coverages (excludes DV)
            let gross = this.settings.settlement_amount || 0;
            if (!gross) {
                if (this.settings.coverage_3rd_party) gross += this.bestOffers['3rd_party'] || 0;
                if (this.settings.coverage_um) gross += this.bestOffers['um'] || 0;
                if (this.settings.coverage_uim) gross += this.bestOffers['uim'] || 0;
                // Fallback: sum all offers EXCEPT DV
                if (!gross) {
                    for (const [k, v] of Object.entries(this.bestOffers)) {
                        if (k !== 'dv') gross += v;
                    }
                }
            }
            const feePercent = this.settings.attorney_fee_percent || 1/3;
            const fee = Math.round(gross * feePercent * 100) / 100;
            const recordsFee = this.expenses.reimbursable || 0;
            const litigationFee = this.expenses.litigation || 0;
            const costs = recordsFee + litigationFee;
            const otherExpenses = this.expenses.other || 0;
            const medBal = this.medicalBalance || 0;
            const subrogation = this.healthSubrogation || 0;
            const totalDeductions = fee + costs + otherExpenses + medBal + subrogation;
            const clientNet = Math.round((gross - totalDeductions) * 100) / 100;

            return {
                method: 'standard',
                gross, fee, recordsFee, litigationFee, costs, otherExpenses,
                afe: fee + costs,
                medicalBalance: medBal, subrogation, totalDeductions, clientNet,
            };
        },

        buildDisbursementLines() {
            const c = this.calculated;
            if (!c) { this.disbursementLines = []; return; }

            const lines = [];
            const dvAmount = this.bestOffers['dv'] || 0;

            // Settlement Proceeds
            lines.push({ section: true, label: 'Settlement Proceeds' });
            if (c.method === 'mahler') {
                lines.push({ label: '3rd Party Settlement', amount: c.gross, indent: true });
            } else if (c.method === 'hamm') {
                lines.push({ label: 'Gross Settlement (All Sources)', amount: c.gross, indent: true });
            } else {
                lines.push({ label: 'Settlement Proceeds', amount: c.gross, indent: true });
            }

            // Attorney Fees & Costs (Records Fee + Litigation Fee only)
            lines.push({ section: true, label: 'Legal Fee & Expenses' });
            const feeLabel = this.settings.attorney_fee_percent >= 0.34
                ? 'Attorney Fee (40%)'
                : 'Attorney Fee (33.33%)';
            lines.push({ label: feeLabel, amount: -c.fee, indent: true });
            if (c.recordsFee > 0) {
                lines.push({ label: 'Records Fee', amount: -c.recordsFee, indent: true });
            }
            if (c.litigationFee > 0) {
                lines.push({ label: 'Litigation Fee', amount: -c.litigationFee, indent: true });
            }

            // Other Expenses (separate from Legal Fee, NOT in Mahler/HAMM calculations)
            if (c.otherExpenses > 0) {
                lines.push({ section: true, label: 'Other Expenses' });
                lines.push({ label: 'Other Expenses', amount: -c.otherExpenses, indent: true });
            }

            // PIP section (method-specific)
            if (c.method === 'mahler' && this.settings.pip_subrogation_amount > 0) {
                const company = this.settings.pip_insurance_company || 'PIP Carrier';
                lines.push({ section: true, label: 'PIP Subrogation (Mahler)' });
                lines.push({ label: `Subrogation Lien → ${company}`, amount: -c.subrogationLien, indent: true });
            } else if (c.method === 'hamm' && this.settings.pip_subrogation_amount > 0) {
                const company = this.settings.pip_insurance_company || 'PIP Carrier';
                lines.push({ section: true, label: 'PIP Subrogation (Hamm)' });
                lines.push({ label: `Total PIP Payment → ${company}`, amount: -c.pip, indent: true });
                lines.push({ label: `Client Credit (PIP Ratio: ${(c.pipRatio * 100).toFixed(2)}%)`, amount: c.clientCredit, indent: true });
            }

            // Medical Bills & Liens
            if (this.medicalBills.providers.length > 0) {
                lines.push({ section: true, label: 'Medical Bills & Liens' });
                for (const p of this.medicalBills.providers) {
                    const amt = p.negotiated_amount !== undefined ? p.negotiated_amount : p.balance;
                    if (amt > 0) {
                        lines.push({ label: p.name, amount: -amt, indent: true });
                    }
                }
            }

            // Health Subrogation
            if (c.subrogation > 0) {
                lines.push({ section: true, label: 'Subrogation' });
                lines.push({ label: 'Health Insurance Subrogation', amount: -c.subrogation, indent: true });
            }

            // DV (Diminished Value) — no attorney fee, pass-through to client
            if (dvAmount > 0) {
                lines.push({ section: true, label: 'Diminished Value' });
                lines.push({ label: 'DV Settlement (No Attorney Fee)', amount: dvAmount, indent: true });
            }

            // Total (includes DV pass-through)
            lines.push({ isTotal: true, label: 'CLIENT NET PROCEEDS', amount: c.clientNet + dvAmount });

            this.disbursementLines = lines;
        },

        _printPage(title, rows) {
            const w = window.open('', '_blank', 'width=800,height=600');
            w.document.write(`<!DOCTYPE html><html><head><title>${title}</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'IBM Plex Sans', sans-serif; font-size: 13px; color: #1a2535; margin: 40px; }
    h1 { font-size: 18px; color: #0F1B2D; margin-bottom: 4px; }
    .meta { font-size: 12px; color: #8a8a82; margin-bottom: 24px; }
    table { width: 100%; border-collapse: collapse; border: 1px solid #ddddd8; }
    td { border-bottom: 1px solid #ddddd8; }
    .section td { background: #f5f5f0; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #8a8a82; padding: 6px 16px; }
    .total td { background: #0F1B2D; color: #fff; font-weight: 700; font-size: 14px; padding: 10px 16px; }
    .amt { text-align: right; font-family: 'IBM Plex Mono', monospace; }
    .neg { color: #b83232; }
    .pos { color: #2a6b4a; }
    @media print { body { margin: 20px; } }
</style></head><body>
<h1>${title}</h1>
<div class="meta">Printed: ${formatDate(new Date().toISOString().split('T')[0])}</div>
<table>${rows}</table>
<script>window.print();<\/script>
</body></html>`);
            w.document.close();
        },

        _buildRows(lines) {
            let rows = '';
            for (const line of lines) {
                if (line.section) {
                    rows += `<tr class="section"><td colspan="2">${line.label}</td></tr>`;
                } else if (line.isRedTotal) {
                    rows += `<tr style="background:#C62828;color:#fff;font-weight:700;"><td style="padding:10px 16px;">${line.label}</td><td class="amt" style="padding:10px 16px; font-family:'IBM Plex Mono',monospace;">${formatCurrency(line.amount)}</td></tr>`;
                } else if (line.isTotal) {
                    rows += `<tr class="total"><td style="padding:10px 16px;">${line.label}</td><td class="amt" style="padding:10px 16px; font-family:'IBM Plex Mono',monospace;">${formatCurrency(line.amount)}</td></tr>`;
                } else {
                    const cls = line.amount < 0 ? 'neg' : (line.amount > 0 ? 'pos' : '');
                    rows += `<tr><td style="padding:6px 16px; ${line.indent ? 'padding-left:32px;' : ''}">${line.label}</td><td class="amt ${cls}" style="padding:6px 16px;">${formatCurrency(line.amount)}</td></tr>`;
                }
            }
            return rows;
        },

        printDisbursement() {
            if (!this.calculated) return;
            this._printPage('Settlement Disbursement Statement', this._buildRows(this.disbursementLines));
        },

        printMethod(method) {
            const calc = method === 'mahler' ? this.mahlerCalc : this.hammCalc;
            if (!calc) return;

            const lines = [];
            if (method === 'mahler') {
                const company = this.settings.pip_insurance_company || 'PIP Carrier';
                lines.push({ section: true, label: 'Gross Settlement' });
                lines.push({ label: 'Gross Settlement', amount: calc.gross, indent: true });
                lines.push({ section: true, label: 'Legal Fee & Expenses' });
                lines.push({ label: `Attorney's Fee (${(this.settings.attorney_fee_percent >= 0.34 ? '40' : '33.33')}% of Gross Settlement)`, amount: calc.fee, indent: true });
                if (calc.recordsFee > 0) lines.push({ label: 'Records Fee', amount: calc.recordsFee, indent: true });
                if (calc.litigationFee > 0) lines.push({ label: 'Litigation Fee', amount: calc.litigationFee, indent: true });
                lines.push({ section: true, label: `Total PIP Payment → ${company}` });
                lines.push({ label: `Total PIP Payment`, amount: calc.pip, indent: true });
                lines.push({ label: 'PIP Percentage of Gross', amount: null, indent: true, note: (calc.pipPercent * 100).toFixed(4) + '%' });
                lines.push({ label: 'Client Credit for Attorney Fee & Cost', amount: calc.clientCredit, indent: true });
                lines.push({ isRedTotal: true, label: `SUBROGATION LIEN → ${company.toUpperCase()}`, amount: calc.subrogationLien });
            } else {
                const company = this.settings.pip_insurance_company || 'PIP Carrier';
                lines.push({ section: true, label: 'Hamm/Winters/Matsyuk Formula' });
                lines.push({ label: 'Gross Settlement (All Sources)', amount: calc.gross, indent: true });
                lines.push({ section: true, label: 'Legal Fee & Expenses' });
                lines.push({ label: `Attorney Fee (${(this.settings.attorney_fee_percent * 100).toFixed(2)}% of Gross)`, amount: -calc.fee, indent: true });
                if (calc.recordsFee > 0) lines.push({ label: 'Records Fee', amount: -calc.recordsFee, indent: true });
                if (calc.litigationFee > 0) lines.push({ label: 'Litigation Fee', amount: -calc.litigationFee, indent: true });
                lines.push({ label: 'Total Legal Fee & Expenses', amount: calc.afe, indent: true });
                lines.push({ section: true, label: 'PIP Subrogation' });
                lines.push({ label: `Total PIP Payment → ${company}`, amount: -calc.pip, indent: true });
                lines.push({ label: `PIP / Gross Settlement Ratio`, amount: null, indent: true, note: (calc.pipRatio * 100).toFixed(4) + '%' });
                lines.push({ isRedTotal: true, label: `CLIENT CREDIT FOR ATTORNEY FEE & COST`, amount: calc.clientCredit });
            }

            // Handle null amounts (info-only rows)
            const printLines = lines.map(l => ({
                ...l,
                amount: l.amount === null ? undefined : l.amount,
            }));

            const title = method === 'mahler' ? 'Mahler Method Calculation' : 'Hamm Method Calculation';
            let rows = '';
            for (const line of printLines) {
                if (line.section) {
                    rows += `<tr class="section"><td colspan="2">${line.label}</td></tr>`;
                } else if (line.isRedTotal) {
                    rows += `<tr style="background:#C62828;color:#fff;font-weight:700;"><td style="padding:10px 16px;">${line.label}</td><td class="amt" style="padding:10px 16px; font-family:'IBM Plex Mono',monospace;">${formatCurrency(line.amount)}</td></tr>`;
                } else if (line.isTotal) {
                    rows += `<tr class="total"><td style="padding:10px 16px;">${line.label}</td><td class="amt" style="padding:10px 16px; font-family:'IBM Plex Mono',monospace;">${formatCurrency(line.amount)}</td></tr>`;
                } else {
                    const amtStr = line.amount !== undefined ? formatCurrency(line.amount) : '';
                    const cls = (line.amount || 0) < 0 ? 'neg' : ((line.amount || 0) > 0 ? 'pos' : '');
                    rows += `<tr><td style="padding:6px 16px; ${line.indent ? 'padding-left:32px;' : ''}">${line.label}</td><td class="amt ${cls}" style="padding:6px 16px;">${amtStr}</td></tr>`;
                }
            }
            this._printPage(title, rows);
        },
    };
}
