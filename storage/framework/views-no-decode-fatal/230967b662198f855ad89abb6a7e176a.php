<?php $__env->startSection('content'); ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>إدارة الإشعارات</h1>
    <a href="<?php echo e(route('admin.notifications.create')); ?>" class="btn btn-primary">إضافة إشعار جديد</a>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>العنوان</th>
                <th>الرسالة</th>
                <th>مباشر من</th>
                <th>فعال</th>
                <th>ينتهي في</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($notification->id); ?></td>
                    <td><?php echo e($notification->title); ?></td>
                    <td><?php echo e(\Illuminate\Support\Str::limit($notification->message, 80)); ?></td>
                    <td><?php echo e(optional($notification->creator)->name ?? 'N/A'); ?></td>
                    <td><?php echo e($notification->is_active ? 'نعم' : 'لا'); ?></td>
                    <td><?php echo e(optional($notification->ends_at)->format('Y-m-d H:i') ?? 'بدون'); ?></td>
                    <td>
                        <form action="<?php echo e(route('admin.notifications.destroy', $notification->id)); ?>" method="POST" onsubmit="return confirm('هل تريد حذف هذا الإشعار؟');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>

<?php echo e($notifications->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\notifications\index.blade.php ENDPATH**/ ?>