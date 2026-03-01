/**
 * Admin Users Page Controller
 */
function usersPage() {
    return {
        users: [],
        loading: true,
        search: '',
        filterRole: '',

        // Modal state
        showModal: false,
        editingUser: null,
        saving: false,

        // Password reset
        showPwModal: false,
        pwUser: null,
        newPassword: '',

        // Form
        form: {
            username: '',
            full_name: '',
            display_name: '',
            email: '',
            password: '',
            job_title: '',
            card_last4: '',
            team: '',
            role: 'staff',
            commission_rate: 10,
            uses_presuit_offer: true,
            permissions: []
        },

        // Team options
        teamOptions: [
            { value: '', label: 'No Team' },
            { value: 'prelitigation', label: 'Prelitigation' },
            { value: 'billing', label: 'Billing' },
            { value: 'attorney', label: 'Attorney' },
            { value: 'accounting', label: 'Accounting' }
        ],

        // Role badge colors
        roleColors: {
            admin: 'bg-purple-100 text-purple-700',
            manager: 'bg-blue-100 text-blue-700',
            accounting: 'bg-yellow-100 text-yellow-700',
            staff: 'bg-green-100 text-green-700',
            attorney: 'bg-indigo-100 text-indigo-700'
        },

        // All permission definitions (matches backend auth.php)
        allPermissions: [
            { key: 'dashboard', label: 'Dashboard' },
            { key: 'cases', label: 'Cases (MR)' },
            { key: 'providers', label: 'Providers' },
            { key: 'mr_tracker', label: 'MR Tracker' },
            { key: 'prelitigation_tracker', label: 'Prelitigation Tracker' },
            { key: 'accounting_tracker', label: 'Accounting Tracker' },
            { key: 'attorney_cases', label: 'Attorney Cases' },
            { key: 'traffic', label: 'Traffic' },
            { key: 'commissions', label: 'Commissions' },
            { key: 'commission_admin', label: 'Commission Admin' },
            { key: 'referrals', label: 'Referrals' },
            { key: 'mbds', label: 'MBDS' },
            { key: 'health_tracker', label: 'Health Tracker' },
            { key: 'expense_report', label: 'Expense Report' },
            { key: 'bank_reconciliation', label: 'Bank Reconciliation' },
            { key: 'reports', label: 'Reports' },
            { key: 'goals', label: 'Goals' },
            { key: 'users', label: 'Users' },
            { key: 'templates', label: 'Templates' },
            { key: 'activity_log', label: 'Activity Log' },
            { key: 'data_management', label: 'Data Management' },
            { key: 'messages', label: 'Messages' }
        ],

        // Role → default permissions mapping (mirrors backend)
        roleDefaults: {
            admin: [
                'dashboard','cases','providers','mr_tracker',
                'prelitigation_tracker','accounting_tracker',
                'attorney_cases','traffic','commissions','commission_admin',
                'referrals','mbds','health_tracker','expense_report',
                'bank_reconciliation','reports','goals',
                'users','templates','activity_log','data_management','messages'
            ],
            manager: [
                'dashboard','cases','providers','mr_tracker',
                'prelitigation_tracker','accounting_tracker',
                'attorney_cases','commissions','referrals',
                'reports','goals','messages','templates'
            ],
            accounting: [
                'dashboard','cases','providers','mr_tracker',
                'accounting_tracker',
                'mbds','health_tracker','expense_report',
                'bank_reconciliation','messages'
            ],
            staff: [
                'dashboard','cases','providers','mr_tracker',
                'prelitigation_tracker',
                'commissions','referrals','goals','messages'
            ],
            attorney: [
                'dashboard','attorney_cases','traffic',
                'commissions','messages'
            ]
        },

        init() {
            this.loadUsers();
        },

        async loadUsers() {
            this.loading = true;
            try {
                let url = 'users?';
                if (this.search) url += `search=${encodeURIComponent(this.search)}&`;
                if (this.filterRole) url += `role=${this.filterRole}&`;
                const res = await api.get(url);
                this.users = res.data || [];
            } catch (e) {
                showToast(e.message, 'error');
            }
            this.loading = false;
        },

        openCreateModal() {
            this.editingUser = null;
            this.form = {
                username: '',
                full_name: '',
                display_name: '',
                email: '',
                password: '',
                job_title: '',
                card_last4: '',
                team: '',
                role: 'staff',
                commission_rate: 10,
                uses_presuit_offer: true,
                permissions: [...this.roleDefaults.staff]
            };
            this.showModal = true;
        },

        openEditModal(user) {
            this.editingUser = user;
            this.form = {
                username: user.username,
                full_name: user.full_name,
                display_name: user.display_name || '',
                email: user.email || '',
                password: '',
                job_title: user.job_title || '',
                card_last4: user.card_last4 || '',
                team: user.team || '',
                role: user.role,
                commission_rate: user.commission_rate,
                uses_presuit_offer: !!user.uses_presuit_offer,
                permissions: Array.isArray(user.permissions)
                    ? [...user.permissions]
                    : [...(this.roleDefaults[user.role] || this.roleDefaults.staff)]
            };
            this.showModal = true;
        },

        onRoleChange() {
            this.form.permissions = [...(this.roleDefaults[this.form.role] || this.roleDefaults.staff)];
        },

        resetPermissions() {
            this.form.permissions = [...(this.roleDefaults[this.form.role] || this.roleDefaults.staff)];
        },

        togglePermission(key) {
            const idx = this.form.permissions.indexOf(key);
            if (idx >= 0) {
                this.form.permissions.splice(idx, 1);
            } else {
                this.form.permissions.push(key);
            }
        },

        async saveUser() {
            if (!this.form.username || !this.form.full_name) {
                showToast('Username and Full Name are required', 'error');
                return;
            }
            if (!this.editingUser && !this.form.password) {
                showToast('Password is required for new users', 'error');
                return;
            }

            this.saving = true;
            try {
                const payload = {
                    username: this.form.username,
                    full_name: this.form.full_name,
                    display_name: this.form.display_name || this.form.full_name,
                    email: this.form.email,
                    job_title: this.form.job_title || '',
                    card_last4: this.form.card_last4 || '',
                    team: this.form.team,
                    role: this.form.role,
                    commission_rate: parseFloat(this.form.commission_rate) || 0,
                    uses_presuit_offer: this.form.uses_presuit_offer ? 1 : 0,
                    permissions: this.form.permissions
                };

                if (this.editingUser) {
                    await api.put(`users/${this.editingUser.id}`, payload);
                    showToast('User updated', 'success');
                } else {
                    payload.password = this.form.password;
                    await api.post('users', payload);
                    showToast('User created', 'success');
                }

                this.showModal = false;
                await this.loadUsers();
            } catch (e) {
                showToast(e.data?.message || e.message || 'Failed to save user', 'error');
            }
            this.saving = false;
        },

        resetPassword(user) {
            this.pwUser = user;
            this.newPassword = '';
            this.showPwModal = true;
        },

        async doResetPassword() {
            if (!this.newPassword) {
                showToast('Enter a new password', 'error');
                return;
            }
            try {
                await api.put(`users/${this.pwUser.id}/reset-password`, {
                    password: this.newPassword
                });
                showToast('Password reset successfully', 'success');
                this.showPwModal = false;
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async toggleActive(user) {
            const action = user.is_active ? 'disable' : 'enable';
            if (!confirm(`Are you sure you want to ${action} ${user.full_name}?`)) return;
            try {
                await api.put(`users/${user.id}/toggle-active`);
                showToast(`User ${action}d`, 'success');
                await this.loadUsers();
            } catch (e) {
                showToast(e.message, 'error');
            }
        }
    };
}
