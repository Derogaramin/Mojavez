<div class="card p-4">
    <h4 class="fw-bold mb-4">📊 آمار درخواست‌ها بر اساس <?= htmlspecialchars($label) ?></h4>
    <table class="table table-hover">
        <thead class="table-light">
            <tr><th><?= htmlspecialchars($label) ?></th><th>تعداد</th></tr>
        </thead>
        <tbody>
        <?php foreach ($stats as $row): ?>
            <tr>
                <?php if($row[$field] !== ''): ?>
                    <td><?= htmlspecialchars($row[$field] ?? '—') ?></td>
                    <td><?= $row['cnt'] ?></td>
                <?php else : ?>
                    <td>-</td>
                    <td>-</td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>