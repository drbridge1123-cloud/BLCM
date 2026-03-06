<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('accounting_tracker');
$pageTitle = 'Accounting';
$currentPage = 'accounting_tracker';
$pageScripts = [
    '/blcm/frontend/assets/js/components/pending-assignments.js',
    '/blcm/frontend/assets/js/pages/accounting/index.js',
    '/blcm/frontend/assets/js/pages/admin/bank-reconciliation.js',
    '/blcm/frontend/assets/js/pages/expense-report.js',
];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
