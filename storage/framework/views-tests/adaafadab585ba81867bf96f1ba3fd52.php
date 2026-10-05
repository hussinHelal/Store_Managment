

<?php $__env->startSection('content'); ?>
<div class="container py-4" style="max-width: 58rem">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a href="<?php echo e(route('installments.index')); ?>" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> الديون</a>
            <h1 class="h3 mt-2">تفاصيل الدين #<?php echo e($installment->id); ?></h1>
        </div>
        <?php if(!$installment->invoice_id): ?>
            <a href="<?php echo e(route('installments.edit', $installment)); ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1" aria-hidden="true"></i> تعديل</a>
        <?php endif; ?>
    </div>

    <dl class="row border-top pt-3">
        <dt class="col-sm-4">العميل</dt><dd class="col-sm-8"><?php echo e($installment->customer); ?></dd>
        <dt class="col-sm-4">المنتجات</dt><dd class="col-sm-8"><?php echo e($installment->item_names ?: ($installment->product?->name ?? 'لا يوجد اسم')); ?></dd>
        <dt class="col-sm-4">إجمالي المبلغ</dt><dd class="col-sm-8"><?php echo e(number_format((float) $installment->product_price, 2)); ?></dd>
        <dt class="col-sm-4">الكمية</dt><dd class="col-sm-8"><?php echo e($installment->item_quantity ?: $installment->quantity); ?></dd>
        <dt class="col-sm-4">المدفوع</dt><dd class="col-sm-8"><?php echo e(number_format((float) $installment->paid_amount, 2)); ?></dd>
        <dt class="col-sm-4">المتبقي</dt><dd class="col-sm-8"><?php echo e(number_format((float) $installment->remaining, 2)); ?></dd>
        <dt class="col-sm-4">الحالة</dt><dd class="col-sm-8"><?php echo e($installment->status); ?></dd>
        <dt class="col-sm-4">تاريخ الدفع</dt><dd class="col-sm-8"><?php echo e($installment->payment_date?->format('Y-m-d') ?? '—'); ?></dd>
        <dt class="col-sm-4">موعد الدفع التالي</dt><dd class="col-sm-8"><?php echo e($installment->next_payment_date?->format('Y-m-d') ?? '—'); ?></dd>
    </dl>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\installments\show.blade.php ENDPATH**/ ?>