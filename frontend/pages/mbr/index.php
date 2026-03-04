<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('mbr');
$pageTitle = 'MBR';
$currentPage = 'mbr';
$pageScripts = ['/CMCdemo/frontend/assets/js/pages/mbr/index.js'];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
