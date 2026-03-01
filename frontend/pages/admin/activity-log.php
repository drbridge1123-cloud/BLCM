<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('activity_log');

$pageTitle = 'Activity Log';
$currentPage = 'activity_log';
$pageScripts = [];
$pageContent = __DIR__ . '/_activity-log-content.php';
require_once __DIR__ . '/../../layouts/main.php';
