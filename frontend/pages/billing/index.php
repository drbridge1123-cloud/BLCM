<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('mr_tracker');
$pageTitle = 'Tracker';
$currentPage = 'mr_tracker';
$pageScripts = [
    '/CMCdemo/frontend/components/document-selector.js',
    '/CMCdemo/frontend/assets/js/pages/billing/mr-tracker.js',
    '/CMCdemo/frontend/assets/js/pages/billing/health-tracker.js'
];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
