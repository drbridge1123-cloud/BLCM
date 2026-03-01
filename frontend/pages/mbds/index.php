<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('mbds');
$pageTitle = 'MBDS';
$currentPage = 'mbds';
$pageScripts = ['/CMC/frontend/assets/js/pages/mbds/index.js'];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
