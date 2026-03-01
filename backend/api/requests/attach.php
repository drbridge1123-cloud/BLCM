<?php
// POST /api/requests/{id}/attach - Attach document to request
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$requestId = (int)($_GET['id'] ?? 0);
if (!$requestId) {
    errorResponse('Request ID is required', 400);
}

$input = getInput();

// Validate required fields
if (empty($input['document_id'])) {
    errorResponse('document_id is required', 400);
}

$documentId = (int)$input['document_id'];

// Verify request exists and is not already sent
$request = dbFetchOne(
    "SELECT rr.*, (SELECT case_number FROM cases WHERE id = (SELECT case_id FROM case_providers WHERE id = rr.case_provider_id)) AS case_number
     FROM record_requests rr WHERE id = ?",
    [$requestId]
);

if (!$request) {
    errorResponse('Request not found', 404);
}

if ($request['send_status'] === 'sent') {
    errorResponse('Cannot attach documents to already sent requests', 422);
}

// Verify document exists and belongs to same case
$document = dbFetchOne(
    "SELECT * FROM case_documents WHERE id = ?",
    [$documentId]
);

if (!$document) {
    errorResponse('Document not found', 404);
}

// Get request's case_id
$requestCaseId = dbFetchOne(
    "SELECT case_id FROM case_providers WHERE id = ?",
    [$request['case_provider_id']]
)['case_id'];

// Verify document belongs to same case
if ($document['case_id'] != $requestCaseId) {
    errorResponse('Document does not belong to the same case as the request', 422);
}

// Check if already attached
$existing = dbFetchOne(
    "SELECT id FROM request_attachments WHERE record_request_id = ? AND case_document_id = ?",
    [$requestId, $documentId]
);

if ($existing) {
    errorResponse('Document is already attached to this request', 422);
}

// Create attachment
$attachmentId = dbInsert('request_attachments', [
    'record_request_id' => $requestId,
    'case_document_id' => $documentId
]);

// Log activity
logActivity($userId, 'document_attached', 'record_request', $requestId, [
    'document_id' => $documentId,
    'file_name' => $document['original_file_name'],
    'case_number' => $request['case_number']
]);

successResponse([
    'id' => $attachmentId,
    'request_id' => $requestId,
    'document_id' => $documentId
], 'Document attached successfully');
