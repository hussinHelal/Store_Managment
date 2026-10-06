<?php $__env->startSection('title', '- الفواتير'); ?>
<?php $__env->startSection('content'); ?>

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 " >
    <div >
        <h1>الفواتير</h1>
        <?php echo $__env->make('components.search-bar', ['placeholder' => 'اسم العميل أو رقم الفاتورة'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <a href="<?php echo e(route('invoices.create')); ?>" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> فاتورة جديدة
    </a>
</div>

<div class="row gx-3 gy-3 mb-4">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <p class="text-muted small mb-0">إجمالي الفواتير</p>
                <span class="badge bg-primary bg-opacity-10 text-primary">الصفحة الحالية</span>
            </div>
            <h2 class="fw-bold mb-1"><?php echo e($invoices->total()); ?></h2>
            <p class="text-secondary mb-0">عرض <?php echo e($invoices->count()); ?> من أصل <?php echo e($invoices->total()); ?> فاتورة.</p>
        </div>
    </div>
</div>

<div class="table-wrapper table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
          <thead class="table-dark">
      <tr>
            <th scope="col">#</th>
            <th scope="col">رقم الفاتورة</th>
            <th scope="col">العميل</th>
            <th scope="col">المنتج</th>
            <th scope="col">الكمية</th>
            <th scope="col">سعر الوحدة</th>
            <th scope="col">المبلغ</th>
            <th scope="col">المبلغ المدفوع</th>
            <th scope="col">التاريخ</th>
            <th scope="col">المبلغ الكلي</th>
            <th scope="col">الحالة</th>
            <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <th scope="row"><?php echo e($invoice->id); ?></th>
          <td><?php echo e($invoice->invoice_number); ?></td>
          <td><?php echo e($invoice->customer); ?></td>
          <td><?php echo e($invoice->item_names ?: ($invoice->product?->name ?? 'محذوف')); ?></td>
          <td><?php echo e($invoice->item_quantity ?: $invoice->quantity); ?></td>
          <td><?php echo e($invoice->product?->price ?? $invoice->product_price); ?></td>
          <td><?php echo e($invoice->total_amount); ?></td>
          <td><?php echo e($invoice->paid_amount); ?></td>
          <td><?php echo e(\Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d')); ?></td>
          <td><?php echo e($invoice->total_amount); ?></td>
          <td><?php echo e($invoice->status); ?></td>
          <td>
              <a href="<?php echo e(route('invoices.edit', $invoice->id)); ?>" class="btn btn-sm btn-primary m-1 rounded">تعديل</a>
              <a href="<?php echo e(route('invoices.print', $invoice->id)); ?>" class="btn btn-sm btn-info m-1 rounded" title="طباعة الفاتورة"><i class="fas fa-print"></i></a>
              <?php if($invoice->status !== 'refunded'): ?>
              <form action="<?php echo e(route('invoices.refund', $invoice->id)); ?>" method="POST" style="display: inline;">
                  <?php echo csrf_field(); ?>
                  <button type="submit" class="btn btn-sm btn-warning m-1 rounded">استرداد</button>
              </form>
              <?php endif; ?>
              <form action="<?php echo e(route('invoices.destroy', $invoice->id)); ?>" method="POST" style="display: inline;">
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('DELETE'); ?>
                  <button type="submit" class="btn btn-sm btn-danger m-1 rounded">حذف</button>
              </form>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="12" class="text-center">لا توجد فواتير.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php echo $__env->make('components.pagination', ['collection' => $invoices], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\invoice\index.blade.php ENDPATH**/ ?>