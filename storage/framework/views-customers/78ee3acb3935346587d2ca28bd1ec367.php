<?php $__env->startSection('title', ' - الديون'); ?>

<?php $__env->startSection('content'); ?>

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
    <div>
        <h1>الديون</h1>
        <?php echo $__env->make('components.search-bar', ['placeholder' => 'اسم العميل'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <a href="<?php echo e(route('installments.create')); ?>" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> دين جديد
    </a>
</div>

<div class="row gx-3 gy-3 mb-4">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <p class="text-muted small mb-0">إجمالي الديون</p>
                <span class="badge bg-primary bg-opacity-10 text-primary">الصفحة الحالية</span>
            </div>
            <h2 class="fw-bold mb-1"><?php echo e($installments->total()); ?></h2>
            <p class="text-secondary mb-0">عرض <?php echo e($installments->count()); ?> من أصل <?php echo e($installments->total()); ?> دين.</p>
        </div>
    </div>
</div>

<div class="table-wrapper table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th scope="col">#</th>
          <th scope="col">الاسم</th>
          <th scope="col">المنتج</th>
          <th scope="col">المبلغ</th>
          <th scope="col">الكمية</th>
          <th scope="col">تاريخ الدفع</th>
          <th scope="col">الدفعه التالية</th>
          <th scope="col">المدفوع</th>
          <th scope="col">المتبقي</th>
          <th scope="col">الحالة</th>
          <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $installments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $installment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <th scope="row"><?php echo e($installment->id); ?></th>
          <td><?php echo e($installment->customer); ?></td>
          <td><?php echo e($installment->item_names ?: ($installment->product?->name ?? 'لا يوجد اسم')); ?></td>
          <td><?php echo e($installment->product?->price ?? $installment->product_price ?? 'لا يوجد سعر'); ?></td>
          <td><?php echo e($installment->item_quantity ?: ($installment->quantity ?? 'لا يوجد كمية')); ?></td>
          <td><?php echo e(\Carbon\Carbon::parse($installment->payment_date)->format('Y-m-d')); ?></td>
          <td><?php echo e(\Carbon\Carbon::parse($installment->next_payment_date)->format('Y-m-d')); ?></td>
          <td><?php echo e($installment->paid_amount); ?></td>
          <td><?php echo e($installment->remaining); ?></td>
          <td><?php echo e($installment->status); ?></td>
          <td>
              <a href="<?php echo e(route('installments.edit', $installment->id)); ?>" class="btn btn-sm btn-primary m-1 rounded">تعديل</a>
              <a href="<?php echo e(route('installments.showPay', $installment->id)); ?>" class="btn btn-sm btn-warning m-1 rounded">دفع</a>
              <form action="<?php echo e(route('installments.destroy', $installment->id)); ?>" method="POST" style="display: inline;">
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('DELETE'); ?>
                  <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('هل أنت متأكد من حذف هذا الدين؟')">حذف</button>
              </form>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="11" class="text-center">لا توجد ديون.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php echo $__env->make('components.pagination', ['collection' => $installments], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\installments\index.blade.php ENDPATH**/ ?>