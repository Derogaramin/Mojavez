<!-- فیلترها -->
<form method="get" action="<?= BASE_URL ?>admin/dashboard" class="card p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <input type="text" class="form-control" name="tracking_code" placeholder="کد رهگیری" value="<?= htmlspecialchars($filters['tracking_code']) ?>">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="name" placeholder="نام" value="<?= htmlspecialchars($filters['name']) ?>">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="national_code" placeholder="کد ملی" value="<?= htmlspecialchars($filters['national_code']) ?>">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="mobile" placeholder="موبایل" value="<?= htmlspecialchars($filters['mobile']) ?>">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="province" placeholder="استان" value="<?= htmlspecialchars($filters['province']) ?>">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="license_title" placeholder="عنوان مجوز" value="<?= htmlspecialchars($filters['license_title'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="village" placeholder="روستا" value="<?= htmlspecialchars($filters['village'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <select class="form-select" name="status">
                <option value="">وضعیت</option>
                <?php foreach (['پیش‌نویس','تکمیل اطلاعات','احراز نشانی','ثبت درخواست صدور','بارگذاری مدارک به سازمان','تأیید اولیه','صدور مجوز','لغو'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">فیلتر</button>
        </div>
    </div>
</form>

<!-- دکمه‌های گزارش -->
<div class="mb-3">
    <a href="<?= BASE_URL ?>admin/report/csv?<?= http_build_query($filters) ?>" class="btn btn-success">خروجی CSV</a>
    <a href="<?= BASE_URL ?>admin/report/pdf?<?= http_build_query($filters) ?>" class="btn btn-info">خروجی PDF</a>
</div>

<!-- جدول -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>اولویت</th>
                        <th>کد رهگیری</th>
                        <th>نام متقاضی</th>
                        <th>استان</th>
                        <th>وضعیت</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($applicants as $app): ?>
                    <tr class="<?= $app['priority'] ? 'table-warning' : '' ?>">
                        <td><?= $app['priority'] ? '<span class="priority-badge">⭐</span>' : '' ?></td>
                        <td><span style="font-family:monospace;"><?= htmlspecialchars($app['tracking_code']) ?></span></td>
                        <td><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?></td>
                        <td><?= htmlspecialchars($app['province']) ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($app['current_status']) ?></span></td>
                        <td><?= htmlspecialchars($app['registration_completed_at'] ?? '—') ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>admin/view/<?= $app['id'] ?>" class="btn btn-sm btn-outline-primary">👁️ مشاهده</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($applicants)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">هیچ درخواستی یافت نشد.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>