

<?php $__env->startSection('content'); ?>
<div class="container py-4" style="max-width: 56rem">
    <div class="mb-4">
        <a href="<?php echo e(route('products.index')); ?>" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> المنتجات</a>
        <h1 class="h3 mt-2"><?php echo e($product->name); ?></h1>
    </div>
    <?php if($product->image): ?>
        <img src="<?php echo e($product->image_url); ?>" alt="<?php echo e($product->name); ?>" class="img-fluid rounded mb-3" style="max-width: 100%; max-height: 20rem; object-fit: contain">
    <?php endif; ?>
    <dl class="row border-top pt-3">
        <dt class="col-sm-3">السعر</dt><dd class="col-sm-9"><?php echo e(number_format((float) $product->price, 2)); ?></dd>
        <dt class="col-sm-3">المخزون</dt><dd class="col-sm-9"><?php echo e($product->stock); ?></dd>
        <dt class="col-sm-3">المبيعات</dt><dd class="col-sm-9"><?php echo e($product->total_sold); ?></dd>
        <dt class="col-sm-3">التصنيف</dt><dd class="col-sm-9"><?php echo e($product->category?->name ?? '—'); ?></dd>
        <dt class="col-sm-3">الباركود</dt><dd class="col-sm-9"><?php echo e($product->barcode ?? '—'); ?></dd>
        <dt class="col-sm-3">الوصف</dt><dd class="col-sm-9"><?php echo e($product->description); ?></dd>
    </dl>
    <a href="<?php echo e(route('products.edit', $product)); ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1" aria-hidden="true"></i> تعديل</a>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\products\show.blade.php ENDPATH**/ ?>