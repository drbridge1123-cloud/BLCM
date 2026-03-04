/**
 * CMC - Shared API Helper & Utilities
 */

const api = {
    async get(endpoint) {
        return this._request(endpoint, { method: 'GET' });
    },
    async post(endpoint, body) {
        return this._request(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
    },
    async put(endpoint, body) {
        return this._request(endpoint, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
    },
    async delete(endpoint) {
        return this._request(endpoint, { method: 'DELETE' });
    },
    async upload(endpoint, formData, onProgress) {
        const url = endpoint.startsWith('http') ? endpoint : `/CMCdemo/backend/api/${endpoint}`;
        try {
            if (onProgress && typeof onProgress === 'function') {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            onProgress(Math.round((e.loaded / e.total) * 100));
                        }
                    });
                    xhr.addEventListener('load', () => {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            if (xhr.status === 401) {
                                window.location.href = '/CMCdemo/frontend/pages/auth/login.php';
                                return;
                            }
                            if (xhr.status >= 200 && xhr.status < 300) {
                                resolve(data);
                            } else {
                                reject({ response: { status: xhr.status }, data });
                            }
                        } catch (error) {
                            reject(error);
                        }
                    });
                    xhr.addEventListener('error', () => reject(new Error('Upload failed')));
                    xhr.open('POST', url);
                    xhr.send(formData);
                });
            }
            const response = await fetch(url, { method: 'POST', body: formData });
            const data = await response.json();
            if (response.status === 401) {
                window.location.href = '/CMCdemo/frontend/pages/auth/login.php';
                return null;
            }
            if (!response.ok) throw { response, data };
            return data;
        } catch (error) {
            if (error.data) throw error;
            console.error('Upload failed:', error);
            showToast('Upload failed. Please try again.', 'error');
            throw error;
        }
    },
    async _request(endpoint, options = {}) {
        const url = endpoint.startsWith('http') ? endpoint : `/CMCdemo/backend/api/${endpoint}`;
        try {
            const res = await fetch(url, options);
            if (res.status === 401) {
                window.location.href = '/CMCdemo/frontend/pages/auth/login.php';
                return null;
            }
            const text = await res.text();
            let data;
            try { data = JSON.parse(text); }
            catch (e) {
                console.error('Invalid JSON:', text.substring(0, 300));
                throw new Error('Server returned invalid response');
            }
            if (!res.ok) throw { response: res, data };
            return data;
        } catch (error) {
            if (error.data) throw error;
            if (error.name !== 'AbortError') {
                console.error('API call failed:', error);
            }
            throw error;
        }
    }
};

// Toast notifications
function showToast(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const colors = {
        success: 'bg-green-500', error: 'bg-red-500',
        warning: 'bg-yellow-500', info: 'bg-blue-500'
    };
    const toast = document.createElement('div');
    toast.className = `${colors[type] || colors.info} text-white px-4 py-2.5 rounded-lg shadow-lg text-sm font-medium transform transition-all duration-300 translate-x-full`;
    toast.textContent = message;
    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.remove('translate-x-full'));
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Format currency
function formatCurrency(amount) {
    const num = parseFloat(amount) || 0;
    return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Parse currency string to number (removes $, commas, spaces)
function parseCurrency(str) {
    if (typeof str === 'number') return str;
    if (!str) return 0;
    return parseFloat(String(str).replace(/[$,\s]/g, '')) || 0;
}

// Format date
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// Debounce
function debounce(fn, ms = 300) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), ms);
    };
}

// URL query params helper
function getQueryParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

// Confirm dialog (Promise-based)
function confirmAction(message) {
    return new Promise((resolve) => {
        if (confirm(message)) {
            resolve(true);
        } else {
            resolve(false);
        }
    });
}

// Days elapsed since a date
function daysElapsed(dateStr) {
    if (!dateStr) return null;
    const date = new Date(dateStr);
    const now = new Date();
    return Math.floor((now - date) / (1000 * 60 * 60 * 24));
}

// Status label mapping
const STATUS_LABELS = {
    treating: 'Treating',
    treatment_complete: 'Tx Complete',
    not_started: 'Not Started',
    requesting: 'Requesting',
    follow_up: 'Follow Up',
    action_needed: 'Action Needed',
    received_partial: 'Partial',
    on_hold: 'On Hold',
    no_records: 'No Records',
    received_complete: 'Complete',
    verified: 'Verified',
    ini: 'Treatment',
    rec: 'Collection',
    verification: 'Verification',
    rfd: 'Demand',
    neg: 'Negotiate',
    lit: 'Litigation',
    final_verification: 'Settlement',
    accounting: 'Accounting',
    closed: 'Closed',
};

function getStatusLabel(status) {
    return STATUS_LABELS[status] || status;
}

// Record type short labels
function getRecordTypeShort(type) {
    const shorts = {
        medical_records: 'MR',
        billing: 'Bill',
        chart: 'Chart',
        imaging: 'Img',
        op_report: 'Op'
    };
    return shorts[type] || type;
}

// Workflow transitions
const FORWARD_TRANSITIONS = {
    ini:                 ['rec'],
    rec:                 ['verification'],
    verification:        ['rfd'],
    rfd:                 ['neg'],
    neg:                 ['lit'],
    lit:                 ['final_verification'],
    final_verification:  ['accounting'],
    accounting:          ['closed'],
    closed:              [],
};

const BACKWARD_TRANSITIONS = {
    ini:                 [],
    rec:                 ['ini'],
    verification:        ['ini', 'rec'],
    rfd:                 ['ini', 'rec', 'verification'],
    neg:                 ['ini', 'rec', 'verification', 'rfd'],
    lit:                 ['ini', 'rec', 'verification', 'rfd', 'neg'],
    final_verification:  ['ini', 'rec', 'verification', 'rfd', 'neg', 'lit'],
    accounting:          ['ini', 'rec', 'verification', 'rfd', 'neg', 'lit', 'final_verification'],
    closed:              ['ini', 'rec', 'verification', 'rfd', 'neg', 'lit', 'final_verification', 'accounting'],
};
