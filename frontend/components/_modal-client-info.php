<!-- Client Info Modal (Reusable) -->
<div x-show="showClientInfoModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(0,0,0,.45);" @click.self="showClientInfoModal = false" @keydown.escape.window="showClientInfoModal = false">
    <div style="background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22); width:100%; max-width:560px; max-height:90vh; overflow:hidden; display:flex; flex-direction:column;" @click.stop>
        <!-- Header -->
        <div style="background:#0F1B2D; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;"
                x-text="clientInfoForm.id ? 'Client Info' : 'Add Client'"></h3>
            <button @click="showClientInfoModal = false" style="background:none; border:none; color:rgba(255,255,255,.4); cursor:pointer; font-size:20px;">&times;</button>
        </div>

        <!-- Loading -->
        <div x-show="clientInfoLoading" style="padding:40px; text-align:center;">
            <span style="color:#8a8a82;">Loading...</span>
        </div>

        <!-- Form -->
        <div x-show="!clientInfoLoading" style="padding:24px; overflow-y:auto; display:flex; flex-direction:column; gap:16px;">
            <!-- Name + DOB -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Name *</label>
                    <input type="text" x-model="clientInfoForm.name" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Date of Birth</label>
                    <input type="date" x-model="clientInfoForm.dob" class="sp-search" style="width:100%;">
                </div>
            </div>

            <!-- Phone + Email -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Phone</label>
                    <input type="text" x-model="clientInfoForm.phone" class="sp-search" style="width:100%;" placeholder="(xxx) xxx-xxxx">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Email</label>
                    <input type="email" x-model="clientInfoForm.email" class="sp-search" style="width:100%;">
                </div>
            </div>

            <!-- Address divider -->
            <div style="font-size:10px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; border-bottom:1px solid #e8e4dc; padding-bottom:4px; margin-top:4px;">Address</div>

            <!-- Street -->
            <div>
                <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Street</label>
                <input type="text" x-model="clientInfoForm.address_street" class="sp-search" style="width:100%;">
            </div>

            <!-- City + State + Zip -->
            <div style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">City</label>
                    <input type="text" x-model="clientInfoForm.address_city" class="sp-search" style="width:100%;">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">State</label>
                    <input type="text" x-model="clientInfoForm.address_state" class="sp-search" style="width:100%; text-transform:uppercase;" maxlength="2">
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Zip</label>
                    <input type="text" x-model="clientInfoForm.address_zip" class="sp-search" style="width:100%;" maxlength="10">
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div x-show="!clientInfoLoading" style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
            <button @click="showClientInfoModal = false" class="sp-btn">Cancel</button>
            <button @click="saveClientInfo()" :disabled="clientInfoSaving" class="sp-new-btn-navy">
                <span x-text="clientInfoSaving ? 'Saving...' : 'Save'"></span>
            </button>
        </div>
    </div>
</div>
