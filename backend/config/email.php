<?php
// SMTP Configuration (Gmail)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'bridgelawpllc@gmail.com');
define('SMTP_PASSWORD', 'lduq heqy sjff fxir');
define('SMTP_ENCRYPTION', 'tls');               // STARTTLS
define('SMTP_FROM_EMAIL', 'bridgelawpllc@gmail.com');
define('SMTP_FROM_NAME', 'Bridge Law & Associates - Records Department');

// eFax Configuration (Faxage)
define('FAX_SERVICE', 'faxage');
define('FAXAGE_USERNAME', 'Bridgelaw');
define('FAXAGE_COMPANY', '123107');
define('FAXAGE_PASSWORD', 'FDbtldk33#');
define('FAXAGE_API_URL', 'https://www.faxage.com/httpsfax.php');
define('FAX_CALLER_ID', '+14255820819');        // Firm's fax number (E.164 format)

// Legacy Phaxio (kept for reference, not active)
define('FAX_API_KEY', '');
define('FAX_API_SECRET', '');
define('FAX_API_URL', 'https://api.phaxio.com/v2.1/faxes');
define('FAX_CALLBACK_URL', '');

// Firm letterhead info
define('FIRM_NAME', 'Bridge Law & Associates, PLLC');
define('FIRM_ADDRESS', '6628 212th St SW, Suite 100');
define('FIRM_CITY_STATE_ZIP', 'Lynnwood, WA 98036');
define('FIRM_PHONE', '425-678-0436');
define('FIRM_FAX', '425-582-0819');
define('FIRM_EMAIL', 'bridgelawpllc@gmail.com');

// Firm attorneys (displayed in letterhead)
define('FIRM_ATTORNEYS', json_encode([
    ['name' => 'Sunjung Baek', 'title' => 'Attorney at Law'],
    ['name' => 'Michael Charbonneau', 'title' => 'Attorney at Law'],
    ['name' => 'Chong Moon Byun', 'title' => 'Attorney at Law'],
]));

// Send settings
define('SEND_TIMEOUT', 30);
define('MAX_SEND_ATTEMPTS', 3);
