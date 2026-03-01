<div x-data="messagesPage()" x-init="init()">

    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-bold text-v2-text">Messages</h1>
            <span class="text-sm text-v2-text-light" x-show="unreadCount > 0">
                <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full" x-text="unreadCount"></span>
                unread
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button @click="markAllRead()" x-show="unreadCount > 0"
                    class="px-3 py-1.5 text-sm border border-v2-card-border rounded-lg hover:bg-v2-bg text-v2-text-mid">
                Mark All Read
            </button>
            <button @click="openComposeModal()"
                    class="px-3 py-1.5 text-sm bg-gold text-navy font-semibold rounded-lg hover:bg-gold/90">
                + New Message
            </button>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex gap-0 border-b border-v2-card-border mb-3">
        <template x-for="f in [{key:'all',label:'All'},{key:'unread',label:'Unread'},{key:'sent',label:'Sent'}]" :key="f.key">
            <button @click="filter = f.key; loadMessages()"
                    class="px-4 py-2 text-sm font-semibold transition-colors border-b-2 -mb-px"
                    :class="filter === f.key
                        ? 'text-gold border-gold'
                        : 'text-v2-text-light border-transparent hover:text-v2-text-mid'"
                    x-text="f.label"></button>
        </template>
    </div>

    <!-- Messages Table -->
    <div class="bg-white rounded-lg shadow-sm border border-v2-card-border overflow-hidden">
        <div x-show="loading" class="px-4 py-8 text-center text-v2-text-light text-sm">Loading...</div>
        <div x-show="!loading && messages.length === 0" class="px-4 py-8 text-center text-v2-text-light text-sm">No messages</div>
        <div x-show="!loading && messages.length > 0" class="divide-y divide-v2-card-border">
            <template x-for="(msg, idx) in messages" :key="msg.id + '-' + idx">
                <div @click="viewMessage(msg)" class="px-4 py-3 hover:bg-v2-bg cursor-pointer flex items-center gap-3 transition-colors"
                     :class="msg.direction === 'received' && !parseInt(msg.is_read) ? 'bg-blue-50/50' : ''">
                    <!-- Direction indicator -->
                    <div class="flex-shrink-0 w-6 text-center">
                        <svg x-show="msg.direction === 'received'" class="w-4 h-4 text-blue-500 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                        <svg x-show="msg.direction === 'sent'" class="w-4 h-4 text-emerald-500 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                    </div>
                    <!-- Unread dot -->
                    <div class="flex-shrink-0 w-2">
                        <div x-show="msg.direction === 'received' && !parseInt(msg.is_read)" class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    </div>
                    <!-- From / To -->
                    <div class="w-32 flex-shrink-0">
                        <span x-show="msg.direction === 'received'" class="text-sm text-v2-text" :class="!parseInt(msg.is_read) ? 'font-semibold' : ''" x-text="msg.from_name"></span>
                        <span x-show="msg.direction === 'sent'" class="text-sm text-v2-text-mid" x-text="'To: ' + msg.to_name"></span>
                    </div>
                    <!-- Subject + preview -->
                    <div class="flex-1 min-w-0">
                        <span class="text-sm text-v2-text truncate block" :class="msg.direction === 'received' && !parseInt(msg.is_read) ? 'font-semibold' : ''" x-text="msg.subject"></span>
                    </div>
                    <!-- Time -->
                    <div class="flex-shrink-0 text-xs text-v2-text-light" x-text="timeAgo(msg.created_at)"></div>
                    <!-- Delete (received only) -->
                    <button x-show="msg.direction === 'received'" @click.stop="deleteMessage(msg.id)" title="Delete"
                            class="flex-shrink-0 p-1 text-v2-text-light hover:text-red-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- View Message Modal -->
    <template x-if="showViewModal">
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="closeViewModal()">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
                <div class="flex items-center justify-between px-5 py-4 border-b border-v2-card-border">
                    <h3 class="font-semibold text-v2-text text-lg">Message</h3>
                    <button @click="closeViewModal()" class="text-v2-text-light hover:text-v2-text">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-3">
                    <div class="flex items-center gap-4 text-sm">
                        <template x-if="viewingMessage.direction === 'received'">
                            <div><span class="text-v2-text-light">From:</span> <span class="font-medium text-v2-text" x-text="viewingMessage.from_name"></span></div>
                        </template>
                        <template x-if="viewingMessage.direction === 'sent'">
                            <div><span class="text-v2-text-light">To:</span> <span class="font-medium text-v2-text" x-text="viewingMessage.to_name"></span></div>
                        </template>
                        <div class="text-v2-text-light text-xs" x-text="timeAgo(viewingMessage.created_at)"></div>
                    </div>
                    <div class="text-sm font-semibold text-v2-text" x-text="viewingMessage.subject"></div>
                    <div class="text-sm text-v2-text bg-v2-bg rounded-lg p-3 whitespace-pre-wrap" x-text="viewingMessage.message"></div>
                </div>
                <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-v2-card-border">
                    <template x-if="viewingMessage.direction === 'received'">
                        <button @click="replyToMessage()" class="px-4 py-2 text-sm bg-gold text-navy font-semibold rounded-lg hover:bg-gold/90">
                            Reply
                        </button>
                    </template>
                    <button @click="closeViewModal()" class="px-4 py-2 text-sm border border-v2-card-border rounded-lg hover:bg-v2-bg text-v2-text-mid">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Compose Modal -->
    <template x-if="showComposeModal">
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="closeComposeModal()">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
                <div class="flex items-center justify-between px-5 py-4 border-b border-v2-card-border">
                    <h3 class="font-semibold text-v2-text text-lg" x-text="composeForm.replyTo ? 'Reply' : 'New Message'"></h3>
                    <button @click="closeComposeModal()" class="text-v2-text-light hover:text-v2-text">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">To</label>
                        <select x-model="composeForm.to_user_id" class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                            <option value="">Select recipient...</option>
                            <template x-for="u in staffList" :key="u.id">
                                <option :value="u.id" x-text="u.full_name" :selected="composeForm.to_user_id == u.id"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Subject</label>
                        <input type="text" x-model="composeForm.subject" maxlength="200"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm"
                               placeholder="Message subject">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-v2-text-mid mb-1">Message</label>
                        <textarea x-model="composeForm.message" rows="5" maxlength="5000"
                                  class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm resize-none"
                                  placeholder="Type your message..."></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-v2-card-border">
                    <button @click="closeComposeModal()" class="px-4 py-2 text-sm border border-v2-card-border rounded-lg hover:bg-v2-bg text-v2-text-mid">
                        Cancel
                    </button>
                    <button @click="sendMessage()" :disabled="sending"
                            class="px-4 py-2 text-sm bg-gold text-navy font-semibold rounded-lg hover:bg-gold/90 disabled:opacity-50">
                        <span x-show="!sending">Send</span>
                        <span x-show="sending">Sending...</span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
