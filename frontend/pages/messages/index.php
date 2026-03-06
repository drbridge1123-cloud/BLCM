<?php
require_once __DIR__ . '/../../../backend/config/app.php';
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
requirePermission('messages');
$pageTitle = 'Messages';
$currentPage = 'messages';
$pageScripts = ['/blcm/frontend/assets/js/pages/messages/index.js'];
$pageContent = __DIR__ . '/_content.php';
require_once __DIR__ . '/../../layouts/main.php';
