<?php ($viewErrors = $errors ?? collect()); ?>
<?php if(session('success') || session('error') || $viewErrors->isNotEmpty()): ?>
    <div class="mb-4">
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if($viewErrors->isNotEmpty()): ?>
            <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <strong>حدثت الأخطاء التالية:</strong>
                <ul class="mb-0 mt-2">
                    <?php $__currentLoopData = $viewErrors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>


<div id="dynamic-alerts" class="mb-4"></div>
<?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\components\alerts.blade.php ENDPATH**/ ?>