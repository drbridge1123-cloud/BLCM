            <!-- Navy Hero Section -->
            <div class="case-hero">
                <!-- Top row: case info + buttons -->
                <div class="hero-top">
                    <div class="hero-left">
                        <div class="hero-id-row">
                            <span class="hero-case-num" x-text="caseData.case_number"></span>
                            <span class="hero-badge"
                                x-text="getPipelineLabel()"></span>
                        </div>
                        <div class="hero-client" x-text="caseData.client_name"></div>
                    </div>
                    <div class="hero-actions">
                        <button @click="showEditModal = true" class="hero-btn hero-btn-ghost">Edit</button>
                        <button @click="openContacts(caseData)" class="hero-btn hero-btn-ghost">Contacts</button>
                        <!-- Status Dropdown -->
                        <!-- Status Dropdown -->
                        <style>
                        .sd-trigger {
                            display:inline-flex; align-items:center; gap:6px;
                            padding:8px 14px; background:#C9A84C; color:#fff;
                            border:none; border-radius:8px; font-size:12.5px; font-weight:700;
                            cursor:pointer; font-family:inherit; transition:background .15s;
                        }
                        .sd-trigger:hover { background:#B8973F; }
                        .sd-trigger svg { width:10px; height:10px; stroke:#fff; stroke-width:2.5; fill:none; }
                        @keyframes sdFadeDown { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:translateY(0); } }
                        .sd-panel {
                            position:absolute; right:0; top:calc(100% + 6px); z-index:999;
                            width:210px; background:#fff; border:1.5px solid #e2ddd6;
                            border-radius:12px; box-shadow:0 8px 28px rgba(15,27,45,.13), 0 2px 8px rgba(15,27,45,.06);
                            animation:sdFadeDown .15s ease;
                        }
                        .sd-header {
                            padding:9px 14px 7px; font-size:8px; font-weight:700; color:#8a8a82;
                            text-transform:uppercase; letter-spacing:.14em; border-bottom:1px solid #f4f2ee;
                        }
                        .sd-list { padding:10px 14px 6px; }
                        .sd-item { display:flex; gap:10px; position:relative; cursor:pointer; }
                        .sd-item:hover .sd-name { color:#1a2535 !important; }
                        .sd-left { width:18px; display:flex; flex-direction:column; align-items:center; flex-shrink:0; }
                        .sd-dot {
                            width:16px; height:16px; border-radius:50%; display:flex; align-items:center;
                            justify-content:center; font-size:7.5px; font-weight:700;
                            font-family:'IBM Plex Mono', monospace; z-index:1; flex-shrink:0;
                        }
                        .sd-dot-done { background:#1a9e6a; color:#fff; }
                        .sd-dot-current { background:#C9A84C; color:#fff; box-shadow:0 0 0 3px rgba(201,168,76,.18); }
                        .sd-dot-pending { background:#fff; border:1.5px solid #e2ddd6; color:#8a8a82; }
                        .sd-line { width:1.5px; flex:1; background:#e2ddd6; margin:2px 0; }
                        .sd-line-done { background:#1a9e6a; opacity:.3; }
                        .sd-right { padding-top:1px; padding-bottom:8px; }
                        .sd-item:last-child .sd-right { padding-bottom:4px; }
                        .sd-name { font-size:12px; font-weight:500; transition:color .1s; }
                        .sd-name-done { color:#8a8a82; }
                        .sd-name-current { color:#1a2535; font-weight:700; }
                        .sd-name-pending { color:#8a8a82; }
                        .sd-curr-tag { font-size:8px; font-weight:700; color:#C9A84C; display:block; margin-top:1px; }
                        </style>
                        <div style="position:relative; display:inline-block;">
                            <button @click="showStatusDropdown = !showStatusDropdown" class="sd-trigger">
                                <span x-text="getPipelineLabel()"></span>
                                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="showStatusDropdown" @click.outside="showStatusDropdown = false" class="sd-panel" style="display:none;">
                                <div class="sd-header">Case Stage</div>
                                <div class="sd-list">
                                    <template x-for="(step, idx) in workflowSteps" :key="step.key">
                                        <div class="sd-item" @click="selectPipelineStep(step.key)">
                                            <div class="sd-left">
                                                <div class="sd-dot"
                                                    :class="getStepState(step.key) === 'completed' ? 'sd-dot-done' : (getStepState(step.key) === 'active' ? 'sd-dot-current' : 'sd-dot-pending')"
                                                    x-text="getStepState(step.key) === 'completed' ? '✓' : (idx + 1)"></div>
                                                <div x-show="idx < workflowSteps.length - 1" class="sd-line"
                                                    :class="getStepState(step.key) === 'completed' ? 'sd-line-done' : ''"></div>
                                            </div>
                                            <div class="sd-right">
                                                <span class="sd-name"
                                                    :class="getStepState(step.key) === 'completed' ? 'sd-name-done' : (getStepState(step.key) === 'active' ? 'sd-name-current' : 'sd-name-pending')"
                                                    x-text="step.label"></span>
                                                <template x-if="getStepState(step.key) === 'active'">
                                                    <span class="sd-curr-tag">&#9679; Current Stage — click to reassign</span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stage Pipeline (Arrow Steps) — clickable to navigate to trackers -->
                <div class="pipeline">
                    <template x-for="(step, idx) in workflowSteps" :key="step.key">
                        <div class="stage" :class="{
                            'stage-done': getStepState(step.key) === 'completed',
                            'stage-cur': getStepState(step.key) === 'active',
                            'stage-first': idx === 0,
                        }" style="cursor:pointer;" @click="goToTracker(step.key)" :title="getTrackerTooltip(step.key)">
                            <span class="stage-num" x-text="idx + 1"></span>
                            <span class="stage-label" x-text="step.label"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Info Strip (5 cards) -->
            <div class="info-strip">
                <div class="info-strip-card">
                    <p class="info-label">Date of Birth</p>
                    <p class="info-value" x-text="formatDate(caseData.client_dob) || '-'"></p>
                </div>
                <div class="info-strip-card">
                    <p class="info-label">Date of Injury</p>
                    <p class="info-value" x-text="formatDate(caseData.doi) || '-'"></p>
                </div>
                <div class="info-strip-card">
                    <p class="info-label">Attorney</p>
                    <p class="info-value" x-text="caseData.attorney_name || '-'"></p>
                </div>
                <div class="info-strip-card">
                    <p class="info-label">Assigned To</p>
                    <p class="info-value" x-text="caseData.assigned_name || '-'"></p>
                </div>
                <div class="info-strip-card" @click="toggleIniCompleted()" style="cursor:pointer;" :title="caseData.ini_completed ? 'Click to undo Treating Completed' : 'Click to activate all treating providers'">
                    <p class="info-label">Treating Completed</p>
                    <p class="info-value" :class="caseData.ini_completed ? 'text-green-600' : 'text-red-500'" x-text="caseData.ini_completed ? 'Yes' : 'No'"></p>
                </div>
            </div>

