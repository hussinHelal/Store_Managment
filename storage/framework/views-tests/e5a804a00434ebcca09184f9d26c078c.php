<?php $__env->startSection('title', ' - الصيانة'); ?>
<?php $__env->startSection('content'); ?>

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
    <div>
        <h1>الصيانة</h1>
        <?php echo $__env->make('components.search-bar', ['placeholder' => 'اسم الجهاز'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <a href="<?php echo e(route('maintenance.create')); ?>" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> جهاز جديد
    </a>
</div>

<div class="row gx-3 gy-3 mb-4">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <p class="text-muted small mb-0">إجمالي طلبات الصيانة</p>
                <span class="badge bg-primary bg-opacity-10 text-primary">الصفحة الحالية</span>
            </div>
            <h2 class="fw-bold mb-1"><?php echo e($maintenances->total()); ?></h2>
            <p class="text-secondary mb-0">عرض <?php echo e($maintenances->count()); ?> من أصل <?php echo e($maintenances->total()); ?> طلب.</p>
        </div>
    </div>
</div>

<div class="table-wrapper table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col">اسم الجهاز</th>
            <th scope="col">الوصف</th>
            <th scope="col">المالك</th>
            <th scope="col">عنوان المالك</th>
            <th scope="col">تليفون</th>
            <th scope="col">الحالة</th>
            <th scope="col">تاريخ الدخول</th>
            <th scope="col">تاريخ الخروج</th>
            <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $maintenances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maintenance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <th scope="row"><?php echo e($maintenance->id); ?></th>
          <td><?php echo e($maintenance->name); ?></td>
          <td><?php echo e($maintenance->description); ?></td>
          <td><?php echo e($maintenance->owner); ?></td>
          <td><?php echo e($maintenance->address); ?></td>
          <td><?php echo e($maintenance->phone); ?></td>
          <td><?php echo e($maintenance->status); ?></td>
          <td><?php echo e(\Carbon\Carbon::parse($maintenance->requested_date)->format('Y-m-d')); ?></td>
          <td><?php echo e(\Carbon\Carbon::parse($maintenance->completed_date)->format('Y-m-d')); ?></td>
          <td>
              <a href="<?php echo e(route('maintenance.edit', $maintenance->id)); ?>" class="btn btn-sm btn-primary rounded">تعديل</a>
              <a href="<?php echo e(route('maintenance.showRepaired', $maintenance->id)); ?>" class="btn btn-sm btn-primary rounded">تغيير الحالة</a>
              <form action="<?php echo e(route('maintenance.destroy', $maintenance->id)); ?>" method="POST" style="display: inline;">
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('DELETE'); ?>
                  <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('هل أنت متأكد من حذف هذا الجهاز؟')">حذف</button>
              </form>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="10" class="text-center">لا توجد طلبات صيانة.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php echo $__env->make('components.pagination', ['collection' => $maintenances], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\Maintenance\index.blade.php ENDPATH**/ ?>