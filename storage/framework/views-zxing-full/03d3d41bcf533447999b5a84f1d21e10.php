
<?php $__env->startSection('title', ' - ' . $supplier->name); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="<?php echo e(route('suppliers.index')); ?>" class="link-secondary text-decoration-none">الموردون</a>
            <h1 class="h3 mt-2 mb-1"><?php echo e($supplier->name); ?></h1>
            <div class="text-body-secondary"><?php echo e($supplier->phone ?: 'بدون هاتف'); ?> <?php if($supplier->email): ?> · <?php echo e($supplier->email); ?> <?php endif; ?></div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if(auth()->user()->can('page.suppliers.manage')): ?>
                <a href="<?php echo e(route('suppliers.edit', $supplier)); ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen ms-1" aria-hidden="true"></i> تعديل البيانات</a>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#purchaseModal"><i class="fa-solid fa-file-circle-plus ms-1" aria-hidden="true"></i> فاتورة شراء</button>
                <?php if((float) $supplier->balance > 0): ?>
                    <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="fa-solid fa-money-bill-transfer ms-1" aria-hidden="true"></i> سداد دفعة</button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-body-secondary mb-2">الرصيد الافتتاحي</div><div class="h4 mb-0"><?php echo e(number_format((float) $supplier->opening_balance, 2)); ?> ج.م</div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-body-secondary mb-2">المستحق للمورد</div><div class="h4 mb-0 <?php echo e((float) $supplier->balance > 0 ? 'text-danger' : 'text-success'); ?>"><?php echo e(number_format((float) $supplier->balance, 2)); ?> ج.م</div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-body-secondary mb-2">فواتير الشراء</div><div class="h4 mb-0"><?php echo e($supplier->purchases->count()); ?></div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-body-secondary mb-2">العنوان</div><div class="mb-0"><?php echo e($supplier->address ?: 'غير محدد'); ?></div></div></div></div>
    </div>

    <section class="mb-4" aria-labelledby="purchases-heading">
        <div class="d-flex justify-content-between align-items-center mb-2"><h2 id="purchases-heading" class="h5 mb-0">فواتير الشراء</h2></div>
        <div class="table-responsive border rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>رقم الفاتورة</th><th>التاريخ</th><th>طريقة الدفع</th><th>الإجمالي</th><th>المدفوع</th><th>المتبقي</th><th>الحالة</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $supplier->purchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purchase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="fw-semibold"><?php echo e($purchase->invoice_number); ?></td>
                        <td><?php echo e($purchase->purchase_date?->format('Y-m-d')); ?></td>
                        <td><?php echo e($purchase->payment_type === 'cash' ? 'كاش' : 'آجل'); ?></td>
                        <td><?php echo e(number_format((float) $purchase->total_amount, 2)); ?> ج.م</td>
                        <td><?php echo e(number_format((float) $purchase->paid_amount, 2)); ?> ج.م</td>
                        <td><?php echo e(number_format((float) $purchase->remaining, 2)); ?> ج.م</td>
                        <td><span class="badge <?php echo e($purchase->statusBadgeClass()); ?>"><?php echo e($purchase->statusLabel()); ?></span></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="text-center text-body-secondary py-4">لا توجد فواتير شراء مسجلة.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section aria-labelledby="payments-heading">
        <h2 id="payments-heading" class="h5 mb-2">سندات السداد</h2>
        <div class="table-responsive border rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>رقم السند</th><th>التاريخ</th><th>الفاتورة المرتبطة</th><th>المبلغ</th><th>ملاحظات</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $supplier->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr><td class="fw-semibold"><?php echo e($payment->receipt_number); ?></td><td><?php echo e($payment->payment_date?->format('Y-m-d')); ?></td><td><?php echo e($payment->purchase?->invoice_number ?: '—'); ?></td><td><?php echo e(number_format((float) $payment->amount, 2)); ?> ج.م</td><td><?php echo e($payment->notes ?: '—'); ?></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center text-body-secondary py-4">لا توجد سندات سداد مسجلة.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php if(auth()->user()->can('page.suppliers.manage')): ?>
<div class="modal fade" id="purchaseModal" tabindex="-1" aria-labelledby="purchaseModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="POST" action="<?php echo e(route('suppliers.purchases.store', $supplier)); ?>">
            <?php echo csrf_field(); ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="purchaseModalTitle">تسجيل فاتورة شراء</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label for="purchase_date" class="form-label">تاريخ الفاتورة</label><input id="purchase_date" name="purchase_date" type="date" class="form-control" value="<?php echo e(old('purchase_date', now()->toDateString())); ?>" required></div>
                <div class="mb-3"><label for="payment_type" class="form-label">طريقة الدفع</label><select id="payment_type" name="payment_type" class="form-select" required><option value="credit">آجل</option><option value="cash">كاش</option></select></div>
                <div class="mb-3"><label for="total_amount" class="form-label">إجمالي الفاتورة (ج.م)</label><input id="total_amount" name="total_amount" type="number" step="0.01" min="0.01" class="form-control" value="<?php echo e(old('total_amount')); ?>" required></div>
                <div class="mb-3"><label for="paid_amount" class="form-label">المدفوع الآن (ج.م، اختياري للآجل)</label><input id="paid_amount" name="paid_amount" type="number" step="0.01" min="0" class="form-control" value="<?php echo e(old('paid_amount', '0')); ?>"></div>
                <div><label for="purchase_notes" class="form-label">ملاحظات</label><textarea id="purchase_notes" name="notes" class="form-control" rows="2" maxlength="2000"><?php echo e(old('notes')); ?></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">حفظ الفاتورة</button></div>
        </form>
    </div></div>
</div>

<?php if((float) $supplier->balance > 0): ?>
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="POST" action="<?php echo e(route('suppliers.payments.store', $supplier)); ?>">
            <?php echo csrf_field(); ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="paymentModalTitle">سداد دفعة للمورد</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button></div>
            <div class="modal-body">
                <p class="text-body-secondary">الرصيد المستحق: <strong><?php echo e(number_format((float) $supplier->balance, 2)); ?> ج.م</strong></p>
                <div class="mb-3"><label for="payment_amount" class="form-label">مبلغ السداد (ج.م)</label><input id="payment_amount" name="amount" type="number" step="0.01" min="0.01" max="<?php echo e($supplier->balance); ?>" class="form-control" value="<?php echo e(old('amount')); ?>" required></div>
                <div class="mb-3"><label for="payment_date" class="form-label">تاريخ السداد</label><input id="payment_date" name="payment_date" type="date" class="form-control" value="<?php echo e(old('payment_date', now()->toDateString())); ?>" required></div>
                <div><label for="payment_notes" class="form-label">ملاحظات</label><textarea id="payment_notes" name="notes" class="form-control" rows="2" maxlength="2000"><?php echo e(old('notes')); ?></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-success">تأكيد السداد</button></div>
        </form>
    </div></div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if($errors->any()): ?>
    <?php $__env->startPush('scripts'); ?><script>document.addEventListener('DOMContentLoaded', () => { const target = document.querySelector('#paymentModal') || document.querySelector('#purchaseModal'); if (target) bootstrap.Modal.getOrCreateInstance(target).show(); });</script><?php $__env->stopPush(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views/suppliers/show.blade.php ENDPATH**/ ?>