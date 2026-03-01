<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('accounting_tracker');
$pageTitle = 'Accounting Tracker';
$currentPage = 'accounting_tracker';
$pageScripts = [
    '/CMC/frontend/assets/js/pages/accounting-tracker/index.js'
];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
