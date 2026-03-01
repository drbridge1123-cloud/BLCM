<?php
function calculateNextFollowup($fromDate = null) {
    $date = $fromDate ? new DateTime($fromDate) : new DateTime();
    $date->modify('+' . DEFAULT_FOLLOWUP_DAYS . ' days');
    return $date->format('Y-m-d');
}

function calculateDeadline($fromDate = null) {
    $date = $fromDate ? new DateTime($fromDate) : new DateTime();
    $date->modify('+' . DEFAULT_DEADLINE_DAYS . ' days');
    return $date->format('Y-m-d');
}

function daysElapsed($fromDate) {
    if (!$fromDate) return null;
    $from = new DateTime($fromDate);
    $now = new DateTime();
    return $from->diff($now)->days;
}

function daysUntil($targetDate) {
    if (!$targetDate) return null;
    $target = new DateTime($targetDate);
    $now = new DateTime();
    $diff = $now->diff($target);
    return $diff->invert ? -$diff->days : $diff->days;
}

function isOverdue($deadline) {
    if (!$deadline) return false;
    return daysUntil($deadline) < 0;
}

function isFollowupDue($lastRequestDate) {
    if (!$lastRequestDate) return false;
    return daysElapsed($lastRequestDate) >= DEFAULT_FOLLOWUP_DAYS;
}

function isDeadlineWarning($deadline) {
    if (!$deadline) return false;
    $days = daysUntil($deadline);
    return $days >= 0 && $days <= DEADLINE_WARNING_DAYS;
}
