<?php $__env->startSection('content'); ?>

    <span class="text-center border border-1 rounded text-bold">عميل جديد</span>
    <form action="<?php echo e(route('customers.store')); ?>" method="POST">
      <?php echo csrf_field(); ?>

      <div class="mb-3">
        <label for="name" class="form-label">الاسم</label>
        <input type="text" class="form-control" id="name" name="name">
      </div>

      <div class="mb-3">
        <label for="phone" class="form-label">التليفون</label>
        <input type="text" class="form-control" id="phone" name="phone">
      </div>

      <div class="mb-3">
        <label for="address" class="form-label">العنوان</label>
        <input type="text" class="form-control" id="address" name="address">
      </div>
      <button type="submit" class="btn btn-primary">انشاء</button>
    </form>
     <div class="mt-3">
        <a href="<?php echo e(route('customers.index')); ?>" class="btn btn-danger">رجوع</a>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\customers\create.blade.php ENDPATH**/ ?>