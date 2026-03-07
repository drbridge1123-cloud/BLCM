/**
 * Pending Assignments Mixin
 * Shows yellow banner with Accept/Decline for cases and providers assigned to current user.
 * Provider assignments are grouped by case for compact display with bulk actions.
 * Spread into any Alpine component: ...pendingAssignmentsMixin()
 */
function pendingAssignmentsMixin() {
    return {
        pendingCaseAssignments: [],
        pendingProviderAssignments: [],
        _expandedGroups: {},

        // ── Case Assignments ──

        async loadPendingCaseAssignments() {
            try {
                const res = await api.get('bl-cases/pending-assignments');
                this.pendingCaseAssignments = res.data || [];
            } catch (e) {
                this.pendingCaseAssignments = [];
            }
        },

        async acceptCaseAssignment(caseId) {
            if (!confirm('Accept this case assignment?')) return;
            try {
                await api.put('bl-cases/' + caseId + '/respond-assignment', { action: 'accept' });
                showToast('Case assignment accepted', 'success');
                this.pendingCaseAssignments = this.pendingCaseAssignments.filter(a => a.id !== caseId);
            } catch (e) {
                showToast(e.data?.message || 'Failed to accept', 'error');
            }
        },

        async declineCaseAssignment(caseId) {
            const reason = prompt('Please enter the reason for declining:');
            if (reason === null) return;
            if (!reason.trim()) {
                showToast('Decline reason is required', 'error');
                return;
            }
            try {
                await api.put('bl-cases/' + caseId + '/respond-assignment', { action: 'decline', reason: reason.trim() });
                showToast('Case assignment declined', 'success');
                this.pendingCaseAssignments = this.pendingCaseAssignments.filter(a => a.id !== caseId);
            } catch (e) {
                showToast(e.data?.message || 'Failed to decline', 'error');
            }
        },

        // ── Provider Assignments (grouped by case) ──

        async loadPendingProviderAssignments() {
            try {
                const res = await api.get('billing/pending-assignments');
                this.pendingProviderAssignments = res.data || [];
            } catch (e) {
                this.pendingProviderAssignments = [];
            }
        },

        getGroupedProviderAssignments() {
            const groups = {};
            for (const pa of this.pendingProviderAssignments) {
                const key = pa.case_id;
                if (!groups[key]) {
                    groups[key] = {
                        case_id: pa.case_id,
                        case_number: pa.case_number,
                        client_name: pa.client_name,
                        deadline: pa.deadline,
                        activated_by_name: pa.activated_by_name,
                        providers: []
                    };
                }
                groups[key].providers.push(pa);
            }
            return Object.values(groups);
        },

        getRecordLabels(group) {
            const types = [];
            const first = group.providers[0];
            if (first?.request_mr) types.push('MR');
            if (first?.request_bill) types.push('Bill');
            if (first?.request_chart) types.push('Chart');
            if (first?.request_img) types.push('Img');
            if (first?.request_op) types.push('OP');
            return types.join(' ');
        },

        isGroupExpanded(caseId) {
            return !!this._expandedGroups[caseId];
        },

        toggleGroupExpand(caseId) {
            this._expandedGroups[caseId] = !this._expandedGroups[caseId];
        },

        async acceptAllProviders(group) {
            const count = group.providers.length;
            if (!confirm(`Accept all ${count} provider assignment(s) for Case #${group.case_number}?`)) return;
            try {
                const ids = group.providers.map(p => p.id);
                await api.put('case-providers/bulk-respond', { provider_ids: ids, action: 'accept' });
                showToast(`${count} provider assignment(s) accepted`, 'success');
                this.pendingProviderAssignments = this.pendingProviderAssignments.filter(a => a.case_id !== group.case_id);
            } catch (e) {
                showToast(e.data?.message || 'Failed to accept', 'error');
            }
        },

        async declineAllProviders(group) {
            const count = group.providers.length;
            const reason = prompt(`Decline all ${count} provider assignment(s) for Case #${group.case_number}?\n\nPlease enter the reason:`);
            if (reason === null) return;
            if (!reason.trim()) {
                showToast('Decline reason is required', 'error');
                return;
            }
            try {
                const ids = group.providers.map(p => p.id);
                await api.put('case-providers/bulk-respond', { provider_ids: ids, action: 'decline', reason: reason.trim() });
                showToast(`${count} provider assignment(s) declined`, 'success');
                this.pendingProviderAssignments = this.pendingProviderAssignments.filter(a => a.case_id !== group.case_id);
            } catch (e) {
                showToast(e.data?.message || 'Failed to decline', 'error');
            }
        },

        // Keep individual methods for backward compatibility
        async acceptProviderAssignment(cpId) {
            if (!confirm('Accept this provider assignment?')) return;
            try {
                await api.put('case-providers/' + cpId + '/respond', { action: 'accept' });
                showToast('Provider assignment accepted', 'success');
                this.pendingProviderAssignments = this.pendingProviderAssignments.filter(a => a.id !== cpId);
            } catch (e) {
                showToast(e.data?.message || 'Failed to accept', 'error');
            }
        },

        async declineProviderAssignment(cpId) {
            const reason = prompt('Please enter the reason for declining:');
            if (reason === null) return;
            if (!reason.trim()) {
                showToast('Decline reason is required', 'error');
                return;
            }
            try {
                await api.put('case-providers/' + cpId + '/respond', { action: 'decline', reason: reason.trim() });
                showToast('Provider assignment declined', 'success');
                this.pendingProviderAssignments = this.pendingProviderAssignments.filter(a => a.id !== cpId);
            } catch (e) {
                showToast(e.data?.message || 'Failed to decline', 'error');
            }
        },
    };
}
