<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('templates');

$pageTitle = 'Letter Templates';
$currentPage = 'templates';
$pageScripts = ['/CMC/frontend/assets/js/pages/admin/templates.js'];
$pageContent = __DIR__ . '/_templates-content.php';
require_once __DIR__ . '/../../layouts/main.php';
