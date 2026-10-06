<?php
    $search = request('search');
?>
<form method="GET" class="mt-3 mb-4 d-flex align-items-center gap-2 " role="search" style="min-width:300px">
    <input type="search" name="search" class="form-control flex-grow-1" placeholder="<?php echo e($placeholder ?? 'ابحث هنا ...'); ?>" value="<?php echo e($search); ?>" autocomplete="off">
    <button type="submit" class="btn btn-outline-secondary">بحث</button>
    <?php if($search): ?>
        <a href="<?php echo e(url()->current()); ?>" class="btn btn-outline-danger">مسح</a>
    <?php endif; ?>
</form>
<?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\components\search-bar.blade.php ENDPATH**/ ?>