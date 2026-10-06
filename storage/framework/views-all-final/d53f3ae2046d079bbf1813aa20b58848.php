<?php $__env->startSection('title', ' - طباعة ملصق المنتج'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>طباعة ملصق المنتج</h1>
    <button type="button" class="btn btn-primary" onclick="window.print()">طباعة الملصق</button>
</div>

<div class="card p-4 border rounded shadow-sm" style="max-width: 600px; margin: 0 auto;">
    <div class="text-center mb-4">
        <h2 class="mb-1"><?php echo e($product->name); ?></h2>
        <p class="text-muted mb-1"><?php echo e($product->category?->name ?? 'بدون صنف'); ?></p>
        <p class="mb-0">السعر: <strong><?php echo e(number_format($product->price, 2)); ?></strong> ج.م</p>
    </div>

    <div class="text-center mb-3">
        <svg id="barcode"></svg>
        <div class="mt-2"><?php echo e($product->barcode ?? $product->id); ?></div>
    </div>

    <div class="d-flex justify-content-between border-top pt-3">
        <span>المخزون: <strong><?php echo e($product->stock); ?></strong></span>
        <span>الباركود: <strong><?php echo e($product->barcode ?? '-'); ?></strong></span>
    </div>
</div>

<div class="mt-4 text-center">
    <a href="<?php echo e(route('products.index')); ?>" class="btn btn-outline-secondary">العودة إلى المنتجات</a>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const code = '<?php echo e(addslashes($product->barcode ?? $product->id)); ?>';
        const barcodeSvg = document.getElementById('barcode');
        if (barcodeSvg && code) {
            JsBarcode(barcodeSvg, code, {
                format: 'CODE128',
                displayValue: true,
                fontSize: 18,
                height: 70,
                width: 2,
                margin: 10,
            });
        }
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\products\print-label.blade.php ENDPATH**/ ?>