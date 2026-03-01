            <!-- Navy Hero Section -->
            <div class="case-hero">
                <!-- Top row: case info + buttons -->
                <div class="hero-top">
                    <div class="hero-left">
                        <div class="hero-id-row">
                            <span class="hero-case-num" x-text="caseData.case_number"></span>
                            <span class="hero-badge"
                                x-text="getStatusLabel(caseData.status)"></span>
                        </div>
                        <div class="hero-client" x-text="caseData.client_name"></div>
                    </div>
                    <div class="hero-actions">
                        <button @click="showEditModal = true" class="hero-btn hero-btn-ghost">Edit</button>
                        <button x-show="caseData && FORWARD_TRANSITIONS[caseData.status] && FORWARD_TRANSITIONS[caseData.status].length > 0"
                            @click="openMoveForwardModal()"
                            class="hero-btn hero-btn-gold">
                            Move to <span x-text="getStatusLabel(FORWARD_TRANSITIONS[caseData.status][0])"></span> &rarr;
                        </button>
                        <button x-show="caseData && BACKWARD_TRANSITIONS[caseData.status] && BACKWARD_TRANSITIONS[caseData.status].length > 0"
                            @click="openSendBackModal()"
                            class="hero-btn hero-btn-gold-outline">Send Back</button>
                    </div>
                </div>

                <!-- Stage Pipeline (Arrow Steps) -->
                <div class="pipeline">
                    <template x-for="(step, idx) in workflowSteps" :key="step.key">
                        <div class="stage" :class="{
                            'stage-done': getStepState(step.key) === 'completed',
                            'stage-cur': getStepState(step.key) === 'active',
                            'stage-first': idx === 0,
                        }">
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

