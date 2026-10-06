
<?php $__env->startSection('title', ' - تعديل المورد'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-3" style="max-width: 52rem">
    <div class="mb-4">
        <a href="<?php echo e(route('suppliers.show', $supplier)); ?>" class="link-secondary text-decoration-none"><?php echo e($supplier->name); ?></a>
        <h1 class="h3 mt-2">تعديل بيانات المورد</h1>
    </div>
    <form method="POST" action="<?php echo e(route('suppliers.update', $supplier)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <?php echo $__env->make('suppliers._form', ['supplier' => $supplier], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
            <a href="<?php echo e(route('suppliers.show', $supplier)); ?>" class="btn btn-outline-secondary">إلغاء</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\suppliers\edit.blade.php ENDPATH**/ ?>