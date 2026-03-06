<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('providers');
$pageTitle = 'Database';
$currentPage = 'providers';
$pageScripts = [
    '/blcm/frontend/assets/js/pages/providers/providers.js',
    '/blcm/frontend/assets/js/pages/providers/insurance-companies.js',
    '/blcm/frontend/assets/js/pages/providers/adjusters.js',
    '/blcm/frontend/assets/js/pages/providers/clients.js',
    '/blcm/frontend/assets/js/pages/admin/templates.js',
];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
