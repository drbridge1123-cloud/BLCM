<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAdmin();

$pageTitle = 'Data Management';
$currentPage = 'data_management';
$pageScripts = ['assets/js/pages/admin/data-management.js'];
$pageContent = __DIR__ . '/_data-management-content.php';
require_once __DIR__ . '/../../layouts/main.php';
