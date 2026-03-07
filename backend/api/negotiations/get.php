<?php
// GET /api/negotiations/{case_id} - Get all negotiations for a case
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('case_id is required');
}

$negotiations = dbFetchAll(
    "SELECT cn.*, COALESCE(u.display_name, u.full_name) AS created_by_name
     FROM case_negotiations cn
     LEFT JOIN users u ON cn.created_by = u.id
     WHERE cn.case_id = ?
     ORDER BY cn.coverage_type, cn.coverage_index, cn.round_number",
    [$caseId]
);

// Group by (coverage_type, coverage_index) → tabs
$tabMap = []; // key => ['rounds' => [], 'best_offer' => 0, 'adjuster_info' => {...}]
$emptyAdj = ['insurance_company' => '', 'party' => '', 'adjuster_phone' => '', 'adjuster_fax' => '', 'adjuster_email' => '', 'claim_number' => ''];
$typeCounts = []; // count how many tabs per type (for labeling)
$bestOffersByType = ['3rd_party' => 0, 'um' => 0, 'uim' => 0, 'pip' => 0, 'pd' => 0, 'dv' => 0, 'bi' => 0];

foreach ($negotiations as $n) {
    $type = $n['coverage_type'];
    $index = (int)$n['coverage_index'];
    $key = "{$type}_{$index}";

    if (!isset($tabMap[$key])) {
        $tabMap[$key] = [
            'coverage_type' => $type,
            'coverage_index' => $index,
            'key' => $key,
            'rounds' => [],
            'best_offer' => 0,
            'adjuster_info' => $emptyAdj,
        ];
        $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
    }

    $tabMap[$key]['rounds'][] = $n;

    // Extract adjuster info from latest round (last one wins)
    if (!empty($n['insurance_company']) || !empty($n['party']) || !empty($n['adjuster_phone']) || !empty($n['adjuster_fax']) || !empty($n['adjuster_email']) || !empty($n['claim_number'])) {
        $tabMap[$key]['adjuster_info'] = [
            'insurance_company' => $n['insurance_company'] ?? '',
            'party' => $n['party'] ?? '',
            'adjuster_phone' => $n['adjuster_phone'] ?? '',
            'adjuster_fax' => $n['adjuster_fax'] ?? '',
            'adjuster_email' => $n['adjuster_email'] ?? '',
            'claim_number' => $n['claim_number'] ?? '',
        ];
    }

    // Best offer per tab: accepted > highest non-rejected
    $offer = (float)$n['offer_amount'];
    if ($n['status'] === 'accepted' && $offer > 0) {
        $tabMap[$key]['best_offer'] = $offer;
    } else {
        $hasAccepted = false;
        foreach ($tabMap[$key]['rounds'] as $r) {
            if ($r['status'] === 'accepted') { $hasAccepted = true; break; }
        }
        if (!$hasAccepted && $offer > $tabMap[$key]['best_offer']) {
            $tabMap[$key]['best_offer'] = $offer;
        }
    }
}

// Build tabs array with labels
$labels = ['3rd_party' => '3rd Party', 'um' => 'UM', 'uim' => 'UIM', 'pip' => 'PIP', 'pd' => 'PD', 'dv' => 'DV', 'bi' => 'BI'];
$tabs = [];
foreach ($tabMap as $key => $tab) {
    $type = $tab['coverage_type'];
    $baseLabel = $labels[$type] ?? $type;
    // If multiple tabs of same type, append index
    if (($typeCounts[$type] ?? 0) > 1) {
        $tab['label'] = "{$baseLabel} ({$tab['coverage_index']})";
    } else {
        $tab['label'] = $baseLabel;
    }
    $tabs[] = $tab;

    // Sum best offers by coverage type (for disbursement)
    $bestOffersByType[$type] = ($bestOffersByType[$type] ?? 0) + $tab['best_offer'];
}

// Determine active coverages (unique types with at least one tab)
$activeCoverages = array_values(array_unique(array_column($tabs, 'coverage_type')));

// Fallback: if adjuster info is empty, populate from case_adjusters table
$caseAdjusters = dbFetchAll(
    "SELECT ca.coverage_type, ca.coverage_index, a.first_name, a.last_name, a.phone, a.fax, a.email, a.claim_number,
            ic.name AS company_name
     FROM case_adjusters ca
     JOIN adjusters a ON a.id = ca.adjuster_id
     LEFT JOIN insurance_companies ic ON ic.id = a.insurance_company_id
     WHERE ca.case_id = ?",
    [$caseId]
);
$adjFallback = [];
foreach ($caseAdjusters as $adj) {
    $key = $adj['coverage_type'] . '_' . ($adj['coverage_index'] ?? 1);
    $adjFallback[$key] = [
        'insurance_company' => $adj['company_name'] ?? '',
        'party' => trim(($adj['first_name'] ?? '') . ' ' . ($adj['last_name'] ?? '')),
        'adjuster_phone' => $adj['phone'] ?? '',
        'adjuster_fax' => $adj['fax'] ?? '',
        'adjuster_email' => $adj['email'] ?? '',
        'claim_number' => $adj['claim_number'] ?? '',
    ];
}
// Apply fallback to tabs with empty adjuster info (match by type_index key)
foreach ($tabs as &$tab) {
    $key = $tab['key'];
    if (isset($adjFallback[$key])) {
        $info = $tab['adjuster_info'];
        $isEmpty = empty($info['insurance_company']) && empty($info['party']) && empty($info['adjuster_phone']) && empty($info['adjuster_fax']) && empty($info['adjuster_email']);
        if ($isEmpty) {
            $tab['adjuster_info'] = $adjFallback[$key];
        }
    }
}
unset($tab);

jsonResponse([
    'success' => true,
    'tabs' => $tabs,
    'best_offers' => $bestOffersByType,
    'active_coverages' => $activeCoverages,
]);
