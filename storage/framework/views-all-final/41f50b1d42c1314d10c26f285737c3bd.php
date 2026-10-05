<?php $__env->startSection('content'); ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>الإشعارات</h1>
</div>

<div class="mb-3 d-flex justify-content-end">
    <a href="<?php echo e(route('notifications.markAllRead')); ?>" class="btn btn-sm btn-outline-primary">وضع الكل كمُقروء</a>
</div>

<div class="list-group">
    <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <a href="<?php echo e(route('notifications.markRead', $notification->id)); ?>" class="list-group-item mb-2 text-decoration-none <?php echo e($notification->is_read ? 'text-muted' : ''); ?>">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1"><?php echo e($notification->title); ?> <?php if(!$notification->is_read): ?> <span class="badge bg-primary">جديد</span> <?php endif; ?></h5>
                    <p class="mb-1"><?php echo e($notification->message); ?></p>
                </div>
                <small class="text-muted"><?php echo e($notification->created_at->format('Y-m-d')); ?></small>
            </div>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="alert alert-info">لا توجد إشعارات نشطة في الوقت الحالي.</div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\notifications\list.blade.php ENDPATH**/ ?>