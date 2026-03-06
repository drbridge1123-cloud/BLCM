<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'BLCM' ?> - Bridge Law Case Management</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%230F1B2D' width='100' height='100' rx='20'/><text x='50' y='65' font-size='48' font-weight='bold' text-anchor='middle' fill='%23C9A84C' font-family='sans-serif'>B</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@400;500;600;700;800;900&family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/blcm/frontend/assets/css/app.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/blcm/frontend/assets/css/app.css') ?>">
    <link rel="stylesheet" href="/blcm/frontend/assets/css/sp-design-system.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/blcm/frontend/assets/css/sp-design-system.css') ?>">
    <link rel="stylesheet" href="/blcm/frontend/assets/css/tailwind.css?v=<?= file_exists($_SERVER['DOCUMENT_ROOT'] . '/blcm/frontend/assets/css/tailwind.css') ? filemtime($_SERVER['DOCUMENT_ROOT'] . '/blcm/frontend/assets/css/tailwind.css') : '0' ?>">
    <!-- Shared JS -->
    <script src="/blcm/frontend/assets/js/app.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/blcm/frontend/assets/js/app.js') ?>"></script>
    <script src="/blcm/frontend/assets/js/utils.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/blcm/frontend/assets/js/utils.js') ?>"></script>
    <script src="/blcm/frontend/assets/js/shared.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/blcm/frontend/assets/js/shared.js') ?>"></script>
    <?php if (!empty($pageHeadScripts)): ?>
        <?php foreach ($pageHeadScripts as $hs): ?>
            <script src="<?= $hs ?>"></script>
        <?php endforeach; ?>
        <script>
            if (typeof pdfjsLib !== 'undefined') {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            }
        </script>
    <?php endif; ?>
</head>
<body class="bg-v2-bg font-franklin min-h-screen" x-data x-init="$store.auth.load()">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <!-- Main content wrapper -->
    <div class="main-content" :class="{ 'expanded': $store.sidebar.collapsed }">
        <!-- Top Header -->
        <?php include __DIR__ . '/../components/header.php'; ?>

        <!-- Page Content -->
        <main class="p-6">
            <?php if (isset($pageContent)) include $pageContent; ?>
        </main>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="fixed top-4 right-4 z-[100] flex flex-col gap-2"></div>

    <!-- Page JS (lazy loaded per page) -->
    <?php if (!empty($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <?php
                $scriptSrc = strpos($script, '/') === 0 ? $script : '/blcm/frontend/' . $script;
                $scriptFile = FRONTEND_PATH . '/' . ltrim(str_replace('/blcm/frontend/', '', $scriptSrc), '/');
                $ver = file_exists($scriptFile) ? filemtime($scriptFile) : time();
            ?>
            <script src="<?= $scriptSrc ?>?v=<?= $ver ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <script src="/blcm/frontend/assets/js/alpine-stores.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/blcm/frontend/assets/js/alpine-stores.js') ?>"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
