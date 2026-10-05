<?php if(isset($collection) && method_exists($collection, 'links')): ?>
    <div class="d-flex justify-content-end mt-3">
        <?php echo e($collection->withQueryString()->links()); ?>

    </div>
<?php endif; ?>
<?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views/components/pagination.blade.php ENDPATH**/ ?>