<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('mr_tracker');
$pageTitle = 'Tracker';
$currentPage = 'mr_tracker';
$pageScripts = [
    '/CMC/frontend/components/document-selector.js',
    '/CMC/frontend/assets/js/pages/mr-tracker/mr-tracker.js',
    '/CMC/frontend/assets/js/pages/mr-tracker/health-tracker.js'
];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
