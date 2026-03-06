/**
 * BLCM Utility Functions & Constants
 */

// Provider type labels
const PROVIDER_TYPES = {
    hospital: 'Hospital', er: 'ER', chiro: 'Chiropractor', imaging: 'Imaging',
    physician: 'Physician', surgery_center: 'Surgery Center', pharmacy: 'Pharmacy',
    acupuncture: 'Acupuncture', massage: 'Massage', pain_management: 'Pain Management',
    pt: 'Physical Therapy', other: 'Other'
};

// Request method labels
const REQUEST_METHODS = {
    email: 'Email', fax: 'Fax', portal: 'Portal', phone: 'Phone',
    mail: 'Mail', chartswap: 'ChartSwap', online: 'Online'
};

// Request type labels
const REQUEST_TYPES = {
    initial: 'Initial', follow_up: 'Follow Up', re_request: 'Re-Request', rfd: 'RFD'
};

// Note types
const NOTE_TYPES = {
    general: 'General', follow_up: 'Follow Up', issue: 'Issue', handoff: 'Handoff'
};

// Difficulty levels
const DIFFICULTY_LEVELS = {
    easy: 'Easy', medium: 'Medium', hard: 'Hard'
};

// Case statuses
const CASE_STATUSES = {
    prelitigation: 'Prelitigation',
    collecting: 'Collection', verification: 'Verification', completed: 'Completed',
    rfd: 'Attorney', final_verification: 'Final Verification',
    disbursement: 'Disbursement', accounting: 'Accounting', closed: 'Closed'
};

// Status colors
const STATUS_COLORS = {
    prelitigation: 'bg-teal-100 text-teal-700',
    collecting: 'bg-blue-100 text-blue-700',
    verification: 'bg-yellow-100 text-yellow-700',
    completed: 'bg-green-100 text-green-700',
    rfd: 'bg-purple-100 text-purple-700',
    final_verification: 'bg-orange-100 text-orange-700',
    disbursement: 'bg-indigo-100 text-indigo-700',
    accounting: 'bg-pink-100 text-pink-700',
    closed: 'bg-gray-100 text-gray-500'
};

// Provider status colors
const PROVIDER_STATUS_COLORS = {
    treating: 'bg-gray-100 text-gray-600',
    not_started: 'bg-gray-100 text-gray-500',
    requesting: 'bg-blue-100 text-blue-700',
    follow_up: 'bg-yellow-100 text-yellow-700',
    action_needed: 'bg-red-100 text-red-700',
    received_partial: 'bg-orange-100 text-orange-700',
    on_hold: 'bg-gray-200 text-gray-600',
    no_records: 'bg-gray-200 text-gray-500',
    received_complete: 'bg-green-100 text-green-700',
    verified: 'bg-emerald-100 text-emerald-700'
};

// Insurance company types
const INSURANCE_TYPES = {
    auto: 'Auto', health: 'Health', workers_comp: "Workers' Comp",
    liability: 'Liability', um_uim: 'UM/UIM', other: 'Other'
};

// Adjuster types
const ADJUSTER_TYPES = {
    pip: 'PIP', um: 'UM', uim: 'UIM', '3rd_party': '3rd Party',
    liability: 'Liability', pd: 'PD', bi: 'BI'
};

// Lookup helpers
function getProviderTypeLabel(type) { return PROVIDER_TYPES[type] || type; }
function getRequestMethodLabel(m) { return REQUEST_METHODS[m] || m; }
function getCaseStatusLabel(s) { return CASE_STATUSES[s] || s; }
function getStatusColor(s) { return STATUS_COLORS[s] || 'bg-gray-100 text-gray-600'; }
function getProviderStatusColor(s) { return PROVIDER_STATUS_COLORS[s] || 'bg-gray-100 text-gray-600'; }

// Build query string from params object
function buildQueryString(params) {
    const parts = [];
    for (const [key, val] of Object.entries(params)) {
        if (val !== '' && val !== null && val !== undefined) {
            parts.push(`${encodeURIComponent(key)}=${encodeURIComponent(val)}`);
        }
    }
    return parts.length ? '?' + parts.join('&') : '';
}

// Relative time
function timeAgo(dateStr) {
    if (!dateStr) return '';
    const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 2592000) return Math.floor(diff / 86400) + 'd ago';
    return formatDate(dateStr);
}

// Truncate text
function truncate(str, len = 40) {
    if (!str) return '';
    return str.length > len ? str.substring(0, len) + '...' : str;
}

// Format date+time → MM/DD/YYYY h:mm AM/PM
function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const time = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    return `${m}/${day}/${d.getFullYear()} ${time}`;
}

// Auto-format phone on blur
function autoFormatPhone(el) {
    const digits = el.value.replace(/\D/g, '');
    if (digits.length === 10) {
        el.value = `(${digits.slice(0,3)}) ${digits.slice(3,6)}-${digits.slice(6)}`;
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

// Format phone
function formatPhoneNumber(phone) {
    if (!phone) return '';
    const digits = phone.replace(/\D/g, '');
    if (digits.length === 10) {
        return `(${digits.slice(0,3)}) ${digits.slice(3,6)}-${digits.slice(6)}`;
    }
    return phone;
}

// Debounced save: returns a function that tracks timers by key
// Usage: this._debounceSave = createDebouncedSave((data) => this.saveLine(data), 500);
//        this._debounceSave(line, line.id);
function createDebouncedSave(saveFn, delay = 500) {
    const timers = {};
    return function(data, key) {
        const k = key || '_default';
        clearTimeout(timers[k]);
        timers[k] = setTimeout(() => saveFn(data), delay);
    };
}

// Days until a target date (negative = overdue)
function daysUntil(dateStr) {
    if (!dateStr) return null;
    const target = new Date(dateStr + 'T00:00:00');
    const now = new Date();
    now.setHours(0, 0, 0, 0);
    return Math.floor((target - now) / (1000 * 60 * 60 * 24));
}

// Deadline info with color coding
function getDeadlineInfo(targetDate) {
    if (!targetDate) return { class: 'text-v2-text-light', label: '-', urgency: 'none' };
    const days = daysUntil(targetDate);
    if (days < 0) return { class: 'text-red-600 font-bold', label: Math.abs(days) + 'd overdue', urgency: 'overdue' };
    if (days <= 3) return { class: 'text-red-500 font-semibold', label: days + 'd left', urgency: 'critical' };
    if (days <= 7) return { class: 'text-amber-600 font-medium', label: days + 'd left', urgency: 'warning' };
    return { class: 'text-v2-text-mid', label: formatDate(targetDate), urgency: 'normal' };
}

// Dynamic scroll container
function initScrollContainer(el, bottomPadding = 16) {
    function update() {
        const t = el.getBoundingClientRect().top;
        el.style.maxHeight = (window.innerHeight - t - bottomPadding) + 'px';
        el.style.overflowY = 'auto';
    }
    requestAnimationFrame(update);
    window.addEventListener('resize', debounce(update, 100));
}
