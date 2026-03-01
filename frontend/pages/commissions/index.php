<?php
require_once __DIR__ . '/../../../backend/config/app.php';
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
requirePermission('commissions');

$pageTitle = 'Commissions';
$currentPage = 'commissions';
$pageStyles = [];
$pageScripts = ['assets/js/pages/commissions/index.js'];
$pageContent = __DIR__ . '/_content.php';
include __DIR__ . '/../../layouts/main.php';
