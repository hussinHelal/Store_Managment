<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="rtl" data-bs-theme="light">
    <?php echo $__env->make('components.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <body>
        <header>
            <?php echo $__env->make('components.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </header>

        <div class="d-flex flex-column flex-lg-row app-shell">
            <?php echo $__env->make('components.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <main class="flex-grow-1 p-4">
                <?php echo $__env->make('components.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->yieldContent('main'); ?>
                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>

        <?php echo $__env->make('components.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->yieldPushContent('scripts'); ?>

    </body>
</html>
<?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views/layouts/app.blade.php ENDPATH**/ ?>