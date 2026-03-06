<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
require_once __DIR__ . '/../../../backend/config/app.php';
requireAuth();
requirePermission('prelitigation_tracker');
$pageTitle = 'Prelitigation Tracker';
$currentPage = 'prelitigation_tracker';
$pageScripts = [
    '/blcm/frontend/assets/js/components/contacts.js',
    '/blcm/frontend/assets/js/pages/prelitigation/index.js'
];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
