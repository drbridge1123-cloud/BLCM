            <!-- Documents Section -->
            <div class="doc-panel c1-section c1-group-end" data-panel
                x-data="{...documentUploader(caseId), docsOpen: false}" x-init="init()">
                <div class="c1-section-header" :class="docsOpen && 'is-open'" @click="docsOpen = !docsOpen; if(docsOpen) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
                    <div class="flex items-center gap-2.5">
                        <span class="c1-num c1-num-olive">03</span>
                        <h3 class="panel-title">Documents</h3>
                        <span class="panel-count" x-text="documents.length"></span>
                    </div>
                    <button @click.stop="docsOpen = true; $nextTick(() => $refs.fileInput.click())"
                        class="doc-upload-btn" style="width:auto; padding:5px 14px;">
                        Upload
                    </button>
                </div>
                <div x-show="docsOpen" x-collapse>
                <div class="doc-body">
                    <!-- Upload Area -->
                    <div @dragover="handleDragOver($event)"
                        @dragleave="handleDragLeave()"
                        @drop="handleDrop($event)">
                        <input type="file" x-ref="fileInput" @change="handleFileSelect($event)"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.tif,.tiff"
                            class="hidden">

                        <template x-if="!selectedFile">
                            <div class="doc-upload-zone" :class="dragOver ? 'drag-active' : ''">
                                <p class="doc-upload-hint">
                                    Drag files here or
                                    <a @click="$refs.fileInput.click()">browse</a>
                                    — PDF, DOC, XLS, JPG, PNG, TIFF (max 10MB)
                                </p>
                            </div>
                        </template>

                        <template x-if="selectedFile">
                            <div class="doc-selected-card">
                                <div class="doc-selected-header">
                                    <div class="doc-selected-label">Selected File</div>
                                    <div class="doc-selected-label" style="text-align:right;">Notes</div>
                                </div>
                                <div class="doc-selected-body" style="display:flex; gap:12px; align-items:center; border-bottom:1px solid #d0cdc5;">
                                    <div class="doc-icon" x-text="selectedFile.name.split('.').pop()"></div>
                                    <div style="flex:1; min-width:0;">
                                        <p style="font-size:13px; font-weight:500; color:#1a2535; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="selectedFile.name"></p>
                                        <p style="font-size:11px; color:#8a8a82;" x-text="(selectedFile.size / 1024 / 1024).toFixed(2) + ' MB'"></p>
                                    </div>
                                    <button type="button" @click="selectedFile = null"
                                        class="icon-btn icon-btn-sm flex-shrink-0" title="Remove">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div style="padding:10px 16px; display:flex; gap:10px; align-items:center; border-bottom:1px solid #d0cdc5;">
                                    <input type="text" x-model="uploadForm.notes" placeholder="Add notes (optional)..."
                                        class="doc-form-input" style="flex:1;">
                                </div>

                                <!-- Provider Template Mode -->
                                <div style="padding:10px 16px; border-bottom:1px solid #d0cdc5;">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" x-model="uploadForm.is_provider_template"
                                            class="rounded border-gray-300 text-gold focus:ring-gold">
                                        <span style="font-size:12px; font-weight:500; color:#1a2535;">
                                            This is a provider name template
                                            <span style="color:#8a8a82;">(allows changing provider name for different providers)</span>
                                        </span>
                                    </label>

                                        <!-- Template Coordinate Picker (shown when template mode is enabled) -->
                                        <template x-if="uploadForm.is_provider_template">
                                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg space-y-3">

                                                <!-- Selection Mode Tabs -->
                                                <div class="flex items-center gap-1 bg-blue-100 rounded-lg p-0.5">
                                                    <button type="button" @click="selectionMode = 'provider_name'"
                                                        class="flex-1 px-2 py-1.5 rounded-md text-xs font-medium transition-colors"
                                                        :class="selectionMode === 'provider_name' ? 'bg-white text-blue-800 shadow-sm' : 'text-blue-600 hover:text-blue-800'">
                                                        Provider Name
                                                        <span x-show="hasCoordinates()" class="text-green-600 ml-1">&#10003;</span>
                                                    </button>
                                                    <button type="button" @click="selectionMode = 'custom_text'; uploadForm.use_custom_text_overlay = true"
                                                        class="flex-1 px-2 py-1.5 rounded-md text-xs font-medium transition-colors"
                                                        :class="selectionMode === 'custom_text' ? 'bg-white text-amber-800 shadow-sm' : 'text-blue-600 hover:text-blue-800'">
                                                        Custom Text
                                                        <span x-show="hasCustomTextCoordinates()" class="text-green-600 ml-1">&#10003;</span>
                                                    </button>
                                                    <button type="button" @click="selectionMode = 'date'; uploadForm.use_date_overlay = true"
                                                        class="flex-1 px-2 py-1.5 rounded-md text-xs font-medium transition-colors"
                                                        :class="selectionMode === 'date' ? 'bg-white text-emerald-800 shadow-sm' : 'text-blue-600 hover:text-blue-800'">
                                                        Date (Optional)
                                                        <span x-show="hasDateCoordinates()" class="text-green-600 ml-1">&#10003;</span>
                                                    </button>
                                                </div>

                                                <!-- Current mode info -->
                                                <div class="flex items-center justify-between">
                                                    <p class="text-xs font-medium"
                                                        :class="selectionMode === 'date' ? 'text-emerald-700' : selectionMode === 'custom_text' ? 'text-amber-700' : 'text-blue-800'"
                                                        x-text="selectionMode === 'date' ? 'Select Date Location' : selectionMode === 'custom_text' ? 'Select Custom Text Location' : 'Select Provider Name Location'"></p>
                                                    <div class="flex gap-2">
                                                        <template x-if="selectionMode === 'provider_name' && hasCoordinates()">
                                                            <button type="button" @click="resetProviderSelection()"
                                                                class="text-xs text-blue-600 hover:text-blue-800 underline">Reset</button>
                                                        </template>
                                                        <template x-if="selectionMode === 'date' && hasDateCoordinates()">
                                                            <button type="button" @click="resetDateSelection()"
                                                                class="text-xs text-emerald-600 hover:text-emerald-800 underline">Reset</button>
                                                        </template>
                                                        <template x-if="selectionMode === 'custom_text' && hasCustomTextCoordinates()">
                                                            <button type="button" @click="resetCustomTextSelection()"
                                                                class="text-xs text-amber-600 hover:text-amber-800 underline">Reset</button>
                                                        </template>
                                                    </div>
                                                </div>

                                                <!-- Custom Text Input (above PDF so it's always visible) -->
                                                <template x-if="uploadForm.use_custom_text_overlay">
                                                    <div>
                                                        <label class="text-xs text-amber-700 font-medium">Custom Text:</label>
                                                        <input type="text" x-model="uploadForm.custom_text_value" placeholder="Enter text to overlay on PDF..."
                                                            class="w-full mt-1 px-2 py-1.5 border border-amber-300 rounded text-sm focus:ring-amber-500 focus:border-amber-500">
                                                    </div>
                                                </template>

                                                <!-- PDF Preview Canvas (only for PDF files) -->
                                                <template x-if="isPdfFile()">
                                                    <div>
                                                        <!-- Loading indicator -->
                                                        <div x-show="pdfLoading && !pdfRendered" class="flex items-center justify-center py-8 bg-white rounded border border-blue-200">
                                                            <div class="spinner mr-2"></div>
                                                            <span class="text-xs text-v2-text-light">Loading PDF preview...</span>
                                                        </div>

                                                        <!-- Canvas container -->
                                                        <div class="relative rounded overflow-hidden border-2 transition-colors"
                                                            :class="selectionMode === 'date' ? 'border-emerald-300' : selectionMode === 'custom_text' ? 'border-amber-300' : 'border-blue-300'"
                                                            style="cursor: crosshair;" x-ref="pdfCanvasContainer">
                                                            <canvas x-ref="pdfCanvas" class="block"></canvas>
                                                            <canvas x-ref="pdfOverlay" class="absolute top-0 left-0"
                                                                @mousedown="onCanvasMouseDown($event)"
                                                                @mousemove="onCanvasMouseMove($event)"
                                                                @mouseup="onCanvasMouseUp($event)"
                                                                @mouseleave="onCanvasMouseUp($event)"
                                                                @touchstart.prevent="onCanvasMouseDown($event)"
                                                                @touchmove.prevent="onCanvasMouseMove($event)"
                                                                @touchend.prevent="onCanvasMouseUp($event)"></canvas>
                                                        </div>

                                                        <!-- Instructions for current mode -->
                                                        <template x-if="pdfRendered">
                                                            <div class="mt-2 space-y-1">
                                                                <template x-if="selectionMode === 'provider_name' && !hasCoordinates()">
                                                                    <p class="text-xs text-blue-600">
                                                                        Drag on the PDF to select the Provider Name area.
                                                                    </p>
                                                                </template>
                                                                <template x-if="selectionMode === 'provider_name' && hasCoordinates()">
                                                                    <p class="text-xs text-green-600">
                                                                        Provider Name area selected (blue). You can also select a Date area.
                                                                    </p>
                                                                </template>
                                                                <template x-if="selectionMode === 'date' && !hasDateCoordinates()">
                                                                    <p class="text-xs text-emerald-600">
                                                                        Drag on the PDF to select the Date area (today's date will be inserted).
                                                                    </p>
                                                                </template>
                                                                <template x-if="selectionMode === 'date' && hasDateCoordinates()">
                                                                    <p class="text-xs text-green-600">
                                                                        Date area selected (green).
                                                                    </p>
                                                                </template>
                                                                <template x-if="selectionMode === 'custom_text' && !hasCustomTextCoordinates()">
                                                                    <p class="text-xs text-amber-600">
                                                                        Drag on the PDF to select the Custom Text area.
                                                                    </p>
                                                                </template>
                                                                <template x-if="selectionMode === 'custom_text' && hasCustomTextCoordinates()">
                                                                    <p class="text-xs text-green-600">
                                                                        Custom Text area selected (orange).
                                                                    </p>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                <!-- Non-PDF file: manual inputs -->
                                                <template x-if="!isPdfFile()">
                                                    <p class="text-xs text-blue-600 italic">
                                                        Upload a PDF file to use the visual coordinate picker.
                                                    </p>
                                                </template>

                                                <!-- Font Size -->
                                                <div class="flex items-center gap-3 flex-wrap">
                                                    <div class="flex items-center gap-2">
                                                        <label class="text-xs text-blue-700 whitespace-nowrap">Provider Font:</label>
                                                        <input type="number" x-model.number="uploadForm.provider_name_font_size" min="6" max="36"
                                                            class="w-16 px-2 py-1 border border-blue-300 rounded text-sm">
                                                    </div>
                                                    <template x-if="uploadForm.use_date_overlay">
                                                        <div class="flex items-center gap-2">
                                                            <label class="text-xs text-emerald-700 whitespace-nowrap">Date Font:</label>
                                                            <input type="number" x-model.number="uploadForm.date_font_size" min="6" max="36"
                                                                class="w-16 px-2 py-1 border border-emerald-300 rounded text-sm">
                                                        </div>
                                                    </template>
                                                    <template x-if="uploadForm.use_custom_text_overlay">
                                                        <div class="flex items-center gap-2">
                                                            <label class="text-xs text-amber-700 whitespace-nowrap">Text Font:</label>
                                                            <input type="number" x-model.number="uploadForm.custom_text_font_size" min="6" max="36"
                                                                class="w-16 px-2 py-1 border border-amber-300 rounded text-sm">
                                                        </div>
                                                    </template>
                                                </div>

                                                <!-- Fine-tune coordinates -->
                                                <template x-if="hasCoordinates()">
                                                    <div class="space-y-1">
                                                        <p class="text-[10px] font-medium text-blue-600">Provider Name (mm):</p>
                                                        <div class="flex gap-2 text-[10px]">
                                                            <label class="flex items-center gap-1 text-blue-500">X<input type="number" x-model.number="uploadForm.provider_name_x" step="0.5" class="w-14 px-1 py-0.5 border border-blue-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-blue-500">Y<input type="number" x-model.number="uploadForm.provider_name_y" step="0.5" class="w-14 px-1 py-0.5 border border-blue-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-blue-500">W<input type="number" x-model.number="uploadForm.provider_name_width" step="0.5" class="w-14 px-1 py-0.5 border border-blue-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-blue-500">H<input type="number" x-model.number="uploadForm.provider_name_height" step="0.5" class="w-14 px-1 py-0.5 border border-blue-200 rounded text-[10px] font-mono"></label>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="hasDateCoordinates()">
                                                    <div class="space-y-1">
                                                        <p class="text-[10px] font-medium text-emerald-600">Date (mm):</p>
                                                        <div class="flex gap-2 text-[10px]">
                                                            <label class="flex items-center gap-1 text-emerald-500">X<input type="number" x-model.number="uploadForm.date_x" step="0.5" class="w-14 px-1 py-0.5 border border-emerald-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-emerald-500">Y<input type="number" x-model.number="uploadForm.date_y" step="0.5" class="w-14 px-1 py-0.5 border border-emerald-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-emerald-500">W<input type="number" x-model.number="uploadForm.date_width" step="0.5" class="w-14 px-1 py-0.5 border border-emerald-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-emerald-500">H<input type="number" x-model.number="uploadForm.date_height" step="0.5" class="w-14 px-1 py-0.5 border border-emerald-200 rounded text-[10px] font-mono"></label>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="hasCustomTextCoordinates()">
                                                    <div class="space-y-1">
                                                        <p class="text-[10px] font-medium text-amber-600">Custom Text (mm):</p>
                                                        <div class="flex gap-2 text-[10px]">
                                                            <label class="flex items-center gap-1 text-amber-500">X<input type="number" x-model.number="uploadForm.custom_text_x" step="0.5" class="w-14 px-1 py-0.5 border border-amber-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-amber-500">Y<input type="number" x-model.number="uploadForm.custom_text_y" step="0.5" class="w-14 px-1 py-0.5 border border-amber-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-amber-500">W<input type="number" x-model.number="uploadForm.custom_text_width" step="0.5" class="w-14 px-1 py-0.5 border border-amber-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-amber-500">H<input type="number" x-model.number="uploadForm.custom_text_height" step="0.5" class="w-14 px-1 py-0.5 border border-amber-200 rounded text-[10px] font-mono"></label>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                </div>

                                <!-- Progress Bar -->
                                <template x-if="uploading">
                                    <div style="padding:0 16px 10px;">
                                        <div style="width:100%; background:#d0cdc5; border-radius:4px; height:6px; margin-bottom:6px;">
                                            <div style="height:6px; border-radius:4px; background:#C9A84C; transition:width 0.3s;"
                                                :style="'width:' + uploadProgress + '%'"></div>
                                        </div>
                                        <p style="font-size:11px; text-align:center; color:#8a8a82;" x-text="uploadProgress + '%'"></p>
                                    </div>
                                </template>

                                <!-- Upload Button -->
                                <div style="padding:10px 16px;">
                                    <button type="button" @click="uploadDocument()" :disabled="uploading" class="doc-upload-btn">
                                        <span x-show="!uploading">Upload Document</span>
                                        <span x-show="uploading">Uploading...</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Documents List -->
                    <div class="doc-list">
                        <template x-if="loading">
                            <div style="display:flex; justify-content:center; padding:24px 0;">
                                <div class="spinner"></div>
                            </div>
                        </template>

                        <template x-if="!loading && documents.length === 0">
                            <p style="text-align:center; color:#8a8a82; padding:24px 0; font-size:13px;">No documents uploaded yet</p>
                        </template>

                        <template x-for="doc in documents" :key="doc.id">
                            <div class="doc-entry group">
                                <div class="doc-icon" x-text="getFileExtension(doc.original_file_name)"></div>
                                <div class="doc-info">
                                    <div class="doc-name" x-text="doc.original_file_name"></div>
                                    <div class="doc-meta">
                                        <template x-if="doc.is_provider_template == 1">
                                            <span class="doc-meta-chip doc-meta-template">Template</span>
                                        </template>
                                        <span class="doc-meta-size" x-text="doc.file_size_formatted"></span>
                                        <span class="doc-meta-date" x-text="formatDate(doc.created_at)"></span>
                                    </div>
                                </div>
                                <div class="doc-actions">
                                    <template x-if="doc.is_provider_template == 1">
                                        <button @click="promptGenerateProviderVersion(doc)" title="Generate for Provider" class="icon-btn icon-btn-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </button>
                                    </template>
                                    <button @click="downloadDocument(doc.id)" title="Download" class="icon-btn icon-btn-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </button>
                                    <button @click="deleteDocument(doc.id, doc.original_file_name)" title="Delete" class="icon-btn icon-btn-danger icon-btn-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                </div>
            </div>
