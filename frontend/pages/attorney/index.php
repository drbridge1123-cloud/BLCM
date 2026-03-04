<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('attorney_cases');
$pageTitle = 'Attorney Cases';
$currentPage = 'attorney_cases';
$pageScripts = [
    '/CMCdemo/frontend/assets/js/components/pending-assignments.js',
    '/CMCdemo/frontend/assets/js/pages/attorney/index.js'
];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
