<?php
require_once __DIR__ . '/../backend/helpers/auth.php';
require_once __DIR__ . '/../backend/config/app.php';
startSecureSession();

if (empty($_SESSION['user_id'])) {
    header('Location: /blcm/frontend/pages/auth/login.php');
} else {
    header('Location: /blcm/frontend/pages/dashboard/index.php');
}
exit;
