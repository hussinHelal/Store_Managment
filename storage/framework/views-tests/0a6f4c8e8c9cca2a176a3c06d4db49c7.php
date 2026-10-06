<?php $__env->startSection('content'); ?>

    <span class="text-center border border-1 rounded text-bold">تعديل بيانات الجهاز</span>
    <form action="<?php echo e(route('maintenance.update', $maintenance->id)); ?>" method="POST">
      <?php echo csrf_field(); ?>
      <?php echo method_field('PUT'); ?>

      <div class="mb-3">
        <label for="name" class="form-label">اسم الجهاز</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo e($maintenance->name); ?>">
      </div>

      <div class="mb-3">
        <label for="owner" class="form-label">اسم المالك</label>
        <input type="text" class="form-control" id="owner" name="owner" value="<?php echo e($maintenance->owner); ?>">
      </div>

      <div class="mb-3">
        <label for="address" class="form-label">العنوان</label>
        <input type="text" class="form-control" id="address" name="address" value="<?php echo e($maintenance->address); ?>">
      </div>

      <div class="mb-3">
        <label for="phone" class="form-label">التليفون</label>
        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo e($maintenance->phone); ?>">
      </div>

      <div class="mb-3">
        <label for="status" class="form-label">الحالة</label>
        <select class="form-select" id="status" name="status">
          <option value="قيد الانتظار" <?php echo e($maintenance->status == 'قيد الانتظار' ? 'selected' : ''); ?>>قيد الانتظار</option>
          <option value="مكتمل" <?php echo e($maintenance->status == 'مكتمل' ? 'selected' : ''); ?>>مكتمل</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">الوصف</label>
        <textarea class="form-control" id="description" name="description"><?php echo e($maintenance->description); ?></textarea>
      </div>

      <button type="submit" class="btn btn-primary">تحديث</button>
    </form>
     <div class="mt-3">
        <a href="<?php echo e(route('maintenance.index')); ?>" class="btn btn-danger">رجوع</a>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\Maintenance\edit.blade.php ENDPATH**/ ?>