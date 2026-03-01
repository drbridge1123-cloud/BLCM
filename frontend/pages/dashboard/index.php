<?php
require_once __DIR__ . '/../../../backend/config/app.php';
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
requirePermission('dashboard');

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
$pageScripts = ['assets/js/pages/dashboard/index.js'];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
