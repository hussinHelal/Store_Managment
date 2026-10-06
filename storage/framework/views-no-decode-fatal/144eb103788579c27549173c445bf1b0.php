<?php $__env->startSection('title', '- الرئيسية'); ?>
<?php $__env->startSection('main'); ?>
<div class="container-fluid py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold text-body mb-1">لوحة التحكم</h1>
            <p class="text-muted small mb-0">نظرة عامة على أداء المتجر والروابط الأكثر استخدامًا.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button id="backupBtn" type="button" class="btn btn-success btn-round" title="تحميل نسخة احتياطية من البيانات" data-format="zip">
                <i class="fas fa-download me-2"></i> تحميل نسخة احتياطية
            </button>
            <a href="<?php echo e(route('invoices.index')); ?>" class="btn btn-outline-primary btn-round">
                <i class="fas fa-file-invoice me-2"></i> عرض الفواتير
            </a>
        </div>
    </div>



    <div class="row g-3 mb-4">
        <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-12 col-sm-6 col-xl-3 ">
                <div class="card dashboard-card h-100 border-0 shadow-sm position-relative overflow-hidden" style="border-top: 4px solid <?php echo e($card['color']); ?>;">
                    <div class="card-body py-4 px-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background-color: <?php echo e($card['color']); ?>20;">
                                <i class="fa-solid <?php echo e($card['icon']); ?> fs-5" style="color: <?php echo e($card['color']); ?>"></i>
                            </div>
                            <span class="badge rounded-pill fw-medium py-2 px-2" style="background-color: <?php echo e($card['color']); ?>15; color: <?php echo e($card['color']); ?>; font-size: 0.75rem;">
                                <?php if($card['trend'] === 'up'): ?>
                                    <i class="fa-solid fa-arrow-trend-up me-1"></i>
                                <?php elseif($card['trend'] === 'down'): ?>
                                    <i class="fa-solid fa-arrow-trend-down me-1"></i>
                                <?php else: ?>
                                    <i class="fa-solid fa-check me-1"></i>
                                <?php endif; ?>
                                <?php echo e($card['badge']); ?>

                            </span>
                        </div>
                        <p class="text-muted small mb-1"><?php echo e($card['label']); ?></p>
                        <h3 class="fw-bold mb-0 text-body"><?php echo e($card['value']); ?></h3>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-8 ">
            <div class="card chart-card h-100 border-0 shadow-sm">
                <div class="card-header bg-body border-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1">مخطط المبيعات الأسبوعية</h5>
                        <p class="text-muted small mb-0">إجمالي المبيعات لكل يوم خلال الأسبوع الماضي.</p>
                    </div>
                    <span class="badge bg-primary text-white"><?php echo e(number_format(array_sum($chartData), 2)); ?> جم إجمالي</span>
                </div>
                <div class="card-body p-4">
                    <div class="chart-container" style="min-height: 320px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12 col-xl-4 shadow-sm">
            <div class="card h-100 border-0 bg-body shadow-sm">
                <div class="card-header bg-body border-0 pb-3">
                    <h5 class="mb-0">الوصول السريع</h5>
                </div>
                <div class="card-body p-4">
                    <div class="list-group list-group-flush">
                        <a href="<?php echo e(route('categories.index')); ?>" class="list-group-item list-group-item-action bg-body-secondary rounded-3 mb-2">التصنيفات</a>
                        <a href="<?php echo e(route('products.index')); ?>" class="list-group-item list-group-item-action bg-body-secondary rounded-3 mb-2">المنتجات</a>
                        <a href="<?php echo e(route('maintenance.index')); ?>" class="list-group-item list-group-item-action bg-body-secondary rounded-3 mb-2">الصيانة</a>
                        <a href="<?php echo e(route('invoices.index')); ?>" class="list-group-item list-group-item-action bg-body-secondary rounded-3 mb-2">الفواتير</a>
                        <a href="<?php echo e(route('customers.index')); ?>" class="list-group-item list-group-item-action bg-body-secondary rounded-3 mb-2">العملاء</a>
                        <a href="<?php echo e(route('installments.index')); ?>" class="list-group-item list-group-item-action bg-body-secondary rounded-3">الديون</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;

        const theme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        const isDark = theme === 'dark';
        const textColor = isDark ? '#e9ecef' : '#6c757d';
        const gridColor = isDark ? 'rgba(233, 236, 239, 0.16)' : 'rgba(108, 117, 125, 0.15)';
        const lineColor = isDark ? '#4dabf7' : '#0d6efd';
        const fillColor = isDark ? 'rgba(77, 171, 247, 0.18)' : 'rgba(13, 110, 253, 0.18)';

        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartLabels, 15, 512) ?>,
                datasets: [{
                    label: 'المبيعات',
                    data: <?php echo json_encode($chartData, 15, 512) ?>,
                    borderColor: lineColor,
                    backgroundColor: fillColor,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointBackgroundColor: lineColor,
                    pointBorderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? 'rgba(30, 41, 59, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                        titleColor: textColor,
                        bodyColor: textColor,
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + context.formattedValue + ' ج.م';
                            }
                        }
                    }
                }
            }
        });
    });
</script>

<script>
    (function () {
        const btn = document.getElementById('backupBtn');
        if (!btn) return;
        btn.addEventListener('click', async function () {
            const format = btn.getAttribute('data-format') || 'zip';
            const url = `<?php echo e(route('backup.export')); ?>?format=${format}`;
            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) {
                    const ct = res.headers.get('content-type') || '';
                    if (ct.includes('application/json')) {
                        const j = await res.json();
                        window.showBootstrapAlert(j.error || j.message || 'حدث خطأ', 'danger');
                    } else {
                        window.showBootstrapAlert('Server error: ' + res.status, 'danger');
                    }
                    return;
                }

                const contentType = res.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    const j = await res.json();
                    window.showBootstrapAlert(j.error || j.message || 'حدث خطأ', 'danger');
                    return;
                }

                const blob = await res.blob();
                const cd = res.headers.get('content-disposition') || '';
                let filename = 'backup';
                const m = cd.match(/filename\*=UTF-8''(.+)|filename=\"?([^\";]+)\"?/);
                if (m) {
                    filename = decodeURIComponent(m[1] || m[2]);
                }
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(a.href);
            } catch (err) {
                window.showBootstrapAlert('خطأ أثناء تنزيل النسخة الاحتياطية: ' + err.message, 'danger');
            }
        });
    })();
</script>

<style>
    .dashboard-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        border-radius: 1rem;
        background: var(--bs-card-bg);
        box-shadow: 0 14px 38px rgba(15, 23, 42, 0.06);
    }
    .dashboard-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 22px 48px rgba(15, 23, 42, 0.12) !important;
    }
    .chart-card {
        border-radius: 1rem;
        background: linear-gradient(180deg, var(--bs-body-bg) 0%, var(--bs-body) 100%);
        box-shadow: 0 24px 55px rgba(15, 23, 42, 0.08);
    }
    .chart-card .card-header {
        background: transparent;
    }
    .chart-container {
        min-height: 320px;
    }
    .btn-round {
        border-radius: 0.75rem;
    }
    [data-bs-theme="dark"] .chart-card {
        box-shadow: 0 24px 55px rgba(255, 255, 255, 0.06);
    }
    [data-bs-theme="dark"] .dashboard-card:hover {
        box-shadow: 0 22px 48px rgba(255, 255, 255, 0.08) !important;
    }
</style>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\index.blade.php ENDPATH**/ ?>