<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'سامانه مجوز صنایع دستی') ?></title>
    <!-- فونت وزیرمتن -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/font.css">
    <!-- Bootstrap RTL -->
    <?php if (USE_CDN): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <?php else: ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>assets/bootstrap.rtl.min.css">
    <?php endif; ?>
    <!-- استایل سفارشی -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <?= $extraHead ?? '' ?>
</head>
<body>
    <!-- نوار بالای عمومی -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 no-print">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>">
                <span style="color:var(--primary);">🏛️</span> سامانه مجوزهای صنایع دستی
            </a>
            <div class="d-flex">
                <a href="<?= BASE_URL ?>tracking" class="btn btn-outline-primary btn-sm me-2">پیگیری</a>
                <a href="<?= BASE_URL ?>admin/login" class="btn btn-outline-secondary btn-sm">پنل مدیریت</a>
            </div>
        </div>
    </nav>

    <!-- محتوای اصلی -->
    <main class="container pb-5">
        <?= $content ?? '' ?>
    </main>

    <!-- فوتر -->
    <footer class="text-center text-muted py-3 mt-auto no-print" style="font-size:0.8125rem;">
        <small>تمامی حقوق متعلق به کافی‌نت محترم می‌باشد. © <?= date('Y') ?></small>
    </footer>

    <?php if (USE_CDN): ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php else: ?>
        <script src="<?= BASE_URL ?>assets/bootstrap.bundle.min.js"></script>
    <?php endif; ?>
</body>
</html>