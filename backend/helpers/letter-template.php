<?php
/**
 * Render a Medical Records Request Letter as HTML.
 *
 * @param array $data Letter data from getRequestLetterData()
 * @param int|null $templateId Optional template ID to use instead of hardcoded template
 * @return string Full HTML document
 */
function renderRequestLetter($data, $templateId = null) {
    // If template ID provided, use database template
    if ($templateId) {
        return renderLetterFromTemplate($templateId, $data);
    }

    // Otherwise use hardcoded template (backward compatible)
    $requestDate = !empty($data['request_date'])
        ? date('F j, Y', strtotime($data['request_date']))
        : date('F j, Y');
    $clientDob = !empty($data['client_dob'])
        ? date('m/d/Y', strtotime($data['client_dob']))
        : 'N/A';
    $doi = !empty($data['doi'])
        ? date('m/d/Y', strtotime($data['doi']))
        : 'N/A';
    $treatmentStart = !empty($data['treatment_start_date'])
        ? date('m/d/Y', strtotime($data['treatment_start_date']))
        : 'Date of Injury';
    $treatmentEnd = !empty($data['treatment_end_date'])
        ? date('m/d/Y', strtotime($data['treatment_end_date']))
        : 'Present';

    // Parse record types
    $recordTypeLabels = [
        'medical_records' => 'Complete Medical Records (including office/chart notes, diagnostic studies, and test results)',
        'billing'         => 'Itemized Billing Statements',
        'chart'           => 'Chart/Progress Notes',
        'imaging'         => 'Imaging Studies (X-rays, MRI, CT scans) and Radiology Reports',
        'op_report'       => 'Operative Reports',
    ];
    $requestedTypes = [];
    if (!empty($data['record_types'])) {
        foreach (explode(',', $data['record_types']) as $type) {
            $type = trim($type);
            if (isset($recordTypeLabels[$type])) {
                $requestedTypes[] = $recordTypeLabels[$type];
            }
        }
    }
    if (empty($requestedTypes)) {
        $requestedTypes[] = 'Complete Medical Records and Billing';
    }

    // Subject line based on request type
    $subjectLine = 'MEDICAL RECORDS REQUEST';
    if (($data['request_type'] ?? '') === 'follow_up') {
        $subjectLine = 'FOLLOW-UP: MEDICAL RECORDS REQUEST';
    } elseif (($data['request_type'] ?? '') === 're_request') {
        $subjectLine = 'SECOND REQUEST: MEDICAL RECORDS';
    } elseif (($data['request_type'] ?? '') === 'rfd') {
        $subjectLine = 'REQUEST FOR DOCUMENTS';
    }

    $authLine = !empty($data['authorization_sent'])
        ? 'A signed HIPAA-compliant authorization is enclosed/attached herewith.'
        : 'A signed authorization will be forwarded under separate cover.';

    $firmName    = htmlspecialchars(FIRM_NAME);
    $firmAddress = htmlspecialchars(FIRM_ADDRESS);
    $firmCSZ     = htmlspecialchars(FIRM_CITY_STATE_ZIP);
    $firmPhone   = htmlspecialchars(FIRM_PHONE);
    $firmFax     = htmlspecialchars(FIRM_FAX);
    $firmEmail   = htmlspecialchars(FIRM_EMAIL);
    $provName    = htmlspecialchars($data['provider_name'] ?? '');
    $provAddress = htmlspecialchars($data['provider_address'] ?? '');
    $clientName  = htmlspecialchars($data['client_name'] ?? '');
    $attorneyName = htmlspecialchars($data['attorney_name'] ?? '');
    $caseNumber  = htmlspecialchars($data['case_number'] ?? '');

    $recordsList = '';
    foreach ($requestedTypes as $i => $rt) {
        $num = $i + 1;
        $recordsList .= "<tr><td style=\"padding:2px 8px;vertical-align:top;\">{$num}.</td><td style=\"padding:2px 0;\">" . htmlspecialchars($rt) . "</td></tr>";
    }

    $notesSection = '';
    if (!empty($data['notes'])) {
        $notesSection = '<p style="margin-top:15px;"><strong>Additional Instructions:</strong> '
            . htmlspecialchars($data['notes']) . '</p>';
    }

    $doiFormatted = !empty($data['doi']) ? date('m/d/Y', strtotime($data['doi'])) : 'N/A';

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; margin: 0; padding: 0; }
        .letter { max-width: 8.5in; margin: 0 auto; padding: 1in; }
        .letterhead { text-align: center; border-bottom: 3px double #1a365d; padding-bottom: 15px; margin-bottom: 30px; }
        .firm-name { font-size: 18pt; font-weight: bold; color: #1a365d; letter-spacing: 2px; margin-bottom: 4px; }
        .firm-info { font-size: 9pt; color: #4a5568; }
        .date { margin-bottom: 25px; }
        .recipient { margin-bottom: 20px; line-height: 1.4; }
        .re-line { margin-bottom: 20px; }
        .re-line strong { text-decoration: underline; }
        .body-text { margin-bottom: 12px; text-align: justify; }
        .records-table { margin: 10px 0 10px 20px; }
        .signature { margin-top: 40px; }
        .footer { margin-top: 40px; font-size: 9pt; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>
<div class="letter">
    <div class="letterhead">
        <div class="firm-name">{$firmName}</div>
        <div class="firm-info">
            {$firmAddress}, {$firmCSZ}<br>
            Phone: {$firmPhone} | Fax: {$firmFax} | Email: {$firmEmail}
        </div>
    </div>

    <div class="date">{$requestDate}</div>

    <div class="recipient">
        Records Department<br>
        {$provName}<br>
        {$provAddress}
    </div>

    <div class="re-line">
        <strong>RE: {$subjectLine}</strong><br>
        <strong>Patient:</strong> {$clientName}<br>
        <strong>Date of Birth:</strong> {$clientDob}<br>
        <strong>Date of Injury:</strong> {$doi}<br>
        <strong>Treatment Dates:</strong> {$treatmentStart} through {$treatmentEnd}<br>
        <strong>Our File No.:</strong> {$caseNumber}
    </div>

    <p class="body-text">Dear Records Department:</p>

    <p class="body-text">
        This firm represents the above-referenced patient in connection with a personal injury matter.
        We are writing to request copies of the following records pertaining to treatment arising
        from the incident on {$doiFormatted}:
    </p>

    <table class="records-table">
        {$recordsList}
    </table>

    <p class="body-text">
        Please provide records for the treatment period of <strong>{$treatmentStart}</strong>
        through <strong>{$treatmentEnd}</strong>.
    </p>

    <p class="body-text">{$authLine}</p>

    <p class="body-text">
        Please forward the requested records to our office at your earliest convenience.
        Records may be sent via fax to <strong>{$firmFax}</strong> or via email to
        <strong>{$firmEmail}</strong>. If there are any copying or processing fees,
        please contact our office so that we may arrange prompt payment.
    </p>

    <p class="body-text">
        Should you have any questions or require additional information, please do not
        hesitate to contact our Records Department at {$firmPhone}.
    </p>

    {$notesSection}

    <p class="body-text">Thank you for your prompt attention to this matter.</p>

    <div class="signature">
        <p>Respectfully,</p>
        <br>
        <p><strong>{$firmName}</strong></p>
        <p>Records Department</p>
        <p>On behalf of {$attorneyName}</p>
    </div>

    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
</div>
</body>
</html>
HTML;
}

/**
 * Gather all data needed to render a request letter.
 *
 * @param int $requestId record_requests.id
 * @return array|null
 */
function getRequestLetterData($requestId) {
    $data = dbFetchOne(
        "SELECT
            rr.id AS request_id,
            rr.case_provider_id,
            rr.request_date,
            rr.request_method,
            rr.request_type,
            rr.sent_to,
            rr.authorization_sent,
            rr.notes,
            rr.send_status,
            rr.template_id,
            rr.template_data,
            cp.treatment_start_date,
            cp.treatment_end_date,
            cp.record_types_needed AS record_types,
            c.case_number,
            c.client_name,
            c.client_dob,
            c.doi,
            c.attorney_name,
            p.name AS provider_name,
            p.address AS provider_address,
            p.fax AS provider_fax,
            p.email AS provider_email,
            p.preferred_method AS provider_preferred_method,
            (SELECT MIN(rr2.request_date) FROM record_requests rr2
             WHERE rr2.case_provider_id = rr.case_provider_id
             AND rr2.request_type = 'initial') AS initial_request_date,
            (SELECT GROUP_CONCAT(DATE_FORMAT(rr3.request_date, '%m/%d/%Y') ORDER BY rr3.request_date SEPARATOR ', ')
             FROM record_requests rr3
             WHERE rr3.case_provider_id = rr.case_provider_id
             AND rr3.request_type = 'follow_up'
             AND rr3.id != rr.id
             AND rr3.send_status = 'sent') AS followup_dates,
            (SELECT GROUP_CONCAT(DATE_FORMAT(rr4.sent_at, '%m/%d/%Y') ORDER BY rr4.sent_at SEPARATOR ', ')
             FROM record_requests rr4
             WHERE rr4.case_provider_id = rr.case_provider_id
             AND rr4.id != rr.id
             AND rr4.send_status = 'sent') AS previous_request_dates
        FROM record_requests rr
        JOIN case_providers cp ON rr.case_provider_id = cp.id
        JOIN cases c ON cp.case_id = c.id
        JOIN providers p ON cp.provider_id = p.id
        WHERE rr.id = ?",
        [$requestId]
    );

    // Merge template_data JSON fields into data array
    if ($data && !empty($data['template_data'])) {
        $extra = json_decode($data['template_data'], true);
        if ($extra) {
            $data = array_merge($data, $extra);
        }
    }

    return $data;
}

/**
 * Render a Health Insurance Ledger Request Letter.
 */
function renderHealthLedgerLetter($data) {
    $requestDate = !empty($data['request_date'])
        ? date('F j, Y', strtotime($data['request_date']))
        : date('F j, Y');
    $clientDob = !empty($data['client_dob'])
        ? date('m/d/Y', strtotime($data['client_dob']))
        : 'N/A';
    $doi = !empty($data['doi'])
        ? date('m/d/Y', strtotime($data['doi']))
        : 'N/A';

    $subjectLine = 'HEALTH INSURANCE LEDGER REQUEST';
    if (($data['request_type'] ?? '') === 'follow_up') {
        $subjectLine = 'FOLLOW-UP: HEALTH INSURANCE LEDGER REQUEST';
    } elseif (($data['request_type'] ?? '') === 're_request') {
        $subjectLine = 'SECOND REQUEST: HEALTH INSURANCE LEDGER';
    }

    $firmName    = htmlspecialchars(FIRM_NAME);
    $firmAddress = htmlspecialchars(FIRM_ADDRESS);
    $firmCSZ     = htmlspecialchars(FIRM_CITY_STATE_ZIP);
    $firmPhone   = htmlspecialchars(FIRM_PHONE);
    $firmFax     = htmlspecialchars(FIRM_FAX);
    $firmEmail   = htmlspecialchars(FIRM_EMAIL);
    $clientName  = htmlspecialchars($data['client_name'] ?? '');
    $carrier     = htmlspecialchars($data['insurance_carrier'] ?? '');
    $caseNumber  = htmlspecialchars($data['case_number'] ?? '');
    $attorneyName = htmlspecialchars($data['attorney_name'] ?? '');

    $notesSection = '';
    if (!empty($data['notes'])) {
        $notesSection = '<p style="margin-top:15px;"><strong>Additional Instructions:</strong> '
            . htmlspecialchars($data['notes']) . '</p>';
    }

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; margin: 0; padding: 0; }
        .letter { max-width: 8.5in; margin: 0 auto; padding: 1in; }
        .letterhead { text-align: center; border-bottom: 3px double #1a365d; padding-bottom: 15px; margin-bottom: 30px; }
        .firm-name { font-size: 18pt; font-weight: bold; color: #1a365d; letter-spacing: 2px; margin-bottom: 4px; }
        .firm-info { font-size: 9pt; color: #4a5568; }
        .date { margin-bottom: 25px; }
        .recipient { margin-bottom: 20px; line-height: 1.4; }
        .re-line { margin-bottom: 20px; }
        .re-line strong { text-decoration: underline; }
        .body-text { margin-bottom: 12px; text-align: justify; }
        .records-table { margin: 10px 0 10px 20px; }
        .signature { margin-top: 40px; }
        .footer { margin-top: 40px; font-size: 9pt; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>
<div class="letter">
    <div class="letterhead">
        <div class="firm-name">{$firmName}</div>
        <div class="firm-info">
            {$firmAddress}, {$firmCSZ}<br>
            Phone: {$firmPhone} | Fax: {$firmFax} | Email: {$firmEmail}
        </div>
    </div>

    <div class="date">{$requestDate}</div>

    <div class="recipient">
        Claims / Ledger Department<br>
        {$carrier}
    </div>

    <div class="re-line">
        <strong>RE: {$subjectLine}</strong><br>
        <strong>Insured / Patient:</strong> {$clientName}<br>
        <strong>Date of Birth:</strong> {$clientDob}<br>
        <strong>Date of Loss:</strong> {$doi}<br>
        <strong>Insurance Carrier:</strong> {$carrier}<br>
        <strong>Our File No.:</strong> {$caseNumber}
    </div>

    <p class="body-text">Dear Claims Department:</p>

    <p class="body-text">
        This firm represents the above-referenced insured in connection with a personal injury matter.
        We are writing to request the following health insurance records and payment information:
    </p>

    <table class="records-table">
        <tr><td style="padding:2px 8px;vertical-align:top;">1.</td><td style="padding:2px 0;">Complete Payment Ledger / Explanation of Benefits (EOB) statements</td></tr>
        <tr><td style="padding:2px 8px;vertical-align:top;">2.</td><td style="padding:2px 0;">Claim Payment History (all payments made to providers)</td></tr>
        <tr><td style="padding:2px 8px;vertical-align:top;">3.</td><td style="padding:2px 0;">Outstanding Balance / Subrogation / Lien Information</td></tr>
        <tr><td style="padding:2px 8px;vertical-align:top;">4.</td><td style="padding:2px 0;">Coverage Verification and Policy Limits</td></tr>
    </table>

    <p class="body-text">
        Please provide all records related to claims arising from the incident on <strong>{$doi}</strong>.
    </p>

    <p class="body-text">
        Please forward the requested records to our office at your earliest convenience.
        Records may be sent via fax to <strong>{$firmFax}</strong> or via email to
        <strong>{$firmEmail}</strong>.
    </p>

    <p class="body-text">
        Should you have any questions or require additional information, please do not
        hesitate to contact our Records Department at {$firmPhone}.
    </p>

    {$notesSection}

    <p class="body-text">Thank you for your prompt attention to this matter.</p>

    <div class="signature">
        <p>Respectfully,</p>
        <br>
        <p><strong>{$firmName}</strong></p>
        <p>Records Department</p>
        <p>On behalf of {$attorneyName}</p>
    </div>

    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
</div>
</body>
</html>
HTML;
}

/**
 * Gather data needed to render a health ledger request letter.
 */
function getHealthLedgerLetterData($requestId) {
    $data = dbFetchOne(
        "SELECT
            hlr.id AS request_id,
            hlr.request_date,
            hlr.request_method,
            hlr.request_type,
            hlr.sent_to,
            hlr.send_status,
            hlr.notes,
            hlr.template_id,
            hlr.template_data,
            hli.case_number,
            hli.client_name,
            hli.insurance_carrier,
            hli.claim_number,
            hli.member_id,
            hli.carrier_contact_email,
            hli.carrier_contact_fax,
            c.client_dob,
            c.doi,
            c.attorney_name,
            c.treatment_end_date
        FROM hl_requests hlr
        JOIN health_ledger_items hli ON hlr.item_id = hli.id
        LEFT JOIN cases c ON hli.case_id = c.id
        WHERE hlr.id = ?",
        [$requestId]
    );

    // Merge template_data JSON fields into data array
    if ($data && !empty($data['template_data'])) {
        $extra = json_decode($data['template_data'], true);
        if ($extra) {
            $data = array_merge($data, $extra);
        }
    }

    return $data;
}

/**
 * Render a Combined Bulk Medical Records Request Letter as HTML.
 * Lists multiple cases in a simple format within one letter.
 *
 * @param array $casesData Array of case data (from bulk-create or preview-bulk)
 * @param array $commonData Common data (request_date, request_type, notes, etc.)
 * @param int|null $templateId Optional template ID to use instead of hardcoded template
 * @return string Full HTML document
 */
function renderBulkRequestLetter($casesData, $commonData, $templateId = null) {
    // If template ID provided, use database template with {{case_list}} placeholder
    if ($templateId) {
        return renderBulkFromTemplate($templateId, $casesData, $commonData);
    }

    // Auto-detect default bulk_request template if none specified
    $defaultTemplate = dbFetchOne(
        "SELECT id FROM letter_templates WHERE template_type = 'bulk_request' AND is_default = 1 AND is_active = 1"
    );
    if ($defaultTemplate) {
        return renderBulkFromTemplate($defaultTemplate['id'], $casesData, $commonData);
    }

    $requestDate = !empty($commonData['request_date'])
        ? date('F j, Y', strtotime($commonData['request_date']))
        : date('F j, Y');

    // Subject line based on request type
    $subjectLine = 'MEDICAL RECORDS REQUEST';
    if (($commonData['request_type'] ?? '') === 'follow_up') {
        $subjectLine = 'FOLLOW-UP: MEDICAL RECORDS REQUEST';
    } elseif (($commonData['request_type'] ?? '') === 're_request') {
        $subjectLine = 'SECOND REQUEST: MEDICAL RECORDS';
    } elseif (($commonData['request_type'] ?? '') === 'rfd') {
        $subjectLine = 'REQUEST FOR DOCUMENTS';
    }

    $authLine = !empty($commonData['authorization_sent'])
        ? 'Signed HIPAA-compliant authorizations are enclosed/attached herewith.'
        : 'Signed authorizations will be forwarded under separate cover.';

    $firmName    = htmlspecialchars(FIRM_NAME);
    $firmAddress = htmlspecialchars(FIRM_ADDRESS);
    $firmCSZ     = htmlspecialchars(FIRM_CITY_STATE_ZIP);
    $firmPhone   = htmlspecialchars(FIRM_PHONE);
    $firmFax     = htmlspecialchars(FIRM_FAX);
    $firmEmail   = htmlspecialchars(FIRM_EMAIL);

    // Use first case's provider info (all should be same provider)
    $firstCase = $casesData[0];
    $provName    = htmlspecialchars($firstCase['provider_name'] ?? '');
    $provAddress = htmlspecialchars($firstCase['provider_address'] ?? '');
    $attorneyName = htmlspecialchars($firstCase['attorney_name'] ?? '');

    // Build case list
    $caseListHtml = '';
    foreach ($casesData as $i => $case) {
        $num = $i + 1;
        $caseNumber = htmlspecialchars($case['case_number'] ?? '');
        $clientName = htmlspecialchars($case['client_name'] ?? '');
        $doi = !empty($case['doi']) ? date('m/d/Y', strtotime($case['doi'])) : 'N/A';

        $treatmentStart = !empty($case['treatment_start_date'])
            ? date('m/d/Y', strtotime($case['treatment_start_date']))
            : (!empty($case['doi']) ? date('m/d/Y', strtotime($case['doi'])) : 'N/A');
        $treatmentEnd = !empty($case['treatment_end_date'])
            ? date('m/d/Y', strtotime($case['treatment_end_date']))
            : 'Present';

        $caseListHtml .= "<tr>
            <td style=\"padding:6px 6px 0 0;vertical-align:top;white-space:nowrap;width:28px;\" rowspan=\"2\">{$num}.</td>
            <td style=\"padding:6px 12px 0 0;vertical-align:top;\"><strong>Case #{$caseNumber}</strong> &ndash; {$clientName}</td>
            <td style=\"padding:6px 0 0 0;vertical-align:top;white-space:nowrap;\">DOI: {$doi}</td>
        </tr>
        <tr>
            <td colspan=\"2\" style=\"padding:2px 0 6px 0;border-bottom:1px solid #e2e8f0;font-size:10pt;color:#555;\">Treatment: {$treatmentStart} to {$treatmentEnd}</td>
        </tr>";
    }

    $notesSection = '';
    if (!empty($commonData['notes'])) {
        $notesSection = '<p style="margin-top:15px;"><strong>Additional Instructions:</strong> '
            . htmlspecialchars($commonData['notes']) . '</p>';
    }

    $caseCount = count($casesData);

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; margin: 0; padding: 0; }
        .letter { max-width: 8.5in; margin: 0 auto; padding: 1in; }
        .letterhead { text-align: center; border-bottom: 3px double #1a365d; padding-bottom: 15px; margin-bottom: 30px; }
        .firm-name { font-size: 18pt; font-weight: bold; color: #1a365d; letter-spacing: 2px; margin-bottom: 4px; }
        .firm-info { font-size: 9pt; color: #4a5568; }
        .date { margin-bottom: 25px; }
        .recipient { margin-bottom: 20px; line-height: 1.4; }
        .re-line { margin-bottom: 20px; }
        .re-line strong { text-decoration: underline; }
        .body { margin-bottom: 20px; text-align: justify; }
        .signature { margin-top: 40px; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        .case-table td { font-size: 11pt; line-height: 1.4; }
    </style>
</head>
<body>
    <div class="letter">
        <div class="letterhead">
            <div class="firm-name">{$firmName}</div>
            <div class="firm-info">
                {$firmAddress}, {$firmCSZ}<br>
                Phone: {$firmPhone} | Fax: {$firmFax} | Email: {$firmEmail}
            </div>
        </div>

        <div class="date">{$requestDate}</div>

        <div class="recipient">
            {$provName}<br>
            {$provAddress}
        </div>

        <div class="re-line">
            <strong>RE: {$subjectLine} &ndash; Multiple Cases</strong>
        </div>

        <div class="body">
            <p>Dear Records Custodian:</p>

            <p>Our office represents the above-referenced clients in personal injury matters. We respectfully request complete copies of medical records and itemized billing statements for the following <strong>{$caseCount} cases</strong>:</p>

            <table class="case-table">
                {$caseListHtml}
            </table>

            <p><strong>Records Requested for All Cases:</strong></p>
            <table>
                <tr><td style="padding:2px 8px;vertical-align:top;">1.</td><td style="padding:2px 0;">Complete Medical Records (including office/chart notes, diagnostic studies, and test results)</td></tr>
                <tr><td style="padding:2px 8px;vertical-align:top;">2.</td><td style="padding:2px 0;">Itemized Billing Statements</td></tr>
            </table>

            <p>{$authLine}</p>

            <p>Please forward the requested records to our office at your earliest convenience. If you have any questions or require additional information, please do not hesitate to contact our office.</p>

            {$notesSection}

            <p>Thank you for your prompt attention to this matter.</p>
        </div>

        <div class="signature">
            <p>Sincerely,</p>
            <p style="margin-top:40px;">
                <strong>{$firmName}</strong>
            </p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Render bulk request letter using a database template.
 * Generates the full letter body (Dear Records Custodian... case list... etc.)
 * and passes it as {{bulk_letter_body}} so the template only needs to provide
 * the letterhead, signature, and footer wrapper.
 */
function renderBulkFromTemplate($templateId, $casesData, $commonData) {
    $template = dbFetchOne("SELECT * FROM letter_templates WHERE id = ? AND is_active = 1", [$templateId]);
    if (!$template) {
        return renderBulkRequestLetter($casesData, $commonData);
    }

    // Template must contain {{bulk_letter_body}} placeholder.
    // If not, fall back to the hardcoded version.
    if (strpos($template['body_template'], '{{bulk_letter_body}}') === false) {
        return renderBulkRequestLetter($casesData, $commonData);
    }

    // --- Build case list HTML (same as hardcoded version) ---
    $caseListHtml = '<table style="border-collapse:collapse;width:100%;margin:10px 0;">';
    foreach ($casesData as $i => $case) {
        $num = $i + 1;
        $caseNumber = htmlspecialchars($case['case_number'] ?? '');
        $clientName = htmlspecialchars($case['client_name'] ?? '');
        $doi = !empty($case['doi']) ? date('m/d/Y', strtotime($case['doi'])) : 'N/A';
        $treatmentStart = !empty($case['treatment_start_date'])
            ? date('m/d/Y', strtotime($case['treatment_start_date']))
            : (!empty($case['doi']) ? date('m/d/Y', strtotime($case['doi'])) : 'N/A');
        $treatmentEnd = !empty($case['treatment_end_date'])
            ? date('m/d/Y', strtotime($case['treatment_end_date']))
            : 'Present';

        $caseListHtml .= "<tr>
            <td style=\"padding:6px 6px 0 0;vertical-align:top;white-space:nowrap;width:28px;font-size:11pt;\" rowspan=\"2\">{$num}.</td>
            <td style=\"padding:6px 12px 0 0;vertical-align:top;font-size:11pt;\"><strong>Case #{$caseNumber}</strong> &ndash; {$clientName}</td>
            <td style=\"padding:6px 0 0 0;vertical-align:top;white-space:nowrap;font-size:11pt;\">DOI: {$doi}</td>
        </tr>
        <tr>
            <td colspan=\"2\" style=\"padding:2px 0 6px 0;border-bottom:1px solid #e2e8f0;font-size:10pt;color:#555;\">Treatment: {$treatmentStart} to {$treatmentEnd}</td>
        </tr>";
    }
    $caseListHtml .= '</table>';

    // --- Build subject line ---
    $subjectLine = 'MEDICAL RECORDS REQUEST';
    if (($commonData['request_type'] ?? '') === 'follow_up') {
        $subjectLine = 'FOLLOW-UP: MEDICAL RECORDS REQUEST';
    } elseif (($commonData['request_type'] ?? '') === 're_request') {
        $subjectLine = 'SECOND REQUEST: MEDICAL RECORDS';
    } elseif (($commonData['request_type'] ?? '') === 'rfd') {
        $subjectLine = 'REQUEST FOR DOCUMENTS';
    }

    $authLine = !empty($commonData['authorization_sent'])
        ? 'Signed HIPAA-compliant authorizations are enclosed/attached herewith.'
        : 'Signed authorizations will be forwarded under separate cover.';

    $caseCount = count($casesData);
    $firstCase = $casesData[0];
    $attorneyName = htmlspecialchars($firstCase['attorney_name'] ?? '');
    $provAddress = htmlspecialchars($firstCase['provider_address'] ?? '');
    $firmPhone = htmlspecialchars(defined('FIRM_PHONE') ? FIRM_PHONE : '');

    $notesHtml = '';
    if (!empty($commonData['notes'])) {
        $notesHtml = '<p style="margin-top:15px;"><strong>Additional Instructions:</strong> '
            . htmlspecialchars($commonData['notes']) . '</p>';
    }

    // --- Compose full letter body (same content as hardcoded) ---
    $bulkLetterBody = <<<BODY
<p style="margin-bottom:15px;"><strong style="text-decoration:underline;">RE: {$subjectLine} &ndash; Multiple Cases</strong></p>

<p>Dear Records Custodian:</p>

<p>Our office represents the above-referenced clients in personal injury matters. We respectfully request complete copies of medical records and itemized billing statements for the following <strong>{$caseCount} cases</strong>:</p>

{$caseListHtml}

<p><strong>Records Requested for All Cases:</strong></p>
<table style="margin:5px 0;">
    <tr><td style="padding:2px 8px;vertical-align:top;">1.</td><td style="padding:2px 0;">Complete Medical Records (including office/chart notes, diagnostic studies, and test results)</td></tr>
    <tr><td style="padding:2px 8px;vertical-align:top;">2.</td><td style="padding:2px 0;">Itemized Billing Statements</td></tr>
</table>

<p>{$authLine}</p>

<p>Please forward the requested records to our office at your earliest convenience. If you have any questions or require additional information, please do not hesitate to contact our office at {$firmPhone}.</p>

{$notesHtml}

<p>Thank you for your prompt attention to this matter.</p>
BODY;

    // --- Pass everything to template engine ---
    $data = [
        'provider_name' => $firstCase['provider_name'] ?? '',
        'provider_address' => $firstCase['provider_address'] ?? '',
        'provider_email' => $firstCase['provider_email'] ?? '',
        'provider_fax' => $firstCase['provider_fax'] ?? '',
        'attorney_name' => $firstCase['attorney_name'] ?? '',
        'request_date' => $commonData['request_date'] ?? date('Y-m-d'),
        'request_type' => $commonData['request_type'] ?? 'initial',
        'notes' => $commonData['notes'] ?? '',
        'authorization_sent' => !empty($commonData['authorization_sent']),
        'request_method' => $commonData['request_method'] ?? 'email',
        'sender_name' => $commonData['sender_name'] ?? '',
        'sender_email' => $commonData['sender_email'] ?? '',
        'case_list' => $caseListHtml,
        'case_count' => (string)$caseCount,
        'bulk_letter_body' => $bulkLetterBody,
        'client_name' => $firstCase['client_name'] ?? '',
        'case_number' => $firstCase['case_number'] ?? '',
        'doi' => $firstCase['doi'] ?? '',
    ];

    return processTemplatePlaceholders($template['body_template'], $data);
}

/**
 * Render letter from database template with placeholder substitution
 *
 * @param int $templateId Template ID from letter_templates table
 * @param array $data Letter data (from getRequestLetterData)
 * @return string Rendered HTML
 */
function renderLetterFromTemplate($templateId, $data) {
    $template = dbFetchOne("SELECT * FROM letter_templates WHERE id = ? AND is_active = 1", [$templateId]);

    if (!$template) {
        // Fallback to hardcoded template if template not found
        return renderRequestLetter($data);
    }

    // Process placeholders in body template
    $html = processTemplatePlaceholders($template['body_template'], $data);

    // Process subject template if exists
    $subject = '';
    if (!empty($template['subject_template'])) {
        $subject = processTemplatePlaceholders($template['subject_template'], $data);
        // Subject should be plain text, strip any HTML tags
        $subject = strip_tags($subject);
    }

    return [
        'subject' => $subject,
        'html' => $html
    ];
}

/**
 * Process {{placeholder}} variables in template
 * Supports: {{variable}}, {{variable|date:format}}, {{#if variable}}...{{/if}}, {{variable|default:value}}
 *
 * @param string $template Template HTML with placeholders
 * @param array $data Data to substitute
 * @return string Processed template with values substituted
 */
function processTemplatePlaceholders($template, $data) {
    // Load firm config
    require_once __DIR__ . '/../config/email.php';

    // Build placeholder map
    $placeholders = [
        // Firm info
        'firm_name' => FIRM_NAME,
        'firm_address' => FIRM_ADDRESS,
        'firm_city_state_zip' => FIRM_CITY_STATE_ZIP,
        'firm_phone' => FIRM_PHONE,
        'firm_fax' => FIRM_FAX,
        'firm_email' => FIRM_EMAIL,

        // Provider info
        'provider_name' => $data['provider_name'] ?? '',
        'provider_address' => $data['provider_address'] ?? '',
        'provider_email' => $data['provider_email'] ?? '',
        'provider_fax' => $data['provider_fax'] ?? '',

        // Client/Case info
        'client_name' => $data['client_name'] ?? '',
        'client_dob' => $data['client_dob'] ?? '',
        'doi' => $data['doi'] ?? '',
        'case_number' => $data['case_number'] ?? '',
        'attorney_name' => $data['attorney_name'] ?? '',

        // Request info
        'request_date' => $data['request_date'] ?? date('Y-m-d'),
        'initial_request_date' => $data['initial_request_date'] ?? '',
        'treatment_start_date' => $data['treatment_start_date'] ?? '',
        'treatment_end_date' => $data['treatment_end_date'] ?? '',
        'notes' => $data['notes'] ?? '',

        // Sender info (passed from preview/send endpoints)
        'sender_name' => $data['sender_name'] ?? '',
        'sender_email' => $data['sender_email'] ?? '',

        // Follow-up dates (comma-separated, pre-formatted from SQL)
        'followup_dates' => $data['followup_dates'] ?? '',

        // All previous request dates (initial + follow-ups, pre-formatted from SQL)
        'previous_request_dates' => $data['previous_request_dates'] ?? '',

        // Request method info
        'request_method' => ucfirst($data['request_method'] ?? 'Email'),
        'recipient_contact' => ($data['request_method'] ?? 'email') === 'fax'
            ? ($data['provider_fax'] ?? $data['carrier_contact_fax'] ?? '')
            : ($data['provider_email'] ?? $data['carrier_contact_email'] ?? ''),

        // Boolean flags
        'authorization_sent' => !empty($data['authorization_sent']),

        // Health ledger / insurance fields
        'insurance_carrier' => $data['insurance_carrier'] ?? '',
        'claim_number' => $data['claim_number'] ?? '',
        'member_id' => $data['member_id'] ?? '',

        // Settlement fields (from template_data JSON)
        'settlement_amount' => $data['settlement_amount'] ?? '',
        'settlement_date' => $data['settlement_date'] ?? '',
        'attorney_fees' => $data['attorney_fees'] ?? '',
        'costs' => $data['costs'] ?? '',
        'proposed_lien_amount' => $data['proposed_lien_amount'] ?? '',

        // Bulk request fields (pre-built HTML, passed via $data)
        'case_list' => $data['case_list'] ?? '',
        'case_count' => $data['case_count'] ?? '',
        'bulk_letter_body' => $data['bulk_letter_body'] ?? '',

        // Computed fields
        'record_types_list' => '', // Will be generated below
        'record_types_checkbox' => '', // Will be generated below
        'firm_attorneys' => '', // Will be generated below
        'firm_logo_base64' => '', // Will be generated below
    ];

    // Generate record types list HTML
    $recordTypeLabels = [
        'medical_records' => 'Complete Medical Records (including office/chart notes, diagnostic studies, and test results)',
        'billing' => 'Itemized Billing Statements',
        'chart' => 'Chart/Progress Notes',
        'imaging' => 'Imaging Studies (X-rays, MRI, CT scans) and Radiology Reports',
        'op_report' => 'Operative Reports',
    ];
    $requestedTypes = [];
    if (!empty($data['record_types'])) {
        foreach (explode(',', $data['record_types']) as $type) {
            $type = trim($type);
            if (isset($recordTypeLabels[$type])) {
                $requestedTypes[] = $recordTypeLabels[$type];
            }
        }
    }
    if (empty($requestedTypes)) {
        $requestedTypes[] = 'Complete Medical Records and Billing';
    }

    $recordsHtml = '<ol style="margin: 0; padding-left: 20px;">';
    foreach ($requestedTypes as $recordType) {
        $recordsHtml .= '<li style="margin-bottom: 5px;">' . htmlspecialchars($recordType) . '</li>';
    }
    $recordsHtml .= '</ol>';
    $placeholders['record_types_list'] = $recordsHtml;

    // Generate checkbox-style record types (X) list
    $checkboxLabels = [
        'medical_records' => 'Medical records, including exams, any imaging &amp; readings, and chart notes',
        'billing'         => 'Itemized medical charges',
        'chart'           => 'Chart/Progress notes',
        'imaging'         => 'Imaging studies (X-rays, MRI, CT scans) and readings',
        'op_report'       => 'Operative Reports',
    ];
    $checkboxHtml = '';
    if (!empty($data['record_types'])) {
        foreach (explode(',', $data['record_types']) as $type) {
            $type = trim($type);
            if (isset($checkboxLabels[$type])) {
                $checkboxHtml .= '<p style="margin: 2px 0; padding-left: 40px; text-indent: 0;">(X)&nbsp;&nbsp;&nbsp;' . $checkboxLabels[$type] . '</p>';
            }
        }
    }
    if (empty($checkboxHtml)) {
        $checkboxHtml = '<p style="margin: 2px 0; padding-left: 40px;">(X)&nbsp;&nbsp;&nbsp;Medical records, including exams, any imaging &amp; readings, and chart notes</p>'
                      . '<p style="margin: 2px 0; padding-left: 40px;">(X)&nbsp;&nbsp;&nbsp;Itemized medical charges</p>';
    }
    $placeholders['record_types_checkbox'] = $checkboxHtml;

    // Generate firm attorneys list HTML
    $attorneysHtml = '';
    if (defined('FIRM_ATTORNEYS')) {
        $attorneys = json_decode(FIRM_ATTORNEYS, true);
        if ($attorneys) {
            foreach ($attorneys as $att) {
                $attorneysHtml .= htmlspecialchars($att['name']) . ', ' . htmlspecialchars($att['title']) . '<br>';
            }
        }
    }
    $placeholders['firm_attorneys'] = $attorneysHtml;

    // Generate firm logo base64
    $logoPath = __DIR__ . '/../../frontend/assets/images/firm-logo.png';
    if (file_exists($logoPath)) {
        $placeholders['firm_logo_base64'] = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }

    // Process conditional blocks: {{#if variable}}...{{else}}...{{/if}}
    $template = preg_replace_callback(
        '/\{\{#if\s+(\w+)\}\}(.*?)(?:\{\{else\}\}(.*?))?\{\{\/if\}\}/s',
        function($matches) use ($placeholders) {
            $variable = $matches[1];
            $ifContent = $matches[2];
            $elseContent = $matches[3] ?? '';

            $value = $placeholders[$variable] ?? null;
            $condition = !empty($value) && $value !== false;

            return $condition ? $ifContent : $elseContent;
        },
        $template
    );

    // Process placeholders with filters: {{variable|filter:param}}
    $template = preg_replace_callback(
        '/\{\{(\w+)(?:\|(\w+)(?::([^}]+))?)?\}\}/',
        function($matches) use ($placeholders) {
            $variable = $matches[1];
            $filter = $matches[2] ?? null;
            $param = $matches[3] ?? null;

            $value = $placeholders[$variable] ?? '';

            // Apply filters
            if ($filter === 'date' && !empty($value)) {
                $format = $param ?? 'm/d/Y';
                try {
                    $value = date($format, strtotime($value));
                } catch (Exception $e) {
                    $value = ''; // Invalid date
                }
            } elseif ($filter === 'currency') {
                if (is_numeric($value) && $value !== '') {
                    $value = '$' . number_format((float)$value, 2);
                }
            } elseif ($filter === 'default' && empty($value)) {
                $value = $param ?? '';
            }

            // Escape HTML (but not for pre-built HTML placeholders)
            $rawHtmlPlaceholders = ['record_types_list', 'record_types_checkbox', 'firm_attorneys', 'firm_logo_base64', 'case_list', 'bulk_letter_body'];
            if (!in_array($variable, $rawHtmlPlaceholders)) {
                $value = htmlspecialchars((string)$value);
            }

            return $value;
        },
        $template
    );

    return $template;
}

/**
 * Get available placeholders for template type
 *
 * @param string $templateType Template type (medical_records, health_ledger, etc.)
 * @return array List of available placeholders with descriptions
 */
function getAvailablePlaceholders($templateType) {
    $commonPlaceholders = [
        'firm_name' => 'Law firm name',
        'firm_address' => 'Law firm address',
        'firm_city_state_zip' => 'Law firm city, state, zip',
        'firm_phone' => 'Law firm phone number',
        'firm_fax' => 'Law firm fax number',
        'firm_email' => 'Law firm email',
        'firm_attorneys' => 'HTML list of firm attorneys from config',
        'firm_logo_base64' => 'Base64-encoded firm logo image (use in img src)',
        'sender_name' => 'Name of the user sending the request',
        'sender_email' => 'Email of the user sending the request',
    ];

    $medicalRecordsPlaceholders = [
        'provider_name' => 'Medical provider name',
        'provider_address' => 'Medical provider full address',
        'provider_email' => 'Medical provider email',
        'provider_fax' => 'Medical provider fax',
        'client_name' => 'Client/patient full name',
        'client_dob' => 'Client date of birth (raw date)',
        'client_dob|date:m/d/Y' => 'Client date of birth (formatted)',
        'doi' => 'Date of injury (raw date)',
        'doi|date:m/d/Y' => 'Date of injury (formatted)',
        'case_number' => 'Case number',
        'attorney_name' => 'Attorney name',
        'request_date' => 'Request date (raw date)',
        'request_date|date:F j, Y' => 'Request date (formatted)',
        'initial_request_date' => 'Date of initial request (auto-detected)',
        'initial_request_date|date:m/d/Y' => 'Initial request date (formatted)',
        'followup_dates' => 'Comma-separated list of previous follow-up dates (pre-formatted mm/dd/yyyy)',
        'previous_request_dates' => 'All previous sent request dates (initial + follow-ups, pre-formatted mm/dd/yyyy)',
        'treatment_start_date' => 'Treatment start date (raw)',
        'treatment_start_date|date:m/d/Y' => 'Treatment start date (formatted)',
        'treatment_end_date' => 'Treatment end date (raw)',
        'treatment_end_date|date:m/d/Y' => 'Treatment end date (formatted)',
        'record_types_list' => 'HTML numbered list of requested record types',
        'record_types_checkbox' => 'HTML (X) checkbox-style list of record types',
        'request_method' => 'Send method: Email or Fax',
        'recipient_contact' => 'Provider email or fax based on send method',
        'notes' => 'Additional notes',
        'authorization_sent' => 'Boolean: true if authorization was sent',
        '{{#if authorization_sent}}...{{/if}}' => 'Conditional: show if authorization sent',
        '{{variable|default:Default Text}}' => 'Use default value if variable is empty',
    ];

    $healthLedgerPlaceholders = [
        'insurance_carrier' => 'Insurance carrier name',
        'claim_number' => 'Insurance claim number',
        'member_id' => 'Member ID (health subrogation)',
        'client_name' => 'Client/patient full name',
        'client_dob' => 'Client date of birth (raw date)',
        'client_dob|date:m/d/Y' => 'Client date of birth (formatted)',
        'doi' => 'Date of injury (raw date)',
        'doi|date:m/d/Y' => 'Date of injury (formatted)',
        'case_number' => 'Case number',
        'attorney_name' => 'Attorney name',
        'request_date|date:F j, Y' => 'Request date (formatted)',
        'request_method' => 'Send method: Email or Fax',
        'recipient_contact' => 'Carrier email or fax based on send method',
        'settlement_amount|currency' => 'Settlement amount (formatted)',
        'settlement_date|date:m/d/Y' => 'Settlement date (formatted)',
        'attorney_fees|currency' => 'Attorney fees (formatted)',
        'costs|currency' => 'Costs (formatted)',
        'proposed_lien_amount|currency' => 'Proposed lien amount (formatted)',
        'treatment_end_date|date:m/d/Y' => 'Last treatment date (formatted)',
        'notes' => 'Additional notes',
    ];

    $balanceVerificationPlaceholders = [
        'provider_name' => 'Medical provider name',
        'provider_address' => 'Medical provider full address',
        'provider_fax' => 'Medical provider fax',
        'provider_email' => 'Medical provider email',
        'client_name' => 'Client/patient full name',
        'client_dob|date:m/d/Y' => 'Client date of birth (formatted)',
        'doi|date:m/d/Y' => 'Date of injury (formatted)',
        'case_number' => 'Case number',
        'attorney_name' => 'Attorney name',
        'request_date|date:F j, Y' => 'Request date (formatted)',
        'request_method' => 'Send method: Email or Fax',
        'recipient_contact' => 'Provider email or fax based on send method',
        'treatment_end_date|date:m/d/Y' => 'Last treatment date (formatted)',
    ];

    $bulkRequestPlaceholders = [
        'case_list' => 'HTML table listing all cases (Case #, Name, DOI, Treatment dates)',
        'case_count' => 'Number of cases in the bulk request',
        'provider_name' => 'Medical provider name',
        'provider_address' => 'Medical provider full address',
        'attorney_name' => 'Attorney name',
        'request_date|date:F j, Y' => 'Request date (formatted)',
        'notes' => 'Additional notes',
        'sender_name' => 'Name of the user sending the request',
        'sender_email' => 'Email of the user sending the request',
        'authorization_sent' => 'Boolean: true if authorization was sent',
        'record_types_list' => 'HTML numbered list of requested record types',
        'record_types_checkbox' => 'HTML (X) checkbox-style list of record types',
    ];

    switch ($templateType) {
        case 'medical_records':
            return array_merge($commonPlaceholders, $medicalRecordsPlaceholders);
        case 'bulk_request':
            return array_merge($commonPlaceholders, $bulkRequestPlaceholders);
        case 'health_ledger':
            return array_merge($commonPlaceholders, $healthLedgerPlaceholders);
        case 'balance_verification':
            return array_merge($commonPlaceholders, $balanceVerificationPlaceholders);
        default:
            return $commonPlaceholders;
    }
}
