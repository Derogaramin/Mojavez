<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card p-5 text-center">
            <div class="display-1 text-success mb-3">✓</div>
            <h3 class="fw-bold mb-4">درخواست شما با موفقیت ثبت شد</h3>
            <div class="alert" style="background:#F0FDF4; color:var(--success); font-size:1.2rem; border-radius:12px;">
                <strong>کد رهگیری شما:</strong><br>
                <span class="fw-bold" style="font-size:1.8rem; letter-spacing:1px; font-family:monospace;"><?= htmlspecialchars($trackingCode) ?></span>
            </div>
            <p class="text-muted mt-3">لطفاً این کد را برای پیگیری‌های بعدی نگهداری کنید.</p>
            <a href="<?= BASE_URL ?>tracking" class="btn btn-primary mt-3">رفتن به صفحه پیگیری</a>
        </div>
    </div>
</div>