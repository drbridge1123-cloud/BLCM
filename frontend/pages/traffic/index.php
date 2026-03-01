<?php
require_once __DIR__ . '/../../../backend/config/app.php';
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
requirePermission('traffic');

$pageTitle = 'Traffic Cases';
$currentPage = 'traffic';
$pageStyles = [];
$pageScripts = ['assets/js/pages/traffic/index.js'];
$pageContent = __DIR__ . '/_content.php';
include __DIR__ . '/../../layouts/main.php';
