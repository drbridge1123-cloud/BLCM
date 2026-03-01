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
    "SELECT cn.*, u.full_name AS created_by_name
     FROM case_negotiations cn
     LEFT JOIN users u ON cn.created_by = u.id
     WHERE cn.case_id = ?
     ORDER BY cn.coverage_type, cn.round_number",
    [$caseId]
);

// Group by coverage type
$grouped = ['3rd_party' => [], 'um' => [], 'uim' => [], 'dv' => []];
$bestOffers = ['3rd_party' => 0, 'um' => 0, 'uim' => 0, 'dv' => 0];
$emptyAdj = ['insurance_company' => '', 'party' => '', 'adjuster_phone' => '', 'adjuster_fax' => '', 'adjuster_email' => '', 'claim_number' => ''];
$adjusterInfo = ['3rd_party' => $emptyAdj, 'um' => $emptyAdj, 'uim' => $emptyAdj, 'dv' => $emptyAdj];

foreach ($negotiations as $n) {
    $type = $n['coverage_type'];
    $grouped[$type][] = $n;

    // Extract adjuster info from the latest round (last one wins)
    if (!empty($n['insurance_company']) || !empty($n['party']) || !empty($n['adjuster_phone']) || !empty($n['adjuster_fax']) || !empty($n['adjuster_email']) || !empty($n['claim_number'])) {
        $adjusterInfo[$type] = [
            'insurance_company' => $n['insurance_company'] ?? '',
            'party' => $n['party'] ?? '',
            'adjuster_phone' => $n['adjuster_phone'] ?? '',
            'adjuster_fax' => $n['adjuster_fax'] ?? '',
            'adjuster_email' => $n['adjuster_email'] ?? '',
            'claim_number' => $n['claim_number'] ?? '',
        ];
    }

    // Best offer = accepted round's offer, or highest offer if none accepted
    $offer = (float)$n['offer_amount'];
    if ($n['status'] === 'accepted' && $offer > 0) {
        $bestOffers[$type] = $offer;
    } elseif ($bestOffers[$type] === 0 || ($offer > $bestOffers[$type] && $n['status'] !== 'rejected')) {
        // Only update if no accepted offer found yet
        $hasAccepted = false;
        foreach ($grouped[$type] as $r) {
            if ($r['status'] === 'accepted') { $hasAccepted = true; break; }
        }
        if (!$hasAccepted && $offer > $bestOffers[$type]) {
            $bestOffers[$type] = $offer;
        }
    }
}

// Determine active coverages (ones with at least one round)
$activeCoverages = [];
foreach ($grouped as $type => $rounds) {
    if (!empty($rounds)) {
        $activeCoverages[] = $type;
    }
}

jsonResponse([
    'success' => true,
    'negotiations' => $negotiations,
    'grouped' => $grouped,
    'best_offers' => $bestOffers,
    'active_coverages' => $activeCoverages,
    'adjuster_info' => $adjusterInfo,
]);
