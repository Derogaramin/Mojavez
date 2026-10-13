<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- کارت وضعیت -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <h4 class="fw-bold">وضعیت: <span class="badge fs-5 px-4 py-2" style="background: var(--primary);"><?= htmlspecialchars($applicant['current_status']) ?></span></h4>
                <p><strong>کد رهگیری:</strong> <?= htmlspecialchars($applicant['tracking_code']) ?></p>
                <p><strong>نام:</strong> <?= htmlspecialchars($applicant['first_name'] . ' ' . $applicant['last_name']) ?></p>
                <?php if ($applicant['priority']): ?><span class="priority-badge">⭐ اولویت</span><?php endif; ?>
            </div>
        </div>
        <!-- تایم‌لاین -->
        <div class="card mb-4">
            <div class="card-header">تاریخچه رویدادها</div>
            <div class="card-body">
                <ul class="timeline">
                    <?php foreach ($statusLogs as $log): ?>
                        <?php $class = (strpos($log['status_name'], 'لغو') !== false) ? 'cancel' : ((strpos($log['status_name'], 'صدور') !== false || strpos($log['status_name'], 'تأیید') !== false) ? 'success' : ''); ?>
                        <li class="timeline-item <?= $class ?>">
                            <div class="time"><?= persianDate($log['created_at']) ?></div>
                            <div class="description"><strong><?= htmlspecialchars($log['status_name']) ?></strong></div>
                            <?php if (!empty($log['description'])): ?>
                                <div class="text-muted small"><?= htmlspecialchars($log['description']) ?></div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- دکمه‌های عملیات -->
        <div class="d-flex justify-content-center gap-3 mb-4">
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#postalModal">
                ✉️ درخواست تغییر کد پستی
            </button>
            <a href="<?= BASE_URL ?>tracking/print/<?= htmlspecialchars($applicant['tracking_code']) ?>" target="_blank" class="btn btn-outline-primary">
                🖨️ مشاهده نسخه چاپی
            </a>
        </div>

        <!-- مودال تغییر کد پستی -->
        <div class="modal fade" id="postalModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header" style="background: var(--primary); color: #fff;">
                        <h5 class="modal-title">درخواست تغییر کد پستی</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="post" action="<?= BASE_URL ?>tracking/change-postal-code">
                        <div class="modal-body">
                            <input type="hidden" name="tracking_code" value="<?= htmlspecialchars($applicant['tracking_code']) ?>">
                            <label class="form-label">کد پستی جدید (۱۰ رقمی)</label>
                            <input type="text" class="form-control" name="new_postal_code" pattern="\d{10}" required>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-warning">ثبت درخواست</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- تاریخچه تغییرات قبلی -->
        <?php if (!empty($postalChanges)): ?>
        <div class="card">
            <div class="card-header">تاریخچه درخواست‌های تغییر کد پستی</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($postalChanges as $pc): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($pc['old_postal_code']) ?> ← <?= htmlspecialchars($pc['new_postal_code']) ?>
                        <small class="text-muted">(<?= htmlspecialchars($pc['requested_at']) ?>)</small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>