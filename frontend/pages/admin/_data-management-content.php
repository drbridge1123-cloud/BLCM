<!-- All sp- styles loaded from shared sp-design-system.css -->

<style>
    .dm-filter { font-size:11px; padding:4px 6px; border:1px solid #d1cdc4; border-radius:5px; background:#fff; color:#1a2535; max-width:130px; }
</style>

<div x-data="dataManagementPage()" x-init="init()">
    <!-- Page header row -->
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <div>
            <div class="sp-eyebrow">Admin</div>
            <h1 class="sp-title" style="font-size:16px;">Data Management</h1>
        </div>
    </div>

    <!-- ═══ Main Card ═══ -->
    <div class="sp-card">
        <div class="sp-gold-bar"></div>

        <div style="padding:24px;">

            <!-- Section: Export -->
            <div style="margin-bottom:28px;">
                <div style="font-size:11px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:14px;">Export Data</div>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:12px;">

                    <!-- Cases -->
                    <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535;">Cases</div>
                            <div style="font-size:18px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#1a2535;" x-text="loading ? '...' : counts.cases"></div>
                        </div>
                        <div style="margin-bottom:8px;">
                            <select x-model="filters.cases.staff" class="dm-filter">
                                <option value="">All Staff</option>
                                <template x-for="s in staffList" :key="s.id">
                                    <option :value="s.id" x-text="s.full_name || s.display_name"></option>
                                </template>
                            </select>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button @click="exportCases()" class="sp-new-btn-navy" style="font-size:11px; padding:5px 12px;">↓ Export CSV</button>
                            <button @click="downloadCasesTemplate()" style="font-size:11px; padding:5px 10px; background:none; border:1px solid #d1cdc4; border-radius:6px; color:#6b7280; cursor:pointer;" title="Download empty template">Template</button>
                        </div>
                    </div>

                    <!-- Providers -->
                    <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535;">Providers</div>
                            <div style="font-size:18px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#1a2535;" x-text="loading ? '...' : counts.providers"></div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button @click="exportProviders()" class="sp-new-btn-navy" style="font-size:11px; padding:5px 12px;">↓ Export CSV</button>
                            <button @click="downloadProvidersTemplate()" style="font-size:11px; padding:5px 10px; background:none; border:1px solid #d1cdc4; border-radius:6px; color:#6b7280; cursor:pointer;" title="Download empty template">Template</button>
                        </div>
                    </div>

                    <!-- Commissions -->
                    <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535;">Commissions</div>
                            <div style="font-size:18px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#1a2535;" x-text="loading ? '...' : counts.commissions"></div>
                        </div>
                        <div style="margin-bottom:8px;">
                            <select x-model="filters.commissions.staff" class="dm-filter">
                                <option value="">All Staff</option>
                                <template x-for="s in staffList" :key="s.id">
                                    <option :value="s.id" x-text="s.full_name || s.display_name"></option>
                                </template>
                            </select>
                        </div>
                        <button @click="exportCommissions()" class="sp-new-btn-navy" style="font-size:11px; padding:5px 12px;">↓ Export CSV</button>
                    </div>

                    <!-- Attorney Cases -->
                    <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535;">Attorney Cases</div>
                            <div style="font-size:18px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#1a2535;" x-text="loading ? '...' : counts.attorneyCases"></div>
                        </div>
                        <div style="margin-bottom:8px;">
                            <select x-model="filters.attorneyCases.staff" class="dm-filter">
                                <option value="">All Attorneys</option>
                                <template x-for="s in staffList" :key="s.id">
                                    <option :value="s.id" x-text="s.full_name || s.display_name"></option>
                                </template>
                            </select>
                        </div>
                        <button @click="exportAttorneyCases()" class="sp-new-btn-navy" style="font-size:11px; padding:5px 12px;">↓ Export CSV</button>
                    </div>

                    <!-- Referrals -->
                    <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535;">Referrals</div>
                            <div style="font-size:18px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#1a2535;" x-text="loading ? '...' : counts.referrals"></div>
                        </div>
                        <div style="margin-bottom:8px;">
                            <select x-model="filters.referrals.staff" class="dm-filter">
                                <option value="">All Leads</option>
                                <template x-for="s in staffList" :key="s.id">
                                    <option :value="s.id" x-text="s.full_name || s.display_name"></option>
                                </template>
                            </select>
                        </div>
                        <button @click="exportReferrals()" class="sp-new-btn-navy" style="font-size:11px; padding:5px 12px;">↓ Export CSV</button>
                    </div>

                    <!-- Expense Report -->
                    <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535;">Expense Report</div>
                        </div>
                        <div style="margin-bottom:8px;">
                            <select x-model="filters.expenseReport.staff" class="dm-filter">
                                <option value="">All Staff</option>
                                <template x-for="s in staffList" :key="s.id">
                                    <option :value="s.id" x-text="s.full_name || s.display_name"></option>
                                </template>
                            </select>
                        </div>
                        <button @click="exportExpenseReport()" class="sp-new-btn-navy" style="font-size:11px; padding:5px 12px;">↓ Export CSV</button>
                    </div>

                </div>
            </div>

            <!-- Divider -->
            <div style="border-top:1px solid #e8e4dc; margin-bottom:28px;"></div>

            <!-- Section: Import -->
            <div style="margin-bottom:28px;">
                <div style="font-size:11px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:14px;">Import Data</div>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:12px;">

                    <!-- Health Ledger Import -->
                    <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535;">Health Ledger</div>
                            <div style="font-size:18px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#1a2535;" x-text="loading ? '...' : counts.healthLedger"></div>
                        </div>
                        <div style="font-size:11px; color:#9ca3af; margin-bottom:8px;">Required: client_name, insurance_carrier</div>
                        <label class="sp-new-btn-navy" style="font-size:11px; padding:5px 12px; cursor:pointer; display:inline-block;">
                            ↑ Import CSV
                            <input type="file" accept=".csv" @change="importHealthLedger($event)" style="display:none;">
                        </label>
                    </div>

                    <!-- Bank Statements Import -->
                    <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535;">Bank Statements</div>
                            <div style="font-size:18px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#1a2535;" x-text="loading ? '...' : counts.bankEntries"></div>
                        </div>
                        <div style="font-size:11px; color:#9ca3af; margin-bottom:8px;">Columns: date, description, amount, check #</div>
                        <label class="sp-new-btn-navy" style="font-size:11px; padding:5px 12px; cursor:pointer; display:inline-block;">
                            ↑ Import CSV
                            <input type="file" accept=".csv" @change="importBankStatements($event)" style="display:none;">
                        </label>
                    </div>

                    <!-- Cost Ledger Import -->
                    <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535;">Cost Ledger</div>
                        </div>
                        <div style="font-size:11px; color:#9ca3af; margin-bottom:8px;">Requires case #. Columns: provider, description, billed, paid</div>
                        <label class="sp-new-btn-navy" style="font-size:11px; padding:5px 12px; cursor:pointer; display:inline-block;">
                            ↑ Import CSV
                            <input type="file" accept=".csv" @change="importCostLedger($event)" style="display:none;">
                        </label>
                    </div>

                    <!-- MBDS Report Import -->
                    <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535;">Medical Balance Report</div>
                        </div>
                        <div style="font-size:11px; color:#9ca3af; margin-bottom:8px;">Requires case #. Columns: provider, charges, PIP paid, balance</div>
                        <label class="sp-new-btn-navy" style="font-size:11px; padding:5px 12px; cursor:pointer; display:inline-block;">
                            ↑ Import CSV
                            <input type="file" accept=".csv" @change="importMbdsReport($event)" style="display:none;">
                        </label>
                    </div>

                </div>
            </div>

            <!-- Divider -->
            <div style="border-top:1px solid #e8e4dc; margin-bottom:28px;"></div>

            <!-- Section: Reference Data -->
            <div>
                <div style="font-size:11px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:14px;">Reference Data</div>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:12px;">

                    <!-- Insurance Companies -->
                    <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535;">Insurance Companies</div>
                            <div style="font-size:18px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#1a2535;" x-text="loading ? '...' : counts.insurance"></div>
                        </div>
                    </div>

                    <!-- Adjusters -->
                    <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535;">Adjusters</div>
                            <div style="font-size:18px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#1a2535;" x-text="loading ? '...' : counts.adjusters"></div>
                        </div>
                    </div>

                    <!-- Templates -->
                    <div style="background:#fafaf8; border:1px solid #e8e4dc; border-radius:10px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <div style="font-size:13px; font-weight:600; color:#1a2535;">Templates</div>
                            <div style="font-size:18px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#1a2535;" x-text="loading ? '...' : counts.templates"></div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div><!-- /sp-card -->
</div>
