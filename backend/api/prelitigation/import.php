<?php
/**
 * POST /api/prelitigation/import
 * Bulk import prelitigation cases from CSV files.
 * Accepts up to 3 files: cases (required), providers (optional), followups (optional).
 */
$userId = requireAuth();
requireAdminOrManager();

if (empty($_FILES['cases'])) {
    errorResponse('Cases CSV file is required', 400);
}

$casesFile = $_FILES['cases'];
if ($casesFile['error'] !== UPLOAD_ERR_OK) {
    errorResponse('Cases file upload error', 400);
}

// Parse cases CSV
$caseRows = parseCSV($casesFile['tmp_name']);
if (empty($caseRows)) {
    errorResponse('No data found in cases CSV', 400);
}

// Parse optional providers CSV
$providerRows = [];
if (!empty($_FILES['providers']) && $_FILES['providers']['error'] === UPLOAD_ERR_OK) {
    $providerRows = parseCSV($_FILES['providers']['tmp_name']);
}

// Parse optional followups CSV
$followupRows = [];
if (!empty($_FILES['followups']) && $_FILES['followups']['error'] === UPLOAD_ERR_OK) {
    $followupRows = parseCSV($_FILES['followups']['tmp_name']);
}

// Build staff name → ID lookup (case-insensitive)
$users = dbFetchAll("SELECT id, full_name, display_name FROM users WHERE is_active = 1");
$staffMap = [];
foreach ($users as $u) {
    $displayName = strtolower(trim($u['display_name'] ?: $u['full_name']));
    $staffMap[$displayName] = (int)$u['id'];
    if ($u['full_name']) {
        $staffMap[strtolower(trim($u['full_name']))] = (int)$u['id'];
    }
}

// Helper: resolve staff name to user ID (exact then partial match)
function resolveStaffId($name, $staffMap) {
    if (!$name) return null;
    $key = strtolower(trim($name));
    if (isset($staffMap[$key])) return $staffMap[$key];
    // Partial match
    foreach ($staffMap as $mapName => $id) {
        if (str_contains($mapName, $key) || str_contains($key, $mapName)) {
            return $id;
        }
    }
    return null;
}

// Helper: normalize date (supports YYYY-MM-DD, M/D/YYYY, MM/DD/YYYY, M-D-YYYY)
function normalizeDate($val) {
    if (!$val || $val === '' || $val === '—') return null;
    $val = trim($val);
    // Already YYYY-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return $val;
    // M/D/YYYY or MM/DD/YYYY
    $formats = ['n/j/Y', 'm/d/Y', 'n-j-Y', 'm-d-Y'];
    foreach ($formats as $fmt) {
        $d = DateTime::createFromFormat($fmt, $val);
        if ($d) return $d->format('Y-m-d');
    }
    return null;
}

// Valid enums
$validTreatmentStatuses = ['in_treatment', 'treatment_done', 'neg', 'rfd'];
$validProviderTypes = ['hospital', 'er', 'chiro', 'imaging', 'physician', 'surgery_center', 'pharmacy', 'acupuncture', 'massage', 'pain_management', 'pt', 'other'];
$validFollowupTypes = ['phone', 'email', 'text', 'in_person', 'other'];
$validContactResults = ['reached', 'voicemail', 'no_answer', 'callback_scheduled', 'treatment_update', 'text'];

// ── Phase 1: Import Cases ──
$casesInserted = 0;
$casesSkipped = 0;
$errors = [];
$caseNumberToId = []; // case_number → case_id map for providers/followups

