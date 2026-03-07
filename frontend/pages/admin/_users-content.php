<!-- All sp- styles loaded from shared sp-design-system.css -->
<style>
.sp-toggle{position:relative;display:inline-block;width:36px;height:20px;flex-shrink:0;vertical-align:middle;}
.sp-toggle input{opacity:0;width:0;height:0;position:absolute;}
.sp-toggle .sp-toggle-track{position:absolute;inset:0;border-radius:10px;background:rgba(255,255,255,.2);transition:background .2s;cursor:pointer;}
.sp-toggle input:checked+.sp-toggle-track{background:#22c55e;}
.sp-toggle .sp-toggle-knob{position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.3);transition:left .2s;}
.sp-toggle input:checked~.sp-toggle-knob{left:18px;}
/* Top-level page tabs */
.usr-page-tabs { display:flex; gap:0; margin-bottom:18px; border-bottom:2px solid #e8e4dc; }
.usr-page-tab { padding:10px 22px; font-size:13px; font-weight:600; color:#8a8a82; background:none; border:none; cursor:pointer; position:relative; font-family:'IBM Plex Sans',sans-serif; transition:color .15s; }
.usr-page-tab:hover { color:#1a2535; }
.usr-page-tab.active { color:#C9A84C; }
.usr-page-tab.active::after { content:''; position:absolute; bottom:-2px; left:0; right:0; height:2px; background:#C9A84C; border-radius:1px; }
</style>

<!-- Page header row -->
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
    <div>
        <div class="sp-eyebrow">Admin</div>
        <h1 class="sp-title" style="font-size:16px;">Users</h1>
    </div>
</div>

<!-- ═══ Page-Level Tabs ═══ -->
<div x-data="{ pageTab: 'users' }">

    <div class="usr-page-tabs">
        <button class="usr-page-tab" :class="pageTab === 'users' && 'active'" @click="pageTab = 'users'">Users</button>
        <template x-if="$store.auth.hasPermission('data_management')">
            <button class="usr-page-tab" :class="pageTab === 'data' && 'active'" @click="pageTab = 'data'">Data Management</button>
        </template>
    </div>

    <!-- ══════════════════════════════════════════════ -->
    <!-- TAB 1: Users                                  -->
    <!-- ══════════════════════════════════════════════ -->
    <div x-show="pageTab === 'users'" x-cloak>
        <div x-data="usersPage()">

            <!-- ═══ Unified Card ═══ -->
            <div class="sp-card">
                <div class="sp-gold-bar"></div>
                <div class="sp-header" style="flex-wrap:wrap; gap:16px;">
                    <div style="flex:1;">
                        <span class="sp-title" style="font-size:14px;">User Management</span>
                    </div>
                    <button @click="openCreateModal()" class="sp-new-btn-navy">+ New User</button>
                </div>

                <div class="sp-toolbar">
                    <form autocomplete="off" @submit.prevent class="sp-toolbar-right" style="display:flex; gap:8px; width:100%;">
                        <input type="search" x-model="search" @input.debounce.300ms="handleSearch()" placeholder="Search users..." class="sp-search" style="flex:1; min-width:200px;" autocomplete="off">
                        <select x-model="filterRole" @change="loadUsers()" class="sp-select">
                            <option value="">All Roles</option><option value="admin">Admin</option><option value="manager">Manager</option><option value="attorney">Attorney</option><option value="paralegal">Paralegal</option><option value="billing">Billing</option><option value="accounting">Accounting</option>
                        </select>
                    </form>
                </div>

                <div style="overflow-x:auto;">
                <table class="sp-table">
                    <thead><tr>
                        <th>Username</th><th>Full Name</th><th>Email</th><th>Job Title</th>
                        <th>Card Last 4</th><th>Role</th><th>Team</th>
                        <th style="text-align:center;">Commission</th><th style="text-align:center; cursor:pointer; user-select:none;" @click="showPasswords=!showPasswords" title="Click to show/hide">Password <span x-text="showPasswords ? '👁' : '👁‍🗨'" style="font-size:11px;"></span></th>
                        <th style="text-align:center;">Status</th>
                    </tr></thead>
                    <tbody>
                        <template x-if="loading"><tr><td colspan="10" class="sp-empty">Loading...</td></tr></template>
                        <template x-if="!loading && users.length === 0"><tr><td colspan="10" class="sp-empty">No users found</td></tr></template>
                        <template x-for="u in users" :key="u.id">
                            <tr @click="openEditModal(u)" style="cursor:pointer;">
                                <td style="font-weight:600;" x-text="u.username"></td>
                                <td x-text="u.full_name"></td>
                                <td style="font-size:12px; color:#6b7280;" x-text="u.email || '—'"></td>
                                <td style="font-size:12px;" x-text="u.job_title || '—'"></td>
                                <td class="sp-mono" style="font-size:12px;" x-text="u.card_last4 || '—'"></td>
                                <td><span class="sp-stage" :class="roleColors[u.role]" x-text="u.role"></span></td>
                                <td style="font-size:12px;" x-text="u.team || '—'"></td>
                                <td style="text-align:center;" class="sp-mono" x-text="u.commission_rate + '%'"></td>
                                <td style="font-size:12px; text-align:center;" class="sp-mono" x-text="showPasswords ? (u.password_plain || '—') : (u.password_plain ? '••••••' : '—')"></td>
                                <td style="text-align:center;"><span class="sp-stage" :style="u.is_active ? 'background:#dcfce7; color:#15803d;' : 'background:#fee2e2; color:#dc2626;'" x-text="u.is_active ? 'Active' : 'Inactive'"></span></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                </div>
            </div>

            <!-- Create/Edit User Modal -->
            <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45);" @click.self="showModal=false">
                <div style="background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22); width:100%; max-width:560px; max-height:90vh; overflow:hidden; display:flex; flex-direction:column;" @click.stop>
                    <div style="background:#0F1B2D; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                        <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;" x-text="editingUser ? 'Edit User' : 'New User'"></h3>
                        <div style="display:flex; align-items:center; gap:14px;">
                            <template x-if="editingUser">
                                <label @click.stop style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; user-select:none;">
                                    <span style="font-size:11px; font-weight:600; letter-spacing:.03em;" :style="editingUser.is_active ? 'color:#4ade80;' : 'color:rgba(255,255,255,.35);'" x-text="editingUser.is_active ? 'Active' : 'Inactive'"></span>
                                    <span class="sp-toggle">
                                        <input type="checkbox" :checked="!!editingUser.is_active" @change="toggleActive(editingUser)">
                                        <span class="sp-toggle-track"></span>
                                        <span class="sp-toggle-knob"></span>
                                    </span>
                                </label>
                            </template>
                            <button @click="showModal=false" style="background:none; border:none; color:rgba(255,255,255,.4); cursor:pointer; font-size:20px;">&times;</button>
                        </div>
                    </div>
                    <div style="padding:24px; overflow-y:auto; display:flex; flex-direction:column; gap:16px;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Username</label><input type="text" x-model="form.username" class="sp-search" style="width:100%;"></div>
                            <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Full Name</label><input type="text" x-model="form.full_name" class="sp-search" style="width:100%;"></div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Display Name</label><input type="text" x-model="form.display_name" class="sp-search" style="width:100%;"></div>
                            <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Email</label><input type="email" x-model="form.email" class="sp-search" style="width:100%;"></div>
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Password</label>
                            <template x-if="!editingUser">
                                <input type="text" x-model="form.password" class="sp-search" style="width:100%;" autocomplete="off">
                            </template>
                            <template x-if="editingUser && !changingPassword">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span class="sp-mono" style="flex:1; padding:8px 12px; background:#f9f9f7; border:1px solid #e8e4dc; border-radius:8px; font-size:13px; color:#333;" x-text="showModalPassword ? (editingUser.password_plain || '(not set)') : (editingUser.password_plain ? '••••••••' : '(not set)')"></span>
                                    <button type="button" @click="showModalPassword=!showModalPassword" style="width:32px; height:32px; border-radius:6px; border:1.5px solid rgba(138,138,130,.2); background:rgba(138,138,130,.06); color:#8a8a82; cursor:pointer; font-size:13px; display:flex; align-items:center; justify-content:center; flex-shrink:0;" :title="showModalPassword ? 'Hide' : 'Show'" x-text="showModalPassword ? '👁' : '👁‍🗨'"></button>
                                    <button type="button" @click="changingPassword=true" style="font-size:11px; font-weight:600; padding:6px 14px; border-radius:6px; border:1.5px solid rgba(201,168,76,.3); background:rgba(201,168,76,.07); color:#B8973F; cursor:pointer; white-space:nowrap;">Change</button>
                                </div>
                            </template>
                            <template x-if="editingUser && changingPassword">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <input type="text" x-model="form.password" class="sp-search" style="flex:1;" autocomplete="off" placeholder="Enter new password">
                                    <button type="button" @click="changingPassword=false; form.password=editingUser.password_plain||''" style="font-size:11px; font-weight:600; padding:6px 14px; border-radius:6px; border:1.5px solid rgba(138,138,130,.2); background:rgba(138,138,130,.06); color:#8a8a82; cursor:pointer; white-space:nowrap;">Cancel</button>
                                </div>
                            </template>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Job Title</label>
                                <template x-if="!addingJobTitle">
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <select x-model="form.job_title" class="sp-select" style="flex:1;">
                                            <option value="">— None —</option>
                                            <template x-for="jt in jobTitleOptions" :key="jt">
                                                <option :value="jt" x-text="jt"></option>
                                            </template>
                                        </select>
                                        <button type="button" @click="addingJobTitle=true; form.job_title=''" style="width:32px; height:32px; border-radius:6px; border:1.5px solid rgba(201,168,76,.3); background:rgba(201,168,76,.07); color:#B8973F; cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center; flex-shrink:0;" title="Add new title">+</button>
                                    </div>
                                </template>
                                <template x-if="addingJobTitle">
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <input type="text" x-model="form.job_title" placeholder="New job title..." class="sp-search" style="flex:1;">
                                        <button type="button" @click="if(form.job_title && !jobTitleOptions.includes(form.job_title)){jobTitleOptions.push(form.job_title); jobTitleOptions.sort();} addingJobTitle=false" style="width:32px; height:32px; border-radius:6px; border:1.5px solid rgba(26,158,106,.3); background:rgba(26,158,106,.07); color:#1a9e6a; cursor:pointer; font-size:14px; display:flex; align-items:center; justify-content:center; flex-shrink:0;" title="Confirm">&#10003;</button>
                                        <button type="button" @click="addingJobTitle=false; form.job_title=editingUser?.job_title||''" style="width:32px; height:32px; border-radius:6px; border:1.5px solid rgba(138,138,130,.2); background:rgba(138,138,130,.06); color:#8a8a82; cursor:pointer; font-size:14px; display:flex; align-items:center; justify-content:center; flex-shrink:0;" title="Cancel">&times;</button>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Card Last 4</label>
                                <template x-if="!editingUser || changingCard">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <input type="text" x-model="form.card_last4" placeholder="1234" maxlength="4" class="sp-search" style="flex:1;">
                                        <template x-if="editingUser">
                                            <button type="button" @click="changingCard=false; form.card_last4=editingUser.card_last4||''" style="font-size:11px; font-weight:600; padding:6px 14px; border-radius:6px; border:1.5px solid rgba(138,138,130,.2); background:rgba(138,138,130,.06); color:#8a8a82; cursor:pointer; white-space:nowrap;">Cancel</button>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="editingUser && !changingCard">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span class="sp-mono" style="flex:1; padding:8px 12px; background:#f9f9f7; border:1px solid #e8e4dc; border-radius:8px; font-size:13px; color:#333;" x-text="editingUser.card_last4 || '(not set)'"></span>
                                        <button type="button" @click="changingCard=true" style="font-size:11px; font-weight:600; padding:6px 14px; border-radius:6px; border:1.5px solid rgba(201,168,76,.3); background:rgba(201,168,76,.07); color:#B8973F; cursor:pointer; white-space:nowrap;">Change</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:12px; align-items:end;">
                            <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Role</label><select x-model="form.role" @change="onRoleChange()" class="sp-select" style="width:100%;"><option value="admin">Admin</option><option value="manager">Manager</option><option value="attorney">Attorney</option><option value="paralegal">Paralegal</option><option value="billing">Billing</option><option value="accounting">Accounting</option></select></div>
                            <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Team</label><select x-model="form.team" class="sp-select" style="width:100%;"><template x-for="opt in teamOptions" :key="opt.value"><option :value="opt.value" x-text="opt.label"></option></template></select></div>
                            <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Commission %</label><input type="number" step="0.01" min="5" max="20" x-model="form.commission_rate" class="sp-search" style="width:100%;"></div>
                            <div style="padding-bottom:4px;"><label style="display:flex; align-items:center; gap:6px; font-size:12px; cursor:pointer;"><input type="checkbox" x-model="form.uses_presuit_offer" style="accent-color:#C9A84C;"><span style="color:#6b7280;">Presuit</span></label></div>
                        </div>
                        <div style="border-top:1px solid #e8e4dc; padding-top:16px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                                <span style="font-size:13px; font-weight:600; color:#1a2535;">Feature Permissions</span>
                                <button @click="resetPermissions()" style="font-size:12px; color:#C9A84C; background:none; border:none; cursor:pointer; text-decoration:underline;">Reset to defaults</button>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:4px;">
                                <template x-for="perm in allPermissions" :key="perm.key || perm.group">
                                    <div :style="perm.group ? 'grid-column:1/-1' : ''">
                                        <div x-show="perm.group" style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; padding:8px 8px 2px; border-top:1px solid #f1f0ec; margin-top:4px;" x-text="perm.group"></div>
                                        <label x-show="perm.key" style="display:flex; align-items:center; gap:6px; padding:4px 8px; border-radius:6px; cursor:pointer; font-size:12px;" onmouseover="this.style.background='rgba(201,168,76,.05)'" onmouseout="this.style.background=''">
                                            <input type="checkbox" :checked="form.permissions.includes(perm.key)" @change="togglePermission(perm.key)" style="accent-color:#C9A84C;"><span x-text="perm.label"></span>
                                        </label>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                        <div>
                            <template x-if="editingUser">
                                <button type="button" @click="deleteUser(editingUser)" style="font-size:11px; font-weight:600; padding:6px 14px; border-radius:6px; cursor:pointer; border:1.5px solid rgba(239,68,68,.3); background:rgba(239,68,68,.05); color:#dc2626;">Delete</button>
                            </template>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <button @click="showModal=false" class="sp-btn">Cancel</button>
                            <button @click="saveUser()" :disabled="saving" class="sp-new-btn-navy" x-text="saving ? 'Saving...' : 'Save'"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /usersPage -->
    </div><!-- /users tab -->

    <!-- ══════════════════════════════════════════════ -->
    <!-- TAB 2: Data Management                        -->
    <!-- ══════════════════════════════════════════════ -->
    <template x-if="pageTab === 'data'">
    <div>
        <?php include __DIR__ . '/_data-management-content.php'; ?>
    </div>
    </template><!-- /data tab -->

</div><!-- /pageTab wrapper -->
