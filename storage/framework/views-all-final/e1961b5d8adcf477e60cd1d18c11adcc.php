<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="rtl" data-bs-theme="light">
    <?php echo $__env->make('components.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <body class="guest-layout">
        <main class="guest-main min-vh-100 d-flex align-items-center justify-content-center p-3">
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <?php echo $__env->make('components.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\layouts\error-guest.blade.php ENDPATH**/ ?>