foreach ($caseRows as $i => $row) {
    $lineNum = $i + 2;

    $caseNumber = trim($row['case_number'] ?? '');
    $clientName = trim($row['client_name'] ?? '');
    $clientDob = normalizeDate($row['client_dob'] ?? '');
    $doi = normalizeDate($row['doi'] ?? '');
    $assignedToName = trim($row['assigned_to'] ?? '');

    // Validate required fields
    if (!$caseNumber) {
        $errors[] = "Cases line {$lineNum}: Missing case_number";
        $casesSkipped++;
        continue;
    }
    if (!$clientName) {
        $errors[] = "Cases line {$lineNum}: Missing client_name for {$caseNumber}";
        $casesSkipped++;
        continue;
    }
    if (!$clientDob) {
        $errors[] = "Cases line {$lineNum}: Missing or invalid client_dob for {$caseNumber}";
        $casesSkipped++;
        continue;
    }
    if (!$doi) {
        $errors[] = "Cases line {$lineNum}: Missing or invalid doi for {$caseNumber}";
        $casesSkipped++;
        continue;
    }

    // Resolve staff
    $assignedToId = resolveStaffId($assignedToName, $staffMap);
    if (!$assignedToId && $assignedToName) {
        $errors[] = "Cases line {$lineNum}: Unknown staff '{$assignedToName}' for {$caseNumber}";
        $casesSkipped++;
        continue;
    }

    // Duplicate check
    $existing = dbFetchOne("SELECT id FROM cases WHERE case_number = ?", [$caseNumber]);
    if ($existing) {
        $errors[] = "Cases line {$lineNum}: Case {$caseNumber} already exists (skipped)";
        // Still map it so providers/followups can reference it
        $caseNumberToId[$caseNumber] = (int)$existing['id'];
        $casesSkipped++;
        continue;
    }

    try {
        // Create client record
        $clientPhone = trim($row['client_phone'] ?? '');
        $clientEmail = trim($row['client_email'] ?? '');
        $clientId = null;

        $hasClientInfo = $clientPhone || $clientEmail
            || trim($row['address_street'] ?? '')
            || trim($row['address_city'] ?? '');

        if ($hasClientInfo || $clientName) {
            $clientId = dbInsert('clients', [
                'name'           => $clientName,
                'dob'            => $clientDob,
                'phone'          => $clientPhone,
                'email'          => $clientEmail,
                'address_street' => trim($row['address_street'] ?? ''),
                'address_city'   => trim($row['address_city'] ?? ''),
                'address_state'  => trim($row['address_state'] ?? ''),
                'address_zip'    => trim($row['address_zip'] ?? ''),
            ]);
        }

        // Treatment status
        $treatmentStatus = strtolower(trim($row['treatment_status'] ?? ''));
        if ($treatmentStatus && !in_array($treatmentStatus, $validTreatmentStatuses)) {
            $treatmentStatus = 'in_treatment';
        }
        if (!$treatmentStatus) {
            $treatmentStatus = 'in_treatment';
        }

        $treatmentEndDate = normalizeDate($row['treatment_end_date'] ?? '');

        // Insert case
        $caseData = [
            'case_number'              => $caseNumber,
            'client_name'              => $clientName,
            'client_dob'               => $clientDob,
            'doi'                      => $doi,
            'client_id'                => $clientId,
            'client_phone'             => $clientPhone,
            'client_email'             => $clientEmail,
            'assigned_to'              => $assignedToId,
            'status'                   => 'ini',
            'assignment_status'        => $assignedToId ? 'accepted' : null,
            'treatment_status'         => $treatmentStatus,
            'treatment_end_date'       => $treatmentEndDate,
            'attorney_name'            => trim($row['attorney_name'] ?? ''),
            'notes'                    => trim($row['notes'] ?? ''),
            'prelitigation_start_date' => date('Y-m-d'),
        ];

        $caseId = dbInsert('cases', $caseData);
        $caseNumberToId[$caseNumber] = $caseId;
        $casesInserted++;

        logActivity($userId, 'import_case', 'case', $caseId, [
            'case_number' => $caseNumber,
            'client_name' => $clientName,
            'source'      => 'csv_import',
        ]);

    } catch (Exception $e) {
        $errors[] = "Cases line {$lineNum}: " . $e->getMessage();
        $casesSkipped++;
    }
}

// ── Phase 2: Import Providers ──
$providersInserted = 0;
$providersSkipped = 0;

