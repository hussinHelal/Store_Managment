<?php $__env->startSection('title', ' - المنتجات'); ?>
<?php $__env->startSection('content'); ?>

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
    <div>
        <h1>المنتجات</h1>
        <?php echo $__env->make('components.search-bar', ['placeholder' => ' اسم المنتج أو الباركود'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <a href="<?php echo e(route('products.create')); ?>" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> منتج جديد
    </a>
</div>

<div class="row gx-3 gy-3 mb-4">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <p class="text-muted small mb-0">إجمالي المنتجات</p>
                <span class="badge bg-primary bg-opacity-10 text-primary">الصفحة الحالية</span>
            </div>
            <h2 class="fw-bold mb-1"><?php echo e($products->total()); ?></h2>
            <p class="text-secondary mb-0">عرض <?php echo e($products->count()); ?> من أصل <?php echo e($products->total()); ?> منتج.</p>
        </div>
    </div>
</div>

<div class="table-wrapper table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th scope="col">#</th>
          <th scope="col">الاسم</th>
          <th scope="col">السعر</th>
          <th scope="col">الوصف</th>
          <th scope="col">الصنف</th>
          <th scope="col">الباركود</th>
          <th scope="col">المخزون</th>
          <th scope="col">الصورة</th>
          <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <th scope="row"><?php echo e($product->id); ?></th>
          <td><?php echo e($product->name); ?></td>
          <td><?php echo e($product->price); ?></td>
          <td><?php echo e($product->description); ?></td>
          <td><?php echo e($product->category?->name ?? 'لا يوجد'); ?></td>
          <td><?php echo e($product->barcode ?? '-'); ?></td>
          <td><?php echo e($product->stock); ?></td>
          <td>
              <?php if($product->image_url): ?>
                <img src="<?php echo e($product->image_url); ?>" alt="<?php echo e($product->name); ?>" class="img-fluid rounded" width="60" height="60" style="aspect-ratio: 1; object-fit: cover;">
              <?php else: ?>
                <span class="text-muted">لا توجد صورة</span>
              <?php endif; ?>
          </td>
          <td>
              <a href="<?php echo e(route('products.printLabel', $product->id)); ?>" target="_blank" class="btn btn-sm btn-info rounded">طباعة</a>
              <a href="<?php echo e(route('products.edit', $product->id)); ?>" class="btn btn-sm btn-primary rounded">تعديل</a>
              <form action="<?php echo e(route('products.destroy', $product->id)); ?>" method="POST" style="display: inline;">
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('DELETE'); ?>
                  <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">حذف</button>
              </form>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="8" class="text-center">لا توجد منتجات.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php echo $__env->make('components.pagination', ['collection' => $products], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\products\index.blade.php ENDPATH**/ ?>