<div class="mb-4">
    <h2 class="fw-bold">اطلاعات شغلی</h2>
</div>

<div class="progress">
    <div class="progress-bar" style="width: 50%;"></div>
</div>
<div class="step-labels">
    <div class="step-label completed">اطلاعات هویتی</div>
    <div class="step-label active">اطلاعات شغلی</div>
    <div class="step-label">اطلاعات ملک</div>
    <div class="step-label">بارگذاری مدارک</div>
</div>

<div class="card p-4">
    <form method="post" action="<?= BASE_URL ?>register/step2">
        <!-- بخش انتخاب عنوان مجوز -->
        <div class="mb-3">
            <label class="form-label">عنوان مجوز درخواستی <span class="text-danger">*</span></label>
            <select class="form-select <?= isset($errors['license_title']) ? 'is-invalid' : '' ?>" name="license_title" id="license_title" onchange="toggleCustomLicense()">
                <option value="">انتخاب کنید...</option>
                <optgroup label="صنایع چوب">
                    <option value="منبت">منبت</option>
                    <option value="معرق">معرق</option>
                    <option value="خراطی">خراطی</option>
                    <option value="پیکر ه‌سازی">پیکره‌سازی</option>
                </optgroup>
                <optgroup label="صنایع بافته‌شده">
                    <option value="فرش">فرش</option>
                    <option value="گلیم">گلیم</option>
                    <option value="جاجیم">جاجیم</option>
                    <option value="زیلو">زیلو</option>
                </optgroup>
                <optgroup label="سفال و سرامیک">
                    <option value="سفالگری">سفالگری</option>
                    <option value="کاشی‌کاری">کاشی‌کاری</option>
                    <option value="سرامیک‌سازی">سرامیک‌سازی</option>
                </optgroup>
                <optgroup label="صنایع فلزی">
                    <option value="قلم‌زنی">قلم‌زنی</option>
                    <option value="مشبک‌کاری">مشبک‌کاری</option>
                    <option value="دواتگری">دواتگری</option>
                </optgroup>
                <option value="سایر">سایر</option>
            </select>
            <div id="custom-license-wrapper" style="display:<?= (isset($stepData['license_title']) && $stepData['license_title'] === 'سایر') ? 'block' : 'none' ?>; margin-top:0.5rem;">
                <label>عنوان مجوز مورد نظر خود را وارد کنید:</label>
                <input type="text" class="form-control <?= isset($errors['custom_license_title']) ? 'is-invalid' : '' ?>" name="custom_license_title" value="<?= htmlspecialchars($stepData['custom_license_title'] ?? '') ?>">
            </div>
            <?php if (!empty($errors['license_title'])): ?>
                <div class="invalid-feedback d-block"><?= implode('<br>', $errors['license_title']) ?></div>
            <?php endif; ?>
        </div>

        <script>
        function toggleCustomLicense() {
            const select = document.getElementById('license_title');
            const wrapper = document.getElementById('custom-license-wrapper');
            if (select.value === 'سایر') {
                wrapper.style.display = 'block';
            } else {
                wrapper.style.display = 'none';
            }
        }
        </script>

        <div class="mb-3">
            <label class="form-label">سابقه فعالیت (ماه) <span class="text-danger">*</span></label>
            <input type="number" class="form-control <?= isset($errors['experience_months']) ? 'is-invalid' : '' ?>"
                   name="experience_months" value="<?= htmlspecialchars($stepData['experience_months'] ?? '') ?>" min="0" required>
            <?php if (!empty($errors['experience_months'])): ?>
                <div class="invalid-feedback"><?= implode('<br>', $errors['experience_months']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label class="form-label">نحوه آموزش و آشنایی با حرفه <span class="text-danger">*</span></label>
            <div>
                <?php foreach (['در خانه', 'استاد شاگردی', 'کارگاهی'] as $method): ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="training_method" value="<?= $method ?>"
                               id="train_<?= $method ?>" <?= (isset($stepData['training_method']) && $stepData['training_method'] === $method) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="train_<?= $method ?>"><?= $method ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($errors['training_method'])): ?>
                <div class="invalid-feedback"><?= implode('<br>', $errors['training_method']) ?></div>
            <?php endif; ?>
        </div>

        <div class="d-flex justify-content-between">
            <a href="<?= BASE_URL ?>register" class="btn btn-outline-secondary">مرحله قبل</a>
            <button type="submit" class="btn btn-primary">مرحله بعد</button>
        </div>
    </form>
</div>