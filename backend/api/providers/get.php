<?php
/**
 * GET /api/providers/{id}
 * Get a single provider with contacts and usage count
 */
requireAuth();

$id = (int)$_GET['id'];

$provider = dbFetchOne(
    "SELECT id, name, type, address, phone, fax, email, portal_url,
            preferred_method, uses_third_party, third_party_name, third_party_contact,
            avg_response_days, difficulty_level, notes, created_at, updated_at
     FROM providers WHERE id = ?",
    [$id]
);

if (!$provider) errorResponse('Provider not found', 404);

$provider['uses_third_party'] = (int)$provider['uses_third_party'];
$provider['avg_response_days'] = $provider['avg_response_days'] !== null ? (int)$provider['avg_response_days'] : null;

// Get contacts
$provider['contacts'] = dbFetchAll(
    "SELECT id, department, contact_type, contact_value, is_primary, verified_at, notes, created_at
     FROM provider_contacts
     WHERE provider_id = ?
     ORDER BY is_primary DESC, id ASC",
    [$id]
);

foreach ($provider['contacts'] as &$c) {
    $c['is_primary'] = (int)$c['is_primary'];
}
unset($c);

// Get usage count (how many case_providers reference this provider)
$provider['usage_count'] = dbCount('case_providers', 'provider_id = ?', [$id]);

successResponse($provider);
