<div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-md-4">
        <div class="card p-4 shadow-sm">
            <div class="text-center mb-4">
                <div class="display-4" style="color:var(--primary);">🔐</div>
                <h4 class="fw-bold mt-2">ورود مدیر</h4>
            </div>
            <?php if (!empty($error)): ?>
                <div class="alert" style="background:#FEE2E2; color:var(--danger); border-right:4px solid var(--danger);"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= BASE_URL ?>admin/login">
                <div class="mb-3">
                    <label class="form-label">نام کاربری</label>
                    <input type="text" class="form-control" name="username" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">رمز عبور</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">ورود</button>
            </form>
        </div>
    </div>
</div>