<!-- All sp- styles loaded from shared sp-design-system.css -->

<div x-data="usersPage()">

    <!-- ═══ Unified Card ═══ -->
    <div class="sp-card">
        <div class="sp-gold-bar"></div>
        <div class="sp-header" style="flex-wrap:wrap; gap:16px;">
            <div style="flex:1;">
                <div class="sp-eyebrow">Admin</div>
                <h1 class="sp-title">Users</h1>
            </div>
            <button @click="openCreateModal()" class="sp-new-btn-navy">+ New User</button>
        </div>

        <div class="sp-toolbar">
            <div class="sp-toolbar-right" style="display:flex; gap:8px; width:100%;">
                <input type="text" x-model="search" @input.debounce.300ms="loadUsers()" placeholder="Search users..." class="sp-search" style="flex:1; min-width:200px;">
                <select x-model="filterRole" @change="loadUsers()" class="sp-select">
                    <option value="">All Roles</option><option value="admin">Admin</option><option value="manager">Manager</option><option value="accounting">Accounting</option><option value="staff">Staff</option><option value="attorney">Attorney</option>
                </select>
            </div>
        </div>

        <table class="sp-table">
            <thead><tr>
                <th>Username</th><th>Full Name</th><th>Role</th>
                <th style="text-align:center;">Commission %</th><th style="text-align:center;">Status</th><th style="text-align:center;">Actions</th>
            </tr></thead>
            <tbody>
                <template x-if="loading"><tr><td colspan="6" class="sp-empty">Loading...</td></tr></template>
                <template x-if="!loading && users.length === 0"><tr><td colspan="6" class="sp-empty">No users found</td></tr></template>
                <template x-for="u in users" :key="u.id">
                    <tr>
                        <td style="font-weight:600;" x-text="u.username"></td>
                        <td x-text="u.full_name"></td>
                        <td><span class="sp-stage" :class="roleColors[u.role]" x-text="u.role"></span></td>
                        <td style="text-align:center;" class="sp-mono" x-text="u.commission_rate + '%'"></td>
                        <td style="text-align:center;"><span class="sp-stage" :style="u.is_active ? 'background:#dcfce7; color:#15803d;' : 'background:#fee2e2; color:#dc2626;'" x-text="u.is_active ? 'Active' : 'Inactive'"></span></td>
                        <td style="text-align:center;">
                            <div class="sp-actions" style="justify-content:center;">
                                <button @click="openEditModal(u)" class="sp-act sp-act-gold" title="Edit">Edit</button>
                                <button @click="resetPassword(u)" class="sp-act sp-act-blue" title="Reset Password">PW</button>
                                <button @click="toggleActive(u)" class="sp-act" :class="u.is_active ? 'sp-act-red' : 'sp-act-green'" x-text="u.is_active ? 'Disable' : 'Enable'"></button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Create/Edit User Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45);" @click.self="showModal=false">
        <div style="background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22); width:100%; max-width:560px; max-height:90vh; overflow:hidden; display:flex; flex-direction:column;" @click.stop>
            <div style="background:#0F1B2D; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;" x-text="editingUser ? 'Edit User' : 'New User'"></h3>
                <button @click="showModal=false" style="background:none; border:none; color:rgba(255,255,255,.4); cursor:pointer; font-size:20px;">&times;</button>
            </div>
            <div style="padding:24px; overflow-y:auto; display:flex; flex-direction:column; gap:16px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Username</label><input type="text" x-model="form.username" :disabled="editingUser" class="sp-search" style="width:100%;"></div>
                    <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Full Name</label><input type="text" x-model="form.full_name" class="sp-search" style="width:100%;"></div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Display Name</label><input type="text" x-model="form.display_name" class="sp-search" style="width:100%;"></div>
                    <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Email</label><input type="email" x-model="form.email" class="sp-search" style="width:100%;"></div>
                </div>
                <template x-if="!editingUser"><div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Password</label><input type="password" x-model="form.password" class="sp-search" style="width:100%;"></div></template>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Job Title</label><input type="text" x-model="form.job_title" placeholder="e.g., Administrator" class="sp-search" style="width:100%;"></div>
                    <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Card Last 4</label><input type="text" x-model="form.card_last4" placeholder="1234" maxlength="4" class="sp-search" style="width:100%;"></div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:12px; align-items:end;">
                    <div><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Role</label><select x-model="form.role" @change="onRoleChange()" class="sp-select" style="width:100%;"><option value="staff">Staff</option><option value="attorney">Attorney</option><option value="manager">Manager</option><option value="accounting">Accounting</option><option value="admin">Admin</option></select></div>
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
                        <template x-for="perm in allPermissions" :key="perm.key">
                            <label style="display:flex; align-items:center; gap:6px; padding:4px 8px; border-radius:6px; cursor:pointer; font-size:12px;" onmouseover="this.style.background='rgba(201,168,76,.05)'" onmouseout="this.style.background=''">
                                <input type="checkbox" :checked="form.permissions.includes(perm.key)" @change="togglePermission(perm.key)" style="accent-color:#C9A84C;"><span x-text="perm.label"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
            <div style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
                <button @click="showModal=false" class="sp-btn">Cancel</button>
                <button @click="saveUser()" :disabled="saving" class="sp-new-btn-navy" x-text="saving ? 'Saving...' : 'Save'"></button>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div x-show="showPwModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45);" @click.self="showPwModal=false">
        <div style="background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22); width:100%; max-width:380px; overflow:hidden;" @click.stop>
            <div style="background:#0F1B2D; padding:18px 24px;">
                <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">Reset Password</h3>
                <p style="font-size:12px; color:#C9A84C; margin-top:4px;" x-text="'User: ' + (pwUser?.full_name || '')"></p>
            </div>
            <div style="padding:24px;"><label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">New Password</label><input type="password" x-model="newPassword" class="sp-search" style="width:100%;"></div>
            <div style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; justify-content:flex-end; gap:10px;">
                <button @click="showPwModal=false" class="sp-btn">Cancel</button>
                <button @click="doResetPassword()" class="sp-new-btn-navy">Reset</button>
            </div>
        </div>
    </div>
</div>
