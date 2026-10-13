<div class="card p-4">
    <h4 class="fw-bold mb-4">👥 کاربران سیستم</h4>
    <a href="<?= BASE_URL ?>admin/users/create" class="btn btn-primary mb-3">➕ کاربر جدید</a>
    <table class="table table-hover">
        <thead>
            <tr><th>نام کاربری</th><th>نام نمایشی</th><th>نقش</th><th>عملیات</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['display_name']) ?></td>
                <td><span class="badge bg-info"><?= htmlspecialchars($user['role']) ?></span></td>
                <td>
                    <a href="<?= BASE_URL ?>admin/users/edit/<?= $user['id'] ?>" class="btn btn-sm btn-warning">ویرایش</a>
                    <a href="<?= BASE_URL ?>admin/users/delete/<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('آیا مطمئن هستید؟')">حذف</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>