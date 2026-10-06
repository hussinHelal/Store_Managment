
<?php $__env->startSection('title', ' - الموردون'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">الموردون والحسابات الدائنة</h1>
            <p class="text-body-secondary mb-0">إدارة الموردين وفواتير الشراء والسداد.</p>
        </div>
        <?php if(auth()->user()->can('page.suppliers.manage')): ?>
            <a href="<?php echo e(route('suppliers.create')); ?>" class="btn btn-primary"><i class="fa-solid fa-plus ms-1" aria-hidden="true"></i> إضافة مورد</a>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-body-secondary small mb-2">إجمالي المستحقات للموردين</div>
                <div class="h3 fw-bold mb-0 text-danger"><?php echo e(number_format($totalDue, 2)); ?> ج.م</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-body-secondary small mb-2">عدد الموردين</div>
                <div class="h3 fw-bold mb-0"><?php echo e($suppliers->total()); ?></div>
            </div></div>
        </div>
    </div>

    <form method="GET" action="<?php echo e(route('suppliers.index')); ?>" class="row g-2 align-items-end mb-3">
        <div class="col-sm-8 col-lg-5">
            <label for="supplier-search" class="form-label">بحث بالاسم أو الهاتف أو البريد</label>
            <input id="supplier-search" class="form-control" type="search" name="search" value="<?php echo e($search); ?>" maxlength="100">
        </div>
        <div class="col-auto"><button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-magnifying-glass ms-1" aria-hidden="true"></i> بحث</button></div>
        <?php if($search !== ''): ?><div class="col-auto"><a class="btn btn-link" href="<?php echo e(route('suppliers.index')); ?>">مسح البحث</a></div><?php endif; ?>
    </form>

    <div class="table-responsive border rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr>
                <th scope="col">المورد</th><th scope="col">الهاتف</th><th scope="col">الفواتير</th>
                <th scope="col">الرصيد الافتتاحي</th><th scope="col">المستحق</th><th scope="col">الإجراءات</th>
            </tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="fw-semibold"><?php echo e($supplier->name); ?></td>
                    <td><?php echo e($supplier->phone ?: '—'); ?></td>
                    <td><?php echo e($supplier->purchases_count); ?></td>
                    <td><?php echo e(number_format((float) $supplier->opening_balance, 2)); ?> ج.م</td>
                    <td class="fw-semibold <?php echo e((float) $supplier->balance > 0 ? 'text-danger' : 'text-success'); ?>"><?php echo e(number_format((float) $supplier->balance, 2)); ?> ج.م</td>
                    <td><a href="<?php echo e(route('suppliers.show', $supplier)); ?>" class="btn btn-sm btn-outline-primary">التفاصيل</a></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-body-secondary py-4">لا يوجد موردون مطابقون للبحث.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-3"><?php echo e($suppliers->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\suppliers\index.blade.php ENDPATH**/ ?>