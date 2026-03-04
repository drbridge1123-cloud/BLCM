<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAdmin();

$pageTitle = 'Users';
$currentPage = 'users';
$pageScripts = [
    '/CMCdemo/frontend/assets/js/pages/admin/users.js',
    'assets/js/pages/admin/data-management.js',
];
$pageContent = __DIR__ . '/_users-content.php';
require_once __DIR__ . '/../../layouts/main.php';
