<?php $__env->startSection('content'); ?>

    <span class="text-center border border-1 rounded text-bold">تعديل صنف</span>
    <form action="<?php echo e(route('categories.update', $category)); ?>" method="POST">
      <?php echo csrf_field(); ?>
      <?php echo method_field('PUT'); ?>

      <div class="mb-3">
        <label for="name" class="form-label">الاسم</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo e($category->name); ?>">
      </div>

      <button type="submit" class="btn btn-primary">تحديث</button>
    </form>

     <div class="mt-3">
        <a href="<?php echo e(route('categories.index')); ?>" class="btn btn-danger">رجوع</a>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\category\edit.blade.php ENDPATH**/ ?>