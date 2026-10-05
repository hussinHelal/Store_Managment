

<?php $__env->startSection('title', '- الملف الشخصي'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $photoUrl = null;
    if ($profile->photo) {
        $photoUrl = str_contains($profile->photo, '/')
            ? asset('storage/'.$profile->photo)
            : asset('uploads/users/'.$profile->photo);
    }
?>
<div class="container py-4" style="max-width: 60rem">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">الملف الشخصي</h1>
            <p class="text-body-secondary mb-0">إدارة بيانات حسابك الشخصي.</p>
        </div>
        <a href="<?php echo e(route('profile.edit', $profile)); ?>" class="btn btn-primary">
            <i class="fa-solid fa-pen me-1" aria-hidden="true"></i> تعديل الملف
        </a>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-md-auto">
            <?php if($photoUrl): ?>
                <img src="<?php echo e($photoUrl); ?>" alt="الصورة الشخصية" class="rounded-circle border" width="96" height="96" style="object-fit: cover">
            <?php else: ?>
                <div class="rounded-circle bg-body-tertiary border d-flex align-items-center justify-content-center fw-semibold" style="width: 96px; height: 96px; font-size: 2rem" aria-hidden="true">
                    <?php if($profile->name): ?>
                        <?php echo e(mb_strtoupper(mb_substr($profile->name, 0, 1))); ?>

                    <?php else: ?>
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col">
            <dl class="row mb-0 border-top pt-3">
                <dt class="col-sm-3">اسم المستخدم</dt>
                <dd class="col-sm-9"><?php echo e($profile->username); ?></dd>
                <dt class="col-sm-3">الاسم</dt>
                <dd class="col-sm-9"><?php echo e($profile->name); ?></dd>
                <dt class="col-sm-3">الدور</dt>
                <dd class="col-sm-9"><?php echo e($profile->getRoleNames()->join(', ') ?: ucfirst($profile->role)); ?></dd>
                <dt class="col-sm-3">تاريخ إنشاء الحساب</dt>
                <dd class="col-sm-9"><?php echo e($profile->created_at?->format('Y-m-d')); ?></dd>
            </dl>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\profile\index.blade.php ENDPATH**/ ?>