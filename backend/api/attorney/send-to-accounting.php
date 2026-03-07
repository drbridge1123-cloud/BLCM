<?php
/**
 * POST /api/attorney/send-to-accounting
 * Link attorney case to main case, auto-generate disbursement items, move to accounting
 */
$userId = requireAuth();
requirePermission('attorney_cases');
$input = getInput();

$errors = validateRequired($input, ['case_id', 'assigned_to']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$caseId = (int)$input['case_id'];
$linkedCaseId = !empty($input['linked_case_id']) ? (int)$input['linked_case_id'] : null;
$assignedTo = (int)$input['assigned_to'];
$note = isset($input['note']) ? sanitizeString($input['note']) : '';

// Validate attorney case
$attCase = dbFetchOne("SELECT * FROM attorney_cases WHERE id = ? AND deleted_at IS NULL", [$caseId]);
if (!$attCase) errorResponse('Case not found', 404);
if ($attCase['phase'] !== 'settled') errorResponse('Case must be in settled phase');
if ($attCase['status'] === 'accounting') errorResponse('Case is already in accounting');
if ($attCase['status'] === 'closed') errorResponse('Case is already closed');

// Validate linked main case (optional)
$mainCase = null;
if ($linkedCaseId) {
    $mainCase = dbFetchOne("SELECT * FROM cases WHERE id = ?", [$linkedCaseId]);
    if (!$mainCase) errorResponse('Linked case not found', 404);
}

// Validate assigned user exists
$assignee = dbFetchOne("SELECT id, COALESCE(display_name, full_name) AS full_name FROM users WHERE id = ?", [$assignedTo]);
if (!$assignee) errorResponse('Assigned user not found', 404);

$today = date('Y-m-d');

// Update attorney case
$updateData = [
    'status' => 'accounting',
    'sent_to_accounting_date' => $today,
    'accounting_assigned_to' => $assignedTo,
];
if ($linkedCaseId) {
    $updateData['case_id'] = $linkedCaseId;
}
// ── Auto-generate disbursement items from main case settlement data ──
$disbursementItems = $mainCase ? buildDisbursementItems($linkedCaseId, $mainCase) : [];

dbTransaction(function() use ($updateData, $caseId, $disbursementItems, $userId, $assignedTo, $attCase, $note, $linkedCaseId) {
    dbUpdate('attorney_cases', $updateData, 'id = ?', [$caseId]);

    foreach ($disbursementItems as $item) {
        dbInsert('accounting_disbursements', [
            'attorney_case_id' => $caseId,
            'disbursement_type' => $item['type'],
            'payee_name' => $item['payee'],
            'amount' => $item['amount'],
            'status' => 'pending',
            'created_by' => $userId,
        ]);
    }

    // Notification
    dbInsert('notifications', [
        'user_id' => $assignedTo,
        'type' => 'status_change',
        'message' => "Attorney case {$attCase['case_number']} ({$attCase['client_name']}) sent to accounting" . ($note ? ": {$note}" : ''),
    ]);

    // Activity log
    logActivity($userId, 'send_to_accounting', 'attorney_case', $caseId, [
        'linked_case_id' => $linkedCaseId,
        'assigned_to' => $assignedTo,
        'disbursement_count' => count($disbursementItems),
        'note' => $note,
    ]);
});

successResponse([
    'disbursement_count' => count($disbursementItems),
], 'Sent to accounting');

// ──────────────────────────────────────────────────────────────
// Helper: Build disbursement items from main case settlement data
// Replicates the logic from disbursement-panel.js server-side
// ──────────────────────────────────────────────────────────────
function buildDisbursementItems($mainCaseId, $mainCase) {
    $items = [];

    // 1. Get settlement settings
    $settlementAmount = (float)($mainCase['settlement_amount'] ?? 0);
    $feePercent = (float)($mainCase['attorney_fee_percent'] ?? 0.3333);
    $coverageUm = (bool)($mainCase['coverage_um'] ?? false);
    $coverageUim = (bool)($mainCase['coverage_uim'] ?? false);
    $policyLimit = (bool)($mainCase['policy_limit'] ?? false);
    $pipAmount = (float)($mainCase['pip_subrogation_amount'] ?? 0);
    $pipCompany = $mainCase['pip_insurance_company'] ?? 'PIP Carrier';
    $clientName = $mainCase['client_name'] ?? 'Client';

    // 2. Best offers from negotiations
    $bestOffers = ['3rd_party' => 0, 'um' => 0, 'uim' => 0];
    $negotiations = dbFetchAll(
        "SELECT coverage_type, offer_amount, status FROM case_negotiations WHERE case_id = ? ORDER BY coverage_type, round_number",
        [$mainCaseId]
    );
    foreach ($negotiations as $n) {
        $type = $n['coverage_type'];
        $offer = (float)$n['offer_amount'];
        if (!isset($bestOffers[$type])) continue;
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

    // 3. Determine calculation method
    $hasUmUim = $coverageUm || $coverageUim || $bestOffers['um'] > 0 || $bestOffers['uim'] > 0;
    $has3rdParty = $bestOffers['3rd_party'] > 0;
    $hasPip = $pipAmount > 0;

    $canMahler = $has3rdParty && $hasPip && !$hasUmUim;
    $canHamm = $hasPip && $hasUmUim;

    // Use saved method preference or auto-detect
    $method = $mainCase['settlement_method'] ?? null;
    if ($method === 'mahler' && !$canMahler) $method = null;
    if ($method === 'hamm' && !$canHamm) $method = null;
    if (!$method) {
        if ($canMahler && !$canHamm) $method = 'mahler';
        elseif ($canHamm && !$canMahler) $method = 'hamm';
        else $method = 'standard';
    }

    // 4. Medical bills
    $mbrReport = dbFetchOne("SELECT id, pip1_name, pip2_name FROM mbr_reports WHERE case_id = ?", [$mainCaseId]);
    $medicalProviders = [];
    $healthSubrogation = 0;
    $pip1Total = 0;

    if ($mbrReport) {
        $mbrLines = dbFetchAll(
            "SELECT id, line_type, provider_name, charges, balance, pip1_amount FROM mbr_lines WHERE report_id = ? ORDER BY sort_order",
            [$mbrReport['id']]
        );

        $provNegotiations = dbFetchAll("SELECT * FROM provider_negotiations WHERE case_id = ?", [$mainCaseId]);
        $provNegMap = [];
        foreach ($provNegotiations as $pn) {
            $provNegMap[$pn['mbr_line_id']] = $pn;
        }

        foreach ($mbrLines as $line) {
            $pip1Total += (float)($line['pip1_amount'] ?? 0);

            if ($line['line_type'] === 'provider') {
                $balance = (float)$line['balance'];
                if (isset($provNegMap[$line['id']])) {
                    $neg = $provNegMap[$line['id']];
                    if ($neg['status'] === 'waived') {
                        $balance = 0;
                    } elseif ($neg['status'] === 'accepted') {
                        $balance = (float)$neg['accepted_amount'];
                    }
                }
                if ($balance > 0) {
                    $medicalProviders[] = [
                        'name' => $line['provider_name'],
                        'amount' => round($balance, 2),
                    ];
                }
            } elseif (in_array($line['line_type'], ['health_subrogation', 'health_subrogation2'])) {
                $healthSubrogation += (float)$line['balance'];
            }
        }
    }

    // Use PIP from MBR if available, else from case settings
    if ($pip1Total > 0 && $pipAmount <= 0) {
        $pipAmount = $pip1Total;
    }

    // 5. Expenses: Records Fee + Litigation Fee for attorney calcs, Other Expenses separate
    $expenses = dbFetchOne(
        "SELECT
            COALESCE(SUM(CASE WHEN expense_category = 'mr_cost' THEN paid_amount ELSE 0 END), 0) AS records_fee,
            COALESCE(SUM(CASE WHEN expense_category = 'litigation' THEN paid_amount ELSE 0 END), 0) AS litigation_fee,
            COALESCE(SUM(CASE WHEN expense_category = 'other' THEN paid_amount ELSE 0 END), 0) AS other_expenses
         FROM mr_fee_payments WHERE case_id = ?",
        [$mainCaseId]
    );
    $recordsFee = round((float)($expenses['records_fee'] ?? 0), 2);
    $litigationFee = round((float)($expenses['litigation_fee'] ?? 0), 2);
    $costs = $recordsFee + $litigationFee; // Records + Litigation only (excludes Other)
    $otherExpenses = round((float)($expenses['other_expenses'] ?? 0), 2);

    // 6. Calculate based on method
    $medicalBalance = 0;
    foreach ($medicalProviders as $p) $medicalBalance += $p['amount'];

    if ($method === 'mahler') {
        $gross = $bestOffers['3rd_party'] ?: $settlementAmount;
        $fee = round($gross * $feePercent, 2);
        $afe = $fee + $costs;
        $attorneyPercent = $gross > 0 ? $afe / $gross : 0;
        $carrierShare = $policyLimit ? 0 : round($pipAmount * (1 - $attorneyPercent), 2);
        $totalDeductions = $fee + $costs + $otherExpenses + $carrierShare + $medicalBalance + $healthSubrogation;
        $clientNet = round($gross - $totalDeductions, 2);

        // PIP carrier share
        if ($carrierShare > 0) {
            $items[] = ['type' => 'lien_payment', 'payee' => $pipCompany, 'amount' => $carrierShare];
        }
    } elseif ($method === 'hamm') {
        $thirdParty = $bestOffers['3rd_party'];
        $umOffer = $bestOffers['um'] + $bestOffers['uim'];
        $gross = $thirdParty + $umOffer + $pipAmount;
        $fee = round($gross * $feePercent, 2);
        $legalFeeAndExpenses = $fee + $costs; // excludes Other Expenses
        $pipRatio = $gross > 0 ? $pipAmount / $gross : 0;
        $clientCredit = round($pipRatio * $legalFeeAndExpenses, 2);
        $totalDeductions = $fee + $costs + $otherExpenses + $pipAmount + $medicalBalance + $healthSubrogation - $clientCredit;
        $clientNet = round($gross - $totalDeductions, 2);

        // Full PIP payment to carrier
        if ($pipAmount > 0) {
            $items[] = ['type' => 'lien_payment', 'payee' => $pipCompany, 'amount' => $pipAmount];
        }
    } else {
        // Standard — exclude DV from gross
        $gross = $settlementAmount;
        if (!$gross) {
            $gross = $bestOffers['3rd_party'] + $bestOffers['um'] + $bestOffers['uim'];
        }
        $fee = round($gross * $feePercent, 2);
        $totalDeductions = $fee + $costs + $otherExpenses + $medicalBalance + $healthSubrogation;
        $clientNet = round($gross - $totalDeductions, 2);
    }

    // DV (Diminished Value) — no attorney fee, pass-through to client
    $dvAmount = $bestOffers['dv'] ?? 0;

    // 7. Build standard disbursement items

    // Attorney Fee
    if ($fee > 0) {
        $items[] = ['type' => 'attorney_fee', 'payee' => 'Law Office', 'amount' => $fee];
    }

    // Records Fee
    if ($recordsFee > 0) {
        $items[] = ['type' => 'mr_cost_reimbursement', 'payee' => 'Law Office (Records Fee)', 'amount' => $recordsFee];
    }

    // Litigation Fee
    if ($litigationFee > 0) {
        $items[] = ['type' => 'mr_cost_reimbursement', 'payee' => 'Law Office (Litigation Fee)', 'amount' => $litigationFee];
    }

    // Other Expenses
    if ($otherExpenses > 0) {
        $items[] = ['type' => 'other', 'payee' => 'Other Expenses', 'amount' => $otherExpenses];
    }

    // Medical providers
    foreach ($medicalProviders as $provider) {
        $items[] = ['type' => 'provider_payment', 'payee' => $provider['name'], 'amount' => $provider['amount']];
    }

    // Health subrogation
    if ($healthSubrogation > 0) {
        $items[] = ['type' => 'lien_payment', 'payee' => 'Health Insurance Subrogation', 'amount' => round($healthSubrogation, 2)];
    }

    // Client net proceeds
    if ($clientNet > 0) {
        $items[] = ['type' => 'client_payment', 'payee' => $clientName, 'amount' => $clientNet];
    }

    // DV pass-through (no attorney fee, separate payment to client)
    if ($dvAmount > 0) {
        $items[] = ['type' => 'client_payment', 'payee' => $clientName . ' (DV - No Attorney Fee)', 'amount' => $dvAmount];
    }

    return $items;
}
