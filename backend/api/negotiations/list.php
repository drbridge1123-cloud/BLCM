<?php
/**
 * GET /api/negotiations?case_id=X
 * List case negotiations grouped by coverage_type
 */
$userId = requireAuth();
requirePermission('cases');

$caseId = (int)($_GET['case_id'] ?? 0);
if (!$caseId) errorResponse('case_id is required');

// All rounds ordered by coverage_type and round
$rows = dbFetchAll(
    "SELECT * FROM case_negotiations
     WHERE case_id = ?
     ORDER BY coverage_type, round_number ASC",
    [$caseId]
);

// Group by coverage_type
$grouped = [];
foreach ($rows as $r) {
    $ct = $r['coverage_type'];
    if (!isset($grouped[$ct])) {
        $grouped[$ct] = ['rounds' => [], 'adjuster' => null, 'best_offer' => null];
    }
    $grouped[$ct]['rounds'][] = $r;

    // Keep latest round's adjuster details
    $grouped[$ct]['adjuster'] = [
        'party'          => $r['party'],
        'adjuster_phone' => $r['adjuster_phone'],
        'adjuster_fax'   => $r['adjuster_fax'],
        'adjuster_email' => $r['adjuster_email'],
        'claim_number'   => $r['claim_number'],
        'insurance_company' => $r['insurance_company'],
    ];

    // Best offer: accepted first, then highest pending
    $offer = (float)$r['offer_amount'];
    $best = $grouped[$ct]['best_offer'];
    if ($r['status'] === 'accepted' && ($best === null || $offer > $best)) {
        $grouped[$ct]['best_offer'] = $offer;
    } elseif ($best === null && $offer > 0) {
        $grouped[$ct]['best_offer'] = $offer;
    } elseif ($best !== null && $r['status'] !== 'accepted' && $offer > $best) {
        // Only override non-accepted best if no accepted exists yet
        $hasAccepted = false;
        foreach ($grouped[$ct]['rounds'] as $prev) {
            if ($prev['status'] === 'accepted') { $hasAccepted = true; break; }
        }
        if (!$hasAccepted) $grouped[$ct]['best_offer'] = $offer;
    }
}

successResponse(['negotiations' => $grouped]);
