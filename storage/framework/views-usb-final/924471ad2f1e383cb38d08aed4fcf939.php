

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('errors.page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make(auth()->check() ? 'layouts.app' : 'layouts.error-guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views/errors/503.blade.php ENDPATH**/ ?>