<?php $__env->startSection('content'); ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>إضافة إشعار جديد</h1>
</div>

<form action="<?php echo e(route('admin.notifications.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>

    <div class="mb-3">
        <label for="title" class="form-label">العنوان</label>
        <input type="text" class="form-control" id="title" name="title" value="<?php echo e(old('title')); ?>" required>
    </div>

    <div class="mb-3">
        <label for="message" class="form-label">الرسالة</label>
        <textarea class="form-control" id="message" name="message" rows="4" required><?php echo e(old('message')); ?></textarea>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label for="starts_at" class="form-label">يبدأ في</label>
            <input type="datetime-local" class="form-control" id="starts_at" name="starts_at" value="<?php echo e(old('starts_at')); ?>">
        </div>
        <div class="col-md-6">
            <label for="ends_at" class="form-label">ينتهي في</label>
            <input type="datetime-local" class="form-control" id="ends_at" name="ends_at" value="<?php echo e(old('ends_at')); ?>">
        </div>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" name="is_active" value="0">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo e(old('is_active', 1) ? 'checked' : ''); ?>>
        <label class="form-check-label" for="is_active">مفعل</label>
    </div>

    <button type="submit" class="btn btn-primary">حفظ الإشعار</button>
    <a href="<?php echo e(route('admin.notifications.index')); ?>" class="btn btn-secondary">إلغاء</a>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\notifications\create.blade.php ENDPATH**/ ?>