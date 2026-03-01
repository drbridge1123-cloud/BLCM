<?php
/**
 * Commission Calculation Functions
 * Ported from Commission/includes/functions.php
 */

/**
 * Calculate legal fee from base amount and fee rate
 */
function calculateLegalFee($base, $feeRate) {
    if ($base <= 0 || $feeRate <= 0) return 0;
    return round(($base * $feeRate) / 100, 2);
}

/**
 * Calculate commission from legal fee and commission rate
 */
function calculateCommission($legalFee, $commissionRate) {
    if ($legalFee <= 0 || $commissionRate <= 0) return 0;
    return round(($legalFee * $commissionRate) / 100, 2);
}

/**
 * Calculate full case financials for employee commission
 * Used for: jimi, dave, ella, soyong
 */
function calculateCaseFinancials($settled, $presuitOffer, $feeRate, $commissionRate, $usesPresuitOffer = true) {
    $settled = (float)$settled;
    $presuitOffer = (float)$presuitOffer;
    $feeRate = (float)$feeRate;
    $commissionRate = (float)$commissionRate;

    $difference = $usesPresuitOffer ? max(0, $settled - $presuitOffer) : $settled;
    $legalFee = calculateLegalFee($settled, $feeRate);
    $discountedLegalFee = calculateLegalFee($difference, $feeRate);
    $commissionBase = $usesPresuitOffer ? $discountedLegalFee : $legalFee;
    $commission = calculateCommission($commissionBase, $commissionRate);

    return [
        'settled' => $settled,
        'presuit_offer' => $presuitOffer,
        'difference' => $difference,
        'fee_rate' => $feeRate,
        'legal_fee' => $legalFee,
        'discounted_legal_fee' => $discountedLegalFee,
        'commission_rate' => $commissionRate,
        'commission' => $commission,
    ];
}

/**
 * Calculate attorney (Chong) commission based on phase and resolution type
 * - Demand: 5% of discounted legal fee
 * - Litigation 33.33% group: 20% of discounted legal fee
 * - Litigation 40% group: 20% of legal fee (no presuit deduction)
 * - UIM: 5% of uim discounted legal fee
 */
function calculateChongCommission($phase, $resolutionType, $settled, $presuitOffer, $discountedLegalFee = null, $manualCommissionRate = null, $manualFeeRate = null, $overrideFeeRate = null) {
    $settled = (float)$settled;
    $presuitOffer = (float)$presuitOffer;

    if ($phase === 'demand') {
        $feeRate = FEE_RATE_STANDARD;
        $legalFee = calculateLegalFee($settled, $feeRate);
        $discLegalFee = $legalFee; // no presuit deduction for demand
        $commission = calculateCommission($discLegalFee, 5);
        return [
            'fee_rate' => $feeRate,
            'legal_fee' => $legalFee,
            'discounted_legal_fee' => $discLegalFee,
            'commission_rate' => 5,
            'commission' => $commission,
            'commission_type' => 'demand_5pct',
        ];
    }

    if ($phase === 'litigation') {
        $resType = $resolutionType ?? '';
        $group33 = ['No Offer Settle', 'File and Bump', 'Post Deposition Settle', 'Mediation', 'Settled Post Arbitration', 'Settlement Conference'];
        $group40 = ['Arbitration Award', 'Beasley'];
        $groupVar = ['Co-Counsel', 'Other'];

        if (in_array($resType, $group33)) {
            $feeRate = $overrideFeeRate ?? FEE_RATE_STANDARD;
            $difference = max(0, $settled - $presuitOffer);
            $legalFee = calculateLegalFee($settled, $feeRate);
            $discLegalFee = calculateLegalFee($difference, $feeRate);
            $commission = calculateCommission($discLegalFee, 20);
            $commType = 'litigation_' . str_replace('.', '', $feeRate) . 'pct';
        } elseif (in_array($resType, $group40)) {
            $feeRate = FEE_RATE_PREMIUM;
            $legalFee = calculateLegalFee($settled, $feeRate);
            $discLegalFee = $legalFee; // no presuit deduction for 40% group
            $commission = calculateCommission($discLegalFee, 20);
            $commType = 'litigation_40pct';
        } elseif (in_array($resType, $groupVar)) {
            $feeRate = $manualFeeRate ?? FEE_RATE_STANDARD;
            $cRate = $manualCommissionRate ?? 20;
            $difference = max(0, $settled - $presuitOffer);
            $legalFee = calculateLegalFee($settled, $feeRate);
            $discLegalFee = calculateLegalFee($difference, $feeRate);
            $commission = calculateCommission($discLegalFee, $cRate);
            $commType = 'litigation_variable';
        } else {
            $feeRate = FEE_RATE_STANDARD;
            $difference = max(0, $settled - $presuitOffer);
            $legalFee = calculateLegalFee($settled, $feeRate);
            $discLegalFee = calculateLegalFee($difference, $feeRate);
            $commission = calculateCommission($discLegalFee, 20);
            $commType = 'litigation_default';
        }

        return [
            'fee_rate' => $feeRate,
            'legal_fee' => $legalFee,
            'discounted_legal_fee' => $discLegalFee,
            'commission_rate' => $manualCommissionRate ?? 20,
            'commission' => $commission,
            'commission_type' => $commType,
        ];
    }

    // UIM
    if ($phase === 'uim') {
        $feeRate = FEE_RATE_STANDARD;
        $legalFee = calculateLegalFee($settled, $feeRate);
        $discLegalFee = $discountedLegalFee ?? $legalFee;
        $commission = calculateCommission($discLegalFee, 5);
        return [
            'fee_rate' => $feeRate,
            'legal_fee' => $legalFee,
            'discounted_legal_fee' => $discLegalFee,
            'commission_rate' => 5,
            'commission' => $commission,
            'commission_type' => 'uim_5pct',
        ];
    }

    return [
        'fee_rate' => 0, 'legal_fee' => 0, 'discounted_legal_fee' => 0,
        'commission_rate' => 0, 'commission' => 0, 'commission_type' => null,
    ];
}

/**
 * Get Chong's resolution types grouped
 */
function getChongResolutionTypes() {
    return [
        'demand' => ['Demand Settlement'],
        'litigation_33' => ['No Offer Settle', 'File and Bump', 'Post Deposition Settle', 'Mediation', 'Settled Post Arbitration', 'Settlement Conference'],
        'litigation_40' => ['Arbitration Award', 'Beasley'],
        'variable' => ['Co-Counsel', 'Other'],
    ];
}

/**
 * Calculate demand deadline (90 days from assigned date)
 */
function calculateDemandDeadline($assignedDate) {
    if (empty($assignedDate)) return null;
    $date = new DateTime($assignedDate);
    $date->modify('+90 days');
    return $date->format('Y-m-d');
}

/**
 * Calculate days between two dates
 */
function calculateDaysBetween($startDate, $endDate) {
    if (empty($startDate) || empty($endDate)) return null;
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    return (int)$start->diff($end)->days;
}

/**
 * Format currency for display
 */
function formatCurrency($amount, $prefix = '$') {
    return $prefix . number_format((float)$amount, 2);
}
