<?php
// Health Tracker is now part of the unified Tracker page (MR Tracker + Health Tracker tabs)
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
header('Location: /CMC/frontend/pages/mr-tracker/?tab=health');
exit;
