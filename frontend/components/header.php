<header class="header-bar bg-white border-b border-v2-card-border px-6 py-3 flex items-center justify-between sticky top-0 z-20">
    <!-- Left: Badge + Page title -->
    <div class="flex items-center gap-4">
        <span class="hidden sm:inline-flex items-center px-3 py-1 bg-navy text-gold text-xs font-bold tracking-wider rounded">BRIDGE LAW & ASSOCIATES</span>
        <div>
            <h1 class="v2-page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
            <?php if (!empty($pageSubtitle)): ?>
                <p class="text-sm text-v2-text-light"><?= $pageSubtitle ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right side: messages -->
    <div class="flex items-center gap-2">
        <a href="/CMC/frontend/pages/messages/index.php" x-data
           class="relative inline-flex items-center p-1.5 rounded-lg hover:bg-v2-bg transition-colors" title="Messages">
            <svg class="w-5 h-5 text-v2-text-mid" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <template x-if="$store.messages && $store.messages.unreadCount > 0">
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[8px] font-bold px-1 py-0.5 rounded-full leading-none min-w-[14px] text-center"
                      x-text="$store.messages.unreadCount > 9 ? '9+' : $store.messages.unreadCount"></span>
            </template>
        </a>
    </div>
</header>
