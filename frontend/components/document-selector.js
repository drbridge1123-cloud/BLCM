/**
 * Document Selector Component
 *
 * Reusable Alpine.js component for selecting documents to attach to requests.
 * Displays checkboxes for available case documents.
 *
 * Usage:
 *   <div x-data="documentSelector(123)" x-init="init()">
 *       <!-- Use component properties and methods -->
 *   </div>
 */

/**
 * Create document selector Alpine component
 * @param {number} caseId - Case ID to load documents from
 * @param {number|null} caseProviderId - Optional provider ID to filter documents
 * @returns {object} Alpine.js component
 */
function documentSelector(caseId, caseProviderId = null) {
    return {
        // State
        caseId: caseId,
        caseProviderId: caseProviderId,
        documents: [],
        selectedDocumentIds: [],
        loading: false,

        /**
         * Initialize component
         */
        async init() {
            await this.loadDocuments();
        },

        /**
         * Load available documents for this case
         */
        async loadDocuments() {
            // Don't load if caseId is missing
            if (!this.caseId) {
                this.documents = [];
                this.loading = false;
                return;
            }

            this.loading = true;
            try {
                const params = new URLSearchParams({ case_id: this.caseId });
                if (this.caseProviderId) {
                    params.append('case_provider_id', this.caseProviderId);
                }

                const response = await api.get(`documents?${params.toString()}`);
                if (response.success) {
                    this.documents = response.data.documents || [];
                }
            } catch (error) {
                console.error('Failed to load documents:', error);
                // Don't show toast for missing documents - it's normal
            } finally {
                this.loading = false;
            }
        },

        /**
         * Toggle document selection
         * @param {number} documentId
         */
        toggleDocument(documentId) {
            const index = this.selectedDocumentIds.indexOf(documentId);
            if (index > -1) {
                this.selectedDocumentIds.splice(index, 1);
            } else {
                this.selectedDocumentIds.push(documentId);
            }

            // Dispatch event for parent components
            this.$dispatch('documents-selected', {
                documentIds: this.selectedDocumentIds,
                documents: this.getSelectedDocuments()
            });
        },

        /**
         * Check if document is selected
         * @param {number} documentId
         * @returns {boolean}
         */
        isSelected(documentId) {
            return this.selectedDocumentIds.includes(documentId);
        },

        /**
         * Select all documents
         */
        selectAll() {
            this.selectedDocumentIds = this.documents.map(d => d.id);
            this.$dispatch('documents-selected', {
                documentIds: this.selectedDocumentIds,
                documents: this.getSelectedDocuments()
            });
        },

        /**
         * Deselect all documents
         */
        deselectAll() {
            this.selectedDocumentIds = [];
            this.$dispatch('documents-selected', {
                documentIds: this.selectedDocumentIds,
                documents: []
            });
        },

        /**
         * Get selected document objects
         * @returns {array}
         */
        getSelectedDocuments() {
            return this.documents.filter(d => this.selectedDocumentIds.includes(d.id));
        },

        /**
         * Get file extension from filename
         * @param {string} filename
         * @returns {string}
         */
        getFileExtension(filename) {
            const parts = filename.split('.');
            return parts.length > 1 ? parts.pop().toUpperCase() : '';
        },

        /**
         * Reset selection
         */
        reset() {
            this.selectedDocumentIds = [];
        },

        /**
         * Set selected document IDs programmatically
         * @param {array} documentIds
         */
        setSelectedDocuments(documentIds) {
            this.selectedDocumentIds = documentIds || [];
        }
    };
}
