<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">جزئیات درخواست #<?= $applicant['id'] ?></h4>
    <a href="<?= BASE_URL ?>admin/edit/<?= $applicant['id'] ?>" class="btn btn-warning">✏️ ویرایش</a>
    <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">بازگشت</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- اطلاعات هویتی -->
        <div class="card mb-4">
            <div class="card-header"><strong>اطلاعات متقاضی</strong></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th>کد رهگیری</th><td><?= htmlspecialchars($applicant['tracking_code']) ?></td></tr>
                    <tr><th>نام</th><td><?= htmlspecialchars($applicant['first_name'] . ' ' . $applicant['last_name']) ?></td></tr>
                    <tr><th>کد ملی</th><td><?= htmlspecialchars($applicant['national_code']) ?></td></tr>
                    <tr><th>موبایل</th><td><?= htmlspecialchars($applicant['mobile']) ?></td></tr>
                    <tr><th>کد پستی</th><td><?= htmlspecialchars($applicant['postal_code']) ?></td></tr>
                    <tr><th>وضعیت</th><td><span class="badge bg-info"><?= htmlspecialchars($applicant['current_status']) ?></span></td></tr>
                    <tr><th>اولویت</th><td><?= $applicant['priority'] ? '<span class="priority-badge">اولویت بالا</span>' : 'عادی' ?></td></tr>
                    <tr><th>تاریخ تولد</th><td><?= persianDate($applicant['birth_date']) ?></td></tr>
                    <tr><th>نام پدر</th><td><?= htmlspecialchars($applicant['father_name'] ?? '') ?></td></tr>
                    <tr><th>وضعیت تأهل</th><td><?= htmlspecialchars($applicant['marital_status'] ?? '') ?></td></tr>
                    <?php if ($applicant['marital_status'] === 'متأهل'): ?>
                        <tr><th>تعداد فرزندان</th><td><?= $applicant['children_count'] ?></td></tr>
                    <?php endif; ?>
                    <tr><th>وضعیت جسمانی</th><td><?= htmlspecialchars($applicant['physical_condition'] ?? '') ?><?= $applicant['physical_condition'] === 'معلولیت' ? ' - ' . htmlspecialchars($applicant['disability_details']) : '' ?></td></tr>
                    <tr><th>دهک درآمدی</th><td><?= $applicant['income_decile'] ? htmlspecialchars($applicant['income_decile']) : '—' ?></td></tr>
                    <tr><th>پرونده قضایی</th><td><?= htmlspecialchars($applicant['has_court_case'] ?? '') ?></td></tr>
                    <tr><th>وام فعال</th><td><?= htmlspecialchars($applicant['has_active_loan'] ?? '') ?></td></tr>
                    <tr><th>اقساط معوقه</th><td><?= htmlspecialchars($applicant['has_overdue_installments'] ?? '') ?></td></tr>
                    <tr><th>تاریخ ثبت‌نام</th><td><?= persianDate($applicant['registration_completed_at']) ?></td></tr>
                </table>
            </div>
        </div>

        <!-- شغلی -->
        <div class="card mb-4">
            <div class="card-header"><strong>اطلاعات شغلی</strong></div>
            <div class="card-body">
                <?php if ($jobDetail): ?>
                <table class="table table-borderless mb-0">
                    <tr><th>عنوان مجوز</th><td><?= htmlspecialchars($jobDetail['license_title']) ?></td></tr>
                    <tr><th>سابقه</th><td><?= $jobDetail['experience_months'] ?> ماه</td></tr>
                    <tr><th>نحوه آموزش</th><td><?= htmlspecialchars($jobDetail['training_method']) ?></td></tr>
                </table>
                <?php else: ?>
                    <p class="text-muted">ثبت نشده</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- ملک -->
        <div class="card mb-4">
            <div class="card-header"><strong>اطلاعات ملک</strong></div>
            <div class="card-body">
                <?php if ($propertyDetail): ?>
                <table class="table table-borderless mb-0">
                    <tr><th>مالکیت</th><td><?= htmlspecialchars($propertyDetail['ownership_status']) ?></td></tr>
                    <tr><th>آدرس</th><td><?= htmlspecialchars(implode(' - ', array_filter([$propertyDetail['province'], $propertyDetail['county'], $propertyDetail['city'], $propertyDetail['district'], $propertyDetail['village']]))) ?></td></tr>
                    <tr><th>آدرس تکمیلی</th><td><?= htmlspecialchars(implode('، ', array_filter([$propertyDetail['address_street'] ?? null, $propertyDetail['address_alley'] ?? null, 'پلاک ' . ($propertyDetail['address_plaque'] ?? '')]))) ?></td></tr>
                    <tr><th>قبض برق</th><td><?= htmlspecialchars($propertyDetail['electricity_bill_id']) ?></td></tr>
                </table>
                <?php else: ?>
                    <p class="text-muted">ثبت نشده</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- مدارک -->
        <div class="card mb-4">
            <div class="card-header"><strong>مدارک</strong></div>
            <div class="card-body">
                <?php if ($documents): ?>
                    <div class="row">
                        <?php foreach ($documents as $doc): ?>
                            <?php if ($doc['doc_type'] === 'product_photos') continue; ?>
                            <div class="col-md-3 mb-2">
                                <div class="card p-2">
                                    <?php if (in_array(pathinfo($doc['file_name'], PATHINFO_EXTENSION), ['jpg','jpeg','png'])): ?>
                                        <img src="<?= BASE_URL ?>document/thumb/<?= $doc['id'] ?>" class="img-fluid" style="max-height:100px;" alt="">
                                    <?php else: ?>
                                        <div class="text-center display-6">📄</div>
                                    <?php endif; ?>
                                    <small><?= htmlspecialchars($doc['original_name']) ?></small>
                                    <button class="btn btn-sm btn-outline-primary view-doc-btn" data-id="<?= $doc['id'] ?>" data-applicant="<?= $applicant['id'] ?>">مشاهده</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">مدرکی بارگذاری نشده.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php
        $productPhotos = array_filter($documents, fn($d) => $d['doc_type'] === 'product_photos');
        if ($productPhotos): ?>
        <div class="card mb-4">
            <div class="card-header"><strong>تصاویر متقاضی با محصولات</strong></div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($productPhotos as $doc): ?>
                        <div class="col-md-2 text-center mb-3">
                            <?php if (in_array(pathinfo($doc['file_name'], PATHINFO_EXTENSION), ['jpg','jpeg','png'])): ?>
                                <img src="<?= BASE_URL ?>document/thumb/<?= $doc['id'] ?>" class="img-thumbnail" style="height:80px; width:100%; object-fit:cover;" alt="">
                            <?php else: ?>
                                <div class="display-6">📄</div>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-primary mt-1 view-doc-btn" data-id="<?= $doc['id'] ?>" data-applicant="<?= $applicant['id'] ?>">بزرگ‌نمایی</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ستون کناری: تغییر وضعیت + تاریخچه -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><strong>تغییر وضعیت</strong></div>
            <div class="card-body">
                <form method="post" action="<?= BASE_URL ?>admin/change-status">
                    <input type="hidden" name="applicant_id" value="<?= $applicant['id'] ?>">
                    <select class="form-select mb-3" name="new_status">
                        <option value="">انتخاب وضعیت جدید...</option>
                        <?php foreach ($statuses as $st): ?>
                            <option value="<?= $st ?>" <?= $st === $applicant['current_status'] ? 'disabled' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary w-100">اعمال تغییر</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>تاریخچه</strong></div>
            <div class="card-body" style="max-height:400px; overflow-y:auto;">
                <ul class="timeline">
                    <?php foreach ($statusLogs as $log): ?>
                        <?php
                        $class = '';
                        if (strpos($log['status_name'], 'لغو') !== false) $class = 'cancel';
                        elseif (strpos($log['status_name'], 'صدور') !== false || strpos($log['status_name'], 'تأیید') !== false) $class = 'success';
                        ?>
                        <li class="timeline-item <?= $class ?>">
                            <div class="time small"><?= htmlspecialchars($log['created_at']) ?></div>
                            <div><strong><?= htmlspecialchars($log['status_name']) ?></strong></div>
                            <?php if ($log['description']): ?>
                                <div class="text-muted small"><?= htmlspecialchars($log['description']) ?></div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>


<!-- Modal برای نمایش فایل -->
<div class="modal fade" id="docModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">پیش‌نمایش مدرک</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center" id="docContent">
        <!-- محتوا با JS بارگذاری می‌شود -->
      </div>
    </div>
  </div>
</div>

<script>
    document.querySelectorAll('.view-doc-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const docId = this.dataset.id;
            const applicantId = this.dataset.applicant;
            const formData = new FormData();
            formData.append('document_id', docId);
            formData.append('applicant_id', applicantId);

            const response = await fetch('<?= BASE_URL ?>document/view', { method: 'POST', body: formData });
            if (!response.ok) return alert('خطا در بارگذاری فایل');

            const blob = await response.blob();
            const url = URL.createObjectURL(blob);
            const contentType = response.headers.get('Content-Type');
            let html = '';
            if (contentType.startsWith('image/')) {
                html = `<img src="${url}" class="img-fluid" style="max-height:80vh;">`;
            } else if (contentType === 'application/pdf') {
                html = `<embed src="${url}" type="application/pdf" width="100%" height="600px">`;
            } else {
                html = `<a href="${url}" target="_blank">دانلود فایل</a>`;
            }
            document.getElementById('docContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('docModal')).show();
        });
    });
</script>