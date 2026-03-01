<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('cases');

$pageTitle = 'Cases';
$currentPage = 'cases';
$pageScripts = ['/CMC/frontend/assets/js/pages/cases/list.js'];
$pageContent = __DIR__ . '/_list-content.php';
require_once __DIR__ . '/../../layouts/main.php';
