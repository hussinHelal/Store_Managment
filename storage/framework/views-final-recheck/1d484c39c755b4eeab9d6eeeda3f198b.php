

<?php $__env->startSection('content'); ?>
<div class="container py-4" style="max-width: 48rem">
    <div class="mb-4">
        <a href="<?php echo e(route('sales.index')); ?>" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> المبيعات</a>
        <h1 class="h3 mt-2">تفاصيل البيع #<?php echo e($sale->id); ?></h1>
    </div>
    <dl class="row border-top pt-3">
        <dt class="col-sm-3">المنتج</dt><dd class="col-sm-9"><?php echo e($sale->products?->name ?? '—'); ?></dd>
            <dt class="col-sm-3">العميل</dt><dd class="col-sm-9"><?php echo e($sale->customer?->name ?? '—'); ?></dd>
            <dt class="col-sm-3">المنتج</dt><dd class="col-sm-9"><?php echo e($sale->products?->name ?? '—'); ?></dd>
        <dt class="col-sm-3">الكمية</dt><dd class="col-sm-9"><?php echo e($sale->quantity); ?></dd>
        <dt class="col-sm-3">الإجمالي</dt><dd class="col-sm-9"><?php echo e(number_format((float) $sale->total, 2)); ?></dd>
        <dt class="col-sm-3">طريقة الدفع</dt><dd class="col-sm-9"><?php echo e($sale->payment_type); ?></dd>
        <dt class="col-sm-3">التاريخ</dt><dd class="col-sm-9"><?php echo e($sale->created_at?->format('Y-m-d')); ?></dd>
    </dl>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\sales\show.blade.php ENDPATH**/ ?>