<?php
require_once __DIR__ . '/../../../backend/config/app.php';
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
requirePermission('goals');

$pageTitle = 'Goals';
$currentPage = 'goals';
$pageScripts = [];
$pageContent = __DIR__ . '/_goals-content.php';
require_once __DIR__ . '/../../layouts/main.php';
