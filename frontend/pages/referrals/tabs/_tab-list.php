<!-- Referrals List Tab -->
<div style="overflow-x:auto;">
    <table class="sp-table sp-table-compact" style="white-space:nowrap;">
        <thead>
            <tr>
                <th class="center" style="width:40px;">#</th>
                <th>Signed</th>
                <th>File #</th>
                <th>Client Name</th>
                <th class="center">Status</th>
                <th>DOL</th>
                <th>Referred By</th>
                <th>Provider</th>
                <th>Body Shop</th>
                <template x-if="isAdmin">
                    <th>Lead</th>
                </template>
                <th>Case Mgr</th>
                <th>Remark</th>
                <th class="center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <template x-if="loading">
                <tr style="cursor:default;"><td colspan="13" class="sp-loading">Loading...</td></tr>
            </template>
            <template x-if="!loading && referrals.length === 0">
                <tr style="cursor:default;"><td colspan="13" class="sp-empty">No referrals found</td></tr>
            </template>
            <template x-for="r in referrals" :key="r.id">
                <tr style="cursor:default;">
                    <td style="text-align:center;"><span style="font-family:'IBM Plex Mono',monospace; font-size:10px; color:#8a8a82;" x-text="r.row_number"></span></td>
                    <td><span class="sp-mono" style="font-size:11px;" x-text="r.signed_date || '—'"></span></td>
                    <td><span class="sp-case-num" style="font-size:11px;" x-text="r.file_number || '—'"></span></td>
                    <td><span class="sp-client" style="font-size:12px;" x-text="r.client_name"></span></td>
                    <td style="text-align:center;">
                        <span x-show="r.status" class="sp-stage" style="font-size:9px; padding:2px 8px;"
                              :style="({
                                  'INI': 'background:rgba(37,99,235,.08); color:#2563eb; border:1px solid rgba(37,99,235,.15);',
                                  'REC': 'background:rgba(6,182,212,.08); color:#0891b2; border:1px solid rgba(6,182,212,.15);',
                                  'NEG': 'background:rgba(217,119,6,.08); color:#D97706; border:1px solid rgba(217,119,6,.15);',
                                  'FILE': 'background:rgba(124,92,191,.08); color:#7C5CBF; border:1px solid rgba(124,92,191,.15);',
                                  'LIT': 'background:rgba(231,76,60,.08); color:#e74c3c; border:1px solid rgba(231,76,60,.15);',
                                  'SETTLE': 'background:rgba(26,158,106,.08); color:#1a9e6a; border:1px solid rgba(26,158,106,.15);',
                                  'RFD': 'background:rgba(138,138,130,.08); color:#8a8a82; border:1px solid rgba(138,138,130,.15);',
                                  'HEALTH': 'background:rgba(20,184,166,.08); color:#0d9488; border:1px solid rgba(20,184,166,.15);'
                              })[r.status] || ''" x-text="r.status"></span>
                    </td>
                    <td><span class="sp-mono" style="font-size:11px;" x-text="r.date_of_loss || '—'"></span></td>
                    <td><span style="font-size:11px; color:#1a2535;" x-text="r.referred_by || '—'"></span></td>
                    <td><span style="font-size:11px; color:#1a2535;" x-text="r.referred_to_provider || '—'"></span></td>
                    <td><span style="font-size:11px; color:#1a2535;" x-text="r.referred_to_body_shop || '—'"></span></td>
                    <template x-if="isAdmin">
                        <td><span style="font-size:11px; color:#1a2535;" x-text="r.lead_name || '—'"></span></td>
                    </template>
                    <td><span style="font-size:11px; color:#1a2535;" x-text="r.case_manager_name || '—'"></span></td>
                    <td><span style="font-size:11px; color:#8a8a82; max-width:120px; display:inline-block; overflow:hidden; text-overflow:ellipsis;" x-text="r.remark || ''"></span></td>
                    <td>
                        <div class="sp-actions">
                            <button @click="openEditModal(r)" class="sp-act sp-act-gold">
                                <span>✎</span>
                                <span class="sp-tip">Edit</span>
                            </button>
                            <button @click="deleteReferral(r.id)" class="sp-act sp-act-red">
                                <span>✕</span>
                                <span class="sp-tip">Delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>
