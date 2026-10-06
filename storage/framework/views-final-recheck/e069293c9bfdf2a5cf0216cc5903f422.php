

<?php $__env->startSection('content'); ?>
<div class="container py-4" style="max-width: 48rem">
    <div class="mb-4">
        <a href="<?php echo e(route('categories.index')); ?>" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> التصنيفات</a>
        <h1 class="h3 mt-2"><?php echo e($category->name); ?></h1>
    </div>
    <p class="text-body-secondary">عدد المنتجات: <?php echo e($category->products_count); ?></p>
    <a href="<?php echo e(route('categories.edit', $category)); ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1" aria-hidden="true"></i> تعديل</a>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\category\show.blade.php ENDPATH**/ ?>