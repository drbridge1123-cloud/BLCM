<?php
/**
 * Send a fax using the configured fax service.
 *
 * @param string $toNumber  Fax number
 * @param string $htmlBody  HTML content (used by Phaxio, ignored by Faxage)
 * @param array  $options   Optional: 'pdf_path', 'attachments', 'caller_id'
 * @return array ['success' => bool, 'fax_id' => string|null, 'error' => string|null]
 */
function sendFax($toNumber, $htmlBody, $options = []) {
    $toNumber = normalizePhoneNumber($toNumber);

    switch (FAX_SERVICE) {
        case 'faxage':
            return sendFaxViaFaxage($toNumber, $options);
        case 'phaxio':
            if (empty(FAX_API_KEY) || empty(FAX_API_SECRET)) {
                return ['success' => false, 'fax_id' => null, 'error' => 'Phaxio not configured'];
            }
            return sendFaxViaPhaxio($toNumber, $htmlBody, $options);
        default:
            return ['success' => false, 'fax_id' => null, 'error' => 'Unknown fax service: ' . FAX_SERVICE];
    }
}

/**
 * Faxage API implementation.
 * Sends PDF files via Faxage HTTP API.
 */
function sendFaxViaFaxage($toNumber, $options = []) {
    if (empty(FAXAGE_USERNAME) || empty(FAXAGE_COMPANY) || empty(FAXAGE_PASSWORD)) {
        return ['success' => false, 'fax_id' => null, 'error' => 'Faxage not configured'];
    }

    // Faxage wants exactly 10 digits (no country code prefix)
    $faxNumber = preg_replace('/[^0-9]/', '', $toNumber);
    if (strlen($faxNumber) === 11 && $faxNumber[0] === '1') {
        $faxNumber = substr($faxNumber, 1);
    }

    // Collect all PDF files to send
    $pdfFiles = [];

    // Letter PDF
    if (!empty($options['pdf_path']) && file_exists($options['pdf_path'])) {
        $pdfFiles[] = $options['pdf_path'];
    }

    // Additional attachments (HIPAA, releases, etc.)
    if (!empty($options['attachments'])) {
        foreach ($options['attachments'] as $att) {
            $path = is_array($att) ? ($att['path'] ?? '') : $att;
            if ($path && file_exists($path)) {
                $pdfFiles[] = $path;
            }
        }
    }

    if (empty($pdfFiles)) {
        return ['success' => false, 'fax_id' => null, 'error' => 'No PDF files to fax'];
    }

    // Build POST fields
    $postFields = [
        'username'  => FAXAGE_USERNAME,
        'company'   => FAXAGE_COMPANY,
        'password'  => FAXAGE_PASSWORD,
        'operation' => 'sendfax',
        'faxno'     => $faxNumber,
    ];

    // Add caller ID if configured
    if (!empty(FAX_CALLER_ID)) {
        $postFields['callerid'] = ltrim(FAX_CALLER_ID, '+');
    }

    // Attach PDF files as base64-encoded data (Faxage API requirement)
    foreach ($pdfFiles as $i => $filePath) {
        $postFields["faxfilenames[$i]"] = basename($filePath);
        $postFields["faxfiledata[$i]"]  = base64_encode(file_get_contents($filePath));
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => FAXAGE_API_URL,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($postFields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'fax_id' => null, 'error' => 'cURL error: ' . $curlError];
    }

    // Faxage returns plain text: "JOBID: 12345" on success, or error codes like ERR01-ERR05
    $response = trim($response);

    if ($httpCode === 200 && preg_match('/^JOBID:\s*(\d+)$/i', $response, $matches)) {
        return [
            'success' => true,
            'fax_id'  => $matches[1],
            'error'   => null
        ];
    }

    // Map Faxage error codes to readable messages
    $errorMessages = [
        'ERR01' => 'Database error on Faxage side',
        'ERR02' => 'Login credentials incorrect or account disabled',
        'ERR03' => 'No valid files provided',
        'ERR04' => 'Invalid fax number format (must be 10 digits)',
        'ERR05' => 'Blocked number (outside service area or restricted)',
    ];

    $errorMsg = $response;
    foreach ($errorMessages as $code => $msg) {
        if (strpos($response, $code) !== false) {
            $errorMsg = "{$code}: {$msg}";
            break;
        }
    }

    return [
        'success' => false,
        'fax_id'  => null,
        'error'   => 'Faxage error: ' . $errorMsg
    ];
}

/**
 * Check fax delivery status via Faxage.
 *
 * @param string $faxId Job ID returned from sendfax
 * @return array ['status' => string, 'error' => string|null]
 */
function checkFaxStatus($faxId) {
    $postFields = [
        'username'  => FAXAGE_USERNAME,
        'company'   => FAXAGE_COMPANY,
        'password'  => FAXAGE_PASSWORD,
        'operation' => 'status',
        'jobid'     => $faxId,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => FAXAGE_API_URL,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($postFields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $response = trim($response);

    // Parse tab-delimited status line: jobid \t jobid \t type \t faxno \t status \t detail \t sent \t completed \t duration
    $parts = explode("\t", $response);
    if (count($parts) >= 6) {
        return [
            'status' => trim($parts[4] ?? ''),   // success / failure / ...
            'detail' => trim($parts[5] ?? ''),   // e.g. "Success", "Busy signal detected"
            'error'  => null
        ];
    }

    return ['status' => $response, 'detail' => null, 'error' => null];
}

/**
 * Phaxio API implementation (legacy).
 */
function sendFaxViaPhaxio($toNumber, $htmlBody, $options = []) {
    $postFields = [
        'to'               => $toNumber,
        'string_data'      => $htmlBody,
        'string_data_type' => 'html',
        'caller_id'        => $options['caller_id'] ?? FAX_CALLER_ID,
    ];

    if (!empty(FAX_CALLBACK_URL)) {
        $postFields['callback_url'] = FAX_CALLBACK_URL;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => FAX_API_URL,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => SEND_TIMEOUT,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Basic ' . base64_encode(FAX_API_KEY . ':' . FAX_API_SECRET)
        ],
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'fax_id' => null, 'error' => 'cURL error: ' . $curlError];
    }

    $data = json_decode($response, true);

    if ($httpCode === 200 && isset($data['success']) && $data['success']) {
        return [
            'success' => true,
            'fax_id'  => $data['data']['id'] ?? null,
            'error'   => null
        ];
    }

    return [
        'success' => false,
        'fax_id'  => null,
        'error'   => $data['message'] ?? ('Phaxio API error (HTTP ' . $httpCode . ')')
    ];
}

/**
 * Normalize a phone/fax number to E.164 format for US numbers.
 */
function normalizePhoneNumber($number) {
    $digits = preg_replace('/[^0-9]/', '', $number);
    if (strlen($digits) === 10) {
        return '+1' . $digits;
    }
    if (strlen($digits) === 11 && $digits[0] === '1') {
        return '+' . $digits;
    }
    return '+' . $digits;
}
