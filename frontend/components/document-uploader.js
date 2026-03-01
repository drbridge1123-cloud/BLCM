/**
 * Document Uploader Component
 *
 * Reusable Alpine.js component for uploading documents to cases.
 * Supports drag-and-drop, progress tracking, file validation,
 * and visual PDF coordinate picker for provider name templates.
 *
 * Usage:
 *   <div x-data="documentUploader(123)" x-init="init()">
 *       <!-- Use component properties and methods -->
 *   </div>
 */

/**
 * Create document uploader Alpine component
 * @param {number} caseId - Case ID to upload documents to
 * @param {number|null} caseProviderId - Optional provider ID
 * @returns {object} Alpine.js component
 */
function documentUploader(caseId, caseProviderId = null) {
    return {
        // State
        caseId: caseId,
        caseProviderId: caseProviderId,
        documents: [],
        loading: false,
        uploading: false,
        uploadProgress: 0,
        dragOver: false,
        selectedFile: null,
        uploadForm: {
            notes: '',
            is_provider_template: false,
            provider_name_x: null,
            provider_name_y: null,
            provider_name_width: null,
            provider_name_height: null,
            provider_name_font_size: 12,
            use_date_overlay: false,
            date_x: null,
            date_y: null,
            date_width: null,
            date_height: null,
            date_font_size: 12,
            use_custom_text_overlay: false,
            custom_text_value: '',
            custom_text_x: null,
            custom_text_y: null,
            custom_text_width: null,
            custom_text_height: null,
            custom_text_font_size: 12
        },

        // PDF Preview state
        pdfRendered: false,
        pdfRenderScale: 1,
        pdfPageWidthPts: 0,
        pdfPageHeightPts: 0,
        pdfLoading: false,

        // Drag selection state
        isDragging: false,
        dragStartX: 0,
        dragStartY: 0,
        selectionMode: 'provider_name', // 'provider_name', 'date', or 'custom_text'
        providerSelectionRect: null,
        dateSelectionRect: null,
        customTextSelectionRect: null,
        _currentRenderTask: null,
        _renderDebounce: null,
        _renderId: 0,

        /**
         * Initialize component
         */
        async init() {
            await this.loadDocuments();

            // Watch for template mode toggle - render PDF preview when enabled
            this.$watch('uploadForm.is_provider_template', (value) => {
                if (value && this.selectedFile && this.selectedFile.type === 'application/pdf') {
                    this.$nextTick(() => this.renderPdfPreview());
                } else if (!value) {
                    this.pdfRendered = false;
                    this.providerSelectionRect = null;
                    this.dateSelectionRect = null;
                    this.customTextSelectionRect = null;
                }
            });
        },

        /**
         * Load documents for this case
         */
        async loadDocuments() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ case_id: this.caseId });
                if (this.caseProviderId) {
                    params.append('case_provider_id', this.caseProviderId);
                }

                const response = await api.get(`documents?${params.toString()}`);
                if (response.success) {
                    this.documents = Array.isArray(response.data) ? response.data : (response.data.documents || []);
                }
            } catch (error) {
                console.error('Failed to load documents:', error);
                showToast('Failed to load documents', 'error');
            } finally {
                this.loading = false;
            }
        },

        /**
         * Handle file input change
         * @param {Event} event - File input change event
         */
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.selectedFile = file;
                this.pdfRendered = false;
                this.providerSelectionRect = null;
                this.dateSelectionRect = null;
                this.selectionMode = 'provider_name';
                // If template mode is already on and file is PDF, render preview
                if (this.uploadForm.is_provider_template && file.type === 'application/pdf') {
                    this.$nextTick(() => this.renderPdfPreview());
                }
            }
        },

        /**
         * Handle drag over event
         * @param {DragEvent} event
         */
        handleDragOver(event) {
            event.preventDefault();
            this.dragOver = true;
        },

        /**
         * Handle drag leave event
         */
        handleDragLeave() {
            this.dragOver = false;
        },

        /**
         * Handle file drop
         * @param {DragEvent} event
         */
        handleDrop(event) {
            event.preventDefault();
            this.dragOver = false;

            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.selectedFile = files[0];
                // Trigger upload automatically after drop
                this.uploadDocument();
            }
        },

        // ==========================================
        // PDF Preview & Coordinate Picker
        // ==========================================

        /**
         * Render PDF preview (debounced to prevent concurrent render conflicts)
         */
        renderPdfPreview() {
            clearTimeout(this._renderDebounce);
            this.pdfLoading = true;
            this._renderDebounce = setTimeout(() => this._doRenderPdf(), 100);
        },

        /**
         * Internal: actual PDF render logic
         */
        async _doRenderPdf() {
            if (!this.selectedFile || this.selectedFile.type !== 'application/pdf') return;
            if (typeof pdfjsLib === 'undefined') {
                console.error('PDF.js library not loaded');
                this.pdfLoading = false;
                return;
            }

            const canvas = this.$refs.pdfCanvas;
            if (!canvas) {
                this.pdfLoading = false;
                return;
            }

            // Cancel any in-progress render and wait for it to finish
            if (this._currentRenderTask) {
                this._currentRenderTask.cancel();
                try { await this._currentRenderTask.promise; } catch(e) {}
                this._currentRenderTask = null;
            }

            const myRenderId = ++this._renderId;
            this.pdfRendered = false;

            try {
                const fileUrl = URL.createObjectURL(this.selectedFile);
                const pdf = await pdfjsLib.getDocument(fileUrl).promise;
                const page = await pdf.getPage(1);

                // Check if superseded by a newer render call
                if (this._renderId !== myRenderId) {
                    URL.revokeObjectURL(fileUrl);
                    return;
                }

                // Get page dimensions at scale 1.0 (in PDF points)
                const viewport1 = page.getViewport({ scale: 1.0 });
                this.pdfPageWidthPts = viewport1.width;
                this.pdfPageHeightPts = viewport1.height;

                // Calculate scale to fit container width
                const container = this.$refs.pdfCanvasContainer || canvas.parentElement;
                const maxWidth = (container && container.clientWidth > 10) ? container.clientWidth - 4 : 480;
                this.pdfRenderScale = maxWidth / viewport1.width;

                const viewport = page.getViewport({ scale: this.pdfRenderScale });

                canvas.width = viewport.width;
                canvas.height = viewport.height;
                canvas.style.width = viewport.width + 'px';
                canvas.style.height = viewport.height + 'px';

                const ctx = canvas.getContext('2d');
                const renderTask = page.render({
                    canvasContext: ctx,
                    viewport: viewport
                });
                this._currentRenderTask = renderTask;
                await renderTask.promise;
                this._currentRenderTask = null;

                // Check again after render
                if (this._renderId !== myRenderId) return;

                this.pdfRendered = true;

                // Size the overlay canvas to match
                const overlay = this.$refs.pdfOverlay;
                if (overlay) {
                    overlay.width = viewport.width;
                    overlay.height = viewport.height;
                    overlay.style.width = viewport.width + 'px';
                    overlay.style.height = viewport.height + 'px';
                }

                URL.revokeObjectURL(fileUrl);

                // Debug: compare canvas buffer vs display dimensions
                const debugOverlay = this.$refs.pdfOverlay;
                if (debugOverlay) {
                    const dispRect = debugOverlay.getBoundingClientRect();
                    console.log('[PDF Render Debug]', {
                        canvasBuffer: { w: canvas.width, h: canvas.height },
                        canvasStyle: { w: canvas.style.width, h: canvas.style.height },
                        overlayBuffer: { w: debugOverlay.width, h: debugOverlay.height },
                        overlayDisplay: { w: dispRect.width, h: dispRect.height },
                        ratio: { x: (debugOverlay.width / dispRect.width).toFixed(4), y: (debugOverlay.height / dispRect.height).toFixed(4) },
                        containerWidth: container ? container.clientWidth : 'N/A',
                        maxWidth: maxWidth,
                        pdfRenderScale: this.pdfRenderScale,
                        pagePts: { w: this.pdfPageWidthPts, h: this.pdfPageHeightPts }
                    });
                }

                // If coordinates already set (e.g. from previous selection), draw them
                this.drawExistingSelections();
            } catch (error) {
                if (error?.name === 'RenderingCancelledException') return;
                console.error('Failed to render PDF preview:', error);
            } finally {
                if (this._renderId === myRenderId) {
                    this.pdfLoading = false;
                }
            }
        },

        /**
         * Draw existing selections from stored mm coordinates
         */
        drawExistingSelections() {
            const canvas = this.$refs.pdfCanvas;
            if (!canvas) return;

            const cw = canvas.width;
            const ch = canvas.height;
            const pageWidthMm = this.pdfPageWidthPts * 25.4 / 72;
            const pageHeightMm = this.pdfPageHeightPts * 25.4 / 72;
            if (!pageWidthMm || !pageHeightMm || cw < 1 || ch < 1) return;

            // Provider name
            if (this.uploadForm.provider_name_x && this.uploadForm.provider_name_y) {
                this.providerSelectionRect = {
                    x: (this.uploadForm.provider_name_x / pageWidthMm) * cw,
                    y: (this.uploadForm.provider_name_y / pageHeightMm) * ch,
                    width: (this.uploadForm.provider_name_width / pageWidthMm) * cw,
                    height: (this.uploadForm.provider_name_height / pageHeightMm) * ch
                };
            }

            // Date
            if (this.uploadForm.use_date_overlay && this.uploadForm.date_x && this.uploadForm.date_y) {
                this.dateSelectionRect = {
                    x: (this.uploadForm.date_x / pageWidthMm) * cw,
                    y: (this.uploadForm.date_y / pageHeightMm) * ch,
                    width: (this.uploadForm.date_width / pageWidthMm) * cw,
                    height: (this.uploadForm.date_height / pageHeightMm) * ch
                };
            }

            // Custom text
            if (this.uploadForm.use_custom_text_overlay && this.uploadForm.custom_text_x && this.uploadForm.custom_text_y) {
                this.customTextSelectionRect = {
                    x: (this.uploadForm.custom_text_x / pageWidthMm) * cw,
                    y: (this.uploadForm.custom_text_y / pageHeightMm) * ch,
                    width: (this.uploadForm.custom_text_width / pageWidthMm) * cw,
                    height: (this.uploadForm.custom_text_height / pageHeightMm) * ch
                };
            }

            this.drawAllOverlays();
        },

        /**
         * Get canvas-relative coordinates from mouse/touch event
         * Scales CSS display pixels to canvas buffer pixels for accuracy
         */
        getCanvasCoords(event) {
            const el = this.$refs.pdfOverlay || this.$refs.pdfCanvas;
            const rect = el.getBoundingClientRect();

            // Scale CSS display pixels → canvas buffer pixels
            // Handles cases where display size differs from buffer (DPI scaling, CSS constraints)
            const scaleX = el.width / rect.width;
            const scaleY = el.height / rect.height;

            let clientX, clientY;
            if (event.touches && event.touches.length > 0) {
                clientX = event.touches[0].clientX;
                clientY = event.touches[0].clientY;
            } else {
                clientX = event.clientX;
                clientY = event.clientY;
            }

            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        },

        /**
         * Canvas mousedown/touchstart - start drag selection
         */
        onCanvasMouseDown(event) {
            event.preventDefault();
            const coords = this.getCanvasCoords(event);
            this.isDragging = true;
            this.dragStartX = coords.x;
            this.dragStartY = coords.y;
        },

        /**
         * Canvas mousemove/touchmove - update drag selection rectangle
         */
        onCanvasMouseMove(event) {
            if (!this.isDragging) return;
            event.preventDefault();
            const coords = this.getCanvasCoords(event);

            const currentRect = {
                x: Math.min(this.dragStartX, coords.x),
                y: Math.min(this.dragStartY, coords.y),
                width: Math.abs(coords.x - this.dragStartX),
                height: Math.abs(coords.y - this.dragStartY)
            };

            // Update the active selection
            if (this.selectionMode === 'date') {
                this.dateSelectionRect = currentRect;
            } else if (this.selectionMode === 'custom_text') {
                this.customTextSelectionRect = currentRect;
            } else {
                this.providerSelectionRect = currentRect;
            }
            this.drawAllOverlays();
        },

        /**
         * Canvas mouseup/touchend - finalize drag selection
         */
        onCanvasMouseUp(event) {
            if (!this.isDragging) return;
            this.isDragging = false;

            const activeRect = this.selectionMode === 'date'
                ? this.dateSelectionRect
                : this.selectionMode === 'custom_text'
                    ? this.customTextSelectionRect
                    : this.providerSelectionRect;

            // Require minimum selection size
            if (activeRect && activeRect.width > 5 && activeRect.height > 3) {
                this.convertCanvasToMm();
            } else {
                // Too small, remove it
                if (this.selectionMode === 'date') {
                    this.dateSelectionRect = null;
                } else if (this.selectionMode === 'custom_text') {
                    this.customTextSelectionRect = null;
                } else {
                    this.providerSelectionRect = null;
                }
                this.drawAllOverlays();
            }
        },

        /**
         * Convert canvas pixel selection to FPDI mm coordinates
         * Uses canvas buffer dimensions (coordinates from getCanvasCoords are in buffer pixels)
         */
        convertCanvasToMm() {
            const canvas = this.$refs.pdfCanvas;
            if (!canvas || !this.pdfPageWidthPts || !this.pdfPageHeightPts) return;

            // Page dimensions in mm
            const pageWidthMm = this.pdfPageWidthPts * 25.4 / 72;
            const pageHeightMm = this.pdfPageHeightPts * 25.4 / 72;

            // Use canvas buffer dimensions (matches buffer-pixel coords from getCanvasCoords)
            const cw = canvas.width;
            const ch = canvas.height;
            if (cw < 1 || ch < 1) return;

            const toMmX = (px) => Math.round((px / cw) * pageWidthMm * 10) / 10;
            const toMmY = (py) => Math.round((py / ch) * pageHeightMm * 10) / 10;

            if (this.selectionMode === 'date' && this.dateSelectionRect) {
                const r = this.dateSelectionRect;
                this.uploadForm.use_date_overlay = true;
                this.uploadForm.date_x = toMmX(r.x);
                this.uploadForm.date_y = toMmY(r.y);
                this.uploadForm.date_width = toMmX(r.width);
                this.uploadForm.date_height = toMmY(r.height);
            } else if (this.selectionMode === 'custom_text' && this.customTextSelectionRect) {
                const r = this.customTextSelectionRect;
                this.uploadForm.use_custom_text_overlay = true;
                this.uploadForm.custom_text_x = toMmX(r.x);
                this.uploadForm.custom_text_y = toMmY(r.y);
                this.uploadForm.custom_text_width = toMmX(r.width);
                this.uploadForm.custom_text_height = toMmY(r.height);
            } else if (this.providerSelectionRect) {
                const r = this.providerSelectionRect;
                this.uploadForm.provider_name_x = toMmX(r.x);
                this.uploadForm.provider_name_y = toMmY(r.y);
                this.uploadForm.provider_name_width = toMmX(r.width);
                this.uploadForm.provider_name_height = toMmY(r.height);
            }

            // Draw verification overlay (red dashed rect at converted mm coordinates)
            this.drawVerificationOverlay();

            // Debug logging for coordinate tracing
            const rect = this.selectionMode === 'date' ? this.dateSelectionRect : this.selectionMode === 'custom_text' ? this.customTextSelectionRect : this.providerSelectionRect;
            const el = this.$refs.pdfOverlay || canvas;
            const displayRect = el.getBoundingClientRect();
            console.log('[PDF Coord Debug]', {
                mode: this.selectionMode,
                canvasBuffer: { w: cw, h: ch },
                displaySize: { w: displayRect.width, h: displayRect.height },
                bufferVsDisplay: { xRatio: (cw / displayRect.width).toFixed(4), yRatio: (ch / displayRect.height).toFixed(4) },
                pageMm: { w: pageWidthMm, h: pageHeightMm },
                selectionPx: rect,
                resultMm: this.selectionMode === 'date'
                    ? { x: this.uploadForm.date_x, y: this.uploadForm.date_y, w: this.uploadForm.date_width, h: this.uploadForm.date_height }
                    : { x: this.uploadForm.provider_name_x, y: this.uploadForm.provider_name_y, w: this.uploadForm.provider_name_width, h: this.uploadForm.provider_name_height }
            });
        },

        /**
         * Draw red verification rect showing where mm coordinates map to on canvas
         * If this doesn't match the selection rect, there's a conversion error
         */
        drawVerificationOverlay() {
            const overlay = this.$refs.pdfOverlay;
            const canvas = this.$refs.pdfCanvas;
            if (!overlay || !canvas) return;

            const cw = canvas.width;
            const ch = canvas.height;
            const pageWidthMm = this.pdfPageWidthPts * 25.4 / 72;
            const pageHeightMm = this.pdfPageHeightPts * 25.4 / 72;
            if (!pageWidthMm || !pageHeightMm) return;

            const ctx = overlay.getContext('2d');

            // Draw red dashed verification rects for all stored mm coordinates
            ctx.setLineDash([4, 4]);
            ctx.lineWidth = 1.5;

            if (this.uploadForm.provider_name_x && this.uploadForm.provider_name_y) {
                const vx = (this.uploadForm.provider_name_x / pageWidthMm) * cw;
                const vy = (this.uploadForm.provider_name_y / pageHeightMm) * ch;
                const vw = (this.uploadForm.provider_name_width / pageWidthMm) * cw;
                const vh = (this.uploadForm.provider_name_height / pageHeightMm) * ch;
                ctx.strokeStyle = 'rgba(255, 0, 0, 0.8)';
                ctx.strokeRect(vx, vy, vw, vh);
            }

            if (this.uploadForm.date_x && this.uploadForm.date_y) {
                const vx = (this.uploadForm.date_x / pageWidthMm) * cw;
                const vy = (this.uploadForm.date_y / pageHeightMm) * ch;
                const vw = (this.uploadForm.date_width / pageWidthMm) * cw;
                const vh = (this.uploadForm.date_height / pageHeightMm) * ch;
                ctx.strokeStyle = 'rgba(255, 0, 0, 0.8)';
                ctx.strokeRect(vx, vy, vw, vh);
            }

            if (this.uploadForm.custom_text_x && this.uploadForm.custom_text_y) {
                const vx = (this.uploadForm.custom_text_x / pageWidthMm) * cw;
                const vy = (this.uploadForm.custom_text_y / pageHeightMm) * ch;
                const vw = (this.uploadForm.custom_text_width / pageWidthMm) * cw;
                const vh = (this.uploadForm.custom_text_height / pageHeightMm) * ch;
                ctx.strokeStyle = 'rgba(255, 0, 0, 0.8)';
                ctx.strokeRect(vx, vy, vw, vh);
            }

            ctx.setLineDash([]);
        },

        /**
         * Draw all selection overlays on canvas
         */
        drawAllOverlays() {
            const overlay = this.$refs.pdfOverlay;
            if (!overlay) return;

            const ctx = overlay.getContext('2d');
            ctx.clearRect(0, 0, overlay.width, overlay.height);

            // Draw provider name selection (blue)
            if (this.providerSelectionRect) {
                const r = this.providerSelectionRect;
                ctx.fillStyle = 'rgba(59, 130, 246, 0.2)';
                ctx.fillRect(r.x, r.y, r.width, r.height);
                ctx.strokeStyle = 'rgba(59, 130, 246, 0.8)';
                ctx.lineWidth = 2;
                ctx.strokeRect(r.x, r.y, r.width, r.height);
                ctx.fillStyle = 'rgba(59, 130, 246, 0.9)';
                ctx.font = '11px Arial';
                ctx.fillText('Provider Name', r.x + 4, r.y > 14 ? r.y - 4 : r.y + r.height + 13);
            }

            // Draw date selection (green)
            if (this.dateSelectionRect) {
                const r = this.dateSelectionRect;
                ctx.fillStyle = 'rgba(16, 185, 129, 0.2)';
                ctx.fillRect(r.x, r.y, r.width, r.height);
                ctx.strokeStyle = 'rgba(16, 185, 129, 0.8)';
                ctx.lineWidth = 2;
                ctx.strokeRect(r.x, r.y, r.width, r.height);
                ctx.fillStyle = 'rgba(16, 185, 129, 0.9)';
                ctx.font = '11px Arial';
                ctx.fillText('Date', r.x + 4, r.y > 14 ? r.y - 4 : r.y + r.height + 13);
            }

            // Draw custom text selection (orange)
            if (this.customTextSelectionRect) {
                const r = this.customTextSelectionRect;
                ctx.fillStyle = 'rgba(245, 158, 11, 0.2)';
                ctx.fillRect(r.x, r.y, r.width, r.height);
                ctx.strokeStyle = 'rgba(245, 158, 11, 0.8)';
                ctx.lineWidth = 2;
                ctx.strokeRect(r.x, r.y, r.width, r.height);
                ctx.fillStyle = 'rgba(245, 158, 11, 0.9)';
                ctx.font = '11px Arial';
                ctx.fillText('Custom Text', r.x + 4, r.y > 14 ? r.y - 4 : r.y + r.height + 13);
            }
        },

        /**
         * Clear the selection overlay canvas
         */
        clearSelectionOverlay() {
            const overlay = this.$refs.pdfOverlay;
            if (!overlay) return;
            const ctx = overlay.getContext('2d');
            ctx.clearRect(0, 0, overlay.width, overlay.height);
        },

        /**
         * Reset all coordinate selections
         */
        resetCoordinateSelection() {
            this.providerSelectionRect = null;
            this.dateSelectionRect = null;
            this.customTextSelectionRect = null;
            this.selectionMode = 'provider_name';
            this.uploadForm.provider_name_x = null;
            this.uploadForm.provider_name_y = null;
            this.uploadForm.provider_name_width = null;
            this.uploadForm.provider_name_height = null;
            this.uploadForm.use_date_overlay = false;
            this.uploadForm.date_x = null;
            this.uploadForm.date_y = null;
            this.uploadForm.date_width = null;
            this.uploadForm.date_height = null;
            this.uploadForm.use_custom_text_overlay = false;
            this.uploadForm.custom_text_value = '';
            this.uploadForm.custom_text_x = null;
            this.uploadForm.custom_text_y = null;
            this.uploadForm.custom_text_width = null;
            this.uploadForm.custom_text_height = null;
            this.clearSelectionOverlay();
        },

        /**
         * Reset only provider name selection
         */
        resetProviderSelection() {
            this.providerSelectionRect = null;
            this.uploadForm.provider_name_x = null;
            this.uploadForm.provider_name_y = null;
            this.uploadForm.provider_name_width = null;
            this.uploadForm.provider_name_height = null;
            this.selectionMode = 'provider_name';
            this.drawAllOverlays();
        },

        /**
         * Reset only date selection
         */
        resetDateSelection() {
            this.dateSelectionRect = null;
            this.uploadForm.date_x = null;
            this.uploadForm.date_y = null;
            this.uploadForm.date_width = null;
            this.uploadForm.date_height = null;
            this.selectionMode = 'date';
            this.drawAllOverlays();
        },

        /**
         * Reset only custom text selection
         */
        resetCustomTextSelection() {
            this.customTextSelectionRect = null;
            this.uploadForm.custom_text_x = null;
            this.uploadForm.custom_text_y = null;
            this.uploadForm.custom_text_width = null;
            this.uploadForm.custom_text_height = null;
            this.selectionMode = 'custom_text';
            this.drawAllOverlays();
        },

        /**
         * Check if selected file is a PDF
         */
        isPdfFile() {
            return this.selectedFile && this.selectedFile.type === 'application/pdf';
        },

        /**
         * Check if provider name coordinates have been set
         */
        hasCoordinates() {
            return this.uploadForm.provider_name_x && this.uploadForm.provider_name_y &&
                   this.uploadForm.provider_name_width && this.uploadForm.provider_name_height;
        },

        /**
         * Check if date coordinates have been set
         */
        hasDateCoordinates() {
            return this.uploadForm.date_x && this.uploadForm.date_y &&
                   this.uploadForm.date_width && this.uploadForm.date_height;
        },

        /**
         * Check if custom text coordinates have been set
         */
        hasCustomTextCoordinates() {
            return this.uploadForm.custom_text_x && this.uploadForm.custom_text_y &&
                   this.uploadForm.custom_text_width && this.uploadForm.custom_text_height;
        },

        // ==========================================
        // Upload & Document Management
        // ==========================================

        /**
         * Upload selected document
         */
        async uploadDocument() {
            if (!this.selectedFile) {
                showToast('Please select a file', 'warning');
                return;
            }

            // Validate file size (10MB max)
            const maxSize = 10 * 1024 * 1024;
            if (this.selectedFile.size > maxSize) {
                showToast('File size exceeds 10MB limit', 'error');
                return;
            }

            // Validate file type
            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'image/jpeg',
                'image/png',
                'image/tiff'
            ];
            if (!allowedTypes.includes(this.selectedFile.type)) {
                showToast('Invalid file type. Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, TIFF', 'error');
                return;
            }

            // Validate template coordinates if provider template is enabled
            if (this.uploadForm.is_provider_template) {
                if (!this.uploadForm.provider_name_x || !this.uploadForm.provider_name_y ||
                    !this.uploadForm.provider_name_width || !this.uploadForm.provider_name_height) {
                    showToast('Please drag on the PDF preview to select the provider name area', 'warning');
                    return;
                }
            }

            this.uploading = true;
            this.uploadProgress = 0;

            try {
                const formData = new FormData();
                formData.append('file', this.selectedFile);
                formData.append('case_id', this.caseId);
                formData.append('document_type', 'other');
                if (this.caseProviderId) {
                    formData.append('case_provider_id', this.caseProviderId);
                }
                if (this.uploadForm.notes) {
                    formData.append('notes', this.uploadForm.notes);
                }

                // Add provider template fields if enabled
                if (this.uploadForm.is_provider_template) {
                    formData.append('is_provider_template', '1');
                    if (this.uploadForm.provider_name_x) formData.append('provider_name_x', this.uploadForm.provider_name_x);
                    if (this.uploadForm.provider_name_y) formData.append('provider_name_y', this.uploadForm.provider_name_y);
                    if (this.uploadForm.provider_name_width) formData.append('provider_name_width', this.uploadForm.provider_name_width);
                    if (this.uploadForm.provider_name_height) formData.append('provider_name_height', this.uploadForm.provider_name_height);
                    formData.append('provider_name_font_size', this.uploadForm.provider_name_font_size);
                }

                // Add date overlay fields if enabled
                if (this.uploadForm.use_date_overlay) {
                    formData.append('use_date_overlay', '1');
                    if (this.uploadForm.date_x) formData.append('date_x', this.uploadForm.date_x);
                    if (this.uploadForm.date_y) formData.append('date_y', this.uploadForm.date_y);
                    if (this.uploadForm.date_width) formData.append('date_width', this.uploadForm.date_width);
                    if (this.uploadForm.date_height) formData.append('date_height', this.uploadForm.date_height);
                    formData.append('date_font_size', this.uploadForm.date_font_size);
                }

                // Add custom text overlay fields if enabled
                if (this.uploadForm.use_custom_text_overlay && this.uploadForm.custom_text_x) {
                    formData.append('use_custom_text_overlay', '1');
                    formData.append('custom_text_value', this.uploadForm.custom_text_value);
                    if (this.uploadForm.custom_text_x) formData.append('custom_text_x', this.uploadForm.custom_text_x);
                    if (this.uploadForm.custom_text_y) formData.append('custom_text_y', this.uploadForm.custom_text_y);
                    if (this.uploadForm.custom_text_width) formData.append('custom_text_width', this.uploadForm.custom_text_width);
                    if (this.uploadForm.custom_text_height) formData.append('custom_text_height', this.uploadForm.custom_text_height);
                    formData.append('custom_text_font_size', this.uploadForm.custom_text_font_size);
                }

                // Upload with progress tracking
                const response = await api.upload('documents/upload', formData, (progress) => {
                    this.uploadProgress = progress;
                });

                if (response.success) {
                    showToast('Document uploaded successfully', 'success');

                    // Reset form
                    this.selectedFile = null;
                    this.uploadForm.notes = '';
                    this.uploadProgress = 0;
                    this.pdfRendered = false;
                    this.providerSelectionRect = null;
                    this.dateSelectionRect = null;
                    this.selectionMode = 'provider_name';

                    // Reload documents
                    await this.loadDocuments();

                    // Dispatch event on window so all listeners (including other Alpine scopes) receive it
                    window.dispatchEvent(new CustomEvent('document-uploaded', {
                        detail: { document: response.data }
                    }));
                }
            } catch (error) {
                console.error('Upload failed:', error);
                showToast(error.data?.message || 'Upload failed', 'error');
            } finally {
                this.uploading = false;
            }
        },

        /**
         * Download document
         * @param {number} documentId
         */
        async downloadDocument(documentId) {
            try {
                // Open download in new window
                const url = `/CMC/backend/api/documents/${documentId}/download`;
                window.open(url, '_blank');
            } catch (error) {
                console.error('Download failed:', error);
                showToast('Download failed', 'error');
            }
        },

        /**
         * Delete document
         * @param {number} documentId
         * @param {string} fileName
         */
        async deleteDocument(documentId, fileName) {
            if (!confirm(`Are you sure you want to delete "${fileName}"?`)) {
                return;
            }

            try {
                const response = await api.delete(`documents/${documentId}`);
                if (response.success) {
                    showToast('Document deleted', 'success');
                    await this.loadDocuments();

                    // Dispatch event on window so all listeners (including other Alpine scopes) receive it
                    window.dispatchEvent(new CustomEvent('document-deleted', {
                        detail: { documentId: documentId }
                    }));
                }
            } catch (error) {
                console.error('Delete failed:', error);
                showToast(error.data?.message || 'Delete failed', 'error');
            }
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
         * Format date
         * @param {string} dateStr
         * @returns {string}
         */
        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        },

        /**
         * Generate provider-specific version of a template document
         * @param {number} documentId - Template document ID
         * @param {string} providerName - Provider name to insert
         */
        async generateProviderVersion(documentId, providerName, customText = null) {
            if (!providerName || providerName.trim() === '') {
                showToast('Please enter provider name', 'warning');
                return;
            }

            try {
                const payload = {
                    document_id: documentId,
                    provider_name: providerName,
                    case_id: this.caseId
                };
                if (customText !== null) {
                    payload.custom_text_value = customText;
                }
                const response = await api.post('documents/generate-provider-version', payload);

                if (response.success) {
                    showToast('Provider-specific document generated', 'success');
                    await this.loadDocuments();

                    // Dispatch event on window so all listeners (including other Alpine scopes) receive it
                    window.dispatchEvent(new CustomEvent('document-generated', {
                        detail: { document: response.data }
                    }));
                }
            } catch (error) {
                console.error('Generation failed:', error);
                showToast(error.data?.message || 'Failed to generate document', 'error');
            }
        },

        /**
         * Prompt user to generate provider version
         * @param {object} doc - Template document
         */
        promptGenerateProviderVersion(doc) {
            // Check if coordinates are configured
            if (!doc.provider_name_x || !doc.provider_name_y || !doc.provider_name_width || !doc.provider_name_height) {
                showToast('Template coordinates not configured. Please re-upload with coordinates set.', 'warning');
                return;
            }

            const providerName = prompt(`Enter provider name for ${doc.original_file_name}:`);
            if (!providerName) return;

            // If custom text overlay is configured, ask for custom text
            let customText = null;
            if (doc.use_custom_text_overlay == 1 && doc.custom_text_x && doc.custom_text_y) {
                customText = prompt('Enter custom text to overlay on PDF:', doc.custom_text_value || '');
                if (customText === null) return; // User cancelled
            }

            this.generateProviderVersion(doc.id, providerName.trim(), customText);
        }
    };
}
