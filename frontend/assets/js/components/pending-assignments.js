/**
 * Pending Assignments Mixin
 * Shows yellow banner with Accept/Decline for cases and providers assigned to current user.
 * Spread into any Alpine component: ...pendingAssignmentsMixin()
 */
function pendingAssignmentsMixin() {
    return {
        pendingCaseAssignments: [],
        pendingProviderAssignments: [],

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

        // ── Provider Assignments ──

        async loadPendingProviderAssignments() {
            try {
                const res = await api.get('billing/pending-assignments');
                this.pendingProviderAssignments = res.data || [];
            } catch (e) {
                this.pendingProviderAssignments = [];
            }
        },

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
