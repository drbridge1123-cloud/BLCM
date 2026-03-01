<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('providers');
$pageTitle = 'Database';
$currentPage = 'providers';
$pageScripts = [
    '/CMC/frontend/assets/js/pages/providers/providers.js',
    '/CMC/frontend/assets/js/pages/providers/insurance-companies.js',
    '/CMC/frontend/assets/js/pages/providers/adjusters.js'
];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
