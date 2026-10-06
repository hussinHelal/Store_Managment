<?php $__env->startSection('content'); ?>

    <span class="text-center border border-1 rounded text-bold">تحديث منتج</span>
    <form action="<?php echo e(route('products.update', $product->id)); ?>" method="POST">
      <?php echo csrf_field(); ?>

      <div class="mb-3">
        <label for="name" class="form-label">الاسم</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo e($product->name); ?>">
      </div>

      <div class="mb-3">
        <label for="quantity" class="form-label">الكمية</label>
        <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo e($product->quantity); ?>">
      </div>


      <button type="submit" class="btn btn-primary">تحديث</button>
    </form>

     <div class="mt-3">
        <a href="<?php echo e(route('sales.index')); ?>" class="btn btn-danger">رجوع</a>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\sales\edit.blade.php ENDPATH**/ ?>