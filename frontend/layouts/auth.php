<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'BLCM' ?></title>
    <link rel="stylesheet" href="/blcm/frontend/assets/css/tailwind.css?v=<?= file_exists($_SERVER['DOCUMENT_ROOT'] . '/blcm/frontend/assets/css/tailwind.css') ? filemtime($_SERVER['DOCUMENT_ROOT'] . '/blcm/frontend/assets/css/tailwind.css') : '0' ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <?php if (isset($contentFile) && file_exists($contentFile)) include $contentFile; ?>
</body>
</html>
