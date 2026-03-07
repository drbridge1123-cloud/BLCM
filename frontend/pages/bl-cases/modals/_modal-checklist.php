<!-- Checklist Modal (Full-Screen) -->
<div x-show="showChecklistModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(0,0,0,.45);" @click.self="showChecklistModal = false" @keydown.escape.window="if(showChecklistModal) showChecklistModal = false">
    <div class="cl-modal" @click.stop>
        <!-- Header -->
        <div class="cl-header">
            <div style="display:flex; align-items:center; gap:14px; flex:1; min-width:0;">
                <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">Checklist</h3>
                <span style="font-size:11px; color:rgba(255,255,255,.5);" x-text="caseData?.case_number"></span>
                <span style="font-size:11px; color:rgba(255,255,255,.35);">&mdash;</span>
                <span style="font-size:11px; color:rgba(255,255,255,.5);" x-text="caseData?.client_name"></span>
                <template x-if="clSummary.initialized">
                    <div style="display:flex; align-items:center; gap:8px; margin-left:auto;">
                        <div style="width:140px; height:6px; background:rgba(255,255,255,.15); border-radius:3px; overflow:hidden;">
                            <div style="height:100%; background:#15803d; border-radius:3px; transition:width .3s;" :style="'width:' + (clSummary.overall?.percent || 0) + '%'"></div>
                        </div>
                        <span style="font-size:11px; color:rgba(255,255,255,.7); font-weight:600;" x-text="(clSummary.overall?.percent || 0) + '%'"></span>
                    </div>
                </template>
            </div>
            <button @click="showChecklistModal = false" style="background:none; border:none; color:rgba(255,255,255,.4); cursor:pointer; font-size:22px; padding:0 4px;">&times;</button>
        </div>

        <!-- Not Initialized State -->
        <template x-if="!clSummary.initialized && !clLoading">
            <div style="padding:80px 40px; text-align:center;">
                <div style="font-size:48px; margin-bottom:16px; opacity:.25;">&#9744;</div>
                <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin:0 0 8px;">No Checklist Yet</h3>
                <p style="font-size:13px; color:#94a3b8; margin:0 0 24px;">Initialize the checklist to start tracking tasks for this case.</p>
                <button @click="clInitialize()" class="cl-btn-primary" :disabled="clInitializing">
                    <span x-show="!clInitializing">Initialize Checklist</span>
                    <span x-show="clInitializing">Initializing...</span>
                </button>
            </div>
        </template>

        <!-- Loading -->
        <div x-show="clLoading" style="padding:60px; text-align:center;">
            <span style="color:#94a3b8;">Loading...</span>
        </div>

        <!-- Main Content -->
        <template x-if="clSummary.initialized && !clLoading">
            <div style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
                <!-- Phase Tabs (CRM style) -->
                <div class="cl-phase-tabs">
                    <template x-for="(phase, idx) in clPhases" :key="phase.phase">
                        <button class="cl-phase-tab" :class="clActivePhase === idx && 'cl-phase-tab-active'"
                                @click="clActivePhase = idx">
                            <span class="cl-phase-num" x-text="idx + 1"></span>
                            <span class="cl-phase-label" x-text="phase.phase.replace(/^Phase \d+ - /, '')"></span>
                            <template x-if="clGetPhaseProgress(phase.phase) !== null">
                                <span class="cl-phase-pct" x-text="clGetPhaseProgress(phase.phase) + '%'"
                                      :style="clGetPhaseProgress(phase.phase) === 100 ? 'color:#15803d;' : ''"></span>
                            </template>
                        </button>
                    </template>
                </div>

                <!-- Toolbar -->
                <div class="cl-toolbar">
                    <button class="cl-toolbar-btn" @click="clExpandCollapseAll()">
                        <span x-text="clAllCollapsed ? 'Expand All' : 'Collapse All'"></span>
                    </button>
                </div>

                <!-- Stage Cards -->
                <div class="cl-content">
                    <template x-if="clPhases[clActivePhase]">
                        <div>
                            <template x-for="stage in clPhases[clActivePhase].stages" :key="stage.stage">
                                <div class="cl-stage-card">
                                    <!-- Stage Header -->
                                    <div class="cl-stage-header" @click="clToggleStage(stage.stage)">
                                        <div style="display:flex; align-items:center; gap:10px; white-space:nowrap;">
                                            <svg :style="clCollapsedStages[stage.stage] ? 'transform:rotate(-90deg)' : ''" style="width:12px; height:12px; flex-shrink:0; transition:transform .2s;" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            <span style="font-size:14px; font-weight:600; color:#1e293b;" x-text="stage.stage"></span>
                                            <span style="font-size:12px; color:#94a3b8;" x-text="clStageProgress(stage)"></span>
                                        </div>
                                    </div>

                                    <!-- Task Table -->
                                    <div x-show="!clCollapsedStages[stage.stage]" x-collapse>
                                        <table class="cl-task-table">
                                            <colgroup>
                                                <col style="width:26%">
                                                <col style="width:7%">
                                                <col style="width:6%">
                                                <col style="width:9%">
                                                <col style="width:8%">
                                                <col style="width:8%">
                                                <col style="width:8%">
                                                <col style="width:6%">
                                                <col style="width:6%">
                                                <col style="width:8%">
                                            </colgroup>
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th style="text-align:center;">Assignee</th>
                                                    <th style="text-align:center;">Priority</th>
                                                    <th style="text-align:center;">Status</th>
                                                    <th style="text-align:center;">Start Date</th>
                                                    <th style="text-align:center;">End Date</th>
                                                    <th style="text-align:center;">Due Date</th>
                                                    <th style="text-align:center;">Duration</th>
                                                    <th style="text-align:center;">Days Till Due</th>
                                                    <th style="text-align:center;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="task in clFilterTasks(stage.tasks)" :key="task.id">
                                                    <tr class="cl-task-row" :class="{
                                                        'cl-task-done': task.status === 'done',
                                                        'cl-task-na': task.status === 'na',
                                                        'cl-task-child': task.parent_task_id !== null
                                                    }">
                                                        <!-- Name (includes tree line, number, title, conditional) -->
                                                        <td>
                                                            <div style="display:flex; align-items:center; gap:8px; width:100%; overflow:hidden;">
                                                                <template x-if="task.parent_task_id">
                                                                    <span class="cl-tree-line">&#9492;&#9472; </span>
                                                                </template>
                                                                <span class="cl-td-num" x-text="task.task_number ? task.task_number + '.' : ''"></span>
                                                                <span class="cl-task-title" :class="{
                                                                    'cl-title-done': task.status === 'done',
                                                                    'cl-title-na': task.status === 'na'
                                                                }" :style="task.has_subtasks ? 'font-weight:600;' : ''" x-text="task.title"></span>
                                                                <template x-if="task.is_conditional">
                                                                    <div style="display:inline-flex; gap:6px; flex-shrink:0;">
                                                                        <button class="cl-cond-btn" :class="task.condition_answer === 'yes' && 'cl-cond-yes'"
                                                                                @click.stop="clSetCondition(task, 'yes')">YES</button>
                                                                        <button class="cl-cond-btn" :class="task.condition_answer === 'no' && 'cl-cond-no'"
                                                                                @click.stop="clSetCondition(task, 'no')">NO</button>
                                                                    </div>
                                                                </template>
                                                                <template x-if="task.notes">
                                                                    <span class="cl-notes-icon" :title="task.notes">&#9998;</span>
                                                                </template>
                                                            </div>
                                                        </td>

                                                        <!-- Assignee (avatar) -->
                                                        <td style="text-align:center; position:relative;">
                                                            <span class="cl-avatar" :class="!task.assigned_to && 'cl-avatar-unassigned'"
                                                                  :style="task.assigned_to ? 'background:' + clAvatarColor(task.assigned_to) + '; border:none;' : ''"
                                                                  @click.stop="task._showAssign = !task._showAssign"
                                                                  :title="task.assigned_name || 'Unassigned'"
                                                                  x-text="task.assigned_to ? clInitials(task.assigned_name) : '+'"></span>
                                                            <div x-show="task._showAssign" @click.outside="task._showAssign = false" class="cl-avatar-dropdown" x-cloak>
                                                                <div @click="clUpdateTask(task.id,'assigned_to',null); task.assigned_to=null; task.assigned_name=null; task._showAssign=false"
                                                                     style="color:#94a3b8;">
                                                                    <span style="width:24px; height:24px; border-radius:50%; border:2px dashed #d1d5db; display:inline-flex; align-items:center; justify-content:center; font-size:14px; color:#9ca3af; flex-shrink:0;">✕</span>
                                                                    Clear
                                                                </div>
                                                                <template x-for="u in staffList" :key="u.id">
                                                                    <div @click="clUpdateTask(task.id,'assigned_to',u.id); task.assigned_to=u.id; task.assigned_name=u.display_name||u.full_name; task._showAssign=false">
                                                                        <span style="width:24px; height:24px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:9px; font-weight:600; color:#fff; letter-spacing:-.5px; flex-shrink:0;"
                                                                              :style="'background:' + clAvatarColor(u.id)"
                                                                              x-text="clInitials(u.display_name || u.full_name)"></span>
                                                                        <span x-text="u.display_name || u.full_name"></span>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </td>

                                                        <!-- Priority (SVG flag) -->
                                                        <td style="text-align:center;">
                                                            <span class="cl-priority-flag" :class="'cl-pri-' + (task.priority || 'normal')"
                                                                    @click.stop="clCyclePriority(task)" :title="(task.priority || 'normal') + ' priority'">
                                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                                    <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                                                                    <line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="2"/>
                                                                </svg>
                                                            </span>
                                                        </td>

                                                        <!-- Status (badge + dropdown) -->
                                                        <td style="text-align:center; position:relative;">
                                                            <span class="cl-status-badge" :class="'cl-st-' + task.status"
                                                                  @click.stop="task._showStatus = !task._showStatus">
                                                                <span x-text="clStatusIcon(task.status)"></span>
                                                                <span x-text="clStatusLabel(task.status)"></span>
                                                            </span>
                                                            <div x-show="task._showStatus" @click.outside="task._showStatus = false" class="cl-status-dropdown" x-cloak>
                                                                <div @click="clUpdateTask(task.id,'status','not_started'); task._showStatus=false">
                                                                    <span class="cl-status-badge cl-st-not_started" style="cursor:pointer;">📋 TO DO</span>
                                                                </div>
                                                                <div @click="clUpdateTask(task.id,'status','in_progress'); task._showStatus=false">
                                                                    <span class="cl-status-badge cl-st-in_progress" style="cursor:pointer;">🔄 IN PROGRESS</span>
                                                                </div>
                                                                <div @click="clUpdateTask(task.id,'status','done'); task._showStatus=false">
                                                                    <span class="cl-status-badge cl-st-done" style="cursor:pointer;">✅ DONE</span>
                                                                </div>
                                                                <div @click="clUpdateTask(task.id,'status','na'); task._showStatus=false">
                                                                    <span class="cl-status-badge cl-st-na" style="cursor:pointer;">N/A</span>
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <!-- Start Date -->
                                                        <td class="cl-date-cell">
                                                            <span @click="$refs['sd'+task.id].showPicker()"
                                                                  x-text="task.start_date ? clFormatDate(task.start_date) : '-'"></span>
                                                            <input type="date" :ref="'sd'+task.id" class="cl-date-hidden"
                                                                   :value="task.start_date||''"
                                                                   @change="clUpdateTask(task.id,'start_date',$event.target.value||null); task.start_date=$event.target.value||null">
                                                        </td>

                                                        <!-- End Date -->
                                                        <td class="cl-date-cell">
                                                            <span @click="$refs['ed'+task.id].showPicker()"
                                                                  x-text="task.end_date ? clFormatDate(task.end_date) : '-'"></span>
                                                            <input type="date" :ref="'ed'+task.id" class="cl-date-hidden"
                                                                   :value="task.end_date||''"
                                                                   @change="clUpdateTask(task.id,'end_date',$event.target.value||null); task.end_date=$event.target.value||null">
                                                        </td>

                                                        <!-- Due Date -->
                                                        <td class="cl-date-cell" :class="clDaysLeft(task)?.overdue && 'cl-overdue'">
                                                            <span @click="$refs['dd'+task.id].showPicker()"
                                                                  x-text="task.due_date ? clFormatDate(task.due_date) : '-'"></span>
                                                            <input type="date" :ref="'dd'+task.id" class="cl-date-hidden"
                                                                   :value="task.due_date||''"
                                                                   @change="clUpdateTask(task.id,'due_date',$event.target.value||null); task.due_date=$event.target.value||null">
                                                        </td>

                                                        <!-- Duration -->
                                                        <td class="cl-td-calc" x-text="clDuration(task)"></td>

                                                        <!-- Days Till Due -->
                                                        <td class="cl-td-calc" :class="{
                                                            'cl-overdue': clDaysLeft(task)?.overdue,
                                                            'cl-days-soon': clDaysLeft(task)?.soon
                                                        }" x-text="clDaysLeft(task)?.text || '-'"></td>

                                                        <!-- Actions (⋮ menu) -->
                                                        <td style="text-align:center; position:relative;">
                                                            <button class="cl-action-btn" title="More actions"
                                                                    @click.stop="clToggleMenu(task.id)" style="font-size:18px; letter-spacing:1px;">&#8942;</button>
                                                            <div x-show="clOpenMenu === task.id" @click.outside="clOpenMenu = null" class="cl-action-dropdown" x-cloak>
                                                                <div @click="clStartAddSubtask(task, clPhases[clActivePhase].phase, stage.stage)">
                                                                    <span>➕</span> Add Subtask
                                                                </div>
                                                                <div class="cl-action-delete" @click="clDeleteTask(task.id)">
                                                                    <span>🗑️</span> Delete
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>

                                        <!-- Add custom task -->
                                        <div style="padding:8px 16px 12px;">
                                            <button class="cl-add-task-btn" @click="clStartAddTask(clPhases[clActivePhase].phase, stage.stage)">
                                                + Add Task
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Add Task / Subtask Modal -->
        <div x-show="clAddingTask" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center" style="background:rgba(0,0,0,.3);" @click.self="clAddingTask = false">
            <div class="cl-add-modal" @click.stop>
                <h4 style="font-size:14px; font-weight:700; margin:0 0 12px;" x-text="clNewTask.parent_task_id ? 'Add Subtask' : 'Add Task'"></h4>
                <p style="font-size:11px; color:#94a3b8; margin:0 0 12px;" x-text="clNewTask.phase + ' > ' + clNewTask.stage"></p>
                <template x-if="clNewTask.parent_task_id">
                    <p style="font-size:11px; color:#C9A84C; margin:-8px 0 12px;">Parent: <span x-text="clNewTask.parent_title"></span></p>
                </template>
                <input type="text" x-model="clNewTask.title" placeholder="Task title..." class="cl-add-input" @keydown.enter="clSaveNewTask()">
                <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:12px;">
                    <button @click="clAddingTask = false" class="cl-btn-ghost">Cancel</button>
                    <button @click="clSaveNewTask()" class="cl-btn-primary" :disabled="!clNewTask.title.trim()">Add</button>
                </div>
            </div>
        </div>
    </div>
</div>
