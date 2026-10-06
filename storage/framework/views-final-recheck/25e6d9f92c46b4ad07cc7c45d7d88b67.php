<?php $__env->startSection('title', ' - العملاء'); ?>
<?php $__env->startSection('content'); ?>

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
    <div>
        <h1>العملاء</h1>
        <?php echo $__env->make('components.search-bar', ['placeholder' => 'اسم العميل'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <a href="<?php echo e(route('customers.create')); ?>" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> عميل جديد
    </a>
</div>

<div class="row gx-3 gy-3 mb-4">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <p class="text-muted small mb-0">إجمالي العملاء</p>
                <span class="badge bg-primary bg-opacity-10 text-primary">الصفحة الحالية</span>
            </div>
            <h2 class="fw-bold mb-1"><?php echo e($customers->total()); ?></h2>
            <p class="text-secondary mb-0">عرض <?php echo e($customers->count()); ?> من أصل <?php echo e($customers->total()); ?> عميل.</p>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body p-3 h-100">
            <p class="text-muted small mb-2">إجمالي المستحقات من الأقساط</p>
            <h2 class="fw-bold mb-0 text-danger"><?php echo e(number_format((float) $totalInstallmentDue, 2)); ?> ج.م</h2>
        </div>
    </div>
</div>

<div class="table-wrapper table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col">الاسم</th>
            <th scope="col">التليفون</th>
            <th scope="col">العنوان</th>
            <th scope="col">مستحق الأقساط</th>
            <th scope="col">حالة الأقساط</th>
            <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <th scope="row"><?php echo e($customer->id); ?></th>
          <td><?php echo e($customer->name); ?></td>
          <td><?php echo e($customer->phone); ?></td>
          <td><?php echo e($customer->address); ?></td>
          <td class="fw-semibold <?php echo e((float) $customer->installment_due > 0 ? 'text-danger' : 'text-success'); ?>">
              <?php echo e(number_format((float) $customer->installment_due, 2)); ?> ج.م
          </td>
          <td>
              <?php
                  $latestOpenInstallment = $customer->namedInstallments
                      ->where('remaining', '>', 0)
                      ->sortByDesc('id')
                      ->first();
                  $hasInstallments = $customer->namedInstallments_count > 0;
                  $installmentState = (float) $customer->installment_due <= 0
                      ? ($hasInstallments ? 'مسدد' : 'لا توجد أقساط')
                      : ((float) ($latestOpenInstallment?->paid_amount ?? 0) > 0 ? 'سداد جزئي' : 'مستحق');
                  $installmentStateClass = $installmentState === 'مسدد'
                      ? 'text-bg-success'
                      : ($installmentState === 'لا توجد أقساط' ? 'text-bg-secondary' : 'text-bg-warning');
              ?>
              <span class="badge <?php echo e($installmentStateClass); ?>"><?php echo e($installmentState); ?></span>
          </td>
          <td>
              <a href="<?php echo e(route('customers.edit', $customer->id)); ?>" class="btn btn-sm btn-primary rounded">تعديل</a>
              <form action="<?php echo e(route('customers.destroy', $customer->id)); ?>" method="POST" style="display: inline;">
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('DELETE'); ?>
                  <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('هل أنت متأكد من حذف هذا العميل؟')">حذف</button>
              </form>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="7" class="text-center">لا يوجد عملاء.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php echo $__env->make('components.pagination', ['collection' => $customers], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\customers\index.blade.php ENDPATH**/ ?>