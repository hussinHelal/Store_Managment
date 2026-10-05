
<?php $__env->startSection('title', ' - إضافة مورد'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-3" style="max-width: 52rem">
    <div class="mb-4">
        <a href="<?php echo e(route('suppliers.index')); ?>" class="link-secondary text-decoration-none">الموردون</a>
        <h1 class="h3 mt-2">إضافة مورد</h1>
    </div>
    <form method="POST" action="<?php echo e(route('suppliers.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php echo $__env->make('suppliers._form', ['supplier' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">حفظ المورد</button>
            <a href="<?php echo e(route('suppliers.index')); ?>" class="btn btn-outline-secondary">إلغاء</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\suppliers\create.blade.php ENDPATH**/ ?>