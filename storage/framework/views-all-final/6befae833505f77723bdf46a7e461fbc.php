<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('errors.page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>@extends('layouts.app')

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="text-center">
        <h1 class="display-4">500</h1>
        <p class="lead">حدث خطأ في الخادم. نأسف للخطأ، سنعمل على إصلاحه قريباً.</p>
        <a href="<?php echo e(route('home')); ?>" class="btn btn-primary">الرجوع إلى الرئيسية</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->check() ? 'layouts.app' : 'layouts.error-guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\errors\500.blade.php ENDPATH**/ ?>