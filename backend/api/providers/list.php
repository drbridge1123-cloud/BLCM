<?php
/**
 * GET /api/providers
 * List all providers with optional filters and sorting
 */
requireAuth();

$type = $_GET['type'] ?? null;
$difficulty = $_GET['difficulty_level'] ?? null;
$search = $_GET['search'] ?? null;
$sortBy = $_GET['sort_by'] ?? 'name';
$sortDir = strtoupper($_GET['sort_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

$where = '1=1';
$params = [];

if ($type) {
    $validTypes = ['hospital', 'er', 'chiro', 'imaging', 'physician', 'surgery_center', 'pharmacy', 'acupuncture', 'massage', 'pain_management', 'pt', 'other'];
    if (!validateEnum($type, $validTypes))
        errorResponse('Invalid provider type');
    $where .= ' AND type = ?';
    $params[] = $type;
}

if ($difficulty) {
    $validDifficulty = ['easy', 'medium', 'hard'];
    if (!validateEnum($difficulty, $validDifficulty))
        errorResponse('Invalid difficulty level');
    $where .= ' AND difficulty_level = ?';
    $params[] = $difficulty;
}

if ($search) {
    $where .= ' AND (name LIKE ? OR phone LIKE ? OR fax LIKE ? OR email LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$allowedSorts = ['name', 'type', 'difficulty_level', 'avg_response_days', 'created_at'];
if (!in_array($sortBy, $allowedSorts))
    $sortBy = 'name';

$providers = dbFetchAll(
    "SELECT id, name, type, address, phone, fax, email, portal_url,
            preferred_method, uses_third_party, third_party_name, third_party_contact,
            avg_response_days, difficulty_level, notes, created_at, updated_at,
            (SELECT COUNT(*) FROM case_providers cp WHERE cp.provider_id = providers.id) AS case_count
     FROM providers
     WHERE {$where}
     ORDER BY {$sortBy} {$sortDir}",
    $params
);

foreach ($providers as &$p) {
    $p['uses_third_party'] = (int) $p['uses_third_party'];
    $p['avg_response_days'] = $p['avg_response_days'] !== null ? (int) $p['avg_response_days'] : null;
    $p['case_count'] = (int) $p['case_count'];
}
unset($p);

successResponse($providers);
