<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('bank_reconciliation');

$pageTitle = 'Bank Reconciliation';
$currentPage = 'bank_reconciliation';
$pageScripts = ['/CMCdemo/frontend/assets/js/pages/admin/bank-reconciliation.js'];
$pageContent = __DIR__ . '/_bank-reconciliation-content.php';
require_once __DIR__ . '/../../layouts/main.php';
