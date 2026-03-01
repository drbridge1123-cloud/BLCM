<?php
// POST /api/templates/preview - Preview template with sample/real data
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

requireAuth();

$input = getInput();

// Two modes:
// 1. Preview existing template with real request data: {template_id, request_id}
// 2. Preview custom template with sample data: {body_template, sample_data}

if (!empty($input['template_id']) && !empty($input['request_id'])) {
    // Mode 1: Preview existing template with real data
    $templateId = (int)$input['template_id'];
    $requestId = (int)$input['request_id'];

    // Load template
    $template = dbFetchOne("SELECT * FROM letter_templates WHERE id = ? AND is_active = 1", [$templateId]);
    if (!$template) {
        errorResponse('Template not found', 404);
    }

    // Load request data
    require_once __DIR__ . '/../../helpers/letter-template.php';
    $letterData = getRequestLetterData($requestId);
    if (!$letterData) {
        errorResponse('Request not found', 404);
    }

    // Render
    $html = renderLetterFromTemplate($templateId, $letterData);

    successResponse(['html' => $html]);

} elseif (!empty($input['body_template'])) {
    // Mode 2: Preview custom template with sample data
    $bodyTemplate = $input['body_template'];
    $sampleData = $input['sample_data'] ?? [];

    // Build sample data if not provided
    if (empty($sampleData)) {
        $firmPhone = defined('FIRM_PHONE') ? FIRM_PHONE : '(425) 678-0436';

        // Sample bulk letter body for bulk_request templates
        $sampleBulkBody = '<p style="margin-bottom:15px;"><strong style="text-decoration:underline;">RE: FOLLOW-UP: MEDICAL RECORDS REQUEST &ndash; Multiple Cases</strong></p>'
            . '<p>Dear Records Custodian:</p>'
            . '<p>Our office represents the above-referenced clients in personal injury matters. We respectfully request complete copies of medical records and itemized billing statements for the following <strong>3 cases</strong>:</p>'
            . '<table style="border-collapse:collapse;width:100%;margin:10px 0;">'
            . '<tr><td style="padding:6px 6px 0 0;vertical-align:top;white-space:nowrap;width:28px;font-size:11pt;" rowspan="2">1.</td>'
            . '<td style="padding:6px 12px 0 0;vertical-align:top;font-size:11pt;"><strong>Case #202023</strong> &ndash; Choi, Min Hee</td>'
            . '<td style="padding:6px 0 0 0;vertical-align:top;white-space:nowrap;font-size:11pt;">DOI: 11/20/2024</td></tr>'
            . '<tr><td colspan="2" style="padding:2px 0 6px 0;border-bottom:1px solid #e2e8f0;font-size:10pt;color:#555;">Treatment: 11/20/2024 to Present</td></tr>'
            . '<tr><td style="padding:6px 6px 0 0;vertical-align:top;white-space:nowrap;width:28px;font-size:11pt;" rowspan="2">2.</td>'
            . '<td style="padding:6px 12px 0 0;vertical-align:top;font-size:11pt;"><strong>Case #202115</strong> &ndash; Garcia Infante, Narciso</td>'
            . '<td style="padding:6px 0 0 0;vertical-align:top;white-space:nowrap;font-size:11pt;">DOI: 03/20/2025</td></tr>'
            . '<tr><td colspan="2" style="padding:2px 0 6px 0;border-bottom:1px solid #e2e8f0;font-size:10pt;color:#555;">Treatment: 03/20/2025 to Present</td></tr>'
            . '<tr><td style="padding:6px 6px 0 0;vertical-align:top;white-space:nowrap;width:28px;font-size:11pt;" rowspan="2">3.</td>'
            . '<td style="padding:6px 12px 0 0;vertical-align:top;font-size:11pt;"><strong>Case #202012</strong> &ndash; Byeon, Seonggyeong</td>'
            . '<td style="padding:6px 0 0 0;vertical-align:top;white-space:nowrap;font-size:11pt;">DOI: 10/29/2024</td></tr>'
            . '<tr><td colspan="2" style="padding:2px 0 6px 0;border-bottom:1px solid #e2e8f0;font-size:10pt;color:#555;">Treatment: 10/29/2024 to Present</td></tr>'
            . '</table>'
            . '<p><strong>Records Requested for All Cases:</strong></p>'
            . '<table style="margin:5px 0;"><tr><td style="padding:2px 8px;vertical-align:top;">1.</td><td style="padding:2px 0;">Complete Medical Records (including office/chart notes, diagnostic studies, and test results)</td></tr>'
            . '<tr><td style="padding:2px 8px;vertical-align:top;">2.</td><td style="padding:2px 0;">Itemized Billing Statements</td></tr></table>'
            . '<p>Signed authorizations will be forwarded under separate cover.</p>'
            . '<p>Please forward the requested records to our office at your earliest convenience. If you have any questions or require additional information, please do not hesitate to contact our office at ' . htmlspecialchars($firmPhone) . '.</p>'
            . '<p>Thank you for your prompt attention to this matter.</p>';

        $sampleData = [
            'client_name' => 'Sample Client Name',
            'case_number' => 'CASE-12345',
            'client_dob' => '1980-01-15',
            'doi' => '2025-06-20',
            'attorney_name' => 'Attorney Name',
            'provider_name' => 'Sample Medical Provider',
            'provider_address' => "123 Medical Plaza\nSeattle, WA 98101",
            'provider_email' => 'provider@example.com',
            'provider_fax' => '(206) 555-1234',
            'request_date' => date('Y-m-d'),
            'initial_request_date' => date('Y-m-d', strtotime('-30 days')),
            'request_method' => 'email',
            'treatment_start_date' => '2025-06-20',
            'treatment_end_date' => '2025-12-15',
            'record_types' => 'medical_records,billing',
            'notes' => 'Sample notes for preview',
            'authorization_sent' => true,
            'sender_name' => 'Ella Kim',
            'sender_email' => 'ella@bridgelawpc.com',
            'followup_dates' => date('m/d/Y', strtotime('-21 days')) . ', ' . date('m/d/Y', strtotime('-14 days')),
            'bulk_letter_body' => $sampleBulkBody,
            'case_list' => '',
            'case_count' => '3',
        ];
    }

    // Process placeholders
    require_once __DIR__ . '/../../helpers/letter-template.php';
    $html = processTemplatePlaceholders($bodyTemplate, $sampleData);

    successResponse(['html' => $html, 'sample_data' => $sampleData]);

} else {
    errorResponse('Either template_id+request_id or body_template must be provided', 400);
}
