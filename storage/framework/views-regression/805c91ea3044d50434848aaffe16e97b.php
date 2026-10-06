

<?php $__env->startSection('content'); ?>
<?php
    $items = is_array($invoice->items) && $invoice->items !== []
        ? $invoice->items
        : [['name' => $invoice->product?->name ?? '—', 'quantity' => $invoice->quantity, 'price' => $invoice->product_price, 'line_total' => $invoice->total_amount]];
?>
<div class="container py-4" style="max-width: 64rem">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a href="<?php echo e(route('invoices.index')); ?>" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> الفواتير</a>
            <h1 class="h3 mt-2">فاتورة <?php echo e($invoice->invoice_number); ?></h1>
        </div>
        <a href="<?php echo e(route('invoices.print', $invoice)); ?>" class="btn btn-outline-primary"><i class="fa-solid fa-print me-1" aria-hidden="true"></i> طباعة</a>
    </div>
    <dl class="row border-top pt-3">
        <dt class="col-sm-3">العميل</dt><dd class="col-sm-9"><?php echo e($invoice->customer); ?></dd>
        <dt class="col-sm-3">التاريخ</dt><dd class="col-sm-9"><?php echo e($invoice->invoice_date?->format('Y-m-d')); ?></dd>
        <dt class="col-sm-3">الحالة</dt><dd class="col-sm-9"><?php echo e($invoice->status); ?></dd>
    </dl>
    <div class="table-responsive border rounded">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>المنتج</th><th>الكمية</th><th>سعر الوحدة</th><th>الإجمالي</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr><td><?php echo e($item['name'] ?? '—'); ?></td><td><?php echo e($item['quantity'] ?? 0); ?></td><td><?php echo e(number_format((float) ($item['price'] ?? 0), 2)); ?></td><td><?php echo e(number_format((float) ($item['line_total'] ?? 0), 2)); ?></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <dl class="row mt-3">
        <dt class="col-sm-3">الإجمالي</dt><dd class="col-sm-9"><?php echo e(number_format((float) $invoice->total_amount, 2)); ?></dd>
        <dt class="col-sm-3">المدفوع</dt><dd class="col-sm-9"><?php echo e(number_format((float) $invoice->paid_amount, 2)); ?></dd>
    </dl>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\invoice\show.blade.php ENDPATH**/ ?>