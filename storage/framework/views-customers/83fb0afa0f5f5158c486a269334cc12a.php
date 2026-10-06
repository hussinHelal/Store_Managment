<?php $__env->startSection('content'); ?>

    <span class="text-center border border-1 rounded text-bold">تغيير الحالة</span>
<?php if($maintenance->status == 'قيد الانتظار'): ?>
    <form action="<?php echo e(route('maintenance.repaired', $maintenance)); ?>" method="POST">
      <?php echo csrf_field(); ?>
      <?php echo method_field('PUT'); ?>

      <div class="mb-3">
        <label for="status" class="form-label">الحالة</label>
        <select class="form-select" id="status" name="status">
          <option value="قيد الانتظار" selected>قيد الانتظار</option>
          <option value="مكتمل">مكتمل</option>
          <option value="مرفوض">مرفوض</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">حفظ الحالة</button>
    </form>
<?php else: ?>
    <div class="alert alert-info">الحالة حالياً: <?php echo e($maintenance->status); ?></div>
<?php endif; ?>


    <div class="mt-3">
      <a href="<?php echo e(route('maintenance.index')); ?>" class="btn btn-secondary">العودة</a>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\Maintenance\repaired.blade.php ENDPATH**/ ?>