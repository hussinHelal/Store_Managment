<?php
    $icons = [
        401 => 'fa-lock',
        403 => 'fa-shield-halved',
        404 => 'fa-magnifying-glass',
        419 => 'fa-clock-rotate-left',
        429 => 'fa-gauge-high',
        500 => 'fa-triangle-exclamation',
        503 => 'fa-screwdriver-wrench',
    ];
?>

<section class="text-center py-5" aria-labelledby="error-title">
    <i class="fa-solid <?php echo e($icons[$status] ?? 'fa-circle-exclamation'); ?> text-primary display-3 mb-3" aria-hidden="true"></i>
    <h1 id="error-title" class="display-5 fw-bold"><?php echo e($status); ?></h1>
    <p class="lead text-body-secondary mx-auto" style="max-width: 36rem"><?php echo e($message); ?></p>
    <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
        <a class="btn btn-outline-secondary" href="<?php echo e($previousUrl); ?>">
            <i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i>
            Go back
        </a>
        <a class="btn btn-primary" href="<?php echo e(route('home')); ?>">
            <i class="fa-solid fa-house me-1" aria-hidden="true"></i>
            Back to home
        </a>
    </div>
</section><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views/errors/page.blade.php ENDPATH**/ ?>