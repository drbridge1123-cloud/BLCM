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
$pageScripts = ['/CMC/frontend/assets/js/pages/cases/detail.js'];
$pageContent = __DIR__ . '/_detail-content.php';
require_once __DIR__ . '/../../layouts/main.php';
