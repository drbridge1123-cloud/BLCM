<?php
/**
 * GET /api/prelitigation/template
 * Download empty CSV templates for prelitigation import.
 * ?type=cases|providers|followups
 */
$userId = requireAuth();

$type = $_GET['type'] ?? 'cases';

switch ($type) {
    case 'cases':
        $headers = [
            'case_number', 'client_name', 'client_dob', 'doi', 'assigned_to',
            'client_phone', 'client_email',
            'address_street', 'address_city', 'address_state', 'address_zip',
            'attorney_name', 'treatment_status', 'treatment_end_date', 'notes'
        ];
        outputCSV('prelitigation-cases-template.csv', $headers, []);
        break;

    case 'providers':
        $headers = [
            'case_number', 'provider_name', 'provider_type',
            'treatment_start_date', 'treatment_end_date'
        ];
        outputCSV('prelitigation-providers-template.csv', $headers, []);
        break;

    case 'followups':
        $headers = [
            'case_number', 'followup_date', 'followup_type',
            'contact_result', 'next_followup_date', 'notes'
        ];
        outputCSV('prelitigation-followups-template.csv', $headers, []);
        break;

    default:
        errorResponse('Invalid template type. Use: cases, providers, or followups', 400);
}
