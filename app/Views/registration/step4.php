<div class="mb-4">
    <h2 class="fw-bold">بارگذاری مدارک</h2>
</div>

<div class="progress">
    <div class="progress-bar" style="width: 100%;"></div>
</div>
<div class="step-labels">
    <div class="step-label completed">اطلاعات هویتی</div>
    <div class="step-label completed">اطلاعات شغلی</div>
    <div class="step-label completed">اطلاعات ملک</div>
    <div class="step-label active">بارگذاری مدارک</div>
</div>

<div class="card p-4">
    <?php if (!empty($errors['files'])): ?>
        <div class="alert" style="background:#FEE2E2; color:var(--danger); border-right:4px solid var(--danger);">
            <?= implode('<br>', $errors['files']) ?>
        </div>
    <?php endif; ?>
    <form method="post" action="<?= BASE_URL ?>register/step4" enctype="multipart/form-data">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="upload-box" onclick="document.getElementById('national_card_front').click()">
                    <input type="file" id="national_card_front" name="national_card_front" accept=".jpg,.jpeg,.png,.pdf" style="display:none" onchange="showFileName(this)">
                    <div class="mb-2">🆔</div>
                    <strong>تصویر کارت ملی (رو) <span class="text-danger">*</span></strong>
                    <p class="text-muted small mb-0" id="file_national_front">فایلی انتخاب نشده</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="upload-box" onclick="document.getElementById('national_card_back').click()">
                    <input type="file" id="national_card_back" name="national_card_back" accept=".jpg,.jpeg,.png,.pdf" style="display:none" onchange="showFileName(this)">
                    <div class="mb-2">🆔</div>
                    <strong>تصویر کارت ملی (پشت)</strong>
                    <p class="text-muted small mb-0" id="file_national_back">فایلی انتخاب نشده</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="upload-box" onclick="document.getElementById('birth_cert').click()">
                    <input type="file" id="birth_cert" name="birth_cert" accept=".jpg,.jpeg,.png,.pdf" style="display:none" onchange="showFileName(this)">
                    <div class="mb-2">📄</div>
                    <strong>تصویر شناسنامه <span class="text-danger">*</span></strong>
                    <p class="text-muted small mb-0" id="file_birth_cert">فایلی انتخاب نشده</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="upload-box" onclick="document.getElementById('ownership_doc').click()">
                    <input type="file" id="ownership_doc" name="ownership_doc" accept=".jpg,.jpeg,.png,.pdf" style="display:none" onchange="showFileName(this)">
                    <div class="mb-2">📁</div>
                    <strong>مدرک مالکیت (<?= htmlspecialchars($ownershipStatus) ?>) <span class="text-danger">*</span></strong>
                    <p class="text-muted small mb-0" id="file_ownership_doc">فایلی انتخاب نشده</p>
                </div>
            </div>
            <!-- مدارک جدید -->
            <div class="col-md-6">
                <div class="upload-box" onclick="document.getElementById('vocational_cert').click()">
                    <input type="file" id="vocational_cert" name="vocational_cert" accept=".jpg,.jpeg,.png,.pdf" style="display:none" onchange="showFileName(this)">
                    <div class="mb-2">🎓</div>
                    <strong>گواهینامه فنی‌وحرفه‌ای</strong>
                    <p class="text-muted small mb-0" id="file_vocational_cert">فایلی انتخاب نشده</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="upload-box" onclick="document.getElementById('education_degree').click()">
                    <input type="file" id="education_degree" name="education_degree" accept=".jpg,.jpeg,.png,.pdf" style="display:none" onchange="showFileName(this)">
                    <div class="mb-2">📜</div>
                    <strong>آخرین مدرک تحصیلی</strong>
                    <p class="text-muted small mb-0" id="file_education_degree">فایلی انتخاب نشده</p>
                </div>
            </div>
        </div>

        <hr class="my-4">
        <h5 class="fw-bold mb-3">تصاویر متقاضی با محصولات حین تولید</h5>
        <p class="text-muted small">حداقل ۵ و حداکثر ۱۵ عکس بارگذاری کنید.</p>
        <div class="row g-3" id="product-photos">
            <?php for ($i = 1; $i <= 15; $i++): ?>
                <div class="col-md-4">
                    <div class="upload-box" onclick="document.getElementById('product_photo_<?= $i ?>').click()">
                        <input type="file" id="product_photo_<?= $i ?>" name="product_photos[]" accept=".jpg,.jpeg,.png" style="display:none" onchange="showFileName(this)">
                        <div class="mb-2">📸</div>
                        <strong>عکس <?= $i ?></strong>
                        <p class="text-muted small mb-0" id="file_product_photo_<?= $i ?>">فایلی انتخاب نشده</p>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <hr class="my-4">
        <h5 class="fw-bold mb-3">مدارک اختیاری (حداکثر ۳ فایل)</h5>
        <div class="row g-3">
            <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="col-md-4">
                <div class="upload-box" onclick="document.getElementById('optional_<?= $i ?>').click()">
                    <input type="file" id="optional_<?= $i ?>" name="optional_<?= $i ?>" accept=".jpg,.jpeg,.png,.pdf" style="display:none" onchange="showFileName(this)">
                    <div class="mb-2">📎</div>
                    <strong>سایر <?= $i ?></strong>
                    <p class="text-muted small mb-0" id="file_optional_<?= $i ?>">فایلی انتخاب نشده</p>
                </div>
            </div>
            <?php endfor; ?>
        </div>
        <p class="text-muted small mt-2">فقط jpg, jpeg, png, pdf - حداکثر ۲ مگابایت</p>

        <div class="mt-4 d-flex justify-content-between">
            <a href="<?= BASE_URL ?>register/step3" class="btn btn-outline-secondary">مرحله قبل</a>
            <button type="submit" class="btn btn-success">تأیید و نهایی‌سازی</button>
        </div>
    </form>
</div>

<script>
function showFileName(input) {
    let text = input.files.length > 0 ? input.files[0].name : 'فایلی انتخاب نشده';
    let targetId = 'file_' + input.id;
    let p = document.getElementById(targetId);
    if (p) p.textContent = text;
    let box = input.closest('.upload-box');
    if (box) {
        if (input.files.length > 0) {
            box.classList.add('has-file');
        } else {
            box.classList.remove('has-file');
        }
    }
}
</script>