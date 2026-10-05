

<?php $__env->startSection('content'); ?>
<div class="container py-4" style="max-width: 48rem">
    <div class="mb-4">
        <a href="<?php echo e(route('users.index')); ?>" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i> الموظفون</a>
        <h1 class="h3 mt-2"><?php echo e($user->name); ?></h1>
    </div>
    <dl class="row border-top pt-3">
        <dt class="col-sm-3">اسم المستخدم</dt><dd class="col-sm-9"><?php echo e($user->username); ?></dd>
        <dt class="col-sm-3">البريد الإلكتروني</dt><dd class="col-sm-9" dir="ltr"><?php echo e($user->email); ?></dd>
        <dt class="col-sm-3">الدور</dt><dd class="col-sm-9"><?php echo e($user->roles->pluck('name')->map(fn ($role) => ['Admin' => 'مدير النظام', 'Cashier' => 'كاشير', 'Inventory Manager' => 'مدير المخزون', 'Sales Manager' => 'مدير المبيعات'][$role] ?? $role)->join('، ') ?: 'غير محدد'); ?></dd>
        <dt class="col-sm-3">الحالة</dt><dd class="col-sm-9"><?php echo e($user->is_active ? 'نشط' : 'غير نشط'); ?></dd>
    </dl>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views/users/show.blade.php ENDPATH**/ ?>