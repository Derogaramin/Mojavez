<?php 
    $admin = false;
    if (Session::get('admin_role') === 'admin'){
        $admin = true;
    }

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'پنل مدیریت') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/font.css">
    <?php if (USE_CDN): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <?php else: ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>assets/bootstrap.rtl.min.css">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <!-- سایدبار -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">پنل مدیریت</div>
            <nav class="nav flex-column">
                <a href="<?= BASE_URL ?>admin/dashboard" class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">📊 داشبورد</a>
                <?php if ($admin): ?>
                    <a href="<?= BASE_URL ?>admin/register" class="nav-link <?= ($currentPage ?? '') === 'register' ? 'active' : '' ?>">➕ ثبت نام متقاضی جدید</a>
                <?php endif; ?>

                <div class="nav-link fw-bold text-white-50 mt-2" data-bs-toggle="collapse" data-bs-target="#myTask" style="cursor:pointer;">📌 وظایف من ▾</div>
                <div class="collapse" id="myTask">
                    <a href="<?= BASE_URL ?>admin/my-tasks" class="nav-link <?= ($currentPage ?? '') === 'tasks' ? 'active' : '' ?>">📌 وظایف من</a>
                    <?php if ($admin): ?>
                        <a href="<?= BASE_URL ?>admin/applications" class="nav-link <?= ($currentPage ?? '') === 'applications' ? 'active' : '' ?>">📋 همه درخواست‌ها</a>
                    <?php endif; ?>
                </div>

                <?php if ($admin): ?>
                    <div class="nav-link fw-bold text-white-50 mt-2" data-bs-toggle="collapse" data-bs-target="#reportsMenu" style="cursor:pointer;">📈 گزارش‌ها ▾</div>
                    <div class="collapse" id="reportsMenu">
                        <a href="<?= BASE_URL ?>admin/reports/by-province" class="nav-link ms-3">• بر اساس استان</a>
                        <a href="<?= BASE_URL ?>admin/reports/by-city" class="nav-link ms-3">• بر اساس شهر</a>
                        <a href="<?= BASE_URL ?>admin/reports/by-village" class="nav-link ms-3">• بر اساس روستا</a>
                        <a href="<?= BASE_URL ?>admin/reports/by-license" class="nav-link ms-3">• بر اساس رشته</a>
                        <a href="<?= BASE_URL ?>admin/reports/by-date" class="nav-link ms-3">• بر اساس زمان ثبت‌نام</a>
                        <a href="<?= BASE_URL ?>admin/applications" class="nav-link ms-3">• فیلتر هوشمند (همه)</a>
                    </div>

                    <div class="nav-link fw-bold text-white-50 mt-2" data-bs-toggle="collapse" data-bs-target="#outputMenu" style="cursor:pointer;">📥 دریافت خروجی ▾</div>
                    <div class="collapse" id="outputMenu">
                        <a href="<?= BASE_URL ?>admin/output/general" class="nav-link ms-3">• گزارش کلی (CSV/PDF)</a>
                        <a href="#" onclick="promptTrackingCode()" class="nav-link ms-3">• گزارش متقاضی خاص</a>
                        <a href="<?= BASE_URL ?>admin/output/grouped" class="nav-link ms-3">• گزارش گروه‌بندی‌شده</a>
                    </div>
                
                    <div class="nav-link fw-bold text-white-50 mt-2" data-bs-toggle="collapse" data-bs-target="#userMenu" style="cursor:pointer;">👥 مدیریت کاربران ▾</div>
                    <div class="collapse" id="userMenu">
                        <a href="<?= BASE_URL ?>admin/users" class="nav-link ms-3">• لیست کاربران</a>
                        <a href="<?= BASE_URL ?>admin/users/create" class="nav-link ms-3">• کاربر جدید</a>
                    </div>
                <?php endif; ?>

                <hr class="border-white-20 my-2 mx-3">
                <a href="<?= BASE_URL ?>admin/logout" class="nav-link text-danger">🚪 خروج</a>
            </nav>
        </aside>
        <script>
        function promptTrackingCode() {
            let code = prompt('کد رهگیری را وارد کنید:');
            if (code) {
                window.location.href = '<?= BASE_URL ?>admin/output/single/' + code;
            }
        }
        </script>

        <!-- محتوای اصلی -->
        <main class="admin-main flex-grow-1">
            
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><?= htmlspecialchars($title ?? 'داشبورد') ?></h3>
            <div>
                <span class="me-3"><?= htmlspecialchars($adminName ?? 'مدیر') ?></span>
                <a href="<?= BASE_URL ?>admin/logout" class="btn btn-outline-danger btn-sm">خروج</a>
            </div>
        </div>

            <?= $content ?>
        </main>
    </div>

    <?php if (USE_CDN): ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php else: ?>
        <script src="<?= BASE_URL ?>assets/bootstrap.bundle.min.js"></script>
    <?php endif; ?>
</body>
</html>