foreach ($providerRows as $i => $row) {
    $lineNum = $i + 2;

    $caseNumber = trim($row['case_number'] ?? '');
    $providerName = trim($row['provider_name'] ?? '');

    if (!$caseNumber || !$providerName) {
        $errors[] = "Providers line {$lineNum}: Missing case_number or provider_name";
        $providersSkipped++;
        continue;
    }

    // Lookup case ID
    $caseId = $caseNumberToId[$caseNumber] ?? null;
    if (!$caseId) {
        $case = dbFetchOne("SELECT id FROM cases WHERE case_number = ?", [$caseNumber]);
        if ($case) {
            $caseId = (int)$case['id'];
            $caseNumberToId[$caseNumber] = $caseId;
        }
    }
    if (!$caseId) {
        $errors[] = "Providers line {$lineNum}: Case {$caseNumber} not found";
        $providersSkipped++;
        continue;
    }

    try {
        // Find or create provider
        $provider = dbFetchOne("SELECT id FROM providers WHERE name = ?", [$providerName]);
        if ($provider) {
            $providerId = (int)$provider['id'];
        } else {
            $providerType = strtolower(trim($row['provider_type'] ?? 'other'));
            if (!in_array($providerType, $validProviderTypes)) {
                $providerType = 'other';
            }
            $providerId = dbInsert('providers', [
                'name' => $providerName,
                'type' => $providerType,
            ]);
        }

        // Check if case_provider already exists
        $existingCp = dbFetchOne(
            "SELECT id FROM case_providers WHERE case_id = ? AND provider_id = ?",
            [$caseId, $providerId]
        );
        if ($existingCp) {
            $providersSkipped++;
            continue;
        }

        $treatmentStart = normalizeDate($row['treatment_start_date'] ?? '');
        $treatmentEnd = normalizeDate($row['treatment_end_date'] ?? '');

        dbInsert('case_providers', [
            'case_id'              => $caseId,
            'provider_id'          => $providerId,
            'treatment_start_date' => $treatmentStart,
            'treatment_end_date'   => $treatmentEnd,
            'overall_status'       => 'treating',
        ]);
        $providersInserted++;

    } catch (Exception $e) {
        $errors[] = "Providers line {$lineNum}: " . $e->getMessage();
        $providersSkipped++;
    }
}

// ── Phase 3: Import Followups ──
$followupsInserted = 0;
$followupsSkipped = 0;

foreach ($followupRows as $i => $row) {
    $lineNum = $i + 2;

    $caseNumber = trim($row['case_number'] ?? '');
    $followupDate = normalizeDate($row['followup_date'] ?? '');
    $followupType = strtolower(trim($row['followup_type'] ?? ''));
    $contactResult = strtolower(trim($row['contact_result'] ?? ''));

    if (!$caseNumber || !$followupDate) {
        $errors[] = "Followups line {$lineNum}: Missing case_number or followup_date";
        $followupsSkipped++;
        continue;
    }

    if (!$followupType || !in_array($followupType, $validFollowupTypes)) {
        $followupType = 'phone';
    }
    if (!$contactResult || !in_array($contactResult, $validContactResults)) {
        $contactResult = 'reached';
    }

    // Lookup case ID
    $caseId = $caseNumberToId[$caseNumber] ?? null;
    if (!$caseId) {
        $case = dbFetchOne("SELECT id FROM cases WHERE case_number = ?", [$caseNumber]);
        if ($case) {
            $caseId = (int)$case['id'];
            $caseNumberToId[$caseNumber] = $caseId;
        }
    }
    if (!$caseId) {
        $errors[] = "Followups line {$lineNum}: Case {$caseNumber} not found";
        $followupsSkipped++;
        continue;
    }

    try {
        $nextFollowupDate = normalizeDate($row['next_followup_date'] ?? '');

        dbInsert('prelitigation_followups', [
            'case_id'             => $caseId,
            'followup_date'       => $followupDate,
            'followup_type'       => $followupType,
            'contact_result'      => $contactResult,
            'next_followup_date'  => $nextFollowupDate,
            'notes'               => trim($row['notes'] ?? ''),
            'created_by'          => $userId,
        ]);
        $followupsInserted++;

    } catch (Exception $e) {
        $errors[] = "Followups line {$lineNum}: " . $e->getMessage();
        $followupsSkipped++;
    }
}

// Log import activity
logActivity($userId, 'prelitigation_import', 'case', null, [
    'cases_inserted'     => $casesInserted,
    'cases_skipped'      => $casesSkipped,
    'providers_inserted' => $providersInserted,
    'providers_skipped'  => $providersSkipped,
    'followups_inserted' => $followupsInserted,
    'followups_skipped'  => $followupsSkipped,
]);

$summary = "Import complete: {$casesInserted} cases";
if ($providersInserted > 0) $summary .= ", {$providersInserted} providers";
if ($followupsInserted > 0) $summary .= ", {$followupsInserted} followups";
if ($casesSkipped > 0) $summary .= " ({$casesSkipped} cases skipped)";

successResponse([
    'cases_inserted'     => $casesInserted,
    'cases_skipped'      => $casesSkipped,
    'providers_inserted' => $providersInserted,
    'providers_skipped'  => $providersSkipped,
    'followups_inserted' => $followupsInserted,
    'followups_skipped'  => $followupsSkipped,
    'errors'             => array_slice($errors, 0, 20),
], $summary);
