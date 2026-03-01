<!-- Move to UIM Modal -->
<div x-show="showToUimModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45);" @click.self="showToUimModal = false">
    <div style="background:#fff; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.22); width:100%; max-width:480px; max-height:90vh; overflow:hidden; display:flex; flex-direction:column;" @click.stop>
        <div style="background:#0F1B2D; padding:18px 24px; flex-shrink:0;">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">Move to UIM</h3>
            <div style="font-size:12px; color:#C9A84C; margin-top:4px;">
                <span x-text="settlingCase?.case_number" style="font-weight:600;"></span> — <span x-text="settlingCase?.client_name"></span>
            </div>
        </div>
        <div style="padding:24px; overflow-y:auto; display:flex; flex-direction:column; gap:16px;">
            <div>
                <label style="display:block; font-size:9.5px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px;">Note</label>
                <textarea x-model="toUimForm.note" rows="2" placeholder="Optional note..." class="sp-search" style="width:100%; resize:none;"></textarea>
            </div>
        </div>
        <div style="padding:14px 24px; border-top:1px solid #e8e4dc; display:flex; justify-content:flex-end; gap:10px; flex-shrink:0;">
            <button @click="showToUimModal = false" class="sp-btn">Cancel</button>
            <button @click="toUim()" class="sp-new-btn-navy">Move to UIM</button>
        </div>
    </div>
</div>
