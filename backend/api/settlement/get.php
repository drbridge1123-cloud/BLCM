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

// Best offers from negotiations — sum across coverage_index per type
$bestOffers = ['3rd_party' => 0, 'um' => 0, 'uim' => 0, 'dv' => 0];
$activeCoverages = [];
$negotiations = dbFetchAll(
    "SELECT coverage_type, coverage_index, offer_amount, status FROM case_negotiations WHERE case_id = ? ORDER BY coverage_type, coverage_index, round_number",
    [$caseId]
);

// Group by (type, index) to find best offer per tab, then sum by type
$tabOffers = []; // "type_index" => best_offer
foreach ($negotiations as $n) {
    $type = $n['coverage_type'];
    $index = (int)$n['coverage_index'];
    $key = "{$type}_{$index}";
    $offer = (float)$n['offer_amount'];

    if (!isset($tabOffers[$key])) {
        $tabOffers[$key] = ['type' => $type, 'best' => 0, 'has_accepted' => false];
    }

    if ($n['status'] === 'accepted' && $offer > 0) {
        $tabOffers[$key]['best'] = $offer;
        $tabOffers[$key]['has_accepted'] = true;
    } elseif (!$tabOffers[$key]['has_accepted'] && $offer > $tabOffers[$key]['best']) {
        $tabOffers[$key]['best'] = $offer;
    }
}
foreach ($tabOffers as $tab) {
    $bestOffers[$tab['type']] = ($bestOffers[$tab['type']] ?? 0) + $tab['best'];
}

// Active coverages (unique types with at least one negotiation round)
$covTypes = array_unique(array_column($negotiations, 'coverage_type'));
foreach (['3rd_party', 'um', 'uim', 'dv'] as $ct) {
    if (in_array($ct, $covTypes)) {
        $activeCoverages[] = $ct;
    }
}

// MBR data - medical bills
$mbrReport = dbFetchOne("SELECT id, pip1_name, pip2_name, health1_name, health2_name, health3_name FROM mbr_reports WHERE case_id = ?", [$caseId]);
$medicalBills = ['total_charges' => 0, 'total_balance' => 0, 'providers' => []];
$healthSubrogation = 0;
$specialEntries = [];
$pip1Total = 0;
$pip2Total = 0;

if ($mbrReport) {
    $mbrLines = dbFetchAll(
        "SELECT id, line_type, provider_name, charges, balance, case_provider_id, pip1_amount, pip2_amount
         FROM mbr_lines WHERE report_id = ? ORDER BY sort_order",
        [$mbrReport['id']]
    );

    foreach ($mbrLines as $line) {
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
    "SELECT pn.*, ml.balance AS mbr_balance
     FROM provider_negotiations pn
     LEFT JOIN mbr_lines ml ON pn.mbr_line_id = ml.id
     WHERE pn.case_id = ?",
    [$caseId]
);

// Calculate negotiated medical balance
$negotiatedMedicalBalance = 0;
$provNegMap = [];
foreach ($providerNegotiations as $pn) {
    $provNegMap[$pn['mbr_line_id']] = $pn;
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

// Expenses from cost ledger (use billed_amount for settlement calculations)
$expenses = dbFetchOne(
    "SELECT
        COALESCE(SUM(CASE WHEN expense_category = 'mr_cost' THEN billed_amount ELSE 0 END), 0) AS reimbursable,
        COALESCE(SUM(CASE WHEN expense_category = 'litigation' THEN billed_amount ELSE 0 END), 0) AS litigation,
        COALESCE(SUM(CASE WHEN expense_category = 'other' THEN billed_amount ELSE 0 END), 0) AS other_expenses,
        COALESCE(SUM(billed_amount), 0) AS total
     FROM mr_fee_payments WHERE case_id = ?",
    [$caseId]
);

// PIP info from MBR report + Contacts
$pipInsuranceCompany = null;
$pipAdj = dbFetchOne(
    "SELECT ic.name AS company_name
     FROM case_adjusters ca
     JOIN adjusters a ON a.id = ca.adjuster_id
     LEFT JOIN insurance_companies ic ON ic.id = a.insurance_company_id
     WHERE ca.case_id = ? AND ca.coverage_type = 'pip'",
    [$caseId]
);
if ($pipAdj) {
    $pipInsuranceCompany = $pipAdj['company_name'] ?? null;
}

$pipInfo = [
    'pip1_name' => $mbrReport['pip1_name'] ?? null,
    'pip2_name' => $mbrReport['pip2_name'] ?? null,
    'pip1_total' => round($pip1Total, 2),
    'pip2_total' => round($pip2Total, 2),
    'contact_company' => $pipInsuranceCompany,
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
    'active_coverages' => $activeCoverages,
    'best_offers' => $bestOffers,
    'medical_bills' => $medicalBills,
    'medical_balance' => round($negotiatedMedicalBalance, 2),
    'health_subrogation' => round($healthSubrogation, 2),
    'special_entries' => $specialEntries,
    'expenses' => [
        'reimbursable' => round((float)$expenses['reimbursable'], 2),
        'litigation' => round((float)$expenses['litigation'], 2),
        'other' => round((float)$expenses['other_expenses'], 2),
        'total' => round((float)$expenses['total'], 2),
    ],
    'pip_info' => $pipInfo,
    'provider_negotiations' => $providerNegotiations,
]);
