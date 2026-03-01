function messagesPage() {
    return {
        messages: [],
        unreadCount: 0,
        loading: false,
        filter: 'all',
        staffList: [],

        // View modal
        showViewModal: false,
        viewingMessage: null,

        // Compose modal
        showComposeModal: false,
        sending: false,
        composeForm: {
            to_user_id: '',
            subject: '',
            message: '',
            replyTo: null
        },

        async init() {
            this.loadStaff();
            await this.loadMessages();

            // Poll for new messages every 30 seconds
            setInterval(() => this.loadUnreadCount(), 30000);
        },

        async loadStaff() {
            try {
                const res = await api.get('users?active_only=1');
                this.staffList = res.data || [];
            } catch(e) { this.staffList = []; }
        },

        async loadMessages() {
            this.loading = true;
            try {
                const res = await api.get('messages?filter=' + this.filter);
                this.messages = res.data || [];
                this.unreadCount = res.unread_count || 0;
                // Sync global store
                if (Alpine.store('messages')) {
                    Alpine.store('messages').unreadCount = this.unreadCount;
                }
            } catch(e) {
                this.messages = [];
            }
            this.loading = false;
        },

        async loadUnreadCount() {
            try {
                const res = await api.get('messages?filter=unread');
                this.unreadCount = res.unread_count || 0;
                if (Alpine.store('messages')) {
                    Alpine.store('messages').unreadCount = this.unreadCount;
                }
            } catch(e) {}
        },

        async viewMessage(msg) {
            this.viewingMessage = msg;
            this.showViewModal = true;

            // Auto-mark as read if unread received message
            if (msg.direction === 'received' && !parseInt(msg.is_read)) {
                try {
                    await api.put('messages/' + msg.id + '/read');
                    msg.is_read = '1';
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                    if (Alpine.store('messages')) {
                        Alpine.store('messages').unreadCount = this.unreadCount;
                    }
                } catch(e) {}
            }
        },

        closeViewModal() {
            this.showViewModal = false;
            this.viewingMessage = null;
        },

        replyToMessage() {
            if (!this.viewingMessage) return;
            const msg = this.viewingMessage;
            this.closeViewModal();
            this.composeForm = {
                to_user_id: msg.from_user_id,
                subject: msg.subject.startsWith('Re: ') ? msg.subject : 'Re: ' + msg.subject,
                message: '',
                replyTo: msg.id
            };
            this.showComposeModal = true;
        },

        openComposeModal() {
            this.composeForm = { to_user_id: '', subject: '', message: '', replyTo: null };
            this.showComposeModal = true;
        },

        closeComposeModal() {
            this.showComposeModal = false;
        },

        async sendMessage() {
            if (!this.composeForm.to_user_id) {
                showToast('Please select a recipient', 'error');
                return;
            }
            if (!this.composeForm.subject.trim()) {
                showToast('Subject is required', 'error');
                return;
            }
            if (!this.composeForm.message.trim()) {
                showToast('Message is required', 'error');
                return;
            }

            this.sending = true;
            try {
                await api.post('messages', {
                    to_user_id: parseInt(this.composeForm.to_user_id),
                    subject: this.composeForm.subject.trim(),
                    message: this.composeForm.message.trim()
                });
                showToast('Message sent', 'success');
                this.closeComposeModal();
                await this.loadMessages();
            } catch(e) {
                showToast(e.data?.message || 'Failed to send message', 'error');
            }
            this.sending = false;
        },

        async deleteMessage(id) {
            if (!confirm('Delete this message?')) return;
            try {
                await api.delete('messages/' + id);
                this.messages = this.messages.filter(m => m.id !== id);
                showToast('Message deleted', 'success');
            } catch(e) {
                showToast(e.data?.message || 'Failed to delete', 'error');
            }
        },

        async markAllRead() {
            try {
                await api.put('messages/read-all');
                this.messages.forEach(m => { if (m.direction === 'received') m.is_read = '1'; });
                this.unreadCount = 0;
                if (Alpine.store('messages')) {
                    Alpine.store('messages').unreadCount = 0;
                }
                showToast('All messages marked as read', 'success');
            } catch(e) {
                showToast('Failed to mark all read', 'error');
            }
        }
    };
}
