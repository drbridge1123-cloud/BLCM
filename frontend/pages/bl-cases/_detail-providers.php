            <!-- Provider List (Summary View) -->
            <div class="c1-section c1-group-end" data-panel>
                <div class="c1-section-header" :class="showProviders && 'is-open'" @click="showProviders = !showProviders; if(showProviders) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
                    <div class="flex items-center gap-2.5">
                        <span class="c1-num c1-num-navy">01</span>
                        <h3 class="panel-title">Providers</h3>
                        <span class="panel-count" x-text="providers.length"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click.stop="window.location.href='/blcm/frontend/pages/billing/?case_id=' + caseId" class="panel-btn">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Manage in Tracker
                        </button>
                        <button @click.stop="showAddProviderModal = true" class="panel-btn-navy">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Provider
                        </button>
                    </div>
                </div>
                <div x-show="showProviders" x-collapse>
                    <div class="px-5 pb-4">
                        <template x-if="providers.length === 0">
                            <p class="text-center text-v2-text-light py-6" style="font-size:13px;">No providers added yet</p>
                        </template>
                        <template x-for="p in providers" :key="p.id">
                            <div class="flex items-center justify-between py-2.5 border-b border-v2-card-border/50 last:border-b-0">
                                <div class="flex items-center gap-4 flex-1 min-w-0">
                                    <div class="min-w-0 flex-1" style="max-width:280px;">
                                        <span class="font-medium text-sm block truncate" x-text="p.provider_name"></span>
                                        <span class="text-xs text-v2-text-light" x-text="getProviderTypeLabel(p.provider_type)"></span>
                                    </div>
                                    <div style="min-width:100px;">
                                        <span class="ps-label text-xs" :class="'ps-label-' + p.overall_status"
                                            x-text="getStatusLabel(p.overall_status)"></span>
                                        <div class="ps-track" style="width:80px;">
                                            <div class="ps-fill" :class="'ps-fill-' + p.overall_status"
                                                :style="'width:' + ({
                                                    treating:'0', treatment_complete:'10', no_records:'0', not_started:'0',
                                                    requesting:'20', follow_up:'35', action_needed:'40',
                                                    on_hold:'60', received_partial:'55',
                                                    received_complete:'100', received:'100',
                                                    verified:'100', done:'100'
                                                }[p.overall_status] || '0') + '%'"></div>
                                        </div>
                                    </div>
                                    <div class="text-xs text-v2-text-mid" style="min-width:90px;">
                                        <span :class="p.days_until_deadline < 0 ? 'text-red-600 font-semibold' : (p.days_until_deadline <= 7 ? 'text-yellow-600' : '')"
                                            x-text="formatDate(p.deadline) || '-'"></span>
                                        <template x-if="p.escalation_tier && p.escalation_tier !== 'normal'">
                                            <span class="escalation-badge ml-1"
                                                :class="p.escalation_css + (p.escalation_tier === 'admin' ? ' escalation-pulse' : '')"
                                                x-text="p.escalation_label"></span>
                                        </template>
                                    </div>
                                    <div class="text-xs text-v2-text-mid" style="min-width:60px;" x-text="p.assigned_name || '-'"></div>
                                </div>
                                <div class="flex gap-1 flex-shrink-0" @click.stop>
                                    <template x-if="p.overall_status === 'treating'">
                                        <button @click="activateProvider(p)" title="Mark Treatment Complete" class="icon-btn icon-btn-sm" style="color:#C9A84C;">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        </button>
                                    </template>
                                    <template x-if="p.overall_status === 'treatment_complete'">
                                        <button @click="rushActivateProvider(p)" title="Rush Activate for Billing" class="icon-btn icon-btn-sm" style="color:#3B82F6;">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        </button>
                                    </template>
                                    <button @click="deleteProvider(p.id)" title="Remove" class="icon-btn icon-btn-danger icon-btn-sm">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
