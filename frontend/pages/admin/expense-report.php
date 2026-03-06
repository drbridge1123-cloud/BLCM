<?php
require_once __DIR__ . '/../../../backend/config/app.php';
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
requirePermission('expense_report');

$pageTitle = 'Expense Report';
$currentPage = 'expense_report';
$pageScripts = ['/blcm/frontend/assets/js/pages/expense-report.js'];
$pageContent = __DIR__ . '/_expense-report-content.php';
require_once __DIR__ . '/../../layouts/main.php';
