<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card p-4 text-center">
            <div class="display-4 mb-3">🔍</div>
            <h4 class="fw-bold mb-4">پیگیری درخواست</h4>
            <?php if (!empty($error)): ?>
                <div class="alert" style="background:#FEE2E2; color:var(--danger); border-right:4px solid var(--danger);"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= BASE_URL ?>tracking/search">
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg text-center" name="tracking_code" placeholder="کد رهگیری خود را وارد کنید" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">جستجو</button>
            </form>
        </div>
    </div>
</div>