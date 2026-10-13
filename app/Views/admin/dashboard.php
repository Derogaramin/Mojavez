<!-- کارت‌های آمار کلی -->
<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-4 text-center bg-light">
            <h2><?= $total ?></h2>
            <p>کل درخواست‌ها</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 text-center bg-warning bg-opacity-25">
            <h2><?= $pending ?></h2>
            <p>در انتظار</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 text-center bg-danger bg-opacity-25">
            <h2><?= $expired ?></h2>
            <p>لغو / منقضی</p>
        </div>
    </div>
</div>

<!-- آمار هر وضعیت -->
<div class="card mt-4">
    <div class="card-header"><strong>آمار وضعیت‌ها</strong></div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($statusCounts as $status => $count): ?>
            <div class="col-md-3">
                <div class="border rounded p-2 text-center">
                    <small><?= htmlspecialchars($status) ?></small>
                    <h5><?= $count ?></h5>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>