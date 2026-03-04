<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('providers');
$pageTitle = 'Database';
$currentPage = 'providers';
$pageScripts = [
    '/CMCdemo/frontend/assets/js/pages/providers/providers.js',
    '/CMCdemo/frontend/assets/js/pages/providers/insurance-companies.js',
    '/CMCdemo/frontend/assets/js/pages/providers/adjusters.js',
    '/CMCdemo/frontend/assets/js/pages/providers/clients.js',
    '/CMCdemo/frontend/assets/js/pages/admin/templates.js',
];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
