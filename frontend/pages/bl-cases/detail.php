<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('cases');

$pageTitle = 'Case Detail';
$currentPage = 'cases';
$pageHeadScripts = [
    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js',
];
$pageScripts = [
    '/blcm/frontend/assets/js/components/contacts.js',
    '/blcm/frontend/assets/js/components/checklist.js',
    '/blcm/frontend/assets/js/pages/bl-cases/detail.js'
];
$pageContent = __DIR__ . '/_detail-content.php';
require_once __DIR__ . '/../../layouts/main.php';
