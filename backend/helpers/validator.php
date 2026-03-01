<?php
/**
 * Input Validation Helpers
 */

function getInput() {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
    return array_merge($_GET, $_POST);
}

function validateRequired($data, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $errors[] = "{$field} is required";
        }
    }
    return $errors;
}

function sanitizeString($value) {
    return trim($value);
}

function validateDate($date) {
    if (empty($date)) return true;
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function validateEnum($value, $allowed) {
    return in_array($value, $allowed, true);
}

function getPaginationParams() {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(MAX_ITEMS_PER_PAGE, max(1, (int)($_GET['per_page'] ?? ITEMS_PER_PAGE)));
    $offset = ($page - 1) * $perPage;
    return [$page, $perPage, $offset];
}

function sanitizeLetterHtml($html) {
    // Allow safe HTML tags for letter content
    $allowed = '<html><head><body><meta><style><div><p><br><span><strong><b><em><i><u>'
             . '<table><thead><tbody><tr><td><th><ol><ul><li><h1><h2><h3><h4><h5><h6>'
             . '<img><a><hr><blockquote><pre><code><sup><sub>';
    return strip_tags($html, $allowed);
}
