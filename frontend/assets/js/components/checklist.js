/**
 * Checklist Modal Mixin
 * Full-screen modal with phase tabs, stage cards, and inline task editing.
 * Spread into any Alpine component: ...checklistMixin()
 */
function checklistMixin() {
    return {
        showChecklistModal: false,
        clLoading: false,
        clInitializing: false,
        clActivePhase: 0,
        clPhases: [],
        clSummary: { initialized: false, overall: {}, phases: [] },
        clCollapsedStages: {},
        clAllCollapsed: false,
        clAddingTask: false,
        clNewTask: { title: '', phase: '', stage: '', parent_task_id: null, parent_title: '' },
        clOpenMenu: null,

        /**
         * Open checklist modal for current case
         */
        openChecklist() {
            this.showChecklistModal = true;
            this.clOpenMenu = null;
            this.clLoadSummary();
            this.clLoadTasks().then(() => this._clAutoSelectPhase());
        },

        /**
         * Auto-select phase tab matching current case status
         */
        _clAutoSelectPhase() {
            const status = this.caseData?.status;
            if (!status || !this.clPhases.length) return;
            const map = {
                ini: 'Phase 1', rec: 'Phase 2', verification: 'Phase 3',
                rfd: 'Phase 4', neg: 'Phase 5', lit: 'Phase 6',
                fbc: 'Phase 7', accounting: 'Phase 8',
            };
            const prefix = map[status];
            if (!prefix) return;
            const idx = this.clPhases.findIndex(p => p.phase.startsWith(prefix));
            if (idx >= 0) this.clActivePhase = idx;
        },

        /**
         * Load task summary (progress)
         */
        async clLoadSummary() {
            try {
                const caseId = this.caseData?.id || this._caseId;
                const r = await api.get(`case-tasks/summary?case_id=${caseId}`);
                if (r.success) {
                    this.clSummary = r;
                }
            } catch (e) {
                console.error('Failed to load checklist summary:', e);
            }
        },

        /**
         * Load full task list (hierarchical)
         */
        async clLoadTasks() {
            this.clLoading = true;
            try {
                const caseId = this.caseData?.id || this._caseId;
                const r = await api.get(`case-tasks?case_id=${caseId}`);
                if (r.success) {
                    this.clPhases = r.phases || [];
                }
            } catch (e) {
                console.error('Failed to load checklist tasks:', e);
            } finally {
                this.clLoading = false;
            }
        },

        /**
         * Initialize checklist from template
         */
        async clInitialize() {
            this.clInitializing = true;
            try {
                const caseId = this.caseData?.id || this._caseId;
                const r = await api.post('case-tasks/initialize', { case_id: caseId });
                if (r.success) {
                    await this.clLoadSummary();
                    await this.clLoadTasks();
                    showToast('Checklist initialized', 'success');
                    window.dispatchEvent(new CustomEvent('checklist-updated'));
                }
            } catch (e) {
                showToast('Failed to initialize checklist', 'error');
            } finally {
                this.clInitializing = false;
            }
        },

        /**
         * Update a single task field
         */
        async clUpdateTask(taskId, field, value) {
            try {
                const body = {};
                body[field] = value;
                const r = await api.put(`case-tasks/${taskId}`, body);
                if (r.success) {
                    this._clUpdateLocal(taskId, field, value);
                    this.clLoadSummary();
                    window.dispatchEvent(new CustomEvent('checklist-updated'));
                }
            } catch (e) {
                showToast('Failed to update task', 'error');
            }
        },

        /**
         * Update local task data without re-fetching
         */
        _clUpdateLocal(taskId, field, value) {
            for (const phase of this.clPhases) {
                for (const stage of phase.stages) {
                    for (const task of stage.tasks) {
                        if (task.id === taskId) {
                            task[field] = value;
                            if (field === 'status') {
                                if (value === 'done') {
                                    task.completed_at = new Date().toISOString();
                                    if (!task.end_date) {
                                        task.end_date = new Date().toISOString().split('T')[0];
                                    }
                                } else {
                                    task.completed_at = null;
                                }
                                if (value === 'in_progress' && !task.start_date) {
                                    task.start_date = new Date().toISOString().split('T')[0];
                                }
                            }
                            return;
                        }
                    }
                }
            }
        },

        /**
         * Set conditional answer (yes/no) on a task
         */
        async clSetCondition(task, answer) {
            const newAnswer = task.condition_answer === answer ? null : answer;
            await this.clUpdateTask(task.id, 'condition_answer', newAnswer);
            task.condition_answer = newAnswer;
        },

        /**
         * Cycle task priority: low → normal → high → urgent → low
         */
        async clCyclePriority(task) {
            const order = ['low', 'normal', 'high', 'urgent'];
            const idx = order.indexOf(task.priority || 'normal');
            const next = order[(idx + 1) % order.length];
            await this.clUpdateTask(task.id, 'priority', next);
        },

        /**
         * Get avatar background color based on userId
         */
        clAvatarColor(userId) {
            const colors = ['#3b82f6','#10b981','#8b5cf6','#ec4899','#f59e0b','#6b7280'];
            return colors[(userId || 0) % colors.length];
        },

        /**
         * Get initials from a name (e.g. "Ella Kim" → "EK")
         */
        clInitials(name) {
            if (!name) return '?';
            return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
        },

        /**
         * Format date string to readable format (e.g. "2026-02-01" → "Feb 1, 2026")
         */
        clFormatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },

        /**
         * Get status display label
         */
        clStatusLabel(status) {
            const labels = { not_started: 'TO DO', in_progress: 'IN PROGRESS', done: 'DONE', na: 'N/A' };
            return labels[status] || status;
        },

        /**
         * Get status icon emoji (CRM-matched)
         */
        clStatusIcon(status) {
            const icons = { not_started: '📋', in_progress: '🔄', done: '✅', na: '' };
            return icons[status] || '📋';
        },

        /**
         * Calculate duration in days (start_date to end_date or today)
         */
        clDuration(task) {
            if (!task.start_date) return '-';
            const start = new Date(task.start_date + 'T00:00:00');
            const end = task.end_date ? new Date(task.end_date + 'T00:00:00') : new Date();
            const days = Math.floor((end - start) / 86400000);
            if (days < 0) return '-';
            return days + ' days';
        },

        /**
         * Calculate days left until due_date
         * Returns { text: string, overdue: boolean } or null
         */
        clDaysLeft(task) {
            if (!task.due_date) return null;
            if (task.status === 'done' || task.status === 'na') return null;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const due = new Date(task.due_date + 'T00:00:00');
            const diff = Math.floor((due - today) / 86400000);
            if (diff < 0) return { text: Math.abs(diff) + ' days ago', overdue: true, soon: false };
            if (diff === 0) return { text: 'Today', overdue: false, soon: true };
            if (diff <= 3) return { text: diff + ' days', overdue: false, soon: true };
            return { text: diff + ' days', overdue: false, soon: false };
        },

        /**
         * Toggle action menu for a task
         */
        clToggleMenu(taskId) {
            this.clOpenMenu = this.clOpenMenu === taskId ? null : taskId;
        },

        /**
         * Delete a task
         */
        async clDeleteTask(taskId) {
            this.clOpenMenu = null;
            if (!confirm('Delete this task and its subtasks?')) return;
            try {
                const r = await api.delete(`case-tasks/${taskId}`);
                if (r.success) {
                    await this.clLoadTasks();
                    this.clLoadSummary();
                    showToast('Task deleted', 'success');
                    window.dispatchEvent(new CustomEvent('checklist-updated'));
                }
            } catch (e) {
                showToast('Failed to delete task', 'error');
            }
        },

        /**
         * Start adding a subtask under a parent
         */
        clStartAddSubtask(parentTask, phase, stage) {
            this.clOpenMenu = null;
            this.clNewTask = {
                title: '',
                phase,
                stage,
                parent_task_id: parentTask.id,
                parent_title: parentTask.title,
            };
            this.clAddingTask = true;
            this.$nextTick(() => {
                const input = document.querySelector('.cl-add-input');
                if (input) input.focus();
            });
        },

        /**
         * Filter tasks: show root tasks + visible subtasks
         * For conditional parents, show children matching condition_answer
         */
        clFilterTasks(allTasks) {
            const parentMap = {};
            for (const t of allTasks) {
                if (t.is_conditional || t.has_subtasks) {
                    parentMap[t.id] = t;
                }
            }

            return allTasks.filter(t => {
                if (!t.parent_task_id) return true;

                const parent = parentMap[t.parent_task_id];
                if (!parent) return true;

                if (parent.is_conditional) {
                    if (!parent.condition_answer) return false;
                    if (t.condition_value && t.condition_value !== parent.condition_answer) return false;
                }

                if (parent.parent_task_id) {
                    const grandparent = parentMap[parent.parent_task_id];
                    if (grandparent && grandparent.is_conditional) {
                        if (!grandparent.condition_answer) return false;
                        if (parent.condition_value && parent.condition_value !== grandparent.condition_answer) return false;
                    }
                }

                return true;
            });
        },

        /**
         * Get phase progress percentage
         */
        clGetPhaseProgress(phaseName) {
            const p = this.clSummary.phases?.find(x => x.phase === phaseName);
            return p ? p.percent : 0;
        },

        /**
         * Stage progress text (e.g., "3/7 done")
         */
        clStageProgress(stage) {
            const tasks = stage.tasks.filter(t => !t.parent_task_id);
            const done = tasks.filter(t => t.status === 'done').length;
            const na = tasks.filter(t => t.status === 'na').length;
            const effective = tasks.length - na;
            if (effective === 0) return '';
            return `${done}/${effective}`;
        },

        /**
         * Toggle stage collapse
         */
        clToggleStage(stageKey) {
            this.clCollapsedStages[stageKey] = !this.clCollapsedStages[stageKey];
        },

        /**
         * Expand/Collapse all stages in current phase
         */
        clExpandCollapseAll() {
            const phase = this.clPhases[this.clActivePhase];
            if (!phase) return;
            this.clAllCollapsed = !this.clAllCollapsed;
            for (const stage of phase.stages) {
                this.clCollapsedStages[stage.stage] = this.clAllCollapsed;
            }
        },

        /**
         * Start adding a custom task
         */
        clStartAddTask(phase, stage) {
            this.clNewTask = { title: '', phase, stage, parent_task_id: null, parent_title: '' };
            this.clAddingTask = true;
            this.$nextTick(() => {
                const input = document.querySelector('.cl-add-input');
                if (input) input.focus();
            });
        },

        /**
         * Save new custom task or subtask
         */
        async clSaveNewTask() {
            if (!this.clNewTask.title.trim()) return;
            try {
                const caseId = this.caseData?.id || this._caseId;
                const payload = {
                    case_id: caseId,
                    title: this.clNewTask.title.trim(),
                    phase: this.clNewTask.phase,
                    stage: this.clNewTask.stage,
                };
                if (this.clNewTask.parent_task_id) {
                    payload.parent_task_id = this.clNewTask.parent_task_id;
                }
                const r = await api.post('case-tasks', payload);
                if (r.success) {
                    this.clAddingTask = false;
                    await this.clLoadTasks();
                    this.clLoadSummary();
                    showToast('Task added', 'success');
                    window.dispatchEvent(new CustomEvent('checklist-updated'));
                }
            } catch (e) {
                showToast('Failed to add task', 'error');
            }
        },

        /**
         * Load summary for header badge (called on case detail load)
         */
        async clLoadBadge() {
            try {
                const caseId = this.caseData?.id || this._caseId;
                if (!caseId) return;
                const r = await api.get(`case-tasks/summary?case_id=${caseId}`);
                if (r.success) {
                    this.clSummary = r;
                }
            } catch (e) {
                // silent fail for badge
            }
        },
    };
}
