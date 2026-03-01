<?php
// GET /api/settlement/{case_id} - Get comprehensive settlement data
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('case_id is required');
}

// Case with settlement columns
$case = dbFetchOne(
    "SELECT id, case_number, client_name, settlement_amount, attorney_fee_percent,
            coverage_3rd_party, coverage_um, coverage_uim, policy_limit, um_uim_limit,
            pip_subrogation_amount, pip_insurance_company, settlement_method
     FROM cases WHERE id = ?",
    [$caseId]
);
if (!$case) {
    errorResponse('Case not found', 404);
}

// Best offers from negotiations
$bestOffers = ['3rd_party' => 0, 'um' => 0, 'uim' => 0, 'dv' => 0];
$negotiations = dbFetchAll(
    "SELECT coverage_type, offer_amount, status FROM case_negotiations WHERE case_id = ? ORDER BY coverage_type, round_number",
    [$caseId]
);
foreach ($negotiations as $n) {
    $type = $n['coverage_type'];
    $offer = (float)$n['offer_amount'];
    if ($n['status'] === 'accepted' && $offer > 0) {
        $bestOffers[$type] = $offer;
    } else {
        $hasAccepted = false;
        foreach ($negotiations as $check) {
            if ($check['coverage_type'] === $type && $check['status'] === 'accepted') {
                $hasAccepted = true;
                break;
            }
        }
        if (!$hasAccepted && $offer > $bestOffers[$type]) {
            $bestOffers[$type] = $offer;
        }
    }
}

// MBDS data - medical bills
$mbdsReport = dbFetchOne("SELECT id, pip1_name, pip2_name, health1_name, health2_name, health3_name FROM mbds_reports WHERE case_id = ?", [$caseId]);
$medicalBills = ['total_charges' => 0, 'total_balance' => 0, 'providers' => []];
$healthSubrogation = 0;
$specialEntries = [];
$pip1Total = 0;
$pip2Total = 0;

if ($mbdsReport) {
    $mbdsLines = dbFetchAll(
        "SELECT id, line_type, provider_name, charges, balance, case_provider_id, pip1_amount, pip2_amount
         FROM mbds_lines WHERE report_id = ? ORDER BY sort_order",
        [$mbdsReport['id']]
    );

    foreach ($mbdsLines as $line) {
        // Accumulate PIP totals from all lines
        $pip1Total += (float)($line['pip1_amount'] ?? 0);
        $pip2Total += (float)($line['pip2_amount'] ?? 0);

        if ($line['line_type'] === 'provider') {
            $medicalBills['total_charges'] += (float)$line['charges'];
            $medicalBills['total_balance'] += (float)$line['balance'];
            $medicalBills['providers'][] = [
                'id' => $line['id'],
                'name' => $line['provider_name'],
                'charges' => (float)$line['charges'],
                'balance' => (float)$line['balance'],
                'case_provider_id' => $line['case_provider_id'],
            ];
        } elseif (in_array($line['line_type'], ['health_subrogation', 'health_subrogation2'])) {
            $healthSubrogation += (float)$line['balance'];
            $specialEntries[] = [
                'type' => $line['line_type'],
                'name' => $line['provider_name'],
                'amount' => (float)$line['balance'],
            ];
        } elseif ($line['line_type'] !== 'provider') {
            $specialEntries[] = [
                'type' => $line['line_type'],
                'name' => $line['provider_name'],
                'amount' => (float)$line['balance'],
            ];
        }
    }
}

// Provider negotiations (negotiated amounts)
$providerNegotiations = dbFetchAll(
    "SELECT pn.*, ml.balance AS mbds_balance
     FROM provider_negotiations pn
     LEFT JOIN mbds_lines ml ON pn.mbds_line_id = ml.id
     WHERE pn.case_id = ?",
    [$caseId]
);

// Calculate negotiated medical balance
$negotiatedMedicalBalance = 0;
$provNegMap = [];
foreach ($providerNegotiations as $pn) {
    $provNegMap[$pn['mbds_line_id']] = $pn;
}

foreach ($medicalBills['providers'] as &$provider) {
    if (isset($provNegMap[$provider['id']])) {
        $neg = $provNegMap[$provider['id']];
        if (in_array($neg['status'], ['accepted', 'waived'])) {
            $provider['negotiated_amount'] = $neg['status'] === 'waived' ? 0 : (float)$neg['accepted_amount'];
        } else {
            $provider['negotiated_amount'] = (float)$provider['balance'];
        }
    } else {
        $provider['negotiated_amount'] = (float)$provider['balance'];
    }
    $negotiatedMedicalBalance += $provider['negotiated_amount'];
}
unset($provider);

// Expenses from cost ledger
$expenses = dbFetchOne(
    "SELECT
        COALESCE(SUM(CASE WHEN expense_category = 'mr_cost' THEN paid_amount ELSE 0 END), 0) AS reimbursable,
        COALESCE(SUM(CASE WHEN expense_category = 'litigation' THEN paid_amount ELSE 0 END), 0) AS litigation,
        COALESCE(SUM(paid_amount), 0) AS total
     FROM mr_fee_payments WHERE case_id = ?",
    [$caseId]
);

// PIP info from MBDS report
$pipInfo = [
    'pip1_name' => $mbdsReport['pip1_name'] ?? null,
    'pip2_name' => $mbdsReport['pip2_name'] ?? null,
    'pip1_total' => round($pip1Total, 2),
    'pip2_total' => round($pip2Total, 2),
];

jsonResponse([
    'success' => true,
    'settings' => [
        'settlement_amount' => (float)$case['settlement_amount'],
        'attorney_fee_percent' => (float)$case['attorney_fee_percent'],
        'coverage_3rd_party' => (bool)$case['coverage_3rd_party'],
        'coverage_um' => (bool)$case['coverage_um'],
        'coverage_uim' => (bool)$case['coverage_uim'],
        'policy_limit' => (bool)$case['policy_limit'],
        'um_uim_limit' => (bool)$case['um_uim_limit'],
        'pip_subrogation_amount' => (float)$case['pip_subrogation_amount'],
        'pip_insurance_company' => $case['pip_insurance_company'],
        'settlement_method' => $case['settlement_method'],
    ],
    'best_offers' => $bestOffers,
    'medical_bills' => $medicalBills,
    'medical_balance' => round($negotiatedMedicalBalance, 2),
    'health_subrogation' => round($healthSubrogation, 2),
    'special_entries' => $specialEntries,
    'expenses' => [
        'reimbursable' => round((float)$expenses['reimbursable'], 2),
        'litigation' => round((float)$expenses['litigation'], 2),
        'total' => round((float)$expenses['total'], 2),
    ],
    'pip_info' => $pipInfo,
    'provider_negotiations' => $providerNegotiations,
]);
