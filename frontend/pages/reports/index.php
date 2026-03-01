<?php
require_once __DIR__ . '/../../../backend/config/app.php';
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
requirePermission('reports');

$pageTitle = 'Reports & Analytics';
$currentPage = 'reports';
$pageScripts = ['assets/js/pages/reports/index.js'];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
