<div class="w-full max-w-md" x-data="{
    username: '', password: '', loading: false, error: '',
    async login() {
        this.error = '';
        this.loading = true;
        try {
            const res = await fetch('/CMCdemo/backend/api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: this.username, password: this.password })
            });
            const data = await res.json();
            if (data.success) {
                window.location.href = '/CMCdemo/frontend/pages/dashboard/index.php';
            } else {
                this.error = data.message || 'Login failed';
            }
        } catch (e) {
            this.error = 'Connection error. Please try again.';
        }
        this.loading = false;
    }
}">
    <div class="bg-white rounded-2xl shadow-xl p-8">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-navy-800 rounded-xl mb-4">
                <span class="text-2xl font-bold text-white">CMC</span>
            </div>
            <h1 class="text-2xl font-bold text-v2-text">Case Management Center</h1>
            <p class="text-v2-text-light mt-1">Bridge Law & Associates</p>
        </div>

        <!-- Error -->
        <div x-show="error" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="error"></div>

        <!-- Form -->
        <form @submit.prevent="login" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-v2-text-mid mb-1">Username</label>
                <input type="text" x-model="username" required autofocus
                    class="w-full px-4 py-2.5 border border-v2-card-border rounded-lg focus:ring-2 focus:ring-gold focus:border-gold outline-none transition"
                    placeholder="Enter username">
            </div>
            <div>
                <label class="block text-sm font-medium text-v2-text-mid mb-1">Password</label>
                <input type="password" x-model="password" required
                    class="w-full px-4 py-2.5 border border-v2-card-border rounded-lg focus:ring-2 focus:ring-gold focus:border-gold outline-none transition"
                    placeholder="Enter password">
            </div>
            <button type="submit" :disabled="loading"
                class="w-full py-2.5 bg-gold text-navy font-semibold rounded-lg font-medium hover:bg-gold/90 disabled:opacity-50 transition">
                <span x-show="!loading">Sign In</span>
                <span x-show="loading">Signing in...</span>
            </button>
        </form>
    </div>
</div>
